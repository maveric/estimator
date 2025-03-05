<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'is_active',
        'tenant_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the tenant that owns the package.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * The assemblies that belong to the package.
     */
    public function assemblies(): BelongsToMany
    {
        return $this->belongsToMany(Assembly::class, 'package_assemblies')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    public function estimatePackages()
    {
        return $this->hasMany(EstimatePackage::class, 'original_package_id');
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
            $quantity = $assembly->pivot->quantity;
            $assemblyCosts = $assembly->calculateCost();
            
            $totalMaterialCost += $assemblyCosts['material_cost'] * $quantity;
            $totalLaborCost += $assemblyCosts['labor_cost'] * $quantity;
            $totalMaterialCharge += $assemblyCosts['material_charge'] * $quantity;
            $totalLaborCharge += $assemblyCosts['labor_charge'] * $quantity;
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
     * Scope a query to only include packages for a specific tenant.
     */
    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }
}
