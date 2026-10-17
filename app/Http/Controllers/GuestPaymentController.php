<?php

namespace App\Http\Controllers;

use App\Models\User;
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
        $user = null;
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

            // 1. Cek User langsung (customer_code, username, pppoe_username, email)
            $user = User::withoutGlobalScopes()
                ->where(function ($q) use ($queryLower) {
                    $q->whereRaw('LOWER(customer_code) = ?', [$queryLower])
                      ->orWhereRaw('LOWER(username) = ?', [$queryLower])
                      ->orWhereRaw('LOWER(pppoe_username) = ?', [$queryLower])
                      ->orWhereRaw('LOWER(email) = ?', [$queryLower]);
                })
                ->first();

            if ($user) {
                $customer = Customer::withoutGlobalScopes()->where('user_id', $user->id)->first();
            }

            // 2. Cek Voucher
            $voucher = Voucher::withoutGlobalScopes()
                ->with(['serviceProfile', 'hotspotUser.customerService.customer'])
                ->whereRaw('LOWER(code) = ?', [$queryLower])
                ->first();

            // 3. Cek Hotspot
            if (!$voucher) {
                $hotspot = HotspotUser::withoutGlobalScopes()
                    ->with(['serviceProfile', 'customerService.customer'])
                    ->whereRaw('LOWER(username) = ?', [$queryLower])
                    ->first();
            } else {
                $hotspot = $voucher->hotspotUser;
            }

            // 4. Cek PPPoE User
            $pppoe = PPPoEUser::withoutGlobalScopes()
                ->with(['serviceProfile', 'customerService.customer'])
                ->whereRaw('LOWER(username) = ?', [$queryLower])
                ->first();

            // 5. Cek Customer Service (Layanan Internet / PPPoE / Static)
            if (!$pppoe && !$hotspot) {
                $customerService = CustomerService::withoutGlobalScopes()
                    ->with(['serviceProfile', 'customer'])
                    ->whereRaw('LOWER(username) = ?', [$queryLower])
                    ->first();
            }

            // 6. Cek Customer langsung (Code, Email, Phone, Name)
            if (!$customer) {
                $customer = Customer::withoutGlobalScopes()
                    ->where(function ($q) use ($queryLower) {
                        $q->whereRaw('LOWER(code) = ?', [$queryLower])
                          ->orWhereRaw('LOWER(email) = ?', [$queryLower])
                          ->orWhereRaw('LOWER(phone) = ?', [$queryLower])
                          ->orWhereRaw('LOWER(name) LIKE ?', ['%' . $queryLower . '%']);
                    })
                    ->first();
            }

            // 7. Jika input berupa angka (misal format tanggal+urutan '2026101607' atau ID '7')
            if (is_numeric($query)) {
                $num = (int)$query;

                // 7a. Coba jika ID langsung
                if (!$customer) {
                    $customer = Customer::withoutGlobalScopes()->find($num);
                }
                if (!$user && !$customer) {
                    $user = User::withoutGlobalScopes()->find($num);
                    if ($user) {
                        $customer = Customer::withoutGlobalScopes()->where('user_id', $user->id)->first();
                    }
                }

                // 7b. Format YmdXX (panjang >= 9 digit, misal 2026101607 -> suffix 07 = 7)
                if (strlen($query) >= 9) {
                    $idSuffix = (int) substr($query, 8);
                    if ($idSuffix > 0) {
                        if (!$customer) {
                            $customer = Customer::withoutGlobalScopes()->find($idSuffix);
                        }
                        if (!$customer) {
                            $customer = Customer::withoutGlobalScopes()
                                ->where('code', (string)$idSuffix)
                                ->orWhere('code', str_pad((string)$idSuffix, 2, '0', STR_PAD_LEFT))
                                ->orWhere('code', str_pad((string)$idSuffix, 3, '0', STR_PAD_LEFT))
                                ->orWhere('code', str_pad((string)$idSuffix, 4, '0', STR_PAD_LEFT))
                                ->orWhere('code', 'like', '%' . $idSuffix)
                                ->first();
                        }
                        if (!$user && !$customer) {
                            $user = User::withoutGlobalScopes()->find($idSuffix);
                            if ($user) {
                                $customer = Customer::withoutGlobalScopes()->where('user_id', $user->id)->first();
                            }
                        }
                    }
                }
            }

            // 8. Relasi fallback dari pppoe / customerService / hotspot / voucher
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

            if ($customer && !$user && $customer->user_id) {
                $user = User::withoutGlobalScopes()->find($customer->user_id);
            }

            // 9. Cek Invoice langsung jika query adalah Nomor Invoice
            $directInvoice = Invoice::withoutGlobalScopes()
                ->whereRaw('LOWER(invoice_number) = ?', [$queryLower])
                ->with(['customer', 'items'])
                ->first();

            // 10. Ambil Tagihan
            if ($directInvoice) {
                $invoices = collect([$directInvoice]);
                if (!$customer) {
                    $customer = $directInvoice->customer;
                }
            } elseif ($customer || $user) {
                $targetIds = array_values(array_unique(array_filter([$customer?->id, $user?->id])));
                $invoices = Invoice::withoutGlobalScopes()
                    ->where(function ($q) use ($targetIds, $customer, $user) {
                        if (!empty($targetIds)) {
                            $q->whereIn('customer_id', $targetIds);
                        }
                        if ($customer?->code) {
                            $q->orWhere('customer_id', $customer->code);
                        }
                        if ($user?->customer_code) {
                            $q->orWhere('customer_id', $user->customer_code);
                        }
                    })
                    ->whereIn('status', ['unpaid', 'partial', 'pending', 'overdue'])
                    ->orderBy('due_date', 'asc')
                    ->with('items')
                    ->get();
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

            if ($invoices->isNotEmpty()) {
                $summary = [
                    'count' => $invoices->count(),
                    'total' => $invoices->sum(fn($i) => $i->total_amount - ($i->paid_amount ?? 0)),
                ];
            } else {
                if ($customer || $user) {
                    $custName = $customer?->name ?? $user?->name ?? 'Pelanggan';
                    $custCode = $customer?->code ?? $user?->customer_code ?? $query;
                    $error = 'Pelanggan "' . e($custName) . '" (' . e($custCode) . ') ditemukan, namun tidak memiliki tagihan aktif yang belum lunas (semua tagihan lunas).';
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
