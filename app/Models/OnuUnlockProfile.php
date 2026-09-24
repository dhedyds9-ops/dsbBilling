<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class OnuUnlockProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid', 'name', 'vendor', 'model', 'hardware_version', 'firmware_range',
        'method', 'required_parameters', 'required_configuration', 'acs_firmware_id',
        'reboot_required', 'verification_rules', 'risk_level', 'approval_policy',
        'is_enabled', 'created_by', 'updated_by'
    ];

    protected $casts = [
        'required_parameters' => 'array',
        'required_configuration' => 'array',
        'verification_rules' => 'array',
        'reboot_required' => 'boolean',
        'is_enabled' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function firmware()
    {
        return $this->belongsTo(AcsFirmware::class, 'acs_firmware_id');
    }
}
