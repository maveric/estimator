<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Estimate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'estimate_number',
        'name',
        'description',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_address',
        'status',
        'markup_percentage',
        'discount_percentage',
        'discount_amount',
        'notes',
        'total_cost',
        'total_charge',
        'valid_until',
        'version',
        'is_temporary',
        'labor_rate_id',
    ];

    protected $casts = [
        'markup_percentage' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'valid_until' => 'date',
        'version' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($estimate) {
            if (!$estimate->estimate_number) {
                $estimate->estimate_number = static::getNextEstimateNumber($estimate->tenant_id);
            }
        });
    }

    public static function getNextEstimateNumber($tenantId)
    {
        return DB::transaction(function () use ($tenantId) {
            // First try to create the sequence if it doesn't exist
            try {
                DB::table('tenant_estimate_sequences')->insertOrIgnore([
                    'tenant_id' => $tenantId,
                    'last_estimate_number' => 0
                ]);
            } catch (\Exception $e) {
                // Ignore any errors, as the sequence might have been created by another process
            }

            // Now get the sequence with a lock
            $sequence = DB::table('tenant_estimate_sequences')
                ->where('tenant_id', $tenantId)
                ->lockForUpdate()
                ->first();

            if (!$sequence) {
                throw new \RuntimeException('Failed to create or retrieve estimate sequence');
            }

            // Get the current max estimate number including soft deleted records
            $maxNumber = static::withTrashed()
                ->where('tenant_id', $tenantId)
                ->max('estimate_number');

            // Use the higher number between the sequence and actual max number
            $nextNumber = max(($maxNumber ?? 0), $sequence->last_estimate_number) + 1;

            // Update the sequence
            DB::table('tenant_estimate_sequences')
                ->where('tenant_id', $tenantId)
                ->update(['last_estimate_number' => $nextNumber]);

            return $nextNumber;
        });
    }

    /**
     * Get the tenant that owns the estimate.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the items for the estimate.
     */
    public function items()
    {
        return $this->hasMany(EstimateItem::class);
    }

    /**
     * Get the assemblies for the estimate.
     */
    public function assemblies()
    {
        return $this->hasMany(EstimateAssembly::class);
    }

    /**
     * Get the packages for the estimate.
     */
    public function packages()
    {
        return $this->hasMany(EstimatePackage::class);
    }

    /**
     * Get the versions for the estimate.
     */
    public function versions()
    {
        return $this->hasMany(EstimateVersion::class);
    }

    /**
     * Scope a query to only include estimates for the current tenant.
     */
    public function scopeForCurrentTenant($query)
    {
        return $query->where('tenant_id', auth()->user()->tenant_id);
    }

    /**
     * Create a new version snapshot of the estimate.
     */
    public function createVersionSnapshot()
    {
        // Load relationships for the snapshot
        $this->load(['items', 'assemblies.items', 'packages.assemblies.items']);
        
        // Create a snapshot of the estimate data
        $snapshot = [
            'estimate' => $this->toArray(),
            'items' => $this->items->toArray(),
            'assemblies' => $this->assemblies->map(function ($assembly) {
                $assemblyArray = $assembly->toArray();
                $assemblyArray['items'] = $assembly->items->toArray();
                return $assemblyArray;
            })->toArray(),
            'packages' => $this->packages->map(function ($package) {
                $packageArray = $package->toArray();
                $packageArray['assemblies'] = $package->assemblies->map(function ($assembly) {
                    $assemblyArray = $assembly->toArray();
                    $assemblyArray['items'] = $assembly->items->toArray();
                    return $assemblyArray;
                })->toArray();
                return $packageArray;
            })->toArray(),
        ];
        
        // Create the version record
        $this->versions()->create([
            'version_number' => $this->version,
            'snapshot' => $snapshot,
        ]);
        
        // Increment the version number
        $this->increment('version');
        
        return $this;
    }

    /**
     * Calculate the total cost of the estimate.
     */
    public function calculateTotals()
    {
        // Calculate direct items cost and charge
        $itemsCost = $this->items->sum(function ($item) {
            return $item->calculateCost();
        });
        
        $itemsCharge = $this->items->sum(function ($item) {
            return $item->calculateCharge();
        });
        
        // Calculate assemblies cost and charge
        $assembliesCost = $this->assemblies->sum(function ($assembly) {
            return $assembly->calculateCost();
        });
        
        $assembliesCharge = $this->assemblies->sum(function ($assembly) {
            return $assembly->calculateCharge();
        });
        
        // Calculate packages cost and charge
        $packagesCost = $this->packages->sum(function ($package) {
            return $package->total_cost;
        });
        
        $packagesCharge = $this->packages->sum(function ($package) {
            return $package->total_charge;
        });
        
        // Calculate subtotals
        $subtotalCost = $itemsCost + $assembliesCost + $packagesCost;
        $subtotalCharge = $itemsCharge + $assembliesCharge + $packagesCharge;
        
        // Calculate markup amount
        $markupAmount = 0;
        if ($this->markup_percentage > 0) {
            $markupAmount = $subtotalCharge * $this->markup_percentage / 100;
        }
        
        // Calculate charge after markup
        $chargeAfterMarkup = $subtotalCharge + $markupAmount;
        
        // Calculate discount amount
        $discountAmount = 0;
        if ($this->discount_percentage > 0) {
            $discountAmount = $chargeAfterMarkup * $this->discount_percentage / 100;
        }
        
        // Calculate final charge
        $totalCharge = $chargeAfterMarkup - $discountAmount;
        
        return [
            'items_cost' => $itemsCost,
            'items_charge' => $itemsCharge,
            'assemblies_cost' => $assembliesCost,
            'assemblies_charge' => $assembliesCharge,
            'packages_cost' => $packagesCost,
            'packages_charge' => $packagesCharge,
            'subtotal_cost' => $subtotalCost,
            'subtotal_charge' => $subtotalCharge,
            'markup_amount' => $markupAmount,
            'discount_amount' => $discountAmount,
            'total_cost' => $subtotalCost,
            'total_charge' => $totalCharge,
        ];
    }
}
