<?php

namespace App\Http\Controllers;

use App\Models\CRM\Customer;
use App\Models\Billing\Invoice;
use App\Models\ISP\Voucher;
use App\Models\ISP\HotspotUser;
use App\Models\ISP\PPPoEUser;
use App\Models\Customer\CustomerService;
use App\Models\ISP\RadiusAccounting;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GuestPaymentController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
        ]);

        $query = trim($validated['q'] ?? '');
        $customer = null;
        $invoices = collect();
        $summary = null;
        $error = null;
        $voucher = null;
        $hotspot = null;
        $pppoe = null;
        $customerService = null;
        $activeSessions = 0;

        if ($query !== '') {
            $queryLower = strtolower($query);

            // 1. Cek Voucher
            $voucher = Voucher::withoutGlobalScopes()
                ->with(['serviceProfile', 'hotspotUser.customerService.customer'])
                ->whereRaw('LOWER(code) = ?', [$queryLower])
                ->first();

            // 2. Cek Hotspot
            if (!$voucher) {
                $hotspot = HotspotUser::withoutGlobalScopes()
                    ->with(['serviceProfile', 'customerService.customer'])
                    ->whereRaw('LOWER(username) = ?', [$queryLower])
                    ->first();
            } else {
                $hotspot = $voucher->hotspotUser;
            }

            // 3. Cek PPPoE User
            $pppoe = PPPoEUser::withoutGlobalScopes()
                ->with(['serviceProfile', 'customerService.customer'])
                ->whereRaw('LOWER(username) = ?', [$queryLower])
                ->first();

            // 4. Cek Customer Service (Layanan Internet / PPPoE / Static)
            if (!$pppoe && !$hotspot) {
                $customerService = CustomerService::withoutGlobalScopes()
                    ->with(['serviceProfile', 'customer'])
                    ->whereRaw('LOWER(username) = ?', [$queryLower])
                    ->first();
            }

            // Cek session aktif
            if ($hotspot) {
                $activeSessions = RadiusAccounting::where(function ($q) use ($hotspot) {
                    $q->where('hotspot_user_id', $hotspot->id)
                      ->orWhere('username', $hotspot->username);
                })->whereNull('acct_stop_time')->count();
            } elseif ($pppoe) {
                $activeSessions = RadiusAccounting::where(function ($q) use ($pppoe) {
                    $q->where('pppoe_user_id', $pppoe->id)
                      ->orWhere('username', $pppoe->username);
                })->whereNull('acct_stop_time')->count();
            }

            // 5. Cek Customer langsung (berdasarkan Code, ID, Email, No HP, atau Nama)
            $customerQuery = Customer::withoutGlobalScopes();
            if (is_numeric($query)) {
                $customerQuery->where('id', (int)$query)
                    ->orWhereRaw('LOWER(code) = ?', [$queryLower])
                    ->orWhereRaw('LOWER(phone) = ?', [$queryLower]);
            } else {
                $customerQuery->whereRaw('LOWER(code) = ?', [$queryLower])
                    ->orWhereRaw('LOWER(email) = ?', [$queryLower])
                    ->orWhereRaw('LOWER(phone) = ?', [$queryLower])
                    ->orWhereRaw('LOWER(name) LIKE ?', ['%' . $queryLower . '%']);
            }
            $customer = $customerQuery->first();

            // Jika belum ketemu dari input langsung, cari customer dari relasi akun internetnya
            if (!$customer) {
                if ($pppoe) {
                    $customer = $pppoe->customerService?->customer ?? $pppoe->customer;
                } elseif ($customerService) {
                    $customer = $customerService->customer;
                } elseif ($hotspot) {
                    $customer = $hotspot->customerService?->customer ?? $hotspot->customer;
                } elseif ($voucher && $voucher->hotspotUser) {
                    $customer = $voucher->hotspotUser->customerService?->customer ?? $voucher->hotspotUser->customer;
                }
            }

            // 6. Cek Invoice langsung jika query adalah Nomor Invoice
            $directInvoice = Invoice::withoutGlobalScopes()
                ->whereRaw('LOWER(invoice_number) = ?', [$queryLower])
                ->with(['customer', 'items'])
                ->first();

            if ($directInvoice) {
                $invoices = collect([$directInvoice]);
                if (!$customer) {
                    $customer = $directInvoice->customer;
                }
            } elseif ($customer) {
                $invoices = Invoice::withoutGlobalScopes()
                    ->where('customer_id', $customer->id)
                    ->whereIn('status', ['unpaid', 'partial', 'overdue'])
                    ->orderBy('due_date', 'asc')
                    ->with('items')
                    ->get();
            }

            if ($invoices->isNotEmpty()) {
                $summary = [
                    'count' => $invoices->count(),
                    'total' => $invoices->sum(fn($i) => $i->total_amount - ($i->paid_amount ?? 0)),
                ];
            } else {
                // Tentukan pesan jika tagihan tidak ada
                if ($customer) {
                    if (!$voucher && !$hotspot && !$pppoe && !$customerService) {
                        $error = 'Pelanggan "' . e($customer->name) . '" (' . e($customer->code) . ') tidak memiliki tagihan aktif yang belum lunas.';
                    }
                } elseif (!$voucher && !$hotspot && !$pppoe && !$customerService) {
                    $error = 'Tidak ditemukan data Tagihan, Pelanggan, PPPoE, Voucher, atau Hotspot dengan pencarian "' . e($query) . '".';
                }
            }
        }

        $activeGateways = [];
        try {
            $gateways = app(\App\Services\Pengaturan\PaymentGatewaySettingsService::class)->getAll();
            foreach ($gateways as $k => $g) {
                if (($g['enabled'] ?? false) && !in_array($k, ['manual_transfer', 'bca_va', 'ewallet'])) {
                    $activeGateways[$k] = $g;
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Swallowed exception caught: " . $e->getMessage());
        }

        return view('guest.payment', compact(
            'query',
            'customer',
            'invoices',
            'summary',
            'error',
            'voucher',
            'hotspot',
            'pppoe',
            'customerService',
            'activeSessions',
            'activeGateways'
        ));
    }

    public function checkout(Request $request, \App\Services\Adapters\Payment\PaymentOrchestrationService $orchestrator)
    {
        $validated = $request->validate([
            'invoice_number' => 'required|string',
            'gateway' => 'required|string'
        ]);

        $invoice = Invoice::withoutGlobalScopes()->with('customer')->where('invoice_number', $validated['invoice_number'])->firstOrFail();
        
        $amountIdr = (int) max(0, $invoice->total_amount - $invoice->paid_amount);
        if ($amountIdr <= 0) {
            return back()->with('error', 'Tagihan sudah lunas.');
        }

        try {
            $result = $orchestrator->initiatePayment(
                gatewayKey: $validated['gateway'],
                customerId: $invoice->customer_id,
                userId: $invoice->customer->user_id ?? 1,
                amountIdr: $amountIdr,
                invoiceIds: [$invoice->id],
                paymentMethodCode: null,
                customerName: $invoice->customer->name ?? 'Guest',
                customerEmail: $invoice->customer->email ?? 'guest@example.com',
                customerPhone: $invoice->customer->phone ?? '00000',
            );

            if ($result['gateway_response']->success) {
                return redirect()->away($result['gateway_response']->redirectUrl);
            } else {
                return back()->with('error', 'Gagal memproses pembayaran: ' . $result['gateway_response']->errorMessage);
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Error sistem: ' . $e->getMessage());
        }
    }

    public function resetSession(Request $request)
    {
        $validated = $request->validate([
            'hotspot_user_id' => 'nullable|exists:hotspot_users,id',
            'pppoe_user_id' => 'nullable|exists:pppoe_users,id',
            'q' => 'required|string'
        ]);

        $hotspot = !empty($validated['hotspot_user_id']) ? HotspotUser::withoutGlobalScopes()->with('serviceProfile')->find($validated['hotspot_user_id']) : null;
        $pppoe = !empty($validated['pppoe_user_id']) ? PPPoEUser::withoutGlobalScopes()->with('serviceProfile')->find($validated['pppoe_user_id']) : null;
        
        $routerId = $hotspot?->serviceProfile?->router_id ?? $pppoe?->serviceProfile?->router_id ?? null;
        $username = $hotspot?->username ?? $pppoe?->username ?? null;

        if ($routerId && $username) {
            try {
                $driver = \App\Integration\MikroTik\MikroTikServiceFactory::makeRouterOSDriverByRouterId($routerId);
                if ($driver && $driver->connect()) {
                    if ($hotspot) {
                        $driver->disconnectHotspotUser($username);
                    } else {
                        $driver->disconnectPppoeUser($username);
                    }
                    $driver->disconnect();
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Guest reset session failed', ['err' => $e->getMessage()]);
            }
        }

        if ($hotspot) {
            RadiusAccounting::where(fn($q) => $q->where('hotspot_user_id', $hotspot->id)->orWhere('username', $hotspot->username))
                ->whereNull('acct_stop_time')
                ->update([
                    'acct_stop_time' => now(),
                    'acct_terminate_cause' => 'Admin-Reset'
                ]);
        } elseif ($pppoe) {
            RadiusAccounting::where(fn($q) => $q->where('pppoe_user_id', $pppoe->id)->orWhere('username', $pppoe->username))
                ->whereNull('acct_stop_time')
                ->update([
                    'acct_stop_time' => now(),
                    'acct_terminate_cause' => 'Admin-Reset'
                ]);
        }

        return redirect()->route('guest.payment', ['q' => $validated['q']])->with('success', 'Sesi perangkat berhasil direset. Silakan koneksi ulang.');
    }
}
