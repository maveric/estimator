<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstimateAssemblyItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'estimate_assembly_id',
        'item_id',
        'quantity',
        'original_cost_rate',
        'original_charge_rate',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'original_cost_rate' => 'decimal:2',
        'original_charge_rate' => 'decimal:2',
    ];

    /**
     * Get the estimate assembly that owns the item.
     */
    public function estimateAssembly()
    {
        return $this->belongsTo(EstimateAssembly::class);
    }

    /**
     * Get the original item.
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Calculate the cost for this estimate assembly item.
     */
    public function calculateCost()
    {
        return $this->quantity * $this->original_cost_rate;
    }

    /**
     * Calculate the charge for this estimate assembly item.
     */
    public function calculateCharge()
    {
        return $this->quantity * $this->original_charge_rate;
    }
} 