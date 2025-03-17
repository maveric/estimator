<?php

namespace Tests\Feature\Livewire\Items;

use App\Livewire\Items\Index;
use App\Models\Item;
use App\Models\Team;
use App\Models\User;
use App\Models\LaborRate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $manager;
    private User $estimator;
    private Team $team;
    private Item $item;
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

        // Create a labor rate
        $this->laborRate = LaborRate::factory()->create([
            'team_id' => $this->team->id,
        ]);

        // Create a test item
        $this->item = Item::factory()->create([
            'team_id' => $this->team->id,
            'name' => 'Test Item',
            'sku' => 'TEST-001',
            'material_cost' => 10.00,
            'material_price' => 15.00,
            'labor_minutes' => 30,
            'labor_rate_id' => $this->laborRate->id,
            'is_active' => true,
        ]);
    }

    #[Test]
    public function admin_can_view_items()
    {
        Livewire::actingAs($this->admin)
            ->test(Index::class)
            ->assertSuccessful()
            ->assertSee($this->item->name)
            ->assertSee($this->item->sku);
    }

    #[Test]
    public function manager_can_view_items()
    {
        Livewire::actingAs($this->manager)
            ->test(Index::class)
            ->assertSuccessful()
            ->assertSee($this->item->name)
            ->assertSee($this->item->sku);
    }

    #[Test]
    public function estimator_can_view_items()
    {
        Livewire::actingAs($this->estimator)
            ->test(Index::class)
            ->assertSuccessful()
            ->assertSee($this->item->name)
            ->assertSee($this->item->sku);
    }

    #[Test]
    public function unauthorized_user_cannot_view_items()
    {
        $unauthorizedUser = User::factory()->create();
        $this->team->users()->attach($unauthorizedUser, ['role' => 'editor']);
        $unauthorizedUser->switchTeam($this->team);

        Livewire::actingAs($unauthorizedUser)
            ->test(Index::class)
            ->assertForbidden();
    }

    #[Test]
    public function admin_can_toggle_item_status()
    {
        Livewire::actingAs($this->admin)
            ->test(Index::class)
            ->call('toggleStatus', $this->item)
            ->assertDispatched('item-updated');

        $this->assertFalse($this->item->fresh()->is_active);
    }

    #[Test]
    public function manager_can_toggle_item_status()
    {
        Livewire::actingAs($this->manager)
            ->test(Index::class)
            ->call('toggleStatus', $this->item)
            ->assertDispatched('item-updated');

        $this->assertFalse($this->item->fresh()->is_active);
    }

    #[Test]
    public function estimator_cannot_toggle_item_status()
    {
        Livewire::actingAs($this->estimator)
            ->test(Index::class)
            ->call('toggleStatus', $this->item)
            ->assertForbidden();

        $this->assertTrue($this->item->fresh()->is_active);
    }

    #[Test]
    public function it_can_search_items()
    {
        $this->markTestSkipped('Need to figure out how to work with Livewire .live search');
        $this->admin->switchTeam($this->team);

        $matchingItem = Item::factory()->create([
            'team_id' => $this->team->id,
            'name' => 'Matching Item',
        ]);

        $nonMatchingItem = Item::factory()->create([
            'team_id' => $this->team->id,
            'name' => 'Non-matching Item',
        ]);

        $response = Livewire::actingAs($this->admin)
            ->test(Index::class)
            ->assertSet('search', '')
            ->set('search', 'Matching')
            ->assertSet('search', 'Matching')
            ->assertSee($matchingItem->name)
            ->assertDontSee($nonMatchingItem->name);
    }

    #[Test]
    public function it_can_show_inactive_items()
    {
        $this->actingAs($this->admin);

        $activeItem = Item::factory()->create([
            'team_id' => $this->team->id,
            'is_active' => true,
        ]);

        $inactiveItem = Item::factory()->create([
            'team_id' => $this->team->id,
            'is_active' => false,
        ]);

        Livewire::test(Index::class)
            ->assertSee($activeItem->name)
            ->assertDontSee($inactiveItem->name)
            ->set('showInactive', true)
            ->assertSee($activeItem->name)
            ->assertSee($inactiveItem->name);
    }

    #[Test]
    public function it_only_shows_items_for_current_team()
    {
        $this->actingAs($this->admin);

        $otherTeam = Team::factory()->create();
        
        $currentTeamItem = Item::factory()->create([
            'team_id' => $this->team->id,
        ]);

        $otherTeamItem = Item::factory()->create([
            'team_id' => $otherTeam->id,
        ]);

        Livewire::test(Index::class)
            ->assertSee($currentTeamItem->name)
            ->assertDontSee($otherTeamItem->name);
    }
} 