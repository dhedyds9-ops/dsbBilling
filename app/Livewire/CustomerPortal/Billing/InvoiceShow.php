<?php

namespace App\Livewire\CustomerPortal\Billing;

use App\Models\Billing\Invoice;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.customer-app')]
class InvoiceShow extends Component
{
    public $invoiceId;
    public $invoice;
    public array $company = [];
    public array $activeGateways = [];
    public $showPaymentModal = false;

    public string $selectedGateway = '';
    public string $selectedPaymentMethod = '';
    public ?string $paymentErrorMessage = null;

    public const GATEWAY_METHOD_OPTIONS = [
        'midtrans' => [
            ['code' => '',          'label' => 'Semua Metode (Snap)', 'category' => 'Umum'],
            ['code' => 'gopay',     'label' => 'GoPay',                'category' => 'E-Wallet'],
            ['code' => 'qris',      'label' => 'QRIS',                 'category' => 'QRIS'],
            ['code' => 'cc',        'label' => 'Kartu Kredit / Debit', 'category' => 'Kartu'],
            ['code' => 'bca_va',    'label' => 'BCA Virtual Account',  'category' => 'VA Bank'],
            ['code' => 'mandiri_va','label' => 'Mandiri Virtual Account','category' => 'VA Bank'],
            ['code' => 'bni_va',    'label' => 'BNI Virtual Account',  'category' => 'VA Bank'],
            ['code' => 'bri_va',    'label' => 'BRI Virtual Account',  'category' => 'VA Bank'],
        ],
        'xendit' => [
            ['code' => '',          'label' => 'Semua Metode',      'category' => 'Umum'],
            ['code' => 'qris',      'label' => 'QRIS',              'category' => 'QRIS'],
            ['code' => 'gopay',     'label' => 'GoPay',             'category' => 'E-Wallet'],
            ['code' => 'ovo',       'label' => 'OVO',               'category' => 'E-Wallet'],
            ['code' => 'dana',      'label' => 'DANA',              'category' => 'E-Wallet'],
            ['code' => 'shopeepay', 'label' => 'ShopeePay',         'category' => 'E-Wallet'],
            ['code' => 'bca_va',    'label' => 'BCA VA',            'category' => 'VA Bank'],
            ['code' => 'bri_va',    'label' => 'BRI VA',            'category' => 'VA Bank'],
            ['code' => 'bni_va',    'label' => 'BNI VA',            'category' => 'VA Bank'],
            ['code' => 'mandiri_va','label' => 'Mandiri VA',        'category' => 'VA Bank'],
            ['code' => 'permata_va','label' => 'Permata VA',        'category' => 'VA Bank'],
            ['code' => 'alfamart',  'label' => 'Alfamart',          'category' => 'Retail'],
            ['code' => 'indomaret', 'label' => 'Indomaret',         'category' => 'Retail'],
        ],
        'tripay' => [
            ['code' => '',          'label' => 'QRIS (Default)',    'category' => 'Umum'],
            ['code' => 'qris',      'label' => 'QRIS',              'category' => 'QRIS'],
            ['code' => 'gopay',     'label' => 'GoPay',             'category' => 'E-Wallet'],
            ['code' => 'ovo',       'label' => 'OVO',               'category' => 'E-Wallet'],
            ['code' => 'dana',      'label' => 'DANA',              'category' => 'E-Wallet'],
            ['code' => 'shopeepay', 'label' => 'ShopeePay',         'category' => 'E-Wallet'],
            ['code' => 'bca_va',    'label' => 'BCA VA',            'category' => 'VA Bank'],
            ['code' => 'mandiri_va','label' => 'Mandiri VA',        'category' => 'VA Bank'],
            ['code' => 'bni_va',    'label' => 'BNI VA',            'category' => 'VA Bank'],
            ['code' => 'bri_va',    'label' => 'BRI VA',            'category' => 'VA Bank'],
            ['code' => 'permata_va','label' => 'Permata VA',        'category' => 'VA Bank'],
            ['code' => 'alfamart',  'label' => 'Alfamart',          'category' => 'Retail'],
            ['code' => 'indomaret', 'label' => 'Indomaret',         'category' => 'Retail'],
            ['code' => 'cc',        'label' => 'Kartu Kredit',      'category' => 'Kartu'],
        ],
        'duitku' => [
            ['code' => '',          'label' => 'QRIS (Default)',    'category' => 'Umum'],
            ['code' => 'qris',      'label' => 'QRIS',              'category' => 'QRIS'],
            ['code' => 'gopay',     'label' => 'GoPay',             'category' => 'E-Wallet'],
            ['code' => 'ovo',       'label' => 'OVO',               'category' => 'E-Wallet'],
            ['code' => 'dana',      'label' => 'DANA',              'category' => 'E-Wallet'],
            ['code' => 'shopeepay', 'label' => 'ShopeePay',         'category' => 'E-Wallet'],
            ['code' => 'bca_va',    'label' => 'BCA VA',            'category' => 'VA Bank'],
            ['code' => 'mandiri_va','label' => 'Mandiri VA',        'category' => 'VA Bank'],
            ['code' => 'bni_va',    'label' => 'BNI VA',            'category' => 'VA Bank'],
            ['code' => 'bri_va',    'label' => 'BRI VA',            'category' => 'VA Bank'],
            ['code' => 'indomaret', 'label' => 'Indomaret',         'category' => 'Retail'],
            ['code' => 'alfamart',  'label' => 'Alfamart',          'category' => 'Retail'],
        ],
        'manual_ewallet' => [
            ['code' => 'manual_ewallet', 'label' => 'Manual Transfer e-Wallet', 'category' => 'Transfer'],
        ],
        'manual_transfer' => [
            ['code' => 'manual',    'label' => 'Manual Transfer via Bank', 'category' => 'Transfer'],
        ],
    ];

