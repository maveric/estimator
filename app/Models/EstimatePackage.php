<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EstimatePackage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'estimate_id',
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
     * Get the tenant that owns the estimate package.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the estimate that owns the package.
     */
    public function estimate(): BelongsTo
    {
        return $this->belongsTo(Estimate::class);
    }

    /**
     * Get the original package.
     */
    public function originalPackage(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'original_package_id');
    }

    /**
     * Get the package that this estimate package is based on.
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * Get the assemblies for this estimate package.
     */
    public function assemblies(): HasMany
    {
        return $this->hasMany(EstimateAssembly::class);
    }

    /**
     * Calculate the total cost of the package.
     */
    public function calculateCost(): array
    {
        $totalMaterialCost = 0;
        $totalLaborCost = 0;
        $totalMaterialCharge = 0;
        $totalLaborCharge = 0;

        foreach ($this->assemblies as $assembly) {
            $totalMaterialCost += $assembly->total_material_cost;
            $totalLaborCost += $assembly->total_labor_cost;
            $totalMaterialCharge += $assembly->total_material_charge;
            $totalLaborCharge += $assembly->total_labor_charge;
        }

        return [
            'material_cost' => $totalMaterialCost,
            'labor_cost' => $totalLaborCost,
            'material_charge' => $totalMaterialCharge,
            'labor_charge' => $totalLaborCharge,
            'total_cost' => $totalMaterialCost + $totalLaborCost,
            'total_charge' => $totalMaterialCharge + $totalLaborCharge,
        ];
    }

    /**
     * Get the total material cost.
     */
    public function getTotalMaterialCostAttribute()
    {
        return $this->assemblies->sum('total_material_cost') * $this->quantity;
    }

    /**
     * Get the total material charge.
     */
    public function getTotalMaterialChargeAttribute()
    {
        return $this->assemblies->sum('total_material_charge') * $this->quantity;
    }

    /**
     * Get the total labor cost.
     */
    public function getTotalLaborCostAttribute()
    {
        return $this->assemblies->sum('total_labor_cost') * $this->quantity;
    }

    /**
     * Get the total labor charge.
     */
    public function getTotalLaborChargeAttribute()
    {
        return $this->assemblies->sum('total_labor_charge') * $this->quantity;
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
