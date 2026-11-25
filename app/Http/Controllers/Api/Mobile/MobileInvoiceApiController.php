<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Billing\Invoice;
use App\Models\CRM\Customer;
use App\Models\Payment\Payment;
use App\Services\Billing\PaymentService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MobileInvoiceApiController extends Controller
{
    /**
     * List subscription invoices (scoped by reseller/branch).
     * GET /api/mobile/invoices
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // RBAC: Technicians are not allowed to view customer billing invoices.
        if ($user && method_exists($user, 'isTechnician') && $user->isTechnician() && !$user->isSuperAdmin() && !$user->hasAnyRole(['administrator', 'manager', 'kasir', 'cashier', 'reseller'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Teknisi tidak memiliki akses data tagihan.',
                'data' => [],
            ], 403);
        }

        $resellerId = $user->getEffectiveResellerId() ?? (
            ($user->hasRole('reseller') || $user->role === 'reseller' || !empty($user->reseller_id))
                ? ($user->reseller_id ?: $user->id)
                : null
        );

        $query = Invoice::withoutGlobalScopes(['branch_isolation', 'reseller_isolation'])
            ->whereNull('invoices.deleted_at')
            ->with([
                'customer' => function ($q) {
                    $q->withoutGlobalScopes(['branch_isolation', 'reseller_isolation'])
                      ->with([
                          'customerServices' => function ($sq) {
                              $sq->withoutGlobalScopes(['branch_isolation', 'reseller_isolation'])
                                 ->with(['serviceProfile', 'pppoeUser', 'hotspotUser']);
                          }
                      ]);
                },
                'items',
                'payments' => function ($q) {
                    $q->withoutGlobalScopes(['branch_isolation', 'reseller_isolation'])
                      ->whereIn('status', ['paid', 'success', 'approved']);
                }
            ]);

        // Reseller or Branch scoping
        if ($resellerId) {
            $query->where(function ($q) use ($resellerId, $user) {
                $q->where('invoices.reseller_id', $resellerId)
                  ->orWhere('invoices.created_by', $resellerId)
                  ->orWhereHas('customer', function ($cq) use ($resellerId, $user) {
                      $cq->withoutGlobalScopes()->where(function ($sq) use ($resellerId, $user) {
                          $sq->where('customers.reseller_id', $resellerId)
                             ->orWhere('customers.created_by', $resellerId)
                             ->orWhereHas('customerServices', function ($csq) use ($resellerId) {
                                 $csq->withoutGlobalScopes()->where('customer_services.reseller_id', $resellerId);
                             });
                          if (!empty($user->branch_id)) {
                              $sq->orWhere('customers.branch_id', $user->branch_id);
                          }
                      });
                  });
                if (!empty($user->branch_id)) {
                    $q->orWhere('invoices.branch_id', $user->branch_id);
                }
            });
        } elseif (!empty($user->branch_id)) {
            $query->where(function ($q) use ($user) {
                $q->where('invoices.branch_id', $user->branch_id)
                  ->orWhereHas('customer', function ($cq) use ($user) {
                      $cq->withoutGlobalScopes()->where('customers.branch_id', $user->branch_id);
                  });
            });
        }

        // Filter by specific customer ID
        if ($customerId = $request->input('customer_id')) {
            $query->where('invoices.customer_id', $customerId);
        }

        // Search by invoice number, customer name, code, phone
        $search = trim((string) ($request->input('q') ?? $request->input('search') ?? ''));
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('invoices.invoice_number', 'LIKE', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->withoutGlobalScopes()->where(function ($sq) use ($search) {
                          $sq->where('name', 'LIKE', "%{$search}%")
                             ->orWhere('code', 'LIKE', "%{$search}%")
                             ->orWhere('phone', 'LIKE', "%{$search}%");
                      });
                  });
            });
        }

        // Filter by Status
        $status = strtolower(trim((string) $request->input('status', '')));
        $today = Carbon::today()->toDateString();
        if ($status === 'unpaid' || $status === 'belum_lunas') {
            $query->whereIn('invoices.status', ['pending', 'unpaid', 'partial', 'draft', 'overdue']);
        } elseif ($status === 'paid' || $status === 'lunas') {
            $query->whereIn('invoices.status', ['paid', 'success', 'approved']);
        } elseif ($status === 'overdue' || $status === 'jatuh_tempo') {
            $query->where(function ($q) use ($today) {
                $q->whereDate('invoices.due_date', '<', $today)
                  ->orWhere('invoices.status', 'overdue');
            })->whereIn('invoices.status', ['pending', 'unpaid', 'partial', 'draft', 'overdue']);
        } elseif (!empty($status) && $status !== 'all' && $status !== 'semua') {
            $query->where('invoices.status', $status);
        }

        $perPage = min(100, max(1, (int) $request->input('per_page', 50)));
        $paginator = $query->orderBy('invoices.created_at', 'desc')->paginate($perPage);

        $items = collect($paginator->items())->map(function (Invoice $inv) {
            $cust = $inv->customer;
            $primaryService = $cust?->customerServices?->first();
            $username = $primaryService?->username
                ?? $primaryService?->pppoeUser?->username
                ?? $primaryService?->hotspotUser?->username;

            $packageName = $primaryService?->serviceProfile?->name
                ?? ($inv->items->first()?->description ?: 'Langganan Internet');

            $latestPayment = $inv->payments->sortByDesc('paid_at')->first();
            $paidAtStr = $latestPayment?->paid_at
                ? Carbon::parse($latestPayment->paid_at)->format('d/m/Y H:i')
                : ($latestPayment?->created_at ? Carbon::parse($latestPayment->created_at)->format('d/m/Y H:i') : null);

            $periodStr = $inv->issue_date
                ? $inv->issue_date->translatedFormat('F Y')
                : Carbon::now()->translatedFormat('F Y');

            $isPaid = in_array(strtolower((string) $inv->status), ['paid', 'success', 'approved', 'lunas']);
            $isOverdue = !$isPaid && $inv->due_date && $inv->due_date->isPast();

            $statusLabel = $isPaid ? 'paid' : ($isOverdue ? 'overdue' : 'unpaid');

            return [
                'id' => $inv->id,
                'invoice_number' => $inv->invoice_number,
                'customer_id' => $inv->customer_id,
                'customer_name' => $cust?->name ?: 'Pelanggan',
                'customer_code' => $cust?->code ?: '-',
                'pppoe_username' => $username,
                'service_profile_name' => $packageName,
                'period' => $periodStr,
                'amount' => (float) $inv->total_amount,
                'total_amount' => (float) $inv->total_amount,
                'paid_amount' => (float) $inv->paid_amount,
                'status' => $statusLabel,
                'due_date' => $inv->due_date ? $inv->due_date->translatedFormat('d F Y') : '-',
                'paid_at' => $paidAtStr,
                'description' => "Tagihan {$packageName} - {$cust?->name}",
                'notes' => $inv->notes ?? null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $items,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    /**
     * List invoices for a specific customer.
     * GET /api/mobile/customers/{id}/invoices
     */
    public function customerInvoices(Request $request, int $id): JsonResponse
    {
        $request->merge(['customer_id' => $id]);
        return $this->index($request);
    }

    /**
     * Cash payment execution by cashier / reseller.
     * POST /api/mobile/invoices/{id}/pay-cash
     */
    public function payCash(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        // RBAC: Technicians are not allowed to collect or execute cash billing payments.
        if ($user && method_exists($user, 'isTechnician') && $user->isTechnician() && !$user->isSuperAdmin() && !$user->hasAnyRole(['administrator', 'manager', 'kasir', 'cashier', 'reseller'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Teknisi tidak memiliki wewenang memproses pembayaran tagihan.',
            ], 403);
        }

        $resellerId = $user->getEffectiveResellerId() ?? (
            ($user->hasRole('reseller') || $user->role === 'reseller' || !empty($user->reseller_id))
                ? ($user->reseller_id ?: $user->id)
                : null
        );

        $query = Invoice::withoutGlobalScopes(['branch_isolation', 'reseller_isolation'])
            ->whereNull('invoices.deleted_at')
            ->with(['customer' => fn($q) => $q->withoutGlobalScopes()]);

        if ($resellerId) {
            $query->where(function ($q) use ($resellerId) {
                $q->where('invoices.reseller_id', $resellerId)
                  ->orWhereHas('customer', function ($cq) use ($resellerId) {
                      $cq->withoutGlobalScopes()->where(function ($sq) use ($resellerId) {
                          $sq->where('customers.reseller_id', $resellerId)
                             ->orWhere('customers.created_by', $resellerId);
                      });
                  });
            });
        }

        $idempotencyKey = $request->header('X-Idempotency-Key') ?: $request->input('idempotency_key');
        $idempotencyKey = $idempotencyKey ? trim((string) $idempotencyKey) : null;

        // Idempotency fast check jika request diulang (misal timeout atau retry mobile)
        if ($idempotencyKey) {
            $existingPayment = Payment::where('gateway_transaction_id', $idempotencyKey)
                ->orWhere('reference_number', $idempotencyKey)
                ->first();
            if ($existingPayment) {
                $inv = $existingPayment->invoices()->first() ?? Invoice::withoutGlobalScopes()->find($id);
                $reqAmount = $request->input('amount') !== null ? (float) $request->input('amount') : null;
                if (($inv && (int) $inv->id !== (int) $id) ||
                    ($reqAmount !== null && abs((float) $existingPayment->amount - $reqAmount) > 0.01)) {
                    return response()->json([
                        'success' => false,
                        'message' => "Konflik Idempotensi: Idempotency Key '{$idempotencyKey}' sudah digunakan untuk transaksi pembayaran berbeda (nominal atau invoice berbeda).",
                    ], 422);
                }

                return response()->json([
                    'success' => true,
                    'idempotent_replay' => true,
                    'message' => "Pembayaran tunai sudah tercatat sebelumnya (Idempotent Replay).",
                    'data' => [
                        'invoice_id' => $inv?->id ?? $id,
                        'invoice_number' => $inv?->invoice_number,
                        'status' => $inv?->status ?? 'paid',
                        'paid_amount' => (float) ($inv?->paid_amount ?? $existingPayment->amount),
                        'payment_id' => $existingPayment->id,
                        'paid_at' => ($existingPayment->paid_at ?? now())->format('d/m/Y H:i'),
                    ],
                ]);
            }
        }

        try {
            return DB::transaction(function () use ($query, $id, $request, $user, $idempotencyKey) {
                // Kunci baris invoice secara atomik untuk mencegah race condition / double payment
                $invoice = $query->lockForUpdate()->findOrFail($id);

                if (in_array(strtolower((string) $invoice->status), ['paid', 'success', 'lunas'])) {
                    return response()->json([
                        'success' => false,
                        'message' => "Invoice {$invoice->invoice_number} sudah lunas sebelumnya.",
                    ], 422);
                }

                $amountToPay = (float) ($request->input('amount') ?? ($invoice->total_amount - ($invoice->paid_amount ?? 0)));
                if ($amountToPay <= 0) {
                    $amountToPay = (float) $invoice->total_amount;
                }

                $refNumber = $idempotencyKey ?: ('PAY-CASH-' . $invoice->id . '-' . date('YmdHis'));

                // Double check idempotency dalam lock
                if ($idempotencyKey) {
                    $existingPayment = Payment::where('gateway_transaction_id', $idempotencyKey)
                        ->orWhere('reference_number', $idempotencyKey)
                        ->lockForUpdate()
                        ->first();
                    if ($existingPayment) {
                        $reqAmount = $request->input('amount') !== null ? (float) $request->input('amount') : null;
                        if ($reqAmount !== null && abs((float) $existingPayment->amount - $reqAmount) > 0.01) {
                            return response()->json([
                                'success' => false,
                                'message' => "Konflik Idempotensi: Idempotency Key '{$idempotencyKey}' sudah digunakan untuk transaksi pembayaran berbeda (nominal tidak cocok).",
                            ], 422);
                        }

                        return response()->json([
                            'success' => true,
                            'idempotent_replay' => true,
                            'message' => "Pembayaran tunai sudah tercatat sebelumnya (Idempotent Replay).",
                            'data' => [
                                'invoice_id' => $invoice->id,
                                'invoice_number' => $invoice->invoice_number,
                                'status' => $invoice->status,
                                'paid_amount' => (float) $invoice->paid_amount,
                                'payment_id' => $existingPayment->id,
                                'paid_at' => ($existingPayment->paid_at ?? now())->format('d/m/Y H:i'),
                            ],
                        ]);
                    }
                }

                $paymentService = app(PaymentService::class);
                $payment = $paymentService->createPayment(
                    customerId: $invoice->customer_id,
                    amount: $amountToPay,
                    userId: $user->id,
                    invoiceIds: [$invoice->id],
                    currency: 'IDR',
                    method: 'cash',
                    status: 'success',
                    referenceNumber: $refNumber,
                    gatewayTransactionId: $idempotencyKey,
                    gateway: 'cash'
                );

                $invoice->refresh();

                return response()->json([
                    'success' => true,
                    'message' => "Pembayaran tunai sebesar Rp " . number_format($amountToPay, 0, ',', '.') . " berhasil dicatat.",
                    'data' => [
                        'invoice_id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                        'status' => $invoice->status,
                        'paid_amount' => (float) $invoice->paid_amount,
                        'payment_id' => $payment->id ?? null,
                        'paid_at' => Carbon::now()->format('d/m/Y H:i'),
                    ],
                ]);
            });
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses pembayaran tunai: ' . $e->getMessage(),
            ], 500);
        }
    }
}
