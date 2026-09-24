<?php

namespace App\Livewire\Admin\Settings;

use App\Livewire\AdminComponent;
use App\Models\Setting;

class Index extends AdminComponent
{
    // General
    public $app_name;
    public $company_name;
    public $company_address;
    public $company_phone;
    public $company_email;
    public $company_website;
    public $company_logo;

    // Billing
    public $invoice_prefix;
    public $invoice_due_days;
    public $tax_percent;
    public $late_fee_percent;
    public $currency;

    // Notification
    public $notif_invoice_before_days;
    public $notif_isolir_before_days;

    // Active Tab (use separate prop to avoid conflict with parent string $activeTab)
    public string $settingsTab = 'general';

    public function mount()
    {
        parent::mount();
        $this->activeModule = 'admin';
        $this->activePage = 'settings';

        // General
        $this->app_name = Setting::getValue('app_name', 'dsBilling');
        $this->company_name = Setting::getValue('company_name', '');
        $this->company_address = Setting::getValue('company_address', '');
        $this->company_phone = Setting::getValue('company_phone', '');
        $this->company_email = Setting::getValue('company_email', '');
        $this->company_website = Setting::getValue('company_website', '');

        // Billing
        $pgGeneral = Setting::getValue('payment_gateway.general.invoice_prefix', 'INV-');
        $this->invoice_prefix = $pgGeneral;
        $this->invoice_due_days = Setting::getValue('invoice_due_days', 14);
        $this->tax_percent = Setting::getValue('tax_percent', 0);
        $this->late_fee_percent = Setting::getValue('late_fee_percent', 0);
        $this->currency = Setting::getValue('currency', 'IDR');

        // Notification
        $this->notif_invoice_before_days = Setting::getValue('notif_invoice_before_days', 3);
        $this->notif_isolir_before_days = Setting::getValue('notif_isolir_before_days', 1);
    }

    public function rules()
    {
        return [
            'app_name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'company_address' => 'nullable|string',
            'company_phone' => 'nullable|string|max:50',
            'company_email' => 'nullable|email|max:255',
            'company_website' => 'nullable|url|max:255',
            'invoice_prefix' => 'nullable|string|max:20',
            'invoice_due_days' => 'nullable|integer|min:1|max:365',
            'tax_percent' => 'nullable|numeric|min:0|max:100',
            'late_fee_percent' => 'nullable|numeric|min:0|max:100',
            'currency' => 'nullable|string|max:10',
            'notif_invoice_before_days' => 'nullable|integer|min:0|max:30',
            'notif_isolir_before_days' => 'nullable|integer|min:0|max:30',
        ];
    }

    public function saveGeneral()
    {
        $this->validate([
            'app_name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'company_address' => 'nullable|string',
            'company_phone' => 'nullable|string|max:50',
            'company_email' => 'nullable|email|max:255',
            'company_website' => 'nullable|string|max:255',
        ]);

        Setting::setMany([
            'app_name' => $this->app_name,
            'company_name' => $this->company_name,
            'company_address' => $this->company_address,
            'company_phone' => $this->company_phone,
            'company_email' => $this->company_email,
            'company_website' => $this->company_website,
        ], 'general');

        \Illuminate\Support\Facades\Cache::forget(Setting::CACHE_KEY);

        $this->dispatch('notify', type: 'success', message: 'Pengaturan umum berhasil disimpan!');
    }

    public function saveBilling()
    {
        $this->validate([
            'invoice_prefix' => 'nullable|string|max:20',
            'invoice_due_days' => 'nullable|integer|min:1',
            'tax_percent' => 'nullable|numeric|min:0|max:100',
            'late_fee_percent' => 'nullable|numeric|min:0|max:100',
            'currency' => 'nullable|string|max:10',
        ]);

        Setting::setMany([
            'invoice_prefix' => $this->invoice_prefix,
            'invoice_due_days' => $this->invoice_due_days,
            'tax_percent' => $this->tax_percent,
            'late_fee_percent' => $this->late_fee_percent,
            'currency' => $this->currency,
        ], 'billing');

        \Illuminate\Support\Facades\Cache::forget(Setting::CACHE_KEY);

        $this->dispatch('notify', type: 'success', message: 'Pengaturan billing berhasil disimpan!');
    }

    public function saveNotification()
    {
        $this->validate([
            'notif_invoice_before_days' => 'nullable|integer|min:0|max:30',
            'notif_isolir_before_days' => 'nullable|integer|min:0|max:30',
        ]);

        Setting::setMany([
            'notif_invoice_before_days' => $this->notif_invoice_before_days,
            'notif_isolir_before_days' => $this->notif_isolir_before_days,
        ], 'notification');

        \Illuminate\Support\Facades\Cache::forget(Setting::CACHE_KEY);

        $this->dispatch('notify', type: 'success', message: 'Pengaturan notifikasi berhasil disimpan!');
    }

    public function render()
    {
        return view('livewire.admin.settings.index');
    }
}
