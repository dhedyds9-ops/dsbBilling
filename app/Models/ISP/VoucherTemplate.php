<?php

declare(strict_types=1);

namespace App\Models\ISP;

use App\Enums\ISP\VoucherTemplateCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class VoucherTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'voucher_templates';

    protected $fillable = [
        'name',
        'slug',
        'category',
        'description',
        'preview_image',
        'is_system',
        'is_active',
        'is_default',
        'created_by',
    ];

    protected $casts = [
        'category' => VoucherTemplateCategory::class,
        'is_system' => 'boolean',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($template) {
            if (empty($template->slug)) {
                $template->slug = Str::slug($template->name);
            }
        });

        static::deleting(function ($template) {
            $template->versions()->delete();
            $template->assets()->delete();
        });
    }

    public function versions(): HasMany
    {
        return $this->hasMany(VoucherTemplateVersion::class);
    }

    public function latestVersion(): HasOne
    {
        return $this->hasOne(VoucherTemplateVersion::class)->latestOfMany('version');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assets(): HasMany
    {
        return $this->hasMany(VoucherTemplateAsset::class);
    }

    public function activate(): self
    {
        $this->is_active = true;
        $this->save();

        return $this;
    }

    public function deactivate(): self
    {
        $this->is_active = false;
        $this->save();

        return $this;
    }

    public function setAsDefault(): self
    {
        static::where('category', $this->category->value)
            ->where('is_default', true)
            ->whereNot('id', $this->id)
            ->update(['is_default' => false]);

        $this->is_default = true;
        $this->save();

        return $this;
    }

    public function duplicate(int $userId): self
    {
        $newTemplate = $this->replicate();
        $newTemplate->name = $this->name . ' (Copy)';
        $newTemplate->slug = Str::slug($newTemplate->name) . '-' . Str::random(6);
        $newTemplate->is_system = false;
        $newTemplate->is_default = false;
        $newTemplate->created_by = $userId;
        $newTemplate->save();

        foreach ($this->versions as $version) {
            $newVersion = $version->replicate();
            $newVersion->voucher_template_id = $newTemplate->id;
            $newVersion->created_by = $userId;
            $newVersion->save();
        }

        foreach ($this->assets as $asset) {
            $newAsset = $asset->replicate();
            $newAsset->voucher_template_id = $newTemplate->id;
            $newAsset->created_by = $userId;
            $newAsset->save();
        }

        return $newTemplate->fresh();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    public function scopeCustom($query)
    {
        return $query->where('is_system', false);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeByCategory($query, VoucherTemplateCategory|string $category)
    {
        return $query->where('category', $category instanceof VoucherTemplateCategory ? $category->value : $category);
    }
}
