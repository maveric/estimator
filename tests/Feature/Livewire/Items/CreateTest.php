<?php

namespace Tests\Feature\Livewire\Items;

use App\Livewire\Items\Create;
use App\Models\Item;
use App\Models\LaborRate;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateTest extends TestCase
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
        $this->seed(RolesAndPermissionsSeeder::class);

        // Create team
        $this->team = Team::factory()->create([
            'name' => 'Test Company',
            'personal_team' => false,
        ]);

        // Create users with roles
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
        $this->team->users()->attach($this->admin, ['role' => 'admin']);

        $this->manager = User::factory()->create();
        $this->manager->assignRole('manager');
        $this->team->users()->attach($this->manager, ['role' => 'editor']);

        $this->estimator = User::factory()->create();
        $this->estimator->assignRole('estimator');
        $this->team->users()->attach($this->estimator, ['role' => 'editor']);

        // Create labor rate
        $this->laborRate = LaborRate::factory()->create([
            'team_id' => $this->team->id,
            'is_default' => true,
        ]);
    }

    #[Test]
    public function admin_can_view_create_item_form()
    {
        $this->actingAs($this->admin);
        $this->admin->switchTeam($this->team);

        Livewire::test(Create::class)
            ->assertViewIs('livewire.items.create')
            ->assertStatus(200);
    }

    #[Test]
    public function manager_can_view_create_item_form()
    {
        $this->actingAs($this->manager);
        $this->manager->switchTeam($this->team);

        Livewire::test(Create::class)
            ->assertViewIs('livewire.items.create')
            ->assertStatus(200);
    }

    #[Test]
    public function estimator_cannot_view_create_item_form()
    {
        $this->actingAs($this->estimator);
        $this->estimator->switchTeam($this->team);

        Livewire::test(Create::class)
            ->assertStatus(403);
    }

    #[Test]
    public function unauthorized_user_cannot_view_create_item_form()
    {
        $user = User::factory()->create();
        $this->team->users()->attach($user, ['role' => 'editor']);

        $this->actingAs($user);
        $user->switchTeam($this->team);

        Livewire::test(Create::class)
            ->assertStatus(403);
    }

    #[Test]
    public function admin_can_create_item()
    {
        $this->actingAs($this->admin);
        $this->admin->switchTeam($this->team);

        $response = Livewire::test(Create::class)
            ->set('name', 'Test Item')
            ->set('description', 'Test Description')
            ->set('sku', 'TEST-001')
            ->set('unit_of_measure', 'EA')
            ->set('material_cost', 10.50)
            ->set('material_price', 15.75)
            ->set('labor_minutes', 30)
            ->set('labor_rate_id', $this->laborRate->id)
            ->set('is_template', true)
            ->set('is_active', true)
            ->call('create');

        $response->assertRedirect(route('items.index'));

        $this->assertDatabaseHas('items', [
            'team_id' => $this->team->id,
            'name' => 'Test Item',
            'description' => 'Test Description',
            'sku' => 'TEST-001',
            'unit_of_measure' => 'EA',
            'material_cost' => '10.5000',
            'material_price' => '15.7500',
            'labor_minutes' => 30,
            'labor_rate_id' => $this->laborRate->id,
            'is_template' => true,
            'is_active' => true,
        ]);

        $this->assertNotNull(session('flash.banner'));
        $this->assertEquals('Item created successfully.', session('flash.banner'));
        $this->assertEquals('success', session('flash.bannerStyle'));
    }

    #[Test]
    public function manager_can_create_item()
    {
        $this->actingAs($this->manager);
        $this->manager->switchTeam($this->team);

        $response = Livewire::test(Create::class)
            ->set('name', 'Test Item')
            ->set('description', 'Test Description')
            ->set('sku', 'TEST-001')
            ->set('unit_of_measure', 'EA')
            ->set('material_cost', 10.50)
            ->set('material_price', 15.75)
            ->set('labor_minutes', 30)
            ->set('labor_rate_id', $this->laborRate->id)
            ->set('is_template', true)
            ->set('is_active', true)
            ->call('create');

        $response->assertRedirect(route('items.index'));

        $this->assertDatabaseHas('items', [
            'team_id' => $this->team->id,
            'name' => 'Test Item',
        ]);

        $this->assertNotNull(session('flash.banner'));
        $this->assertEquals('Item created successfully.', session('flash.banner'));
        $this->assertEquals('success', session('flash.bannerStyle'));
    }

    #[Test]
    public function estimator_cannot_create_item()
    {
        $this->actingAs($this->estimator);
        $this->estimator->switchTeam($this->team);

        Livewire::test(Create::class)
            ->assertStatus(403);
    }

    #[Test]
    public function validates_required_fields()
    {
        $this->actingAs($this->admin);
        $this->admin->switchTeam($this->team);

        Livewire::test(Create::class)
            ->set('name', '')
            ->set('sku', '')
            ->set('unit_of_measure', '')
            ->set('material_cost', '')
            ->set('material_price', '')
            ->set('labor_minutes', '')
            ->set('labor_rate_id', '')
            ->call('create')
            ->assertHasErrors([
                'name' => 'required',
                'sku' => 'required',
                'unit_of_measure' => 'required',
                'material_cost' => 'required',
                'material_price' => 'required',
                'labor_minutes' => 'required',
                'labor_rate_id' => 'required',
            ]);
    }

    #[Test]
    public function validates_decimal_fields()
    {
        $this->actingAs($this->admin);
        $this->admin->switchTeam($this->team);

        Livewire::test(Create::class)
            ->set('material_cost', -1)
            ->set('material_price', -1)
            ->set('labor_minutes', -1)
            ->call('create')
            ->assertHasErrors([
                'material_cost' => 'min',
                'material_price' => 'min',
                'labor_minutes' => 'min',
            ]);
    }

    #[Test]
    public function validates_unique_sku()
    {
        $this->actingAs($this->admin);
        $this->admin->switchTeam($this->team);

        $existingItem = Item::factory()->create([
            'team_id' => $this->team->id,
            'sku' => 'TEST-001',
        ]);

        Livewire::test(Create::class)
            ->set('name', 'Test Item')
            ->set('sku', 'TEST-001')
            ->set('unit_of_measure', 'EA')
            ->set('material_cost', 10.50)
            ->set('material_price', 15.75)
            ->set('labor_minutes', 30)
            ->set('labor_rate_id', $this->laborRate->id)
            ->call('create')
            ->assertHasErrors(['sku' => 'unique']);
    }
} 