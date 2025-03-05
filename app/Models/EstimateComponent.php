<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class EstimateComponent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'estimate_package_id',
        'estimate_id',
        'name',
        'description',
        'quantity',
        'original_component_id',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if (($model->estimate_package_id && $model->estimate_id) || 
                (!$model->estimate_package_id && !$model->estimate_id)) {
                throw new \InvalidArgumentException('Component must belong to either a package or estimate, but not both.');
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function estimatePackage()
    {
        return $this->belongsTo(EstimatePackage::class);
    }

    public function originalComponent()
    {
        return $this->belongsTo(Component::class, 'original_component_id');
    }

    public function items()
    {
        return $this->hasMany(EstimateItem::class);
    }

    public function estimate()
    {
        return $this->belongsTo(Estimate::class);
    }
}
