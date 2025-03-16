<?php

namespace Tests\Feature\Models;

use App\Models\Item;
use App\Models\LaborRate;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaborRateTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_create_a_labor_rate()
    {
        $laborRate = LaborRate::factory()->create();

        $this->assertDatabaseHas('labor_rates', [
            'id' => $laborRate->id,
            'name' => $laborRate->name,
        ]);
    }

    #[Test]
    public function it_belongs_to_a_team()
    {
        $team = Team::factory()->create();
        $laborRate = LaborRate::factory()->create(['team_id' => $team->id]);

        $this->assertInstanceOf(Team::class, $laborRate->team);
        $this->assertEquals($team->id, $laborRate->team->id);
    }

    #[Test]
    public function it_can_have_many_items()
    {
        $laborRate = LaborRate::factory()->create();
        $items = Item::factory()->count(3)->create(['labor_rate_id' => $laborRate->id]);

        $this->assertCount(3, $laborRate->items);
        $items->each(fn ($item) => $this->assertTrue($laborRate->items->contains($item)));
    }

    #[Test]
    public function it_can_be_set_as_default()
    {
        $laborRate = LaborRate::factory()->default()->create();

        $this->assertTrue($laborRate->is_default);
    }

    #[Test]
    public function it_can_be_set_as_inactive()
    {
        $laborRate = LaborRate::factory()->inactive()->create();

        $this->assertFalse($laborRate->is_active);
    }

    #[Test]
    public function it_can_filter_by_team()
    {
        $team = Team::factory()->create();
        $teamLaborRate = LaborRate::factory()->create(['team_id' => $team->id]);
        $otherLaborRate = LaborRate::factory()->create();

        $laborRates = LaborRate::forTeam($team->id)->get();

        $this->assertTrue($laborRates->contains($teamLaborRate));
        $this->assertFalse($laborRates->contains($otherLaborRate));
    }

    #[Test]
    public function it_can_filter_active_labor_rates()
    {
        $activeLaborRate = LaborRate::factory()->create();
        $inactiveLaborRate = LaborRate::factory()->inactive()->create();

        $laborRates = LaborRate::active()->get();

        $this->assertTrue($laborRates->contains($activeLaborRate));
        $this->assertFalse($laborRates->contains($inactiveLaborRate));
    }

    #[Test]
    public function it_can_filter_default_labor_rates()
    {
        $defaultLaborRate = LaborRate::factory()->default()->create();
        $nonDefaultLaborRate = LaborRate::factory()->create();

        $laborRates = LaborRate::default()->get();

        $this->assertTrue($laborRates->contains($defaultLaborRate));
        $this->assertFalse($laborRates->contains($nonDefaultLaborRate));
    }

    #[Test]
    public function it_logs_activity_when_updated()
    {
        $laborRate = LaborRate::factory()->create();

        $laborRate->update(['name' => 'Updated Name']);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => LaborRate::class,
            'subject_id' => $laborRate->id,
            'description' => 'Labor rate has been updated',
        ]);
    }
}
