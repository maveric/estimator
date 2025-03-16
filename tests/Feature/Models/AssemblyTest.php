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

class AssemblyTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;
    private Team $team;
    private Assembly $assembly;

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
    public function it_can_create_an_assembly()
    {
        $assembly = Assembly::factory()->create();

        $this->assertDatabaseHas('assemblies', [
            'id' => $assembly->id,
            'name' => $assembly->name,
        ]);
    }

    #[Test]
    public function it_belongs_to_a_team()
    {
        $team = Team::factory()->create();
        $assembly = Assembly::factory()->create(['team_id' => $team->id]);

        $this->assertInstanceOf(Team::class, $assembly->team);
        $this->assertEquals($team->id, $assembly->team->id);
    }

    #[Test]
    public function it_can_have_items()
    {
        $assembly = Assembly::factory()->create();
        $items = Item::factory()->count(3)->create();

        $assembly->items()->attach($items->pluck('id')->toArray(), ['quantity' => 1]);

        $this->assertCount(3, $assembly->items);
        $items->each(fn ($item) => $this->assertTrue($assembly->items->contains($item)));
    }

    #[Test]
    public function it_can_calculate_total_material_cost()
    {
        $assembly = Assembly::factory()->create();
        $item1 = Item::factory()->create(['material_cost' => 100]);
        $item2 = Item::factory()->create(['material_cost' => 200]);

        $assembly->items()->attach([
            $item1->id => ['quantity' => 2],
            $item2->id => ['quantity' => 1],
        ]);

        // (100 * 2) + (200 * 1) = 400
        $this->assertEquals(400.00, $assembly->total_material_cost);
    }

    #[Test]
    public function it_can_calculate_total_material_price()
    {
        $assembly = Assembly::factory()->create();
        $item1 = Item::factory()->create(['material_price' => 150]);
        $item2 = Item::factory()->create(['material_price' => 300]);

        $assembly->items()->attach([
            $item1->id => ['quantity' => 2],
            $item2->id => ['quantity' => 1],
        ]);

        // (150 * 2) + (300 * 1) = 600
        $this->assertEquals(600.00, $assembly->total_material_price);
    }

    #[Test]
    public function it_can_calculate_total_labor_hours()
    {
        $assembly = Assembly::factory()->create();
        $item1 = Item::factory()->create(['labor_minutes' => 60]);
        $item2 = Item::factory()->create(['labor_minutes' => 30]);

        $assembly->items()->attach([
            $item1->id => ['quantity' => 2],
            $item2->id => ['quantity' => 1],
        ]);

        // ((60 * 2) + (30 * 1)) / 60 = 2.5 hours
        $this->assertEquals(2.50, $assembly->total_labor_hours);
    }

    #[Test]
    public function it_can_calculate_total_labor_cost()
    {
        $laborRate = LaborRate::factory()->create([
            'cost_rate' => 60.00, // $60/hour
        ]);

        $assembly = Assembly::factory()->create();
        $item1 = Item::factory()->create([
            'labor_minutes' => 60,
            'labor_rate_id' => $laborRate->id,
        ]);
        $item2 = Item::factory()->create([
            'labor_minutes' => 30,
            'labor_rate_id' => $laborRate->id,
        ]);

        $assembly->items()->attach([
            $item1->id => ['quantity' => 2],
            $item2->id => ['quantity' => 1],
        ]);

        // Item 1: 60 minutes * 2 = 120 minutes = 2 hours * $60 = $120
        // Item 2: 30 minutes = 0.5 hours * $60 = $30
        // Total: $120 + $30 = $150
        $this->assertEquals(150.00, $assembly->total_labor_cost);
    }

    #[Test]
    public function it_can_calculate_total_labor_price()
    {
        $laborRate = LaborRate::factory()->create([
            'price_rate' => 90.00, // $90/hour
        ]);

        $assembly = Assembly::factory()->create();
        $item1 = Item::factory()->create([
            'labor_minutes' => 60,
            'labor_rate_id' => $laborRate->id,
        ]);
        $item2 = Item::factory()->create([
            'labor_minutes' => 30,
            'labor_rate_id' => $laborRate->id,
        ]);

        $assembly->items()->attach([
            $item1->id => ['quantity' => 2],
            $item2->id => ['quantity' => 1],
        ]);

        // Item 1: 60 minutes * 2 = 120 minutes = 2 hours * $90 = $180
        // Item 2: 30 minutes = 0.5 hours * $90 = $45
        // Total: $180 + $45 = $225
        $this->assertEquals(225.00, $assembly->total_labor_price);
    }

    #[Test]
    public function it_can_calculate_total_cost()
    {
        $laborRate = LaborRate::factory()->create([
            'cost_rate' => 60.00, // $60/hour
        ]);

        $assembly = Assembly::factory()->create();
        $item1 = Item::factory()->create([
            'material_cost' => 100,
            'labor_minutes' => 60,
            'labor_rate_id' => $laborRate->id,
        ]);
        $item2 = Item::factory()->create([
            'material_cost' => 200,
            'labor_minutes' => 30,
            'labor_rate_id' => $laborRate->id,
        ]);

        $assembly->items()->attach([
            $item1->id => ['quantity' => 2],
            $item2->id => ['quantity' => 1],
        ]);

        // Material cost: (100 * 2) + (200 * 1) = 400
        // Labor cost: ((60 * 2) + 30) minutes = 150 minutes = 2.5 hours * $60 = 150
        // Total: $400 + $150 = $550
        $this->assertEquals(550.00, $assembly->total_cost);
    }

    #[Test]
    public function it_can_calculate_total_price()
    {
        $laborRate = LaborRate::factory()->create([
            'price_rate' => 90.00, // $90/hour
        ]);

        $assembly = Assembly::factory()->create();
        $item1 = Item::factory()->create([
            'material_price' => 150,
            'labor_minutes' => 60,
            'labor_rate_id' => $laborRate->id,
        ]);
        $item2 = Item::factory()->create([
            'material_price' => 300,
            'labor_minutes' => 30,
            'labor_rate_id' => $laborRate->id,
        ]);

        $assembly->items()->attach([
            $item1->id => ['quantity' => 2],
            $item2->id => ['quantity' => 1],
        ]);

        // Material price: (150 * 2) + (300 * 1) = 600
        // Labor price: ((60 * 2) + 30) minutes = 150 minutes = 2.5 hours * $90 = 225
        // Total: $600 + $225 = $825
        $this->assertEquals(825.00, $assembly->total_price);
    }

    #[Test]
    public function it_can_filter_by_team()
    {
        $team = Team::factory()->create();
        $teamAssembly = Assembly::factory()->create(['team_id' => $team->id]);
        $otherAssembly = Assembly::factory()->create();

        $assemblies = Assembly::forTeam($team->id)->get();

        $this->assertTrue($assemblies->contains($teamAssembly));
        $this->assertFalse($assemblies->contains($otherAssembly));
    }

    #[Test]
    public function it_can_filter_active_assemblies()
    {
        $activeAssembly = Assembly::factory()->create();
        $inactiveAssembly = Assembly::factory()->inactive()->create();

        $assemblies = Assembly::active()->get();

        $this->assertTrue($assemblies->contains($activeAssembly));
        $this->assertFalse($assemblies->contains($inactiveAssembly));
    }

    #[Test]
    public function it_can_filter_template_assemblies()
    {
        $templateAssembly = Assembly::factory()->template()->create();
        $nonTemplateAssembly = Assembly::factory()->create();

        $assemblies = Assembly::template()->get();

        $this->assertTrue($assemblies->contains($templateAssembly));
        $this->assertFalse($assemblies->contains($nonTemplateAssembly));
    }

    #[Test]
    public function it_logs_activity()
    {
        $assembly = Assembly::factory()->create();

        $assembly->update(['name' => 'Updated Name']);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Assembly::class,
            'subject_id' => $assembly->id,
            'description' => 'Assembly has been updated',
        ]);
    }
}
