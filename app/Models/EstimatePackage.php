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
        $totalMaterialCost = 0;
        $totalLaborCost = 0;
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

        foreach ($this->assemblies as $assembly) {
            foreach ($assembly->items as $item) {
                // Calculate material costs
                $materialCost = $item->material_cost_rate * $item->quantity;
                $materialCharge = $item->material_charge_rate * $item->quantity;
                
                // Calculate labor costs (convert minutes to hours)
                $laborHours = ($item->labor_units * $item->quantity) / 60;
                
                // Use item's labor rate if set, otherwise use primary labor rate
                $laborRate = $item->laborRate ?? $primaryLaborRate;
                $laborCost = $laborHours * $laborRate->cost_rate;
                $laborCharge = $laborHours * $laborRate->charge_rate;
                
                // Multiply by assembly quantity
                $totalMaterialCost += $materialCost * $assembly->quantity;
                $totalLaborCost += $laborCost * $assembly->quantity;
                $totalMaterialCharge += $materialCharge * $assembly->quantity;
                $totalLaborCharge += $laborCharge * $assembly->quantity;
            }
        }

        // Multiply by package quantity
        $totalMaterialCost *= $this->quantity;
        $totalLaborCost *= $this->quantity;
        $totalMaterialCharge *= $this->quantity;
        $totalLaborCharge *= $this->quantity;

        return [
            'total_cost' => $totalMaterialCost + $totalLaborCost,
            'total_charge' => $totalMaterialCharge + $totalLaborCharge
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
}
