<?php

namespace App\Flare\View\Livewire\Kingdom\Logs;

use Livewire\Component;
use Livewire\WithPagination;
use App\Flare\View\Livewire\Core\DataTables\WithSelectAll;
use App\Flare\View\Livewire\Core\DataTables\WithSorting;

class DataTable extends Component {

    use WithSorting, WithPagination, WithSelectAll;

    public $attackLogs;

    public $character;

    public $search             = '';

    public $sortField          = 'items.status';

    public $perPage            = 10;

    protected $paginationTheme = 'bootstrap';

    public function getDataQueryProperty() {
        $character = $this->character;
        $logs      = $this->attackLogs;

        if (strval($this->search) !== '') {
            $logs = $logs->filter(function($log) {
                if (strpos($log->status, strval($this->search)) !== false) {
                    return $log;
                }
            })->all();
        }

        if (is_array($logs)) {
            $logs = collect($logs);
        }

        if ($this->sortBy === 'asc') {
            $logs = $logs->sortBy($this->sortField);
        } else {
            $logs = $logs->sortByDesc($this->sortField);
        }

        return $logs->transform(function($log) {
            $log->from_kingdom_name = $log->from_kingdom->name;
            $log->to_kingdom_name   = $log->to_kingdom->name;

            return $log;
        });
    }

    public function getDataProperty() {

        return $this->dataQuery->paginate($this->perPage);
    }

    public function fetchKingdomAttackLogs() {
        return $this->data;
    }

    public function render()
    {
        $this->selectAllRenderHook();

        return view('components.livewire.kingdom.logs.data-table', [
            'logs' => $this->fetchKingdomAttackLogs()
        ]);
    }
}
