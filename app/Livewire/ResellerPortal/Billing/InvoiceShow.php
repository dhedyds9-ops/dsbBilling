<?php

namespace App\Livewire\ResellerPortal\Billing;

use App\Livewire\AdminComponent;
use App\Models\Billing\Invoice;
use Illuminate\Support\Facades\Auth;

class InvoiceShow extends AdminComponent
{
    public $invoiceId;
    public $invoice;
    public array $company = [];

    public function mount($id = null)
    {
        parent::mount();
        $this->activeModule = 'reseller-portal';
        $this->activePage = 'reseller-portal.billing.invoices';
        $this->invoiceId = $id;
        
        $resellerId = Auth::id();
        $this->invoice = Invoice::whereHas('customer', function ($q) use ($resellerId) {
            $q->where('reseller_id', $resellerId);
        })->with(['customer', 'items', 'payments'])->findOrFail($id);

        try {
            $this->company = app(\App\Services\Pengaturan\CompanySettingsService::class)->getAll();
        } catch (\Throwable) {
            $this->company = [
                'company_name' => config('app.name', 'dsBilling'),
                'company_address' => 'Jl. dsBilling No. 123',
                'company_city' => 'Kota B',
                'company_province' => 'Provinsi C',
                'company_phone' => '08123456789',
                'company_email' => 'hello@dsbilling.test',
                'company_website' => 'dsbilling.test',
                'partner_name' => '',
                'partner_address' => '',
                'partner_phone' => '',
                'partner_email' => '',
                'partner_website' => '',
            ];
        }

        $this->breadcrumbs = [
            ['label' => 'Reseller Portal', 'url' => '#'],
            ['label' => 'Tagihan', 'url' => '#'],
            ['label' => 'Tagihan Pelanggan', 'url' => route('reseller-portal.billing.invoices')],
            ['label' => 'Detail ' . $this->invoice->invoice_number, 'url' => '#'],
        ];
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
                'openingText' => 'Terima kasih atas kepercayaannya. Berikut adalah rincian tagihan Anda:',
                'footerText' => 'Pembayaran dapat dilakukan ke rekening yang tertera di bawah. Mohon cantumkan Nomor Invoice pada keterangan transfer.',
                'termsText' => "1. Tagihan jatuh tempo pada tanggal yang tertera.\n2. Keterlambatan pembayaran dapat menyebabkan pemutusan layanan sementara.\n3. Harap konfirmasi setelah melakukan pembayaran.",
            ];
        }

        return view('livewire.reseller-portal.billing.invoice-show', array_merge(
            compact('companyAddress', 'partnerAddress', 'showPartner'),
            $invoiceDefaults
        ));
    }
}
