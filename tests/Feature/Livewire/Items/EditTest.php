<?php

namespace Tests\Feature\Livewire\Items;

use App\Livewire\Items\Edit;
use App\Models\Item;
use App\Models\LaborRate;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EditTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $manager;
    private $estimator;
    private $team;
    private $item;
    private $laborRate;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles and permissions
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        // Create users with different roles
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->manager = User::factory()->create();
        $this->manager->assignRole('manager');

        $this->estimator = User::factory()->create();
        $this->estimator->assignRole('estimator');

        // Create a team and attach users
        $this->team = Team::factory()->create([
            'user_id' => $this->admin->id,
        ]);

        $this->admin->teams()->attach($this->team, ['role' => 'admin']);
        $this->manager->teams()->attach($this->team, ['role' => 'admin']);
        $this->estimator->teams()->attach($this->team, ['role' => 'admin']);

        $this->admin->switchTeam($this->team);
        $this->manager->switchTeam($this->team);
        $this->estimator->switchTeam($this->team);

        // Create a labor rate for the team
        $this->laborRate = LaborRate::factory()->create([
            'team_id' => $this->team->id,
        ]);

        // Create an item for testing
        $this->item = Item::factory()->create([
            'team_id' => $this->team->id,
            'labor_rate_id' => $this->laborRate->id,
        ]);
    }

    #[Test]
    public function admin_can_view_edit_item_form()
    {
        $this->actingAs($this->admin);

        Livewire::test(Edit::class, ['item' => $this->item])
            ->assertOk()
            ->assertViewIs('livewire.items.edit');
    }

    #[Test]
    public function manager_can_view_edit_item_form()
    {
        $this->actingAs($this->manager);

        Livewire::test(Edit::class, ['item' => $this->item])
            ->assertOk()
            ->assertViewIs('livewire.items.edit');
    }

    #[Test]
    public function estimator_cannot_view_edit_item_form()
    {
        $this->actingAs($this->estimator);

        Livewire::test(Edit::class, ['item' => $this->item])
            ->assertForbidden();
    }

    #[Test]
    public function unauthorized_user_cannot_view_edit_item_form()
    {
        $unauthorizedUser = User::factory()->create();
        $this->actingAs($unauthorizedUser);

        Livewire::test(Edit::class, ['item' => $this->item])
            ->assertForbidden();
    }

    #[Test]
    public function admin_can_update_item()
    {
        $this->actingAs($this->admin);

        $laborRate = LaborRate::factory()->create([
            'team_id' => $this->team->id,
        ]);

        $item = Item::factory()->create([
            'team_id' => $this->team->id,
        ]);

        Livewire::test(Edit::class, ['item' => $item])
            ->set('name', 'Updated Item')
            ->set('description', 'Updated Description')
            ->set('sku', 'TEST-456')
            ->set('unit_of_measure', 'BOX')
            ->set('material_cost', '20.5000')
            ->set('material_price', '25.7500')
            ->set('labor_minutes', 45)
            ->set('labor_rate_id', $laborRate->id)
            ->set('is_template', true)
            ->set('is_active', true)
            ->call('save')
            ->assertRedirect(route('items.index'));

        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'team_id' => $this->team->id,
            'name' => 'Updated Item',
            'description' => 'Updated Description',
            'sku' => 'TEST-456',
            'unit_of_measure' => 'BOX',
            'material_cost' => '20.5000',
            'material_price' => '25.7500',
            'labor_minutes' => 45,
            'labor_rate_id' => $laborRate->id,
            'is_template' => true,
            'is_active' => true,
        ]);
    }

    #[Test]
    public function manager_can_update_item()
    {
        $this->actingAs($this->manager);

        Livewire::test(Edit::class, ['item' => $this->item])
            ->set('name', 'Manager Updated Item')
            ->set('sku', 'MANAGER-SKU')
            ->set('unit_of_measure', 'EA')
            ->set('material_cost', '10.5000')
            ->set('material_price', '15.7500')
            ->set('labor_minutes', '30')
            ->set('labor_rate_id', $this->laborRate->id)
            ->call('save')
            ->assertRedirect(route('items.index'));

        $this->assertDatabaseHas('items', [
            'id' => $this->item->id,
            'name' => 'Manager Updated Item',
            'sku' => 'MANAGER-SKU',
        ]);
    }

    #[Test]
    public function estimator_cannot_update_item()
    {
        $this->actingAs($this->estimator);

        $response = $this->get(route('items.edit', $this->item));
        $response->assertForbidden();

        $this->assertDatabaseMissing('items', [
            'id' => $this->item->id,
            'name' => 'Should Not Update',
        ]);
    }

    #[Test]
    public function validates_required_fields()
    {
        $this->actingAs($this->admin);

        Livewire::test(Edit::class, ['item' => $this->item])
            ->set('name', '')
            ->set('sku', '')
            ->set('unit_of_measure', '')
            ->set('material_cost', '')
            ->set('material_price', '')
            ->set('labor_minutes', '')
            ->set('labor_rate_id', '')
            ->call('save')
            ->assertHasErrors([
                'name',
                'sku',
                'unit_of_measure',
                'material_cost',
                'material_price',
                'labor_minutes',
                'labor_rate_id',
            ]);
    }

    #[Test]
    public function validates_unique_sku()
    {
        $this->actingAs($this->admin);

        // Create another item with a known SKU
        $existingItem = Item::factory()->create([
            'team_id' => $this->team->id,
            'sku' => 'EXISTING-SKU',
        ]);

        Livewire::test(Edit::class, ['item' => $this->item])
            ->set('sku', 'EXISTING-SKU')
            ->call('save')
            ->assertHasErrors(['sku']);
    }

    #[Test]
    public function validates_decimal_fields()
    {
        $this->actingAs($this->admin);

        Livewire::test(Edit::class, ['item' => $this->item])
            ->set('material_cost', -1)
            ->set('material_price', -1)
            ->set('labor_minutes', -1)
            ->call('save')
            ->assertHasErrors([
                'material_cost',
                'material_price',
                'labor_minutes',
            ]);
    }

    #[Test]
    public function validates_labor_rate_belongs_to_team()
    {
        $this->actingAs($this->admin);

        // Create a labor rate for a different team
        $otherTeam = Team::factory()->create();
        $otherLaborRate = LaborRate::factory()->create([
            'team_id' => $otherTeam->id,
        ]);

        Livewire::test(Edit::class, ['item' => $this->item])
            ->set('labor_rate_id', $otherLaborRate->id)
            ->call('save')
            ->assertHasErrors(['labor_rate_id']);
    }
} 