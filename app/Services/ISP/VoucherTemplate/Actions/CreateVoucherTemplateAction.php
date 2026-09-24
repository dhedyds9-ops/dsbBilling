<?php

namespace App\Services\ISP\VoucherTemplate\Actions;

use App\Models\ISP\VoucherTemplate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class CreateVoucherTemplateAction
{
    /**
     * @throws Throwable
     */
    public function execute(array $data, int $userId): VoucherTemplate
    {
        return DB::transaction(function () use ($data, $userId) {
            $data['created_by'] = $userId;
            $data['is_system'] = false;
            
            $template = VoucherTemplate::create($data);
            
            if (!empty($data['template_code'])) {
                $settingsData = $data['settings'] ?? null;
                if (!is_array($settingsData)) {
                    $settingsData = is_string($settingsData) && $settingsData !== ''
                        ? ['note' => $settingsData]
                        : [];
                }
                if (empty($settingsData)) {
                    $settingsData['note'] = 'Initial version';
                }
                $template->versions()->create([
                    'version' => 1,
                    'template_code' => $data['template_code'],
                    'settings' => $settingsData,
                    'created_by' => $userId,
                ]);
            }
            
            Log::info('Voucher template created', ['template_id' => $template->id, 'user_id' => $userId]);
            
            return $template;
        });
    }
}
