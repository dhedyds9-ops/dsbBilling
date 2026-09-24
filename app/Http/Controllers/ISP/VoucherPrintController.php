<?php

namespace App\Http\Controllers\ISP;

use App\Http\Controllers\Controller;
use App\Models\ISP\Voucher;
use App\Models\ISP\VoucherTemplate;
use App\Services\VoucherTemplate\TemplateRenderer;
use App\Services\VoucherTemplate\TemplateContextBuilder;
use App\Models\Setting;
use Illuminate\Http\Request;

class VoucherPrintController extends Controller
{
    public function previewTemplate($id)
    {
        $template = \App\Models\ISP\VoucherTemplate::with('latestVersion')->find($id);
        if (!$template || !$template->latestVersion) {
            return response()->json(['html' => '<div class="p-4 text-center text-red-500">Template not found.</div>']);
        }

        $companySettings = class_exists(\App\Models\Setting::class) && method_exists(\App\Models\Setting::class, 'getGroup')
            ? \App\Models\Setting::getGroup('company') 
            : [];
            
        $logoUrl = isset($companySettings['logo']) && $companySettings['logo'] 
            ? (str_starts_with($companySettings['logo'], 'http') ? $companySettings['logo'] : asset('storage/' . $companySettings['logo'])) 
            : 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMjAiIGhlaWdodD0iMzAiPjx0ZXh0IHk9IjIwIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMjAiIGZvbnQtd2VpZ2h0PSJib2xkIiBmaWxsPSIjMzMzIj5MT0dPIERJU0lOSTwvdGV4dD48L3N2Zz4=';

        $dummyContext = [
            'username' => 'WN-8F3K2A',
            'password' => 'samasaja',
            'price' => '5000',
            'duration' => '30 Hari',
            'quota' => 'Unlimited',
            'company' => [
                'name' => $companySettings['name'] ?? 'WINETS.ID',
                'logo' => $logoUrl,
            ]
        ];

        $globalContext = [
            'company' => $dummyContext['company'],
            'currency' => 'IDR',
            'vouchers' => [$dummyContext]
        ];

        try {
            $renderer = app(\App\Services\VoucherTemplate\TemplateRenderer::class);
            $renderResult = $renderer->render($template->latestVersion->template_code, $globalContext, $template->latestVersion->settings ?? []);
            
            $voucherHtml = $renderResult['html'] ?? '';
            $cssCode = $renderResult['css'] ?? ($template->latestVersion->css_code ?? '');
            
            $previewHtml = '
            <div id="swal-tpl-preview-wrap" class="mt-5 text-left select-none">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold text-slate-500 tracking-wider uppercase">Preview Template <span class="text-emerald-500">(Aktual)</span></span>
                    <span class="text-[10px] text-slate-400">Preview sesuai template</span>
                </div>
                <div class="relative mx-auto flex justify-center w-full bg-slate-50 dark:bg-slate-800 rounded-xl overflow-hidden border-2 border-slate-200 dark:border-slate-600 shadow-sm p-4" style="min-height: 200px; display: flex; align-items: center; justify-content: center;">
                    <style>' . $cssCode . '</style>
                    <div style="transform: scale(0.9); transform-origin: center;">' . $voucherHtml . '</div>
                </div>
            </div>';
            
            return response()->json(['html' => $previewHtml]);
        } catch (\Throwable $e) {
            return response()->json(['html' => '<div class="p-4 text-center text-red-500">Error rendering template: ' . $e->getMessage() . '</div>']);
        }
    }

    public function print(Request $request)
    {
        $voucherIds = $request->input('ids');
        if (empty($voucherIds)) {
            $voucherIds = [];
            if ($request->has('ids_string')) {
                $voucherIds = explode(',', $request->input('ids_string'));
            }
        }
        
        if (empty($voucherIds) || !is_array($voucherIds)) {
            return redirect()->back()->with('error', 'Tidak ada voucher yang dipilih untuk dicetak.');
        }

        $vouchers = Voucher::whereIn('id', $voucherIds)
            ->with(['serviceProfile', 'nasDevice', 'hotspotUser', 'owner'])
            ->get();
            
        if ($vouchers->isEmpty()) {
            return redirect()->back()->with('error', 'Voucher tidak ditemukan.');
        }

        if ($request->filled('template_id')) {
            $template = VoucherTemplate::with('latestVersion')->find($request->template_id);
        } else {
            $template = VoucherTemplate::where('is_default', true)
                ->where('is_active', true)
                ->with('latestVersion')
                ->first();

            if (!$template) {
                $template = VoucherTemplate::where('is_active', true)
                    ->with('latestVersion')
                    ->first();
            }
        }

        if (!$template || !$template->latestVersion) {
            return redirect()->back()->with('error', 'Tidak ada template yang aktif. Silakan buat atau set default template terlebih dahulu.');
        }

        $companySettings = class_exists(Setting::class) && method_exists(Setting::class, 'getGroup')
            ? Setting::getGroup('company') 
            : [];
            
        $builder = new TemplateContextBuilder();
        $globalContext = $builder->buildFromVoucherModels(null, $vouchers->first()->serviceProfile, null, null, $companySettings, 'IDR');

        $voucherContexts = [];
        foreach ($vouchers as $voucher) {
            $context = $builder->buildFromVoucherModels($voucher, $voucher->serviceProfile, null, $voucher->reseller, $companySettings, 'IDR');
            $voucherContexts[] = $context['voucher'] ?? [];
        }

        $globalContext['vouchers'] = $voucherContexts;

        $renderer = app(TemplateRenderer::class);
        $content = $template->latestVersion->template_code;
        $settings = $template->latestVersion->settings ?? [];
        if (is_string($settings)) {
            $settings = json_decode($settings, true) ?? [];
        }
        if (!is_array($settings)) {
            $settings = [];
        }
        $cssCode = $template->latestVersion->css_code ?? null;
        $jsCode = $template->latestVersion->js_code ?? null;
        
        try {
            $res = $renderer->renderBatch($content, $globalContext, $settings, $cssCode, $jsCode);
            $html = $res["html"] ?? "";
            $css = $res["css"] ?? "";
            $errors = $res["errors"] ?? [];
        } catch (\Throwable $e) {
            $html = '';
            $css = '';
            $errors = ['Gagal merender template: ' . $e->getMessage()];
        }

        return view('isp.vouchers.print', compact('html', 'css', 'errors'));
    }
}

