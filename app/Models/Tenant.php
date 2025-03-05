<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'subdomain',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    // Relationships
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function laborRates()
    {
        return $this->hasMany(LaborRate::class);
    }

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function assemblies()
    {
        return $this->hasMany(Assembly::class);
    }

    /**
     * Get the packages for the tenant.
     */
    public function packages()
    {
        return $this->hasMany(Package::class);
    }

    /**
     * Get the categories for the tenant.
     */
    public function categories()
    {
        // Implementation of categories method
    }

    public function estimates()
    {
        return $this->hasMany(Estimate::class);
    }
}
