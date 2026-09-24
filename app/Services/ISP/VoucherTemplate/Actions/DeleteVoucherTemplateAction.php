<?php

namespace App\Services\ISP\VoucherTemplate\Actions;

use App\Models\ISP\VoucherTemplate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class DeleteVoucherTemplateAction
{
    /**
     * @throws Throwable
     */
    public function execute(VoucherTemplate $template, int $userId): void
    {
        if ($template->is_system) {
            throw new \Exception('System templates cannot be deleted.');
        }

        DB::transaction(function () use ($template, $userId) {
            $templateId = $template->id;
            $template->delete(); // Soft deletes if model uses it, boot method handles relations
            
            Log::info('Voucher template deleted', ['template_id' => $templateId, 'user_id' => $userId]);
        });
    }
}
