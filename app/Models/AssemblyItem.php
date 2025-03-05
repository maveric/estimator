<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class AssemblyItem extends Pivot
{
    protected $table = 'assembly_items';
    
    protected $fillable = [
        'assembly_id',
        'item_id',
        'quantity',
        'tenant_id',
    ];
    
    /**
     * Get the assembly that this item belongs to.
     */
    public function assembly()
    {
        return $this->belongsTo(Assembly::class);
    }
    
    /**
     * Get the item in this assembly.
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }
    
    /**
     * Get the tenant that this assembly item belongs to.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
} 