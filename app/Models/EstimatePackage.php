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
     * Calculate the total cost and charge of the package.
     */
    public function calculateCost(): array
    {
        $materialCost = 0;
        $laborCost = 0;
        $materialCharge = 0;
        $laborCharge = 0;

        // Sum up assembly totals (which already include their quantities)
        foreach ($this->assemblies as $assembly) {
            $assemblyTotals = $assembly->calculateTotals();
            $materialCost += $assemblyTotals['material_cost'];
            $materialCharge += $assemblyTotals['material_charge'];
            $laborCost += $assemblyTotals['labor_cost'];
            $laborCharge += $assemblyTotals['labor_charge'];
        }

        return [
            'total_cost' => $materialCost + $laborCost,
            'total_charge' => $materialCharge + $laborCharge
        ];
    }

    /**
     * Get the total cost.
     */
    public function getTotalCostAttribute()
    {
        $costs = $this->calculateCost();
        return $costs['total_cost'];
    }

    /**
     * Get the total charge.
     */
    public function getTotalChargeAttribute()
    {
        $costs = $this->calculateCost();
        return $costs['total_charge'];
    }

    /**
     * Calculate totals for the package, including material and labor costs/charges.
     */
    public function calculateTotals(): array
    {
        $materialCost = 0;
        $laborCost = 0;
        $materialCharge = 0;
        $laborCharge = 0;

        // Sum up assembly totals (which already include their quantities)
        foreach ($this->assemblies as $assembly) {
            $assemblyTotals = $assembly->calculateTotals();
            $materialCost += $assemblyTotals['material_cost'];
            $materialCharge += $assemblyTotals['material_charge'];
            $laborCost += $assemblyTotals['labor_cost'];
            $laborCharge += $assemblyTotals['labor_charge'];
        }

        return [
            'material_cost' => $materialCost,
            'material_charge' => $materialCharge,
            'labor_cost' => $laborCost,
            'labor_charge' => $laborCharge
        ];
    }
}
