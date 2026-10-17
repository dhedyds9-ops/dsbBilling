<?php

namespace App\Services\ISP\VoucherTemplate\Actions;

use App\Models\ISP\VoucherTemplate;
use App\Services\VoucherTemplate\LegacyCompatibilityLayer;
use App\Services\VoucherTemplate\TemplateValidator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportVoucherTemplateAction
{
    public function __construct(
        private LegacyCompatibilityLayer $legacyLayer,
        private TemplateValidator $validator
    ) {}

    public function execute(string $name, string $legacyContent, string $category, int $userId): VoucherTemplate
    {
        return DB::transaction(function () use ($name, $legacyContent, $category, $userId) {
            $modernContent = $this->legacyLayer->translate($legacyContent)['source'];
            
            $validation = $this->validator->validate($modernContent);
            if (!$validation['is_valid']) {
                $errorMsgs = array_map(function ($err) {
                    return is_array($err) ? ($err['message'] ?? json_encode($err)) : (string) $err;
                }, $validation['errors']);
                throw new \Exception('Imported template syntax is invalid after conversion: ' . implode(', ', $errorMsgs));
            }
            
            $validCategory = \App\Enums\ISP\VoucherTemplateCategory::tryFrom($category) ?? \App\Enums\ISP\VoucherTemplateCategory::Wifi;

            $template = VoucherTemplate::create([
                'name' => $name,
                'category' => $validCategory,
                'description' => 'Imported legacy template',
                'is_system' => false,
                'is_active' => false,
                'created_by' => $userId,
            ]);
            
            $template->versions()->create([
                'version' => 1,
                'template_code' => $modernContent,
                'settings' => ['note' => 'Imported from legacy'],
                'created_by' => $userId,
            ]);
            
            Log::info('Legacy template imported', ['template_id' => $template->id, 'user_id' => $userId]);
            
            return $template;
        });
    }
}
