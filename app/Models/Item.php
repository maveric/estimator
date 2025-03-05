<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'category_id',
        'name',
        'sku',
        'description',
        'material_cost_rate',
        'material_charge_rate',
        'labor_units',
        'unit_of_measure',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'material_cost_rate' => 'decimal:2',
        'material_charge_rate' => 'decimal:2',
        'labor_units' => 'decimal:2',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function assemblies()
    {
        return $this->belongsToMany(Assembly::class, 'assembly_items')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function estimateItems()
    {
        return $this->hasMany(EstimateItem::class, 'original_item_id');
    }

    /**
     * Get the category that owns the item.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the categories for the item.
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_item')
                    ->withPivot('tenant_id')
                    ->withTimestamps();
    }

    /**
     * Get the labor rate associated with this item.
     */
    public function laborRate()
    {
        return $this->belongsTo(LaborRate::class);
    }

    /**
     * Calculate the total cost of the item.
     */
    public function calculateCost($quantity = 1): array
    {
        // Material costs
        $materialCost = $this->material_cost_rate * $quantity;
        $materialCharge = $this->material_charge_rate * $quantity;
        
        // Labor costs
        $laborUnits = $this->labor_units * $quantity;
        $laborRate = $this->laborRate;
        
        $laborCost = $laborUnits * ($laborRate ? $laborRate->cost_rate : 0);
        $laborCharge = $laborUnits * ($laborRate ? $laborRate->charge_rate : 0);
        
        return [
            'material_cost' => $materialCost,
            'labor_cost' => $laborCost,
            'material_charge' => $materialCharge,
            'labor_charge' => $laborCharge,
            'total_cost' => $materialCost + $laborCost,
            'total_charge' => $materialCharge + $laborCharge,
        ];
    }
}
