<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Package extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'team_id',
        'name',
        'description',
        'sku',
        'is_template',
        'is_active',
    ];

    protected $casts = [
        'is_template' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'description', 'sku', 'is_template', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Package has been {$eventName}");
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function assemblies()
    {
        return $this->belongsToMany(Assembly::class)
            ->withPivot('quantity')
            ->withTimestamps();
    }

    // Scopes
    public function scopeForTeam($query, $teamId)
    {
        return $query->where('team_id', $teamId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeTemplate($query)
    {
        return $query->where('is_template', true);
    }

    // Calculated attributes
    public function getTotalMaterialCostAttribute()
    {
        return $this->assemblies->sum(function ($assembly) {
            return $assembly->total_material_cost * $assembly->pivot->quantity;
        });
    }

    public function getTotalMaterialPriceAttribute()
    {
        return $this->assemblies->sum(function ($assembly) {
            return $assembly->total_material_price * $assembly->pivot->quantity;
        });
    }

    public function getTotalLaborHoursAttribute()
    {
        return round($this->assemblies->sum(function ($assembly) {
            return $assembly->total_labor_hours * $assembly->pivot->quantity;
        }), 4);
    }

    public function getTotalLaborCostAttribute()
    {
        return $this->assemblies->sum(function ($assembly) {
            return $assembly->total_labor_cost * $assembly->pivot->quantity;
        });
    }

    public function getTotalLaborPriceAttribute()
    {
        return $this->assemblies->sum(function ($assembly) {
            return $assembly->total_labor_price * $assembly->pivot->quantity;
        });
    }

    public function getTotalCostAttribute()
    {
        return $this->total_material_cost + $this->total_labor_cost;
    }

    public function getTotalPriceAttribute()
    {
        return $this->total_material_price + $this->total_labor_price;
    }
}
