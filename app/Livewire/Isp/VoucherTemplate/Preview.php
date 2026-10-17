<?php

namespace App\Livewire\Isp\VoucherTemplate;

use App\Models\ISP\VoucherTemplate;
use App\Services\VoucherTemplate\TemplateRenderer;
use Livewire\Component;

class Preview extends Component
{
    public VoucherTemplate $template;
    public $previewHtml = '';
    public $previewCss = '';
    public $batchCount = 1;
    public $selectedPrice = '5000'; // For conditional testing

    public function mount($id) { $template = \App\Models\ISP\VoucherTemplate::findOrFail($id);
        $this->template = $template->load('latestVersion');
        $this->generatePreview();
    }

    public function updatedBatchCount()
    {
        $this->generatePreview();
    }

    public function updatedSelectedPrice()
    {
        $this->generatePreview();
    }

    public function generatePreview()
    {
        $renderer = app(TemplateRenderer::class);
        $content = $this->template->latestVersion?->template_code ?? '';
        
        $durationMap = [
            '2000' => '1 Jam',
            '3000' => '2 Jam',
            '5000' => '3 Jam',
            '7000' => '5 Jam',
            '10000' => '10 Jam',
            '25000' => '24 Jam (1 Hari)',
            '65000' => '7 Hari',
            '70000' => '30 Hari (1 Bulan)',
        ];
        $duration = $durationMap[$this->selectedPrice] ?? '3 Jam';
        
        $builder = new \App\Services\VoucherTemplate\TemplateContextBuilder();
        $companySettings = class_exists(\App\Models\Setting::class) && method_exists(\App\Models\Setting::class, 'getGroup')
            ? \App\Models\Setting::getGroup('company') 
            : [];
            
        $globalContext = $builder->buildFromVoucherModels(null, null, null, null, $companySettings, 'IDR');

        $priceNum = (int) $this->selectedPrice;
        $priceFormatted = 'Rp ' . number_format($priceNum, 0, ',', '.');
        $packageName = 'Paket ' . number_format($priceNum, 0, ',', '.');

        $vouchers = [];
        for ($i = 0; $i < $this->batchCount; $i++) {
            $code = 'VC' . rand(10000, 99999);
            $user = 'user' . rand(100, 999);
            $pass = (string) rand(1000, 9999);

            $vouchers[] = [
                'code' => $code,
                'username' => $user,
                'password' => $pass,
                'secret' => $pass,
                'price' => $priceNum,
                'total' => $priceNum,
                'duration' => $duration,
                'timelimit' => $duration,
                'validity' => '1 Hari',
                'validperiod' => '1 Hari',
                'package_name' => $packageName,
                'plan_name' => $packageName,
                'type' => 'HOTSPOT',
                'owner_name' => 'Admin MStore',
                'created_date' => now()->format('d/m/Y'),
                'created_at' => now()->format('d M Y H:i'),
                'voucher' => [
                    'code' => $code,
                    'username' => $user,
                    'password' => $pass,
                    'price' => $priceNum,
                    'duration' => $duration,
                    'validity' => '1 Hari',
                    'created_at' => now()->format('d M Y H:i'),
                ],
                'package' => [
                    'name' => $packageName,
                    'price' => $priceNum,
                    'duration' => $duration,
                    'speed' => 'Up to 10 Mbps',
                ],
            ];
        }

        $globalContext['vouchers'] = $vouchers;
        $globalContext['hotspotdns'] = 'wifi.hotspot.net';
        $globalContext['_theme'] = 'default/';
        $globalContext['_c'] = [
            'CompanyName' => $companySettings['name'] ?? 'MStore WiFi Hotspot',
            'currency_code' => 'Rp',
            'dec_point' => ',',
            'thousands_sep' => '.',
            'show-logo' => 'logo.png',
        ];
        
        $settings = $this->template->latestVersion?->settings ?? [];
        if (is_string($settings)) {
            $settings = json_decode($settings, true) ?? [];
        }
        if (!is_array($settings)) {
            $settings = [];
        }
        $cssCode = $this->template->latestVersion?->css_code ?? null;
        $jsCode = $this->template->latestVersion?->js_code ?? null;

        try {
            $res = $renderer->renderBatch($content, $globalContext, $settings, $cssCode, $jsCode);
            $this->previewHtml = $res["html"] ?? "";
            $this->previewCss = $res["css"] ?? "";
            if (!empty($res["errors"])) {
                $this->previewHtml = implode("<br>", $res["errors"]);
            }
        } catch (\Throwable $e) {
            $this->previewHtml = '<div class="alert alert-danger">Rendering Error: ' . $e->getMessage() . '</div>';
            $this->previewCss = '';
        }
    }

    public function render()
    {
        return view('livewire.isp.voucher-template.preview', [
            'template' => $this->template,
            'previewHtml' => $this->previewHtml,
            'previewCss' => $this->previewCss,
            'batchCount' => $this->batchCount,
            'selectedPrice' => $this->selectedPrice,
        ])->layout('layouts.enterprise');
    }
}




