<?php

namespace Database\Factories;

use App\Models\Tag;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TagFactory extends Factory
{
    protected $model = Tag::class;

    public function definition()
    {
        return [
            'name' => ['en' => $this->faker->word],
            'type' => null,
            'slug' => ['en' => Str::slug($this->faker->word)],
            'order_column' => 1,
            'team_id' => Team::factory(),
        ];
    }
} 