    public function openModal() { $this->showPaymentModal = true; }
    public function closeModal() { $this->showPaymentModal = false; }

    public function updatedSelectedGateway(): void
    {
        $this->selectedPaymentMethod = '';
        $this->paymentErrorMessage = null;
    }

    public function gatewayOptions(): array
    {
        $out = [];
        foreach ($this->activeGateways as $k => $g) {
            $out[$k] = $g['name'] ?? $g['label'] ?? $k;
        }
        return $out;
    }

    public function paymentMethodOptions(): array
    {
        if ($this->selectedGateway === '') return [];
        $defs = self::GATEWAY_METHOD_OPTIONS[$this->selectedGateway] ?? [];
        return $defs;
    }

    public function groupedPaymentMethodOptions(): array
    {
        $opts = $this->paymentMethodOptions();
        $grouped = [];
        foreach ($opts as $o) {
            $cat = $o['category'] ?? 'Lainnya';
            $grouped[$cat][] = $o;
        }
        return $grouped;
    }

    public function mount($id)
    {
        $this->invoiceId = $id;

        $customer = \App\Models\CRM\Customer::where('user_id', auth()->id())->first();
        if (!$customer) {
            abort(403, 'Profil pelanggan tidak ditemukan.');
        }

        $this->invoice = Invoice::with(['items', 'payments', 'customer'])->where('customer_id', $customer->id)
            ->where('id', $id)
            ->firstOrFail();

        // Check if returning from payment gateway
        if (request()->query('paid') == '1') {
            $this->dispatch('toast', ['type' => 'success', 'message' => 'Pembayaran sedang diproses oleh sistem. Harap tunggu beberapa saat hingga status diperbarui.']);
        } elseif (request()->query('paid') == '0') {
            $this->paymentErrorMessage = 'Pembayaran dibatalkan atau gagal.';
        }

        try {
            $this->company = app(\App\Services\Pengaturan\CompanySettingsService::class)->getAll();
            $gateways = app(\App\Services\Pengaturan\PaymentGatewaySettingsService::class)->getAll();
            $this->activeGateways = [];
            foreach ($gateways as $k => $g) {
                if (!($g['enabled'] ?? false)) continue;
                if (in_array($k, ['bca_va', 'ewallet'])) continue;

                if ($k === 'manual_transfer') {
                    $mappedAccounts = [];
                    foreach (($g['bank_accounts'] ?? []) as $ba) {
                        $mappedAccounts[] = [
                            'id' => $ba['id'] ?? null,
                            'bank' => $ba['bank_name'] ?? ($ba['bank'] ?? 'Bank'),
                            'account_number' => $ba['account_number'] ?? '',
                            'account_name' => $ba['account_holder'] ?? ($ba['account_name'] ?? ''),
                            'branch' => $ba['branch'] ?? '',
                            'active' => (bool)($ba['active'] ?? true),
                        ];
                    }
                    $g['bank_accounts'] = $mappedAccounts;
                }

                if ($k === 'manual_ewallet') {
                    $mappedProviders = [];
                    foreach (($g['providers'] ?? []) as $p) {
                        $mappedProviders[] = [
                            'id' => $p['id'] ?? null,
                            'name' => $p['name'] ?? 'e-Wallet',
                            'number' => $p['number'] ?? '',
                            'holder' => $p['holder'] ?? '',
                            'active' => (bool)($p['active'] ?? true),
                        ];
                    }
                    $g['providers'] = $mappedProviders;
                }

                if (!isset($g['name']) && isset($g['label'])) {
                    $g['name'] = $g['label'];
                }
                $this->activeGateways[$k] = $g;
            }

            $keys = array_keys($this->activeGateways);
            if (count($keys) > 0 && $this->selectedGateway === '') {
                $this->selectedGateway = (string)($keys[0] ?? '');
            }
        } catch (\Throwable $e) {
            \Log::error("InvoiceShow mount error: " . $e->getMessage());
            $this->company = [
                'name' => config('app.name', 'dsBilling Enterprise'),
                'legal_name' => '',
                'npwp' => '',
                'address' => '',
                'rt' => '',
                'rw' => '',
                'village' => '',
                'district' => '',
                'city' => '',
                'province' => '',
                'postal_code' => '',
                'phone' => '',
                'mobile' => '',
                'email' => config('mail.from.address', ''),
                'website' => '',
                'billing_email' => '',
                'support_email' => '',
                'ceo_name' => '',
                'ceo_nik' => '',
                'director_name' => '',
                'finance_name' => '',
                'finance_email' => '',
                'head_noc_name' => '',
                'established_date' => '',
                'operational_hours' => 'Senin - Jumat 08:00 - 17:00',
                'bank_1_name' => '',
                'bank_1_account' => '',
                'bank_1_holder' => '',
                'bank_2_name' => '',
                'bank_2_account' => '',
                'bank_2_holder' => '',
                'bank_3_name' => '',
                'bank_3_account' => '',
                'bank_3_holder' => '',
                'tax_office' => '',
                'signature_name' => '',
                'signature_title' => '',
                'signature_text' => '',
                'logo_url' => '',
                'stamp_url' => '',
                'partner_name' => '',
                'partner_legal_name' => '',
                'partner_npwp' => '',
                'partner_address' => '',
                'partner_rt' => '',
                'partner_rw' => '',
                'partner_village' => '',
                'partner_district' => '',
                'partner_city' => '',
                'partner_province' => '',
                'partner_postal_code' => '',
                'partner_phone' => '',
                'partner_mobile' => '',
                'partner_email' => '',
                'partner_website' => '',
                'partner_logo_url' => '',
                'invoice_opening_text' => '',
                'invoice_footer_text' => '',
                'terms_and_conditions' => '',
            ];
        }
    }

