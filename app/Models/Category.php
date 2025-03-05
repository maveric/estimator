<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'is_active',
    ];

    /**
     * Get the items that belong to this category.
     */
    public function items()
    {
        return $this->belongsToMany(Item::class, 'category_item')
                    ->withPivot('tenant_id')
                    ->withTimestamps();
    }

    /**
     * Get the assemblies that belong to this category.
     */
    public function assemblies()
    {
        return $this->belongsToMany(Assembly::class, 'assembly_category')
                    ->withPivot('tenant_id')
                    ->withTimestamps();
    }

    /**
     * Scope a query to only include categories for the current tenant.
     */
    public function scopeForCurrentTenant($query)
    {
        return $query->where('tenant_id', auth()->user()->tenant_id);
    }

    /**
     * Scope a query to only include active categories.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
} 