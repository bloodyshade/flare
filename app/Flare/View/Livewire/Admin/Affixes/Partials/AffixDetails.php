<?php

namespace App\Flare\View\Livewire\Admin\Affixes\Partials;

use App\Flare\Models\ItemAffix;
use Livewire\Component;

class AffixDetails extends Component
{

    public $itemAffix;

    public $types = [
        'suffix',
        'prefix'
    ];

    protected $rules = [
        'itemAffix.name'        => 'required',
        'itemAffix.type'        => 'required',
        'itemAffix.description' => 'required',
        'itemAffix.cost'        => 'required',
    ];

    protected $messages = [
        'itemAffix.name.required'        => 'Name is required.',
        'itemAffix.type.required'        => 'Type is required.',
        'itemAffix.description.required' => 'Description is required.',
        'itemAffix.cost.required'        => 'Cost is required.',
    ];

    protected $listeners = ['validateInput'];

    public function validateInput(string $functionName, int $index) {     
        $this->validate();

        $this->itemAffix->save();

        $this->emitTo('manage', 'storeModel', $this->itemAffix->refresh());
        $this->emitTo('manage', $functionName, $index, true);
    }

    public function mount() {

        if (is_null($this->itemAffix)) {
            $this->itemAffix = new ItemAffix;
        }
    }

    public function render()
    {
        return view('components.livewire.admin.affixes.partials.affix-details');
    }
}