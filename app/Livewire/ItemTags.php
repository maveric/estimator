<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Item;
use App\Models\Tag;

class ItemTags extends Component
{
    public Item $item;
    public string $newTag = '';
    public $tags = [];
    public $suggestions = [];
    public $showSuggestions = false;

    protected $listeners = ['tagAdded' => '$refresh'];

    public function mount(Item $item)
    {
        $this->item = $item;
        $this->loadTags();
    }

    public function loadTags()
    {
        $this->tags = $this->item->tags->map(function ($tag) {
            return [
                'id' => $tag->id,
                'name' => $tag->name,
            ];
        })->toArray();
    }

    public function updatedNewTag($value)
    {
        if (strlen($value) >= 2) {
            $this->suggestions = Tag::query()
                ->where('team_id', $this->item->team_id)
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
            $this->showSuggestions = true;
        } else {
            $this->suggestions = [];
            $this->showSuggestions = false;
        }
    }

    public function selectSuggestion($tagName)
    {
        $this->newTag = $tagName;
        $this->addTag();
    }

    public function addTag()
    {
        if (empty($this->newTag)) {
            return;
        }

        // Split by commas and trim whitespace
        $tags = array_map('trim', explode(',', $this->newTag));
        
        // Add each tag
        foreach ($tags as $tag) {
            if (!empty($tag)) {
                $this->item->attachTag($tag);
            }
        }

        $this->newTag = '';
        $this->suggestions = [];
        $this->showSuggestions = false;
        $this->loadTags();
    }

    public function removeTag($tagName)
    {
        $this->item->detachTag($tagName);
        $this->loadTags();
    }

    public function render()
    {
        return view('livewire.item-tags');
    }
}
