<?php

namespace App\Http\Controllers;

use App\Models\CRM\Customer;
use App\Models\Billing\Invoice;
use App\Models\AAA\Voucher;
use App\Models\AAA\HotspotUser;
use App\Models\AAA\RadiusAccounting;
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
        $activeSessions = 0;

        if ($query !== '') {
            $queryLower = strtolower($query);

            // 1. Cek Voucher
            $voucher = Voucher::with(['serviceProfile', 'hotspotUser'])
                ->whereRaw('LOWER(code) = ?', [$queryLower])
                ->first();

            // 2. Cek Hotspot
            if (!$voucher) {
                $hotspot = HotspotUser::with('serviceProfile')
                    ->whereRaw('LOWER(username) = ?', [$queryLower])
                    ->first();
            } else {
                $hotspot = $voucher->hotspotUser;
            }

            // Cek session aktif
            if ($hotspot) {
                $activeSessions = RadiusAccounting::where('hotspot_user_id', $hotspot->id)
                    ->whereNull('acct_stop_time')
                    ->count();
            }

            // 3. Cek Tagihan jika bukan voucher/hotspot
            if (!$voucher && !$hotspot) {
                $customer = Customer::query()
                    ->whereRaw('LOWER(code) = ?', [$queryLower])
                    ->orWhereRaw('LOWER(email) = ?', [$queryLower])
                    ->orWhereRaw('LOWER(phone) = ?', [$queryLower])
                    ->orWhereRaw('LOWER(name) LIKE ?', ['%' . $queryLower . '%'])
                    ->first();

                $directInvoice = Invoice::query()
                    ->whereRaw('LOWER(invoice_number) = ?', [$queryLower])
                    ->with('customer')
                    ->first();

                if ($customer) {
                    $invoices = Invoice::query()
                        ->where('customer_id', $customer->id)
                        ->whereIn('status', ['unpaid', 'partial', 'overdue'])
                        ->orderBy('due_date', 'asc')
                        ->with('items')
                        ->get();

                    if ($invoices->isEmpty()) {
                        $error = 'Tidak ditemukan tagihan belum bayar untuk pelanggan ini.';
                    }
                } elseif ($directInvoice) {
                    $invoices = collect([$directInvoice]);
                    $customer = $directInvoice->customer;
                } else {
                    $error = 'Tidak ditemukan data Tagihan, Voucher, atau Hotspot dengan pencarian "' . e($query) . '".';
                }

                if ($invoices->isNotEmpty()) {
                    $summary = [
                        'count' => $invoices->count(),
                        'total' => $invoices->sum(fn($i) => $i->total_amount - ($i->paid_amount ?? 0)),
                    ];
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
        } catch (\Throwable $e) {}

        return view('guest.payment', compact(
            'query',
            'customer',
            'invoices',
            'summary',
            'error',
            'voucher',
            'hotspot',
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

        $invoice = Invoice::with('customer')->where('invoice_number', $validated['invoice_number'])->firstOrFail();
        
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
            'hotspot_user_id' => 'required|exists:hotspot_users,id',
            'q' => 'required|string'
        ]);

        $hotspot = HotspotUser::with('serviceProfile')->find($validated['hotspot_user_id']);
        
        if ($hotspot && $hotspot->serviceProfile && $hotspot->serviceProfile->router_id) {
            try {
                $driver = \App\Integration\MikroTik\MikroTikServiceFactory::makeRouterOSDriverByRouterId($hotspot->serviceProfile->router_id);
                if ($driver && $driver->connect()) {
                    $driver->disconnectHotspotUser($hotspot->username);
                    $driver->disconnect();
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Guest reset session failed', ['err' => $e->getMessage()]);
            }

            RadiusAccounting::where('hotspot_user_id', $hotspot->id)
                ->whereNull('acct_stop_time')
                ->update([
                    'acct_stop_time' => now(),
                    'acct_terminate_cause' => 'Admin-Reset'
                ]);
        }

        return redirect()->route('guest.payment', ['q' => $validated['q']])->with('success', 'Sesi perangkat berhasil direset. Silakan login kembali.');
    }
}
