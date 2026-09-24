<?php

namespace App\Models\ISP;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnuParameterMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'onu_id',
        'semantic_key',
        'actual_path',
    ];

    public function onu()
    {
        return $this->belongsTo(Onu::class);
    }
}
