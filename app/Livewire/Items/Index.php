<?php

namespace App\Livewire\Items;

use App\Models\Item;
use App\Models\Tag;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $showInactive = false;
    public $selectedTags = [];
    public $tagSearch = '';
    public $tagSuggestions = [];
    public $showTagSuggestions = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'showInactive' => ['except' => false],
        'selectedTags' => ['except' => []],
    ];

    public function mount()
    {
        if (!auth()->user()->can('view items')) {
            abort(403);
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingShowInactive()
    {
        $this->resetPage();
    }

    public function updatingSelectedTags()
    {
        $this->resetPage();
    }

    public function updatedTagSearch($value)
    {
        if (strlen($value) >= 2) {
            $this->tagSuggestions = Tag::query()
                ->where('team_id', Auth::user()->currentTeam->id)
                ->where(function ($query) use ($value) {
                    $query->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.en'))) LIKE ?", ['%' . strtolower($value) . '%']);
                })
                ->limit(5)
                ->get()
                ->map(function ($tag) {
                    return [
                        'id' => $tag->id,
                        'name' => $tag->name,
                    ];
                })
                ->toArray();
            $this->showTagSuggestions = true;
        } else {
            $this->tagSuggestions = [];
            $this->showTagSuggestions = false;
        }
    }

    public function selectTag($tagName)
    {
        if (!in_array($tagName, $this->selectedTags)) {
            $this->selectedTags[] = $tagName;
        }
        $this->tagSearch = '';
        $this->tagSuggestions = [];
        $this->showTagSuggestions = false;
    }

    public function removeTag($tagName)
    {
        $this->selectedTags = array_values(array_filter($this->selectedTags, fn($tag) => $tag !== $tagName));
    }

    public function toggleStatus(Item $item)
    {
        if (!auth()->user()->can('edit items')) {
            abort(403);
        }

        // Ensure user can only toggle items from their team
        if ($item->team_id !== Auth::user()->currentTeam->id) {
            abort(403);
        }

        $item->update(['is_active' => !$item->is_active]);

        $this->dispatch('item-updated');
    }

    public function render()
    {
        $query = Item::query()
            ->forTeam(Auth::user()->currentTeam->id)
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('sku', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->when(!$this->showInactive, fn ($query) => $query->where('is_active', true))
            ->when($this->selectedTags, function ($query) {
                $query->withAllTags($this->selectedTags);
            })
            ->orderBy('name');

        $items = $query->paginate(10);

        return view('livewire.items.index', [
            'items' => $items,
        ]);
    }
}
