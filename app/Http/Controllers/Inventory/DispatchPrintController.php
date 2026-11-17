<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryDispatch;
use App\Services\Pengaturan\CompanySettingsService;
use Illuminate\Http\Request;

class DispatchPrintController extends Controller
{
    /**
     * Tampilkan halaman cetak Surat Jalan (Goods Dispatch Note) untuk dicetak / diprint.
     */
    public function print(Request $request, $id)
    {
        $dispatch = InventoryDispatch::with([
            'warehouse',
            'branch',
            'recipient',
            'dispatcher',
            'customer',
            'installation',
            'ticket',
            'requisition',
            'items.item',
        ])->findOrFail($id);

        $company = [];
        try {
            if (class_exists(CompanySettingsService::class)) {
                $company = app(CompanySettingsService::class)->getAll();
            }
        } catch (\Throwable $e) {
            $company = [];
        }

        return view('inventory.dispatch.print', compact('dispatch', 'company'));
    }
}
