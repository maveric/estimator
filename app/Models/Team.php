<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Jetstream\Events\TeamCreated;
use Laravel\Jetstream\Events\TeamDeleted;
use Laravel\Jetstream\Events\TeamUpdated;
use Laravel\Jetstream\Team as JetstreamTeam;

class Team extends JetstreamTeam
{
    use HasFactory;

    protected $fillable = [
        'name',
        'personal_team',
        'company_name',
        'address',
        'city',
        'state',
        'zip',
        'phone',
        'email',
        'website',
        'default_labor_cost',
        'default_labor_rate',
        'currency',
        'decimal_places',
        'date_format',
        'show_cost_in_estimates',
    ];

    protected $dispatchesEvents = [
        'created' => TeamCreated::class,
        'updated' => TeamUpdated::class,
        'deleted' => TeamDeleted::class,
    ];

    protected function casts(): array
    {
        return [
            'personal_team' => 'boolean',
            'default_labor_cost' => 'decimal:4',
            'default_labor_rate' => 'decimal:4',
            'decimal_places' => 'integer',
            'show_cost_in_estimates' => 'boolean',
        ];
    }
} 