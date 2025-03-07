<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Assembly extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'category_id',
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the category that owns the assembly.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * The items that belong to the assembly.
     */
    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'assembly_items')
            ->withPivot('quantity', 'tenant_id')
            ->withTimestamps();
    }

    /**
     * The packages that this assembly belongs to.
     */
    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(Package::class, 'package_assemblies')
            ->withPivot('quantity', 'tenant_id')
            ->withTimestamps();
    }

    public function estimateAssemblies()
    {
        return $this->hasMany(EstimateAssembly::class, 'original_assembly_id');
    }

    /**
     * Get the categories for the assembly.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'assembly_category')
                    ->withPivot('tenant_id')
                    ->withTimestamps();
    }

    /**
     * Calculate the total cost of the assembly.
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

        foreach ($this->items as $item) {
            $quantity = $item->pivot->quantity;
            
            // Material costs
            $materialCost = $item->material_cost_rate * $quantity;
            $materialCharge = $item->material_charge_rate * $quantity;
            
            // Labor costs (convert minutes to hours)
            $laborUnits = $item->labor_units * $quantity;
            $laborHours = $laborUnits / 60;
            
            $laborCost = $laborHours * $primaryLaborRate->cost_rate;
            $laborCharge = $laborHours * $primaryLaborRate->charge_rate;
            
            $totalMaterialCost += $materialCost;
            $totalLaborCost += $laborCost;
            $totalMaterialCharge += $materialCharge;
            $totalLaborCharge += $laborCharge;
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
}