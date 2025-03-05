<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EstimateItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'estimate_id',
        'estimate_assembly_id',
        'item_id',
        'original_item_id',
        'labor_rate_id',
        'name',
        'description',
        'unit_of_measure',
        'quantity',
        'material_cost_rate',
        'material_charge_rate',
        'labor_units',
        'original_cost_rate',
        'original_charge_rate',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'material_cost_rate' => 'decimal:2',
        'material_charge_rate' => 'decimal:2',
        'labor_units' => 'decimal:2',
        'original_cost_rate' => 'decimal:2',
        'original_charge_rate' => 'decimal:2',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if (($model->estimate_id && $model->estimate_assembly_id) || 
                (!$model->estimate_id && !$model->estimate_assembly_id)) {
                throw new \InvalidArgumentException('Item must belong to either an estimate or an estimate assembly, but not both.');
            }
        });
    }

    /**
     * Get the tenant that owns the estimate item.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the estimate that owns the item.
     */
    public function estimate()
    {
        return $this->belongsTo(Estimate::class);
    }

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
    public function originalItem()
    {
        return $this->belongsTo(Item::class, 'original_item_id');
    }

    /**
     * Get the item this estimate item is based on.
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get the labor rate for this item.
     */
    public function laborRate()
    {
        return $this->belongsTo(LaborRate::class);
    }

    /**
     * Get the total material cost.
     */
    public function getTotalMaterialCostAttribute()
    {
        $assemblyQuantity = $this->estimateAssembly ? $this->estimateAssembly->quantity : 1;
        return $this->material_cost_rate * $this->quantity * $assemblyQuantity;
    }

    /**
     * Get the total material charge.
     */
    public function getTotalMaterialChargeAttribute()
    {
        $assemblyQuantity = $this->estimateAssembly ? $this->estimateAssembly->quantity : 1;
        return $this->material_charge_rate * $this->quantity * $assemblyQuantity;
    }

    /**
     * Get the total labor units.
     */
    public function getTotalLaborUnitsAttribute()
    {
        $assemblyQuantity = $this->estimateAssembly ? $this->estimateAssembly->quantity : 1;
        return $this->labor_units * $this->quantity * $assemblyQuantity;
    }

    /**
     * Get the total labor cost.
     */
    public function getTotalLaborCostAttribute()
    {
        if (!$this->laborRate) return 0;
        // Convert minutes to hours for hourly rate calculation
        return ($this->total_labor_units / 60) * $this->laborRate->cost_rate;
    }

    /**
     * Get the total labor charge.
     */
    public function getTotalLaborChargeAttribute()
    {
        if (!$this->laborRate) return 0;
        // Convert minutes to hours for hourly rate calculation
        return ($this->total_labor_units / 60) * $this->laborRate->charge_rate;
    }

    /**
     * Get the total cost.
     */
    public function getTotalCostAttribute()
    {
        return $this->total_material_cost + $this->total_labor_cost;
    }

    /**
     * Get the total charge.
     */
    public function getTotalChargeAttribute()
    {
        return $this->total_material_charge + $this->total_labor_charge;
    }

    /**
     * Calculate the cost for this estimate item.
     */
    public function calculateCost()
    {
        return $this->quantity * $this->original_cost_rate;
    }

    /**
     * Calculate the charge for this estimate item.
     */
    public function calculateCharge()
    {
        return $this->quantity * $this->original_charge_rate;
    }
}
