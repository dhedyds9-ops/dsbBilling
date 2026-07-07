<?php

namespace App\Models\ACS;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConfigurationProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'acs_configuration_profiles';

    protected $fillable = [
        'uuid',
        'name',
        'type',
        'vendor',
        'model',
        'config',
        'description',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'config' => 'array',
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
