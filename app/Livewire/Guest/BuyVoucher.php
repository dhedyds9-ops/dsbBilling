<?php

namespace App\Livewire\Guest;

use App\Models\Billing\Invoice;
use App\Models\CRM\Customer;
use App\Models\ISP\ServiceProfile;
use App\Models\VoucherOrder;
use App\Services\Adapters\Payment\PaymentOrchestrationService;
use App\Services\Billing\InvoiceService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;

class BuyVoucher extends Component
{
    public $service_profile_id;
    public $wa_number;
    public $serviceProfile;

    public function mount()
    {
        $this->service_profile_id = request()->query('paket');
        $this->wa_number = request()->query('phone');

        if ($this->service_profile_id) {
            $this->serviceProfile = ServiceProfile::where('status', 'active')
                ->where('service_type', 'voucher')
                ->find($this->service_profile_id);
        }

        if (!$this->serviceProfile) {
            // Redirect if not found
            return redirect('/#packages');
        }
    }

    protected $rules = [
        'wa_number' => 'required|string|min:9|max:15',
    ];

    public function checkout(PaymentOrchestrationService $orchestration, InvoiceService $invoiceService)
    {
        $this->validate();

        $waNumber = preg_replace('/[^0-9]/', '', $this->wa_number);
        if (str_starts_with($waNumber, '0')) {
            $waNumber = '62' . substr($waNumber, 1);
        }

        try {
            DB::beginTransaction();

            // 1. Cari atau Buat Customer Guest
            $customer = Customer::firstOrCreate(
                ['phone' => $waNumber],
                [
                    'code' => 'GST-' . strtoupper(Str::random(6)),
                    'name' => 'Guest ' . $waNumber,
                    'email' => $waNumber . '@guest.local', // Dummy email required by system
                ]
            );

            // 2. Buat Invoice
            $invoice = $invoiceService->createInvoice(
                customerId: $customer->id,
                userId: 1, // System
                items: [
                    [
                        'description' => 'Voucher: ' . $this->serviceProfile->name,
                        'unit_price' => (float)$this->serviceProfile->base_price,
                        'quantity' => 1,
                    ]
                ],
                issueDate: now(),
                dueDate: now()->addHours(24),
                currency: 'IDR'
            );

            // 3. Buat Voucher Order
            $voucherOrder = VoucherOrder::create([
                'invoice_id' => $invoice->id,
                'service_profile_id' => $this->serviceProfile->id,
                'wa_number' => $waNumber,
                'service_profile_name' => $this->serviceProfile->name,
                'unit_price' => $this->serviceProfile->base_price,
                'quantity' => 1,
                'total_amount' => $this->serviceProfile->base_price,
                'status' => VoucherOrder::STATUS_PENDING,
            ]);

            DB::commit();

            // 4. Initiate Payment
            $defaultGateway = \App\Models\Setting::getValue('payment.default_gateway', 'midtrans');
            
            $result = $orchestration->initiatePayment(
                gatewayKey: $defaultGateway,
                customerId: $customer->id,
                userId: 1, // System
                amountIdr: (int) $this->serviceProfile->base_price,
                invoiceIds: [$invoice->id],
                paymentMethodCode: null,
                customerName: $customer->name,
                customerEmail: $customer->email,
                customerPhone: $customer->phone,
                successRedirectUrl: route('guest.buy-voucher.success', ['uuid' => $voucherOrder->uuid]),
            );

            if ($result['gateway_response']->success) {
                $voucherOrder->update([
                    'status' => VoucherOrder::STATUS_PAYMENT_PROCESSING,
                    'payment_reference' => $result['payment']->reference_number ?? null,
                ]);
                return redirect()->away($result['gateway_response']->redirectUrl);
            } else {
                session()->flash('error', 'Gagal memproses pembayaran: ' . $result['gateway_response']->errorMessage);
            }

        } catch (\Throwable $e) {
            DB::rollBack();
            session()->flash('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.guest.buy-voucher')->layout('layouts.guest');
    }
}
