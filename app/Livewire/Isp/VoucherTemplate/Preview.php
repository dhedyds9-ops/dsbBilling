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
        ];
        $duration = $durationMap[$this->selectedPrice] ?? '1 Hari';
        
        $builder = new \App\Services\VoucherTemplate\TemplateContextBuilder();
        $companySettings = class_exists(\App\Models\Setting::class) && method_exists(\App\Models\Setting::class, 'getGroup')
            ? \App\Models\Setting::getGroup('company') 
            : [];
            
        $globalContext = $builder->buildFromVoucherModels(null, null, null, null, $companySettings, 'IDR');

        $vouchers = [];
        for ($i = 0; $i < $this->batchCount; $i++) {
            $vouchers[] = [
                'code' => 'DEMO' . rand(1000, 9999),
                'username' => 'user' . rand(100, 999),
                'password' => rand(1000, 9999),
                'price' => $this->selectedPrice,
                'duration' => $duration,
                'validity' => '1 Hari',
                'package_name' => 'Voucher ' . $this->selectedPrice,
                'created_at' => now()->format('Y-m-d H:i'),
            ];
        }

        $globalContext['vouchers'] = $vouchers;
        
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
        return view('livewire.isp.voucher-template.preview')->layout('layouts.enterprise');
    }
}




