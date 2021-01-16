<?php

namespace App\Flare\View\Livewire\Admin\Kingdoms\Buildings\Partials;

use App\Admin\Services\UpdateKingdomsService;
use App\Flare\Models\GameBuilding;
use App\Flare\Models\GameUnit;
use Livewire\Component;

class Attributes extends Component
{
    public $gameBuilding;

    public $gameUnits;

    public $editing;

    public $selectedUnits = [];

    public $everyXLevels;

    protected $rules = [
        'gameBuilding.is_walls'                   => 'nullable',
        'gameBuilding.is_church'                  => 'nullable',
        'gameBuilding.is_farm'                    => 'nullable',
        'gameBuilding.is_resource_building'       => 'nullable',
        'gameBuilding.trains_units'               => 'nullable',
        'gameBuilding.wood_cost'                  => 'nullable',
        'gameBuilding.clay_cost'                  => 'nullable',
        'gameBuilding.stone_cost'                 => 'nullable',
        'gameBuilding.iron_cost'                  => 'nullable',
        'gameBuilding.increase_population_amount' => 'nullable',
        'gameBuilding.increase_morale_amount'     => 'nullable',
        'gameBuilding.decrease_morale_amount'     => 'nullable',
        'gameBuilding.increase_wood_amount'       => 'nullable',
        'gameBuilding.increase_clay_amount'       => 'nullable',
        'gameBuilding.increase_stone_amount'      => 'nullable',
        'gameBuilding.increase_iron_amount'       => 'nullable',
        'gameBuilding.increase_durability_amount' => 'nullable',
        'gameBuilding.increase_defence_amount'    => 'nullable',
        'gameBuilding.time_to_build'              => 'nullable',
        'gameBuilding.time_increase_amount'       => 'nullable',
    ];

    protected $listeners = ['validateInput', 'update'];

    public function getUnitSelectionIsDisabledProperty() {
        if (is_null($this->gameBuilding)) {
            return true;
        }

        if (!$this->gameBuilding->trains_units) {
            return true;
        }

        if ($this->gameUnits->isEmpty()) {
            return true;
        }

        return false;
    }

    public function mount() {
        if (is_array($this->gameBuilding)) {
            $this->gameBuilding = GameBuilding::find($this->gameBuilding['id']);
        }

        $this->gameUnits = GameUnit::all();
    }

    public function update($id) {
        $this->gameBuilding = GameBuilding::find($id);

        $this->selectedUnits = $this->gameBuilding->units()->pluck('game_unit_id')->toArray();
    }

    public function validateInput(string $functionName, int $index) {
        $this->validate();

        $isValid = $this->validateSelectedUnits();

        if (!$isValid) {
            return $this->addError('error', 'Your selected units and units per level are greator then your max level.');
        }

        $this->gameBuilding->save();

        $gameBuilding = $this->gameBuilding->refresh();

        $kingdomService = new UpdateKingdomsService();

        if ($gameBuilding->units->isEmpty() && $gameBuilding->trains_unit) {
            $kingdomService->assignUnits($gameBuilding, $this->selectedUnits, $this->everyXLevels);

            $this->selectedUnits = [];
        }

        $kingdomService->updateKingdomBuildings($this->gameBuilding->refresh(), $this->selectedUnits, $this->everyXLevels);

        $message = 'Created Building: ' . $this->gameBuilding->refresh()->name;

        if ($this->editing) {
            $message = 'Updated Building: ' . $this->gameBuilding->refresh()->name;
        }
        
        $this->emitTo('core.form-wizard', $functionName, $index, true, [
            'type'    => 'success',
            'message' => $message,
        ]);
    }

    public function render()
    {
        return view('components.livewire.admin.kingdoms.buildings.partials.attributes');
    }

    protected function validateSelectedUnits() {
        $total = (count($this->selectedUnits) * $this->everyXLevels) - (count($this->selectedUnits) - 1);

        if ($total > $this->gameBuilding->max_level) {
            return false;
        }

        return true;
    }
}
