<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Component extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
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

    public function items()
    {
        return $this->belongsToMany(Item::class, 'component_items')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function packages()
    {
        return $this->belongsToMany(Package::class, 'package_components')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function estimateComponents()
    {
        return $this->hasMany(EstimateComponent::class, 'original_component_id');
    }
}