    public function proceedPay()
    {
        $this->paymentErrorMessage = null;

        if ($this->selectedGateway === '') {
            $msg = 'Pilih metode pembayaran terlebih dahulu.';
            $this->paymentErrorMessage = $msg;
            $this->dispatch('toast', ['type' => 'error', 'message' => $msg]);
            return null;
        }
        return $this->pay($this->selectedGateway, $this->selectedPaymentMethod !== '' ? $this->selectedPaymentMethod : null);
    }

    public function pay(string $gatewayKey, ?string $paymentMethodCode = null)
    {
        $this->paymentErrorMessage = null;

        if ($gatewayKey === 'manual_transfer' || $gatewayKey === 'manual_ewallet') {
            $msg = 'Silakan transfer sesuai nomor rekening / e-wallet yang tertera, lalu kirim bukti pembayaran ke WhatsApp Admin/CS.';
            $this->dispatch('toast', ['type' => 'info', 'message' => $msg]);
            return null;
        }

        if ($this->invoice->status === 'paid') {
            $msg = 'Tagihan sudah lunas.';
            $this->paymentErrorMessage = $msg;
            $this->dispatch('toast', ['type' => 'error', 'message' => $msg]);
            return null;
        }

        $amountIdr = (int) max(0, (float)$this->invoice->total_amount - (float)$this->invoice->paid_amount);
        if ($amountIdr <= 0) {
            $msg = 'Tagihan tidak memiliki sisa pembayaran.';
            $this->paymentErrorMessage = $msg;
            $this->dispatch('toast', ['type' => 'error', 'message' => $msg]);
            return null;
        }

        $registry = app(\App\Services\Adapters\Payment\PaymentGatewayRegistry::class);
        if (!$registry->get($gatewayKey)) {
            $msg = 'Metode pembayaran tidak dikenali: ' . $gatewayKey;
            $this->paymentErrorMessage = $msg;
            $this->dispatch('toast', ['type' => 'error', 'message' => $msg]);
            return null;
        }
        if (!$registry->isEnabled($gatewayKey)) {
            $msg = 'Metode pembayaran ini sedang dinonaktifkan.';
            $this->paymentErrorMessage = $msg;
            $this->dispatch('toast', ['type' => 'error', 'message' => $msg]);
            return null;
        }

        $orchestrator = app(\App\Services\Adapters\Payment\PaymentOrchestrationService::class);
        $customer = $this->invoice->customer;

        \Log::info('[InvoiceShow::pay] INITIATE', [
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number ?? null,
            'gateway' => $gatewayKey,
            'payment_method_code' => $paymentMethodCode,
            'amount_idr' => $amountIdr,
            'customer_id' => $this->invoice->customer_id,
            'customer_name' => $customer->name ?? null,
            'invoice_status' => $this->invoice->status,
            'user_id' => auth()->id(),
        ]);

        try {
            $result = $orchestrator->initiatePayment(
                gatewayKey: $gatewayKey,
                customerId: $this->invoice->customer_id,
                userId: auth()->id(),
                amountIdr: $amountIdr,
                invoiceIds: [$this->invoice->id],
                paymentMethodCode: $paymentMethodCode,
                customerName: $customer->name ?? 'Customer',
                customerEmail: $customer->email ?? 'customer@example.com',
                customerPhone: $customer->phone ?? ($customer->mobile ?? '00000'),
                successRedirectUrl: route('customer-portal.billing.invoice-show', ['id' => $this->invoice->id]) . '?paid=1',
                failureRedirectUrl: route('customer-portal.billing.invoice-show', ['id' => $this->invoice->id]) . '?paid=0',
            );

            $gatewayResp = $result['gateway_response'];

            \Log::info('[InvoiceShow::pay] ORCHESTRATOR_RESPONSE', [
                'gateway' => $gatewayKey,
                'success' => $gatewayResp->success,
                'payment_mode' => $gatewayResp->paymentMode ?? null,
                'redirect_url_empty' => empty($gatewayResp->redirectUrl),
                'gateway_ref_id' => $gatewayResp->gatewayReferenceId ?? null,
                'payment_id' => $result['payment']->id ?? null,
                'error_message' => $gatewayResp->errorMessage ?? null,
            ]);

            if ($gatewayResp->success) {
                $redirectUrl = $gatewayResp->redirectUrl;
                if ($redirectUrl !== '') {
                    // Fallback 1: Livewire 4 component redirect (full page, non-navigate)
                    try {
                        $this->redirect($redirectUrl, navigate: false);
                    } catch (\Throwable) {
                    }
                    // Fallback 2: Native Laravel RedirectResponse (Livewire official docs style)
                    return redirect()->away($redirectUrl);
                }
                $msg = 'Gateway tidak mengembalikan URL pembayaran.';
                $this->paymentErrorMessage = $msg;
                $this->dispatch('toast', ['type' => 'error', 'message' => $msg]);
            } else {
                $msg = 'Gagal memproses pembayaran: ' . ($gatewayResp->errorMessage ?? 'Unknown error');
                $this->paymentErrorMessage = $msg;
                $this->dispatch('toast', ['type' => 'error', 'message' => $msg]);
            }
        } catch (\Throwable $e) {
            \Log::error('[InvoiceShow::pay] EXCEPTION gateway=' . $gatewayKey, [
                'invoice_id' => $this->invoice->id,
                'err' => $e->getMessage(),
                'file' => $e->getFile() . ':' . $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            $msg = 'Error sistem: ' . $e->getMessage();
            $this->paymentErrorMessage = $msg;
            $this->dispatch('toast', ['type' => 'error', 'message' => $msg]);
        }
        return null;
    }

    public function render()
    {
        $companyAddress = \App\Services\Pengaturan\CompanySettingsService::formatAddressLine($this->company);
        $showPartner = \App\Services\Pengaturan\CompanySettingsService::shouldShowPartner($this->company);
        $partnerAddress = $showPartner
            ? \App\Services\Pengaturan\CompanySettingsService::formatAddressLine($this->company, 'partner_')
            : '';

        try {
            $invoiceDefaults = app(\App\Services\Pengaturan\CompanySettingsService::class)->getInvoiceDefaults();
        } catch (\Throwable) {
            $invoiceDefaults = [
                'openingText' => 'Terima kasih telah mempercayakan layanan kami. Berikut adalah rincian tagihan Anda:',
                'footerText' => 'Pembayaran dapat dilakukan via transfer bank atau e-wallet yang tertera. Mohon sertakan nomor invoice sebagai referensi.',
                'termsText' => "1. Tagihan harus dibayar paling lambat tanggal jatuh tempo.\n2. Keterlambatan pembayaran dapat mengakibatkan penangguhan layanan.\n3. Keluhan tagihan disertakan bukti pembayaran yang sah.",
            ];
        }

        $gatewayOptions = $this->gatewayOptions();

        return view('livewire.customer-portal.billing.invoice-show', array_merge(
            compact('companyAddress', 'partnerAddress', 'showPartner', 'gatewayOptions'),
            $invoiceDefaults
        ));
    }
}



