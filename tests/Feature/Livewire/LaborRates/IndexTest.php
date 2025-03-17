<?php

namespace Tests\Feature\Livewire\LaborRates;

use App\Livewire\LaborRates\Index;
use App\Models\LaborRate;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $manager;
    private User $estimator;
    private Team $team;
    private LaborRate $laborRate;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and permissions
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        // Create users with roles
        $this->admin = User::factory()->create(['name' => 'Admin User']);
        $this->admin->assignRole('admin');

        $this->manager = User::factory()->create(['name' => 'Manager User']);
        $this->manager->assignRole('manager');

        $this->estimator = User::factory()->create(['name' => 'Estimator User']);
        $this->estimator->assignRole('estimator');

        // Create team and add users
        $this->team = Team::factory()->create(['user_id' => $this->admin->id]);
        $this->team->users()->attach($this->manager, ['role' => 'admin']);
        $this->team->users()->attach($this->estimator, ['role' => 'editor']);

        // Switch users to team
        $this->admin->switchTeam($this->team);
        $this->manager->switchTeam($this->team);
        $this->estimator->switchTeam($this->team);

        // Create a labor rate for testing
        $this->laborRate = LaborRate::factory()->create([
            'team_id' => $this->team->id,
            'name' => 'Test Rate',
            'cost_rate' => 75.00,
            'price_rate' => 100.00,
            'is_active' => true,
        ]);
    }

    #[Test]
    public function admin_can_view_labor_rates()
    {
        Livewire::actingAs($this->admin)
            ->test(Index::class)
            ->assertSuccessful()
            ->assertSee($this->laborRate->name);
    }

    #[Test]
    public function manager_can_view_labor_rates()
    {
        Livewire::actingAs($this->manager)
            ->test(Index::class)
            ->assertSuccessful()
            ->assertSee($this->laborRate->name);
    }

    #[Test]
    public function estimator_can_view_labor_rates()
    {
        Livewire::actingAs($this->estimator)
            ->test(Index::class)
            ->assertSuccessful()
            ->assertSee($this->laborRate->name);
    }

    #[Test]
    public function admin_can_toggle_labor_rate_status()
    {
        Livewire::actingAs($this->admin)
            ->test(Index::class)
            ->call('toggleStatus', $this->laborRate)
            ->assertDispatched('labor-rate-updated');

        $this->assertFalse($this->laborRate->fresh()->is_active);
    }

    #[Test]
    public function manager_can_toggle_labor_rate_status()
    {
        Livewire::actingAs($this->manager)
            ->test(Index::class)
            ->call('toggleStatus', $this->laborRate)
            ->assertDispatched('labor-rate-updated');

        $this->assertFalse($this->laborRate->fresh()->is_active);
    }

    #[Test]
    public function estimator_cannot_toggle_labor_rate_status()
    {
        Livewire::actingAs($this->estimator)
            ->test(Index::class)
            ->call('toggleStatus', $this->laborRate)
            ->assertForbidden();

        $this->assertTrue($this->laborRate->fresh()->is_active);
    }

    #[Test]
    public function unauthorized_user_cannot_view_labor_rates()
    {
        $unauthorizedUser = User::factory()->create();
        $this->team->users()->attach($unauthorizedUser, ['role' => 'editor']);
        $unauthorizedUser->switchTeam($this->team);

        Livewire::actingAs($unauthorizedUser)
            ->test(Index::class)
            ->assertForbidden();
    }

    #[Test]
    public function it_can_render_labor_rates_list()
    {
        $laborRates = LaborRate::factory()->count(3)->create([
            'team_id' => $this->team->id,
            'cost_rate' => 75.00,
            'price_rate' => 100.00,
        ]);

        Livewire::actingAs($this->admin)
            ->test(Index::class)
            ->assertSee($laborRates[0]->name)
            ->assertSee(number_format($laborRates[0]->cost_rate, 2))
            ->assertSee(number_format($laborRates[0]->price_rate, 2));
    }

    #[Test]
    public function it_can_search_labor_rates()
    {
        $matchingRate = LaborRate::factory()->create([
            'team_id' => $this->team->id,
            'name' => 'Matching Rate'
        ]);

        $nonMatchingRate = LaborRate::factory()->create([
            'team_id' => $this->team->id,
            'name' => 'Different Rate'
        ]);

        Livewire::actingAs($this->admin)
            ->test(Index::class)
            ->set('search', 'Matching')
            ->assertSee($matchingRate->name)
            ->assertDontSee($nonMatchingRate->name);
    }

    #[Test]
    public function it_can_show_inactive_labor_rates()
    {
        $activeRate = LaborRate::factory()->create([
            'team_id' => $this->team->id,
            'is_active' => true,
            'name' => 'Active Rate'
        ]);

        $inactiveRate = LaborRate::factory()->create([
            'team_id' => $this->team->id,
            'is_active' => false,
            'name' => 'Inactive Rate'
        ]);

        $component = Livewire::actingAs($this->admin)
            ->test(Index::class);

        // By default, only active rates are shown
        $component->assertSee($activeRate->name)
            ->assertDontSee($inactiveRate->name);

        // Show inactive rates
        $component->set('showInactive', true)
            ->assertSee($activeRate->name)
            ->assertSee($inactiveRate->name);
    }

    #[Test]
    public function it_only_shows_labor_rates_for_current_team()
    {
        $otherTeam = Team::factory()->create();
        
        $currentTeamRate = LaborRate::factory()->create([
            'team_id' => $this->team->id,
            'name' => 'Current Team Rate'
        ]);

        $otherTeamRate = LaborRate::factory()->create([
            'team_id' => $otherTeam->id,
            'name' => 'Other Team Rate'
        ]);

        Livewire::actingAs($this->admin)
            ->test(Index::class)
            ->assertSee($currentTeamRate->name)
            ->assertDontSee($otherTeamRate->name);
    }
}
