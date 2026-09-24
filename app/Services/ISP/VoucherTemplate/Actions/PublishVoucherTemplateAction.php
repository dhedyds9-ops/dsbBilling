<?php

namespace App\Services\ISP\VoucherTemplate\Actions;

use App\Models\ISP\VoucherTemplate;
use App\Models\ISP\VoucherTemplateVersion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PublishVoucherTemplateAction
{
    public function execute(VoucherTemplate $template, int $userId): VoucherTemplate
    {
        // Currently, publishing just means setting it to active.
        return DB::transaction(function () use ($template, $userId) {
            $template->activate();
            Log::info('Voucher template published', ['template_id' => $template->id, 'user_id' => $userId]);
            return $template;
        });
    }
}
