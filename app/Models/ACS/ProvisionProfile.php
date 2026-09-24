<?php

namespace App\Models\ACS;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProvisionProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'acs_provision_profiles';

    protected $fillable = [
        'uuid',
        'name',
        'vendor',
        'model',
        'provision_template_id',
        'description',
        'status',
        'created_by',
        'updated_by',
    ];

    public function template()
    {
        return $this->belongsTo(ProvisionTemplate::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
