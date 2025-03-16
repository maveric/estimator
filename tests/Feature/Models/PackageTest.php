<?php

namespace Tests\Feature\Models;

use App\Models\Assembly;
use App\Models\Item;
use App\Models\LaborRate;
use App\Models\Package;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PackageTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;
    private Team $team;
    private Package $package;

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
    public function it_can_create_a_package()
    {
        $package = Package::factory()->create();

        $this->assertDatabaseHas('packages', [
            'id' => $package->id,
            'name' => $package->name,
        ]);
    }

    #[Test]
    public function it_belongs_to_a_team()
    {
        $team = Team::factory()->create();
        $package = Package::factory()->create(['team_id' => $team->id]);

        $this->assertInstanceOf(Team::class, $package->team);
        $this->assertEquals($team->id, $package->team->id);
    }

    #[Test]
    public function it_can_have_assemblies()
    {
        $package = Package::factory()->create();
        $assemblies = Assembly::factory()->count(3)->create();

        $package->assemblies()->attach($assemblies->pluck('id')->toArray(), ['quantity' => 1]);

        $this->assertCount(3, $package->assemblies);
        $assemblies->each(fn ($assembly) => $this->assertTrue($package->assemblies->contains($assembly)));
    }

    #[Test]
    public function it_can_calculate_total_material_cost()
    {
        $package = Package::factory()->create();
        $assembly1 = Assembly::factory()->create();
        $assembly2 = Assembly::factory()->create();

        $laborRate = LaborRate::factory()->create();
        $item1 = Item::factory()->create([
            'material_cost' => 100,
            'labor_rate_id' => $laborRate->id,
        ]);
        $item2 = Item::factory()->create([
            'material_cost' => 200,
            'labor_rate_id' => $laborRate->id,
        ]);

        $assembly1->items()->attach([
            $item1->id => ['quantity' => 2],
        ]);
        $assembly2->items()->attach([
            $item2->id => ['quantity' => 1],
        ]);

        $package->assemblies()->attach([
            $assembly1->id => ['quantity' => 2],
            $assembly2->id => ['quantity' => 1],
        ]);

        // Assembly 1: (100 * 2) = 200 * 2 = 400
        // Assembly 2: (200 * 1) = 200 * 1 = 200
        // Total: 400 + 200 = 600
        $this->assertEquals(600.00, $package->total_material_cost);
    }

    #[Test]
    public function it_can_calculate_total_material_price()
    {
        $package = Package::factory()->create();
        $assembly1 = Assembly::factory()->create();
        $assembly2 = Assembly::factory()->create();

        $laborRate = LaborRate::factory()->create();
        $item1 = Item::factory()->create([
            'material_price' => 150,
            'labor_rate_id' => $laborRate->id,
        ]);
        $item2 = Item::factory()->create([
            'material_price' => 300,
            'labor_rate_id' => $laborRate->id,
        ]);

        $assembly1->items()->attach([
            $item1->id => ['quantity' => 2],
        ]);
        $assembly2->items()->attach([
            $item2->id => ['quantity' => 1],
        ]);

        $package->assemblies()->attach([
            $assembly1->id => ['quantity' => 2],
            $assembly2->id => ['quantity' => 1],
        ]);

        // Assembly 1: (150 * 2) = 300 * 2 = 600
        // Assembly 2: (300 * 1) = 300 * 1 = 300
        // Total: 600 + 300 = 900
        $this->assertEquals(900.00, $package->total_material_price);
    }

    #[Test]
    public function it_can_calculate_total_labor_hours()
    {
        $package = Package::factory()->create();
        $assembly1 = Assembly::factory()->create();
        $assembly2 = Assembly::factory()->create();

        $laborRate = LaborRate::factory()->create();
        $item1 = Item::factory()->create([
            'labor_minutes' => 60,
            'labor_rate_id' => $laborRate->id,
        ]);
        $item2 = Item::factory()->create([
            'labor_minutes' => 30,
            'labor_rate_id' => $laborRate->id,
        ]);

        $assembly1->items()->attach([
            $item1->id => ['quantity' => 2],
        ]);
        $assembly2->items()->attach([
            $item2->id => ['quantity' => 1],
        ]);

        $package->assemblies()->attach([
            $assembly1->id => ['quantity' => 2],
            $assembly2->id => ['quantity' => 1],
        ]);

        // Assembly 1: (60 * 2) = 120 minutes = 2 hours * 2 = 4 hours
        // Assembly 2: (30 * 1) = 30 minutes = 0.5 hours * 1 = 0.5 hours
        // Total: 4 + 0.5 = 4.5 hours
        $this->assertEquals(4.50, $package->total_labor_hours);
    }

    #[Test]
    public function it_can_calculate_total_labor_cost()
    {
        $package = Package::factory()->create();
        $assembly1 = Assembly::factory()->create();
        $assembly2 = Assembly::factory()->create();

        $laborRate = LaborRate::factory()->create([
            'cost_rate' => 60.00, // $60/hour
        ]);
        $item1 = Item::factory()->create([
            'labor_minutes' => 60,
            'labor_rate_id' => $laborRate->id,
        ]);
        $item2 = Item::factory()->create([
            'labor_minutes' => 30,
            'labor_rate_id' => $laborRate->id,
        ]);

        $assembly1->items()->attach([
            $item1->id => ['quantity' => 2],
        ]);
        $assembly2->items()->attach([
            $item2->id => ['quantity' => 1],
        ]);

        $package->assemblies()->attach([
            $assembly1->id => ['quantity' => 2],
            $assembly2->id => ['quantity' => 1],
        ]);

        // Assembly 1: (60 * 2) = 120 minutes = 2 hours * $60 = $120 * 2 = $240
        // Assembly 2: (30 * 1) = 30 minutes = 0.5 hours * $60 = $30 * 1 = $30
        // Total: $240 + $30 = $270
        $this->assertEquals(270.00, $package->total_labor_cost);
    }

    #[Test]
    public function it_can_calculate_total_labor_price()
    {
        $package = Package::factory()->create();
        $assembly1 = Assembly::factory()->create();
        $assembly2 = Assembly::factory()->create();

        $laborRate = LaborRate::factory()->create([
            'price_rate' => 90.00, // $90/hour
        ]);
        $item1 = Item::factory()->create([
            'labor_minutes' => 60,
            'labor_rate_id' => $laborRate->id,
        ]);
        $item2 = Item::factory()->create([
            'labor_minutes' => 30,
            'labor_rate_id' => $laborRate->id,
        ]);

        $assembly1->items()->attach([
            $item1->id => ['quantity' => 2],
        ]);
        $assembly2->items()->attach([
            $item2->id => ['quantity' => 1],
        ]);

        $package->assemblies()->attach([
            $assembly1->id => ['quantity' => 2],
            $assembly2->id => ['quantity' => 1],
        ]);

        // Assembly 1: (60 * 2) = 120 minutes = 2 hours * $90 = $180 * 2 = $360
        // Assembly 2: (30 * 1) = 30 minutes = 0.5 hours * $90 = $45 * 1 = $45
        // Total: $360 + $45 = $405
        $this->assertEquals(405.00, $package->total_labor_price);
    }

    #[Test]
    public function it_can_calculate_total_cost()
    {
        $package = Package::factory()->create();
        $assembly1 = Assembly::factory()->create();
        $assembly2 = Assembly::factory()->create();

        $laborRate = LaborRate::factory()->create([
            'cost_rate' => 60.00, // $60/hour
        ]);
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

        $assembly1->items()->attach([
            $item1->id => ['quantity' => 2],
        ]);
        $assembly2->items()->attach([
            $item2->id => ['quantity' => 1],
        ]);

        $package->assemblies()->attach([
            $assembly1->id => ['quantity' => 2],
            $assembly2->id => ['quantity' => 1],
        ]);

        // Material cost:
        // Assembly 1: (100 * 2) = 200 * 2 = 400
        // Assembly 2: (200 * 1) = 200 * 1 = 200
        // Total material cost: 400 + 200 = 600

        // Labor cost:
        // Assembly 1: (60 * 2) = 120 minutes = 2 hours * $60 = $120 * 2 = $240
        // Assembly 2: (30 * 1) = 30 minutes = 0.5 hours * $60 = $30 * 1 = $30
        // Total labor cost: $240 + $30 = $270

        // Total cost: $600 + $270 = $870
        $this->assertEquals(870.00, $package->total_cost);
    }

    #[Test]
    public function it_can_calculate_total_price()
    {
        $package = Package::factory()->create();
        $assembly1 = Assembly::factory()->create();
        $assembly2 = Assembly::factory()->create();

        $laborRate = LaborRate::factory()->create([
            'price_rate' => 90.00, // $90/hour
        ]);
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

        $assembly1->items()->attach([
            $item1->id => ['quantity' => 2],
        ]);
        $assembly2->items()->attach([
            $item2->id => ['quantity' => 1],
        ]);

        $package->assemblies()->attach([
            $assembly1->id => ['quantity' => 2],
            $assembly2->id => ['quantity' => 1],
        ]);

        // Material price:
        // Assembly 1: (150 * 2) = 300 * 2 = 600
        // Assembly 2: (300 * 1) = 300 * 1 = 300
        // Total material price: 600 + 300 = 900

        // Labor price:
        // Assembly 1: (60 * 2) = 120 minutes = 2 hours * $90 = $180 * 2 = $360
        // Assembly 2: (30 * 1) = 30 minutes = 0.5 hours * $90 = $45 * 1 = $45
        // Total labor price: $360 + $45 = $405

        // Total price: $900 + $405 = $1,305
        $this->assertEquals(1305.00, $package->total_price);
    }

    #[Test]
    public function it_can_filter_by_team()
    {
        $team = Team::factory()->create();
        $teamPackage = Package::factory()->create(['team_id' => $team->id]);
        $otherPackage = Package::factory()->create();

        $packages = Package::forTeam($team->id)->get();

        $this->assertTrue($packages->contains($teamPackage));
        $this->assertFalse($packages->contains($otherPackage));
    }

    #[Test]
    public function it_can_filter_active_packages()
    {
        $activePackage = Package::factory()->create();
        $inactivePackage = Package::factory()->inactive()->create();

        $packages = Package::active()->get();

        $this->assertTrue($packages->contains($activePackage));
        $this->assertFalse($packages->contains($inactivePackage));
    }

    #[Test]
    public function it_can_filter_template_packages()
    {
        $templatePackage = Package::factory()->template()->create();
        $nonTemplatePackage = Package::factory()->create();

        $packages = Package::template()->get();

        $this->assertTrue($packages->contains($templatePackage));
        $this->assertFalse($packages->contains($nonTemplatePackage));
    }

    #[Test]
    public function it_logs_activity()
    {
        $package = Package::factory()->create();

        $package->update(['name' => 'Updated Name']);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Package::class,
            'subject_id' => $package->id,
            'description' => 'Package has been updated',
        ]);
    }
}
