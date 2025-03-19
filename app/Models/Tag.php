<?php

namespace App\Models;

use Spatie\Tags\Tag as SpatieTag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use ArrayAccess;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tag extends SpatieTag
{
    use HasFactory;

    protected $fillable = [
        'name',
        'team_id',
        'type',
        'slug',
        'order_column',
    ];

    // Relationships
    public function items()
    {
        return $this->morphedByMany(Item::class, 'taggable');
    }

    // Scopes
    public function scopeForTeam(Builder $query, $teamId): Builder
    {
        return $query->where('team_id', $teamId);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tag) {
            if (!$tag->team_id) {
                $tag->team_id = auth()->user()?->currentTeam?->id;
            }
        });
    }

    public static function findFromString(string $name, ?string $type = null, ?string $locale = null, ?int $teamId = null)
    {
        $locale = $locale ?? static::getLocale();
        $teamId = $teamId ?? auth()->user()?->currentTeam?->id;

        if (!$teamId) {
            throw new \RuntimeException('No team ID provided and no authenticated user with current team.');
        }

        return static::query()
            ->where('type', $type)
            ->where('team_id', $teamId)
            ->where(function ($query) use ($name, $locale) {
                $query->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.{$locale}'))) = ?", [strtolower($name)])
                    ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(slug, '$.{$locale}'))) = ?", [strtolower($name)]);
            })
            ->first();
    }

    public static function findOrCreateFromString(string $name, ?string $type = null, ?string $locale = null, ?int $teamId = null)
    {
        $locale = $locale ?? static::getLocale();
        $teamId = $teamId ?? auth()->user()?->currentTeam?->id;

        if (!$teamId) {
            throw new \RuntimeException('No team ID provided and no authenticated user with current team.');
        }

        $tag = static::findFromString($name, $type, $locale, $teamId);

        if (!$tag) {
            $tag = static::create([
                'name' => [$locale => $name],
                'type' => $type,
                'team_id' => $teamId,
            ]);
        }

        return $tag;
    }

    public static function findOrCreate(ArrayAccess|array|string $values, ?string $type = null, ?string $locale = null): Collection|static|SpatieTag
    {
        $locale = $locale ?? static::getLocale();
        $teamId = auth()->user()?->currentTeam?->id;

        if (!$teamId) {
            throw new \RuntimeException('No team ID provided and no authenticated user with current team.');
        }

        $tags = collect($values)->map(function ($value) use ($type, $locale, $teamId) {
            if ($value instanceof self) {
                return $value;
            }

            return static::findOrCreateFromString($value, $type, $locale, $teamId);
        });

        return is_string($values) ? $tags->first() : $tags;
    }
} 