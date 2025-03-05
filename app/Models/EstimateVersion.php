<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstimateVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'estimate_id',
        'version_number',
        'snapshot',
    ];

    protected $casts = [
        'snapshot' => 'array',
        'version_number' => 'integer',
    ];

    /**
     * Get the estimate that owns the version.
     */
    public function estimate()
    {
        return $this->belongsTo(Estimate::class);
    }
} 