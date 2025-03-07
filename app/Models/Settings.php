<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    protected $fillable = [
        'tenant_id'
    ];

    public static function getPrimary()
    {
        return static::where('tenant_id', auth()->user()->current_tenant_id)->first();
    }
} 