<?php

namespace App\Services\Billing;

use App\Models\AuditLog;
use App\Models\Billing\Invoice;
use App\Models\Billing\Subscription;
use App\Models\Customer\CustomerService;
use App\Models\User;
use App\Enums\UserRole;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class CustomerBillingDueDateService
{
    /**
     * Get resolved due date for CustomerService.
     */
    public function getDueDate(CustomerService $customerService): ?string
    {
        // 1. Direct attribute on CustomerService
        $attrs = $customerService->attributes;
        if (is_array($attrs) && !empty($attrs['due_date'])) {
            return Carbon::parse($attrs['due_date'])->format('Y-m-d');
        }

        // 2. Next billing date from Subscription
        $subscription = Subscription::withoutGlobalScopes()
            ->where('customer_service_id', $customerService->id)
            ->first();

        if ($subscription && $subscription->next_billing_date) {
            return $subscription->next_billing_date->format('Y-m-d');
        }

        // 3. Fallback to latest unpaid invoice due_date
        $invoice = Invoice::withoutGlobalScopes()
            ->where('customer_id', $customerService->customer_id)
            ->whereIn('status', ['unpaid', 'pending'])
            ->latest('id')
            ->first();

        if ($invoice && $invoice->due_date) {
            return $invoice->due_date->format('Y-m-d');
        }

        return null;
    }

    /**
     * Check if user is authorized to manage due date for this service.
     */
    public function canManageDueDate(?User $user, CustomerService $customerService): bool
    {
        if (!$user) {
            return false;
        }

        // Administrator / SuperAdmin always authorized
        if ($user->isSuperAdmin() || $user->roles->contains('name', UserRole::Administrator->value) || $user->roles->contains('name', 'admin')) {
            return true;
        }

        // Branch Isolation: if user belongs to a specific branch, service must match
        if (!empty($user->branch_id) && !empty($customerService->branch_id)) {
            if ((int)$user->branch_id !== (int)$customerService->branch_id) {
                return false;
            }
        }

        // Reseller Isolation: reseller can only manage their own customers
        if ($user->roles->contains('name', UserRole::Reseller->value) || !empty($user->getEffectiveResellerId())) {
            $effectiveResellerId = $user->getEffectiveResellerId();
            if ((int)$customerService->reseller_id !== (int)$effectiveResellerId) {
                return false;
            }
            return true;
        }

        // Technicians or NOC staff without billing permission should never manage billing due dates
        if ($user->roles->contains('name', UserRole::Teknisi->value) || $user->roles->contains('name', 'teknisi') || $user->roles->contains('name', 'noc')) {
            return $user->hasPermission('invoice.update');
        }

        // Staff / Manager permissions check
        if ($user->hasPermission('invoice.update') || $user->hasPermission('customer.update')) {
            return true;
        }

        if ($user->roles->whereIn('name', [UserRole::Manager->value, UserRole::Kasir->value])->isNotEmpty()) {
            return true;
        }

        return false;
    }

    /**
     * Update customer service due date, corresponding subscription, and unpaid invoices.
     * Paid invoices are IMMUTABLE and never modified.
     * No duplicate invoices are generated.
     * MikroTik credentials and device configurations are untouched.
     *
     * @throws AuthorizationException
     */
    public function updateDueDate(
        CustomerService $customerService,
        string $newDueDate,
        ?string $reason = null,
        ?int $actorId = null
    ): array {
        $actorId = $actorId ?? auth()->id() ?? 1;
        $actor = User::find($actorId);

        if (!$this->canManageDueDate($actor, $customerService)) {
            throw new AuthorizationException('Anda tidak memiliki izin billing untuk mengubah tanggal jatuh tempo pelanggan ini.');
        }

        $parsedNewDueDate = Carbon::parse($newDueDate)->format('Y-m-d');
        $oldDueDate = $this->getDueDate($customerService);

        return DB::transaction(function () use ($customerService, $parsedNewDueDate, $oldDueDate, $reason, $actorId) {
            // 1. Update CustomerService attributes
            $attrs = is_array($customerService->attributes) ? $customerService->attributes : [];
            $attrs['due_date'] = $parsedNewDueDate;
            $customerService->attributes = $attrs;
            $customerService->updated_by = $actorId;
            $customerService->save();

            // 2. Update Subscription next_billing_date
            $subscription = Subscription::withoutGlobalScopes()
                ->where('customer_service_id', $customerService->id)
                ->first();

            if ($subscription) {
                $subscription->update([
                    'next_billing_date' => Carbon::parse($parsedNewDueDate),
                    'updated_by' => $actorId,
                ]);
            }

            // 3. Update UNPAID invoices (status = unpaid/pending). Paid invoices are IMMUTABLE.
            $unpaidInvoices = Invoice::withoutGlobalScopes()
                ->where('customer_id', $customerService->customer_id)
                ->whereIn('status', ['unpaid', 'pending'])
                ->get();

            $updatedInvoicesCount = 0;
            foreach ($unpaidInvoices as $invoice) {
                if ($this->invoiceMatchesCustomerService($invoice, $customerService)) {
                    $invoice->update([
                        'due_date' => Carbon::parse($parsedNewDueDate),
                        'updated_by' => $actorId,
                    ]);
                    $updatedInvoicesCount++;
                }
            }

            // 4. Record Audit Log
            AuditLog::create([
                'auditable_type' => CustomerService::class,
                'auditable_id' => $customerService->id,
                'event' => 'due_date_updated',
                'old_values' => ['due_date' => $oldDueDate],
                'new_values' => ['due_date' => $parsedNewDueDate],
                'user_id' => $actorId,
                'ip_address' => request()?->ip() ?? '127.0.0.1',
                'user_agent' => request()?->userAgent() ?? 'System',
                'notes' => $reason ?: 'Perubahan tanggal jatuh tempo layanan billing',
            ]);

            return [
                'success' => true,
                'old_due_date' => $oldDueDate,
                'new_due_date' => $parsedNewDueDate,
                'updated_invoices_count' => $updatedInvoicesCount,
                'customer_service_id' => $customerService->id,
            ];
        });
    }

    /**
     * Determine if an invoice corresponds to the given CustomerService,
     * particularly when a customer has multiple services.
     */
    protected function invoiceMatchesCustomerService(Invoice $invoice, CustomerService $customerService): bool
    {
        // If customer only has 1 service, all their invoices match
        $totalServices = CustomerService::withoutGlobalScopes()
            ->where('customer_id', $customerService->customer_id)
            ->count();

        if ($totalServices <= 1) {
            return true;
        }

        // Multiple services: match by contract_id if distinct
        if (!empty($customerService->contract_id) && (int)$invoice->contract_id === (int)$customerService->contract_id) {
            // If contract matches and not shared with other services, it's a match
            $sharedContract = CustomerService::withoutGlobalScopes()
                ->where('contract_id', $customerService->contract_id)
                ->where('id', '!=', $customerService->id)
                ->exists();

            if (!$sharedContract) {
                return true;
            }
        }

        // Check if invoice items or item_details match service profile name or username
        $serviceProfileName = $customerService->serviceProfile?->name;
        $username = $customerService->username;

        // Check relational items
        if ($invoice->relationLoaded('items') || $invoice->items()->exists()) {
            foreach ($invoice->items as $item) {
                $desc = strtolower($item->description ?? '');
                if ($serviceProfileName && str_contains($desc, strtolower($serviceProfileName))) {
                    return true;
                }
                if ($username && str_contains($desc, strtolower($username))) {
                    return true;
                }
            }
        }

        // Check item_details array
        if (is_array($invoice->item_details)) {
            foreach ($invoice->item_details as $item) {
                $desc = strtolower($item['description'] ?? '');
                if ($serviceProfileName && str_contains($desc, strtolower($serviceProfileName))) {
                    return true;
                }
                if ($username && str_contains($desc, strtolower($username))) {
                    return true;
                }
            }
        }

        // If cannot differentiate, default to matching to ensure unpaid invoice doesn't get orphaned
        return true;
    }
}
