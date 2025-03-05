<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    protected $fillable = [
        'tenant_id',
        'default_material_markup',
        'default_labor_markup',
        'primary_labor_rate'
    ];

    protected $casts = [
        'default_material_markup' => 'decimal:2',
        'default_labor_markup' => 'decimal:2',
        'primary_labor_rate' => 'decimal:2'
    ];

    public static function getPrimary()
    {
        return static::where('tenant_id', auth()->user()->current_tenant_id)->first();
    }

    public static function getPrimaryLaborRate()
    {
        $primary = static::getPrimary();
        return $primary ? $primary->primary_labor_rate : 50.00;
    }

    public static function getDefaultMaterialMarkup()
    {
        $primary = static::getPrimary();
        return $primary ? $primary->default_material_markup : 1.25;
    }

    public static function getDefaultLaborMarkup()
    {
        $primary = static::getPrimary();
        return $primary ? $primary->default_labor_markup : 1.25;
    }
} 