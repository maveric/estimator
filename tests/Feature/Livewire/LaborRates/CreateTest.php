<?php

namespace Tests\Feature\Livewire\LaborRates;

use App\Livewire\LaborRates\Create;
use App\Models\LaborRate;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $manager;
    private User $estimator;
    private Team $team;

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
    }

    #[Test]
    public function admin_can_create_labor_rate()
    {
        Livewire::actingAs($this->admin)
            ->test(Create::class)
            ->set('name', 'Test Rate')
            ->set('cost_rate', 75.00)
            ->set('price_rate', 100.00)
            ->set('is_active', true)
            ->call('save')
            ->assertDispatched('labor-rate-created');

        $this->assertDatabaseHas('labor_rates', [
            'name' => 'Test Rate',
            'cost_rate' => 75.00,
            'price_rate' => 100.00,
            'is_active' => true,
            'team_id' => $this->team->id,
        ]);
    }

    #[Test]
    public function manager_can_create_labor_rate()
    {
        Livewire::actingAs($this->manager)
            ->test(Create::class)
            ->set('name', 'Manager Rate')
            ->set('cost_rate', 75.00)
            ->set('price_rate', 100.00)
            ->set('is_active', true)
            ->call('save')
            ->assertDispatched('labor-rate-created');

        $this->assertDatabaseHas('labor_rates', [
            'name' => 'Manager Rate',
            'cost_rate' => 75.00,
            'price_rate' => 100.00,
            'is_active' => true,
            'team_id' => $this->team->id,
        ]);
    }

    #[Test]
    public function estimator_cannot_create_labor_rate()
    {
        Livewire::actingAs($this->estimator)
            ->test(Create::class)
            ->assertForbidden();
    }

    #[Test]
    public function unauthorized_user_cannot_create_labor_rate()
    {
        $unauthorizedUser = User::factory()->create();
        $this->team->users()->attach($unauthorizedUser, ['role' => 'editor']);
        $unauthorizedUser->switchTeam($this->team);

        Livewire::actingAs($unauthorizedUser)
            ->test(Create::class)
            ->assertForbidden();
    }

    #[Test]
    public function it_validates_required_fields()
    {
        Livewire::actingAs($this->admin)
            ->test(Create::class)
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
            ->test(Create::class)
            ->set('name', 'Test Rate')
            ->set('cost_rate', 'not-a-number')
            ->set('price_rate', 'not-a-number')
            ->call('save')
            ->assertHasErrors(['cost_rate', 'price_rate']);
    }

    #[Test]
    public function it_can_render_create_form()
    {
        Livewire::actingAs($this->admin)
            ->test(Create::class)
            ->assertSuccessful()
            ->assertViewIs('livewire.labor-rates.create');
    }

    #[Test]
    public function it_requires_valid_input()
    {
        Livewire::actingAs($this->admin)
            ->test(Create::class)
            ->call('save')
            ->assertHasErrors(['name', 'cost_rate', 'price_rate']);
    }

    #[Test]
    public function it_can_create_labor_rate()
    {
        Livewire::actingAs($this->admin)
            ->test(Create::class)
            ->set('name', 'Test Rate')
            ->set('cost_rate', 50)
            ->set('price_rate', 75)
            ->set('is_default', true)
            ->set('is_active', true)
            ->call('save')
            ->assertRedirect(route('labor-rates.index'));

        $this->assertDatabaseHas('labor_rates', [
            'team_id' => $this->team->id,
            'name' => 'Test Rate',
            'cost_rate' => 50,
            'price_rate' => 75,
            'is_default' => true,
            'is_active' => true,
        ]);
    }

    #[Test]
    public function it_unsets_other_default_rates_when_setting_new_default()
    {
        // Create an existing default rate
        $existingDefault = LaborRate::factory()->create([
            'team_id' => $this->team->id,
            'is_default' => true,
        ]);

        Livewire::actingAs($this->admin)
            ->test(Create::class)
            ->set('name', 'New Default Rate')
            ->set('cost_rate', 50)
            ->set('price_rate', 75)
            ->set('is_default', true)
            ->call('save');

        $this->assertDatabaseHas('labor_rates', [
            'id' => $existingDefault->id,
            'is_default' => false,
        ]);
    }

    #[Test]
    public function it_sets_first_rate_as_default_automatically()
    {
        Livewire::actingAs($this->admin)
            ->test(Create::class)
            ->set('name', 'First Rate')
            ->set('cost_rate', 50)
            ->set('price_rate', 75)
            ->set('is_default', false) // Explicitly set to false
            ->call('save');

        $this->assertDatabaseHas('labor_rates', [
            'team_id' => $this->team->id,
            'name' => 'First Rate',
            'is_default' => true, // Should be true despite being set to false
        ]);
    }

    #[Test]
    public function it_validates_numeric_fields()
    {
        Livewire::actingAs($this->admin)
            ->test(Create::class)
            ->set('name', 'Test Rate')
            ->set('cost_rate', 'not-a-number')
            ->set('price_rate', 'not-a-number')
            ->call('save')
            ->assertHasErrors([
                'cost_rate' => 'numeric',
                'price_rate' => 'numeric',
            ]);
    }

    #[Test]
    public function it_validates_minimum_values_for_rates()
    {
        Livewire::actingAs($this->admin)
            ->test(Create::class)
            ->set('name', 'Test Rate')
            ->set('cost_rate', -1)
            ->set('price_rate', -1)
            ->call('save')
            ->assertHasErrors([
                'cost_rate' => 'min',
                'price_rate' => 'min',
            ]);
    }
}
