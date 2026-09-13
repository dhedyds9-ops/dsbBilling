<?php

declare(strict_types=1);

namespace App\Models\ISP;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoucherTemplateVersion extends Model
{
    use HasFactory;

    protected $table = 'voucher_template_versions';

    protected $fillable = [
        'voucher_template_id',
        'version',
        'template_code',
        'css_code',
        'js_code',
        'variables_schema',
        'settings',
        'created_by',
    ];

    protected $casts = [
        'variables_schema' => 'array',
        'settings' => 'array',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(VoucherTemplate::class, 'voucher_template_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function renderPreview(): string
    {
        $html = $this->template_code ?? '';
        $css = $this->css_code ?? '';
        $js = $this->js_code ?? '';

        $styleTag = $css ? "<style>\n{$css}\n</style>\n" : '';
        $scriptTag = $js ? "<script>\n{$js}\n</script>\n" : '';

        return "<!DOCTYPE html>\n<html>\n<head>\n<meta charset=\"UTF-8\">\n<title>Template Preview</title>\n{$styleTag}</head>\n<body>\n{$html}\n{$scriptTag}</body>\n</html>";
    }

    public function restoreAsCurrent(int $userId): self
    {
        $latestVersion = $this->template->latestVersion;
        $newVersionNumber = $latestVersion ? $latestVersion->version + 1 : 1;

        $newVersion = new self();
        $newVersion->voucher_template_id = $this->voucher_template_id;
        $newVersion->version = $newVersionNumber;
        $newVersion->template_code = $this->template_code;
        $newVersion->css_code = $this->css_code;
        $newVersion->js_code = $this->js_code;
        $newVersion->variables_schema = $this->variables_schema;
        $newVersion->settings = $this->settings;
        $newVersion->created_by = $userId;
        $newVersion->save();

        return $newVersion;
    }
}
