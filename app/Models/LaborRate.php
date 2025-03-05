<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LaborRate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'cost_rate',
        'charge_rate',
        'effective_from',
        'effective_to',
        'is_default',
        'is_primary'
    ];

    protected $casts = [
        'cost_rate' => 'decimal:2',
        'charge_rate' => 'decimal:2',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_default' => 'boolean',
        'is_primary' => 'boolean'
    ];

    /**
     * Get the tenant that owns the labor rate.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the items that use this labor rate.
     */
    public function items()
    {
        return $this->hasMany(Item::class);
    }

    /**
     * Get the estimate items that use this labor rate.
     */
    public function estimateItems()
    {
        return $this->hasMany(EstimateItem::class);
    }

    // Scope to get currently active labor rates
    public function scopeActive($query)
    {
        return $query->where('effective_from', '<=', now())
            ->where(function ($query) {
                $query->whereNull('effective_to')
                    ->orWhere('effective_to', '>=', now());
            });
    }

    // Scope to get labor rates active at a specific date
    public function scopeActiveAt($query, $date)
    {
        return $query->where('effective_from', '<=', $date)
            ->where(function ($query) use ($date) {
                $query->whereNull('effective_to')
                    ->orWhere('effective_to', '>=', $date);
            });
    }
}
