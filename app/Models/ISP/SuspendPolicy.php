<?php

namespace App\Models\ISP;

use App\Enums\ISP\SuspendPolicyAction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * SSOT: Dynamic Suspend Policy (bukan hardcode 128k!)
 *
 * Setiap ISP bisa membuat multiple policy:
 *  - Default 128k / Strict 64k / Portal 512k / Redirect Only / Disconnect / Disable Secret
 *
 * Per-package via service_profiles.suspend_policy_id
 * Per-pelanggan via customer_services.overridden_suspend_policy_id
 */
class SuspendPolicy extends Model
{
    use HasFactory;

    protected $table = 'suspend_policies';

    protected $fillable = [
        'name', 'slug', 'description', 'action_type',
        'rate_download_kbps', 'rate_upload_kbps',
        'burst_download_kbps', 'burst_upload_kbps',
        'burst_threshold_kbps', 'burst_time_seconds',
        'address_list', 'redirect_url',
        'is_default', 'is_active',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'action_type' => SuspendPolicyAction::class,
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'rate_download_kbps' => 'integer',
        'rate_upload_kbps' => 'integer',
    ];

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

    public static function resolveForService(CustomerService $cs): self
    {
        if (!empty($cs->overridden_suspend_policy_id)) {
            $found = self::find($cs->overridden_suspend_policy_id);
            if ($found && $found->is_active) return $found;
        }
        $sp = $cs->serviceProfile;
        if ($sp && !empty($sp->suspend_policy_id)) {
            $found = self::find($sp->suspend_policy_id);
            if ($found && $found->is_active) return $found;
        }
        return self::where('is_default', true)->active()->first()
            ?? self::active()->firstOrFail();
    }

    /**
     * Build MikroTik Rate-Limit string format: 100k/50k [burst...]
     */
    public function mikrotikRateLimitString(): ?string
    {
        if ($this->action_type !== SuspendPolicyAction::RateLimit) return null;
        $dl = (int)$this->rate_download_kbps;
        $ul = (int)$this->rate_upload_kbps;
        if ($dl <= 0 || $ul <= 0) return null;
        $base = "{$dl}k/{$ul}k";
        if ((int)$this->burst_download_kbps > 0 && (int)$this->burst_upload_kbps > 0) {
            $burstDl = (int)$this->burst_download_kbps;
            $burstUl = (int)$this->burst_upload_kbps;
            $threshold = (int)($this->burst_threshold_kbps ?? (int)(($dl + $burstDl) / 2));
            $burstTime = (int)($this->burst_time_seconds ?? 8);
            $base .= " {$burstDl}k/{$burstUl}k {$threshold}k {$burstTime}s";
        }
        return $base;
    }

    public function replyAttributes(): array
    {
        $attrs = [];
        $rl = $this->mikrotikRateLimitString();
        if (!empty($rl)) {
            $attrs['MikroTik-Rate-Limit'] = $rl;
        }
        if (!empty($this->address_list)) {
            $attrs['MikroTik-Address-List'] = $this->address_list;
        }
        return $attrs;
    }

    public function serviceProfiles(): HasMany
    {
        return $this->hasMany(ServiceProfile::class, 'suspend_policy_id');
    }
}
