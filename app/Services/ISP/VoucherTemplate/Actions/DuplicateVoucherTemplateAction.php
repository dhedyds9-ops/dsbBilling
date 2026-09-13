<?php

namespace App\Services\ISP\VoucherTemplate\Actions;

use App\Models\ISP\VoucherTemplate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class DuplicateVoucherTemplateAction
{
    /**
     * @throws Throwable
     */
    public function execute(VoucherTemplate $template, string $newName, int $userId): VoucherTemplate
    {
        return DB::transaction(function () use ($template, $newName, $userId) {
            $newTemplate = $template->replicate(['is_system', 'is_default']);
            $newTemplate->name = $newName;
            $newTemplate->is_system = false;
            $newTemplate->is_default = false;
            $newTemplate->created_by = $userId;
            $newTemplate->slug = null; // will be regenerated
            $newTemplate->save();
            
            $latestVersion = $template->latestVersion;
            if ($latestVersion) {
                $newTemplate->versions()->create([
                    'version' => 1,
                    'template_code' => $latestVersion->template_code,
                    'settings' => ['note' => 'Duplicated from ' . $template->name],
                    'created_by' => $userId,
                ]);
            }
            
            foreach ($template->assets as $asset) {
                $newAsset = $asset->replicate(['template_id']);
                $newAsset->template_id = $newTemplate->id;
                $newAsset->save();
            }
            
            Log::info('Voucher template duplicated', ['old_id' => $template->id, 'new_id' => $newTemplate->id, 'user_id' => $userId]);
            
            return $newTemplate;
        });
    }
}
