<?php

namespace App\Services\ISP\VoucherTemplate\Actions;

use App\Models\ISP\VoucherTemplate;
use App\Services\VoucherTemplate\TemplateValidator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class UpdateVoucherTemplateAction
{
    public function __construct(private TemplateValidator $validator) {}

    /**
     * @throws Throwable
     */
    public function execute(VoucherTemplate $template, array $data, int $userId, bool $createNewVersion = false): VoucherTemplate
    {
        if ($template->is_system) {
            throw new \Exception('System templates cannot be edited directly.');
        }

        return DB::transaction(function () use ($template, $data, $userId, $createNewVersion) {
            $template->update([
                'name' => $data['name'] ?? $template->name,
                'category' => $data['category'] ?? $template->category,
                'description' => $data['description'] ?? $template->description,
                'is_active' => $data['is_active'] ?? $template->is_active,
            ]);
            
            if (isset($data['template_code'])) {
                $content = $data['template_code'];
                
                // Validate template syntax
                $validation = $this->validator->validate($content);
                if (!$validation['is_valid']) {
                    throw new \Exception('Invalid template syntax: ' . implode(', ', $validation['errors']));
                }

                if ($createNewVersion) {
                    $latestVersion = $template->versions()->max('version') ?? 0;
                    $settingsData = $data['settings'] ?? null;
                    if (!is_array($settingsData)) {
                        $settingsData = is_string($settingsData) && $settingsData !== ''
                            ? ['note' => $settingsData]
                            : [];
                    }
                    if (empty($settingsData)) {
                        $settingsData['note'] = 'Updated via editor';
                    }
                    $template->versions()->create([
                        'version' => $latestVersion + 1,
                        'template_code' => $content,
                        'settings' => $settingsData,
                        'created_by' => $userId,
                    ]);
                } else {
                    $latest = $template->latestVersion;
                    if ($latest) {
                        $latest->update(['template_code' => $content]);
                    } else {
                        $template->versions()->create([
                            'version' => 1,
                            'template_code' => $content,
                            'settings' => ['note' => 'Initial version'],
                            'created_by' => $userId,
                        ]);
                    }
                }
            }
            
            Log::info('Voucher template updated', ['template_id' => $template->id, 'user_id' => $userId]);
            
            return $template;
        });
    }
}
