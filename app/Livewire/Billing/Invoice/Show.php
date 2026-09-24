<?php

namespace App\Livewire\Billing\Invoice;

use App\Livewire\AdminComponent;
use App\Models\Billing\Invoice;

class Show extends AdminComponent
{
    public $invoiceId;
    public $invoice;
    public array $company = [];

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'billing';
        $this->activePage = 'invoices';
        $this->invoiceId = $id;
        $this->invoice = Invoice::with(['customer', 'items', 'payments'])->findOrFail($id);

        try {
            $this->company = app(\App\Services\Pengaturan\CompanySettingsService::class)->getAll();
        } catch (\Throwable) {
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

        $this->breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Billing', 'url' => route('billing.invoices.index')],
            ['label' => 'Invoices', 'url' => route('billing.invoices.index')],
            ['label' => $this->invoice->invoice_number],
        ];
    }

    public $showPaymentModal = false;
    public $paymentMethod = 'cash';
    public $paymentAmount = 0;
    public $paymentDate;
    public $paymentReference = '';

    public function openPaymentModal()
    {
        $this->paymentAmount = (float)$this->invoice->total_amount - (float)$this->invoice->paid_amount;
        $this->paymentDate = now()->format('Y-m-d');
        $this->paymentReference = 'MANUAL-' . time();
        $this->showPaymentModal = true;
    }

    public function processPayment(\App\Services\Billing\PaymentService $paymentService)
    {
        $this->validate([
            'paymentMethod' => 'required|in:cash,bank_transfer',
            'paymentAmount' => 'required|numeric|min:1|max:' . ($this->invoice->total_amount - $this->invoice->paid_amount),
            'paymentDate' => 'required|date',
        ]);

        try {
            $paymentService->createPayment(
                $this->invoice->customer_id,
                $this->paymentAmount,
                auth()->id(),
                [$this->invoiceId],
                'IDR',
                $this->paymentMethod,
                'success',
                $this->paymentReference,
                null,
                \Carbon\Carbon::parse($this->paymentDate),
                'manual'
            );

            session()->flash('success', 'Pembayaran berhasil ditambahkan!');
            $this->showPaymentModal = false;

            $this->invoice = Invoice::with(['customer', 'items', 'payments'])->findOrFail($this->invoiceId);
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal memproses pembayaran: ' . $e->getMessage());
        }
    }

    public function sendWhatsApp()
    {
        try {
            app(\App\Services\Notifications\WhatsApp\WhatsAppNotificationService::class)->notifyInvoiceCreated($this->invoice);
            session()->flash('success', 'Tagihan berhasil dikirim ke WhatsApp pelanggan!');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengirim WhatsApp: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $timeline = [
            ['date' => $this->invoice->created_at, 'title' => 'Invoice Dibuat', 'description' => 'Invoice berhasil dibuat', 'type' => 'create'],
        ];

        $activities = [
            ['user' => 'Admin', 'action' => 'Membuat invoice', 'module' => 'Billing', 'time' => 'Baru saja'],
        ];

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

        return view('livewire.billing.invoice.show', array_merge(
            compact('timeline', 'activities', 'companyAddress', 'partnerAddress', 'showPartner'),
            $invoiceDefaults
        ));
    }
}
