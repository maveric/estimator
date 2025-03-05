<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstimateAssembly extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'estimate_id',
        'estimate_package_id',
        'assembly_id',
        'original_assembly_id',
        'package_id',
        'original_package_id',
        'name',
        'description',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Check if the model has either estimate_id or estimate_package_id, but not both
            if ($model->estimate_package_id && !property_exists($model, 'estimate_package_id')) {
                // If estimate_package_id property doesn't exist, we'll skip this validation
                return;
            }
            
            if (($model->estimate_id && $model->estimate_package_id) || 
                (!$model->estimate_id && !$model->estimate_package_id && property_exists($model, 'estimate_package_id'))) {
                throw new \InvalidArgumentException('Assembly must belong to either an estimate or an estimate package, but not both.');
            }
        });
    }

    /**
     * Get the tenant that owns the estimate assembly.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the estimate that owns the assembly.
     */
    public function estimate()
    {
        return $this->belongsTo(Estimate::class);
    }

    /**
     * Get the estimate package that owns the assembly.
     */
    public function estimatePackage()
    {
        return $this->belongsTo(EstimatePackage::class);
    }

    /**
     * Get the original assembly.
     */
    public function originalAssembly()
    {
        return $this->belongsTo(Assembly::class, 'original_assembly_id');
    }

    /**
     * Get the assembly that this estimate assembly is based on.
     */
    public function assembly(): BelongsTo
    {
        return $this->belongsTo(Assembly::class);
    }

    /**
     * Get the package that this estimate assembly is based on.
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * Get the items for this estimate assembly.
     */
    public function items()
    {
        return $this->hasMany(EstimateItem::class);
    }

    /**
     * Calculate the cost for this estimate assembly.
     */
    public function calculateCost()
    {
        $itemsCost = $this->items->sum(function ($item) {
            return $item->calculateCost();
        });
        
        return $itemsCost * $this->quantity;
    }

    /**
     * Calculate the charge for this estimate assembly.
     */
    public function calculateCharge()
    {
        $itemsCharge = $this->items->sum(function ($item) {
            return $item->calculateCharge();
        });
        
        return $itemsCharge * $this->quantity;
    }

    /**
     * Get the total material cost.
     */
    public function getTotalMaterialCostAttribute()
    {
        return $this->items->sum('total_material_cost') * $this->quantity;
    }

    /**
     * Get the total material charge.
     */
    public function getTotalMaterialChargeAttribute()
    {
        return $this->items->sum('total_material_charge') * $this->quantity;
    }

    /**
     * Get the total labor units.
     */
    public function getTotalLaborUnitsAttribute()
    {
        return $this->items->sum('total_labor_units') * $this->quantity;
    }

    /**
     * Get the total labor cost.
     */
    public function getTotalLaborCostAttribute()
    {
        return $this->items->sum('total_labor_cost') * $this->quantity;
    }

    /**
     * Get the total labor charge.
     */
    public function getTotalLaborChargeAttribute()
    {
        return $this->items->sum('total_labor_charge') * $this->quantity;
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
}
