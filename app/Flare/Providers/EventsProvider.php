<?php
namespace App\Flare\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Flare\Events\CreateCharacterEvent;
use App\Flare\Events\UpdateCharacterSheetEvent;
use App\Flare\Events\UpdateCharacterInventoryEvent;
use App\Flare\Listeners\UpdateCharacterInventoryListener;
use App\Flare\Listeners\UpdateCharacterSheetListener;
use App\Flare\Listeners\CreateCharacterListener;

class EventsProvider extends ServiceProvider {

    protected $listen = [

        // When a user registers for the first tme:
        CreateCharacterEvent::class => [
            CreateCharacterListener::class,
        ],

        // When a character sheet should update.
        UpdateCharacterSheetEvent::class => [
            UpdateCharacterSheetListener::class,
        ],

        // When a characetr inventory updates:
        UpdateCharacterInventoryEvent::class => [
            UpdateCharacterInventoryListener::class,
        ]
    ];

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
