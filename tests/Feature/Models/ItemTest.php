<?php

namespace Tests\Feature\Models;

use App\Models\Assembly;
use App\Models\Item;
use App\Models\LaborRate;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ItemTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;
    private Team $team;
    private Item $item;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->team = Team::factory()->create([
            'user_id' => $this->user->id,
            'default_labor_cost' => 50,
            'default_labor_rate' => 75,
        ]);
    }

    #[Test]
    public function it_can_create_an_item()
    {
        $item = Item::factory()->create();

        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'name' => $item->name,
        ]);
    }

    #[Test]
    public function it_belongs_to_a_team()
    {
        $team = Team::factory()->create();
        $item = Item::factory()->create(['team_id' => $team->id]);

        $this->assertInstanceOf(Team::class, $item->team);
        $this->assertEquals($team->id, $item->team->id);
    }

    #[Test]
    public function it_belongs_to_a_labor_rate()
    {
        $laborRate = LaborRate::factory()->create();
        $item = Item::factory()->create(['labor_rate_id' => $laborRate->id]);

        $this->assertInstanceOf(LaborRate::class, $item->laborRate);
        $this->assertEquals($laborRate->id, $item->laborRate->id);
    }

    #[Test]
    public function it_can_belong_to_many_assemblies()
    {
        $item = Item::factory()->create();
        $assemblies = Assembly::factory()->count(3)->create();

        $item->assemblies()->attach($assemblies->pluck('id')->toArray(), ['quantity' => 1]);

        $this->assertCount(3, $item->assemblies);
        $assemblies->each(fn ($assembly) => $this->assertTrue($item->assemblies->contains($assembly)));
    }

    #[Test]
    public function it_calculates_labor_cost_using_labor_rate()
    {
        $laborRate = LaborRate::factory()->create([
            'cost_rate' => 60.00, // $60/hour
        ]);

        $item = Item::factory()->create([
            'labor_minutes' => 30,
            'labor_rate_id' => $laborRate->id,
        ]);

        // 30 minutes = 0.5 hours * $60/hour = $30
        $this->assertEquals(30.00, $item->labor_cost);
    }

    #[Test]
    public function it_calculates_labor_price_using_labor_rate()
    {
        $laborRate = LaborRate::factory()->create([
            'price_rate' => 90.00, // $90/hour
        ]);

        $item = Item::factory()->create([
            'labor_minutes' => 30,
            'labor_rate_id' => $laborRate->id,
        ]);

        // 30 minutes = 0.5 hours * $90/hour = $45
        $this->assertEquals(45.00, $item->labor_price);
    }

    #[Test]
    public function it_calculates_total_cost()
    {
        $laborRate = LaborRate::factory()->create([
            'cost_rate' => 60.00, // $60/hour
        ]);

        $item = Item::factory()->create([
            'material_cost' => 100.00,
            'labor_minutes' => 30,
            'labor_rate_id' => $laborRate->id,
        ]);

        // Material cost: $100
        // Labor cost: 30 minutes = 0.5 hours * $60/hour = $30
        // Total cost: $100 + $30 = $130
        $this->assertEquals(130.00, $item->total_cost);
    }

    #[Test]
    public function it_calculates_total_price()
    {
        $laborRate = LaborRate::factory()->create([
            'price_rate' => 90.00, // $90/hour
        ]);

        $item = Item::factory()->create([
            'material_price' => 150.00,
            'labor_minutes' => 30,
            'labor_rate_id' => $laborRate->id,
        ]);

        // Material price: $150
        // Labor price: 30 minutes = 0.5 hours * $90/hour = $45
        // Total price: $150 + $45 = $195
        $this->assertEquals(195.00, $item->total_price);
    }

    #[Test]
    public function it_can_filter_by_team()
    {
        $team = Team::factory()->create();
        $teamItem = Item::factory()->create(['team_id' => $team->id]);
        $otherItem = Item::factory()->create();

        $items = Item::forTeam($team->id)->get();

        $this->assertTrue($items->contains($teamItem));
        $this->assertFalse($items->contains($otherItem));
    }

    #[Test]
    public function it_can_filter_active_items()
    {
        $activeItem = Item::factory()->create();
        $inactiveItem = Item::factory()->inactive()->create();

        $items = Item::active()->get();

        $this->assertTrue($items->contains($activeItem));
        $this->assertFalse($items->contains($inactiveItem));
    }

    #[Test]
    public function it_can_filter_template_items()
    {
        $templateItem = Item::factory()->template()->create();
        $nonTemplateItem = Item::factory()->create();

        $items = Item::template()->get();

        $this->assertTrue($items->contains($templateItem));
        $this->assertFalse($items->contains($nonTemplateItem));
    }

    #[Test]
    public function it_logs_activity_when_updated()
    {
        $item = Item::factory()->create();

        $item->update(['name' => 'Updated Name']);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Item::class,
            'subject_id' => $item->id,
            'description' => 'Item has been updated',
        ]);
    }
} 