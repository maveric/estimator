<?php

namespace Tests\Feature\Livewire\LaborRates;

use App\Livewire\LaborRates\Edit;
use App\Models\LaborRate;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EditTest extends TestCase
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
            'name' => 'Original Rate',
            'cost_rate' => 75.00,
            'price_rate' => 100.00,
            'is_active' => true,
        ]);
    }

    #[Test]
    public function admin_can_edit_labor_rate()
    {
        Livewire::actingAs($this->admin)
            ->test(Edit::class, ['laborRate' => $this->laborRate])
            ->set('name', 'Updated Rate')
            ->set('cost_rate', 85.00)
            ->set('price_rate', 150.00)
            ->call('save')
            ->assertDispatched('labor-rate-updated');

        $this->assertDatabaseHas('labor_rates', [
            'id' => $this->laborRate->id,
            'name' => 'Updated Rate',
            'cost_rate' => 85.00,
            'price_rate' => 150.00,
        ]);
    }

    #[Test]
    public function manager_can_edit_labor_rate()
    {
        Livewire::actingAs($this->manager)
            ->test(Edit::class, ['laborRate' => $this->laborRate])
            ->set('name', 'Manager Updated Rate')
            ->set('cost_rate', 85.00)
            ->set('price_rate', 150.00)
            ->call('save')
            ->assertDispatched('labor-rate-updated');

        $this->assertDatabaseHas('labor_rates', [
            'id' => $this->laborRate->id,
            'name' => 'Manager Updated Rate',
            'cost_rate' => 85.00,
            'price_rate' => 150.00,
        ]);
    }

    #[Test]
    public function estimator_cannot_edit_labor_rate()
    {
        Livewire::actingAs($this->estimator)
            ->test(Edit::class, ['laborRate' => $this->laborRate])
            ->assertForbidden();
    }

    #[Test]
    public function unauthorized_user_cannot_edit_labor_rate()
    {
        $unauthorizedUser = User::factory()->create();
        $this->team->users()->attach($unauthorizedUser, ['role' => 'editor']);
        $unauthorizedUser->switchTeam($this->team);

        Livewire::actingAs($unauthorizedUser)
            ->test(Edit::class, ['laborRate' => $this->laborRate])
            ->assertForbidden();
    }

    #[Test]
    public function admin_cannot_edit_labor_rate_from_another_team()
    {
        $otherTeam = Team::factory()->create();
        $otherTeamRate = LaborRate::factory()->create([
            'team_id' => $otherTeam->id,
        ]);

        Livewire::actingAs($this->admin)
            ->test(Edit::class, ['laborRate' => $otherTeamRate])
            ->assertForbidden();
    }

    #[Test]
    public function it_validates_required_fields()
    {
        Livewire::actingAs($this->admin)
            ->test(Edit::class, ['laborRate' => $this->laborRate])
            ->set('name', '')
            ->set('cost_rate', '')
            ->set('price_rate', '')
            ->call('save')
            ->assertHasErrors(['name', 'cost_rate', 'price_rate']);
    }

    #[Test]
    public function it_validates_rate_is_numeric()
    {
        Livewire::actingAs($this->admin)
            ->test(Edit::class, ['laborRate' => $this->laborRate])
            ->set('name', 'Test Rate')
            ->set('cost_rate', 'not-a-number')
            ->set('price_rate', 'not-a-number')
            ->call('save')
            ->assertHasErrors(['cost_rate', 'price_rate']);
    }

    #[Test]
    public function it_validates_rates_are_positive()
    {
        Livewire::actingAs($this->admin)
            ->test(Edit::class, ['laborRate' => $this->laborRate])
            ->set('name', 'Test Rate')
            ->set('cost_rate', -100)
            ->set('price_rate', -100)
            ->call('save')
            ->assertHasErrors(['cost_rate', 'price_rate']);
    }
}
