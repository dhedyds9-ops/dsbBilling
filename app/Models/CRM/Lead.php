<?php

namespace App\Models\CRM;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'phone',
        'email',
        'address',
        'province',
        'city',
        'district',
        'village',
        'source',
        'status',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [];

    public function prospect()
    {
        return $this->hasOne(Prospect::class);
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
