<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Item extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'team_id',
        'name',
        'description',
        'sku',
        'unit_of_measure',
        'material_cost',
        'material_price',
        'labor_minutes',
        'labor_rate_id',
        'is_template',
        'is_active',
    ];

    protected $casts = [
        'material_cost' => 'decimal:4',
        'material_price' => 'decimal:4',
        'labor_minutes' => 'decimal:4',
        'is_template' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Activity Logging
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'description', 'sku', 'unit_of_measure', 'material_cost', 'material_price', 'labor_minutes', 'labor_rate_id', 'is_template', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Item has been {$eventName}")
            ->useLogName('item');
    }

    // Relationships
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function laborRate()
    {
        return $this->belongsTo(LaborRate::class);
    }

    public function assemblies()
    {
        return $this->belongsToMany(Assembly::class)
            ->withPivot('quantity')
            ->withTimestamps();
    }

    // Scopes
    public function scopeForTeam(Builder $query, $teamId): Builder
    {
        return $query->where('team_id', $teamId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeTemplate(Builder $query): Builder
    {
        return $query->where('is_template', true);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    // Calculated attributes
    public function getLaborCostAttribute()
    {
        return ($this->labor_minutes / 60) * ($this->laborRate?->cost_rate ?? 0);
    }

    public function getLaborPriceAttribute()
    {
        return ($this->labor_minutes / 60) * ($this->laborRate?->price_rate ?? 0);
    }

    public function getTotalCostAttribute()
    {
        return $this->material_cost + $this->labor_cost;
    }

    public function getTotalPriceAttribute()
    {
        return $this->material_price + $this->labor_price;
    }

    // Helper Methods
    public function calculateLaborCost(float $hourlyRate): float
    {
        return round(($this->labor_minutes / 60) * $hourlyRate, 4);
    }

    public function calculateLaborPrice(float $hourlyRate): float
    {
        return round(($this->labor_minutes / 60) * $hourlyRate, 4);
    }

    public function updateLaborRates(float $costRate, float $priceRate): void
    {
        $this->labor_cost = $this->calculateLaborCost($costRate);
        $this->labor_price = $this->calculateLaborPrice($priceRate);
        $this->save();
    }
} 