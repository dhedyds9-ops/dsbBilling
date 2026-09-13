<?php

namespace App\Services\ISP\VoucherTemplate\Actions;

use App\Models\ISP\VoucherTemplate;
use App\Models\ISP\VoucherTemplateVersion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RollbackVoucherTemplateAction
{
    public function execute(VoucherTemplate $template, VoucherTemplateVersion $version, int $userId): VoucherTemplate
    {
        if ($template->is_system) {
            throw new \Exception('Cannot rollback system templates.');
        }
        
        return DB::transaction(function () use ($template, $version, $userId) {
            $latestVersion = $template->versions()->max('version') ?? 0;
            
            $template->versions()->create([
                'version' => $latestVersion + 1,
                'template_code' => $version->template_code,
                'settings' => ['note' => 'Rolled back to version ' . $version->version],
                'created_by' => $userId,
            ]);
            
            Log::info('Voucher template rolled back', ['template_id' => $template->id, 'rolled_back_to' => $version->version, 'user_id' => $userId]);
            
            return $template;
        });
    }
}
