<?php

namespace App\Http\Controllers\CustomerPortal;

use App\Http\Controllers\Controller;
use App\Models\Billing\Invoice;
use App\Models\Setting;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function print80mm($id)
    {
        $invoice = Invoice::with(['items', 'customer'])->findOrFail($id);
        
        // Ensure customer can only view their own invoice
        if ($invoice->customer->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $company = [
            'name' => Setting::getValue('company.name', 'BillingHub'),
            'address' => Setting::getValue('company.address', 'Jl. Contoh Alamat No. 123'),
            'phone' => Setting::getValue('company.phone', '08123456789'),
            'logo_url' => Setting::getValue('company.logo_url', null),
        ];

        return view('customer-portal.billing.invoice-80mm', compact('invoice', 'company'));
    }
}
