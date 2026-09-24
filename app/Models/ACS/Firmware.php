<?php

namespace App\Models\ACS;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Firmware extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'acs_firmwares';

    protected $fillable = [
        'uuid',
        'vendor_id',
        'model',
        'version',
        'release_date',
        'checksum',
        'download_url',
        'file_path',
        'notes',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'release_date' => 'date',
    ];

    public function vendor()
    {
        return $this->belongsTo(\App\Models\ISP\Vendor::class);
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
