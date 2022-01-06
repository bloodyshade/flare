<?php

namespace App\Flare\View\Livewire\Admin\Monsters;

use App\Flare\Models\CharacterSnapShot;
use App\Flare\Models\GameMap;
use Livewire\Component;
use Livewire\WithPagination;
use App\Flare\Models\Monster;
use App\Flare\Models\User;
use App\Flare\View\Livewire\Core\DataTables\WithSorting;
use Cache;

class DataTable extends Component
{
    use WithPagination, WithSorting;

    public $adventureId = null;
    public $onlyMapName = null;
    public $withCelestials = false;
    public $search      = '';
    public $sortField   = 'max_level';
    public $perPage     = 10;
    public $published   = true;
    public $canTest     = false;
    public $testCharacters = [];
    public $only = null;

    protected $paginationTheme = 'bootstrap';

    public function fetchMonsters() {
        if ($this->search !== '') {
            $this->page = 1;
        }

        $monsters = Monster::where('monsters.name', 'like', '%'.$this->search.'%');

        if (!is_null($this->adventureId)) {
            $monsters = $monsters->join('adventure_monster', function($join) {
                $join->on('adventure_monster.monster_id', 'monsters.id')
                     ->where('adventure_monster.adventure_id', $this->adventureId);
            })->select('monsters.*');
        }

        if (!is_null($this->onlyMapName)) {
            $gameMap = GameMap::where('name', $this->onlyMapName)->first();

            $monsters = $monsters->where('game_map_id', $gameMap->id);
        }

        if ($this->only === 'celestials') {
            $monsters = $monsters->where('is_celestial_entity', true);
        }

        return $monsters->where('published', $this->published)
                        ->orderBy($this->sortField, $this->sortBy)
                        ->paginate($this->perPage);
    }

    public function mount() {
        $this->canTest = User::where('is_test', true)->get()->isNotEmpty() && !Cache::has('processing-battle');

        $this->testCharacters = User::with('character')->where('is_test', true)->get();
    }

    public function render()
    {
        return view('components.livewire.admin.monsters.data-table', [
            'monsters' => $this->fetchMonsters()
        ]);
    }
}
