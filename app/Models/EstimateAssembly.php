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
        $totalMaterialCost = 0;
        $totalLaborCost = 0;

        // Get the primary labor rate
        $primaryLaborRate = LaborRate::where('is_primary', true)
            ->where('tenant_id', $this->tenant_id)
            ->active()
            ->first();
            
        if (!$primaryLaborRate) {
            throw new \RuntimeException('No primary labor rate found');
        }

        foreach ($this->items as $item) {
            // Calculate material costs
            $materialCost = $item->material_cost_rate * $item->quantity;
            
            // Calculate labor costs (convert minutes to hours)
            $laborHours = ($item->labor_units * $item->quantity) / 60;
            
            // Use item's labor rate if set, otherwise use primary labor rate
            $laborRate = $item->laborRate ?? $primaryLaborRate;
            $laborCost = $laborHours * $laborRate->cost_rate;
            
            $totalMaterialCost += $materialCost;
            $totalLaborCost += $laborCost;
        }
        
        return ($totalMaterialCost + $totalLaborCost) * $this->quantity;
    }

    /**
     * Calculate the charge for this estimate assembly.
     */
    public function calculateCharge()
    {
        $totalMaterialCharge = 0;
        $totalLaborCharge = 0;

        // Get the primary labor rate
        $primaryLaborRate = LaborRate::where('is_primary', true)
            ->where('tenant_id', $this->tenant_id)
            ->active()
            ->first();
            
        if (!$primaryLaborRate) {
            throw new \RuntimeException('No primary labor rate found');
        }

        foreach ($this->items as $item) {
            // Calculate material charges
            $materialCharge = $item->material_charge_rate * $item->quantity;
            
            // Calculate labor charges (convert minutes to hours)
            $laborHours = ($item->labor_units * $item->quantity) / 60;
            
            // Use item's labor rate if set, otherwise use primary labor rate
            $laborRate = $item->laborRate ?? $primaryLaborRate;
            $laborCharge = $laborHours * $laborRate->charge_rate;
            
            $totalMaterialCharge += $materialCharge;
            $totalLaborCharge += $laborCharge;
        }
        
        return ($totalMaterialCharge + $totalLaborCharge) * $this->quantity;
    }

    /**
     * Get the total material cost.
     */
    public function getTotalMaterialCostAttribute()
    {
        $totalMaterialCost = 0;

        foreach ($this->items as $item) {
            $materialCost = $item->material_cost_rate * $item->quantity;
            $totalMaterialCost += $materialCost;
        }
        
        return $totalMaterialCost * $this->quantity;
    }

    /**
     * Get the total material charge.
     */
    public function getTotalMaterialChargeAttribute()
    {
        $totalMaterialCharge = 0;

        foreach ($this->items as $item) {
            $materialCharge = $item->material_charge_rate * $item->quantity;
            $totalMaterialCharge += $materialCharge;
        }
        
        return $totalMaterialCharge * $this->quantity;
    }

    /**
     * Get the total labor units.
     */
    public function getTotalLaborUnitsAttribute()
    {
        $totalLaborUnits = 0;

        foreach ($this->items as $item) {
            $laborUnits = $item->labor_units * $item->quantity;
            $totalLaborUnits += $laborUnits;
        }
        
        return $totalLaborUnits * $this->quantity;
    }

    /**
     * Get the total labor cost.
     */
    public function getTotalLaborCostAttribute()
    {
        $totalLaborCost = 0;

        // Get the primary labor rate
        $primaryLaborRate = LaborRate::where('is_primary', true)
            ->where('tenant_id', $this->tenant_id)
            ->active()
            ->first();
            
        if (!$primaryLaborRate) {
            throw new \RuntimeException('No primary labor rate found');
        }

        foreach ($this->items as $item) {
            // Calculate labor costs (convert minutes to hours)
            $laborHours = ($item->labor_units * $item->quantity) / 60;
            
            // Use item's labor rate if set, otherwise use primary labor rate
            $laborRate = $item->laborRate ?? $primaryLaborRate;
            $laborCost = $laborHours * $laborRate->cost_rate;
            
            $totalLaborCost += $laborCost;
        }
        
        return $totalLaborCost * $this->quantity;
    }

    /**
     * Get the total labor charge.
     */
    public function getTotalLaborChargeAttribute()
    {
        $totalLaborCharge = 0;

        // Get the primary labor rate
        $primaryLaborRate = LaborRate::where('is_primary', true)
            ->where('tenant_id', $this->tenant_id)
            ->active()
            ->first();
            
        if (!$primaryLaborRate) {
            throw new \RuntimeException('No primary labor rate found');
        }

        foreach ($this->items as $item) {
            // Calculate labor charges (convert minutes to hours)
            $laborHours = ($item->labor_units * $item->quantity) / 60;
            
            // Use item's labor rate if set, otherwise use primary labor rate
            $laborRate = $item->laborRate ?? $primaryLaborRate;
            $laborCharge = $laborHours * $laborRate->charge_rate;
            
            $totalLaborCharge += $laborCharge;
        }
        
        return $totalLaborCharge * $this->quantity;
    }

    /**
     * Get the total cost.
     */
    public function getTotalCostAttribute()
    {
        return $this->calculateCost();
    }

    /**
     * Get the total charge.
     */
    public function getTotalChargeAttribute()
    {
        return $this->calculateCharge();
    }
}
