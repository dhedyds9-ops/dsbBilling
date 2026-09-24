<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InstallationChecklist extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'installation_id',
        'item',
        'is_checked',
        'notes',
    ];

    protected $casts = [
        'is_checked' => 'boolean',
    ];

    public function installation()
    {
        return $this->belongsTo(Installation::class);
    }
}
