<?php

namespace App\Models\ACS;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProvisionTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'acs_provision_templates';

    protected $fillable = [
        'uuid',
        'name',
        'vendor',
        'model',
        'firmware',
        'tr069_script',
        'tr181_script',
        'config_json',
        'description',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tr069_script' => 'array',
        'tr181_script' => 'array',
        'config_json' => 'array',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
