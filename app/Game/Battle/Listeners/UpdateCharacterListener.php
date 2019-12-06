<?php

namespace App\Game\Battle\Listeners;

use App\Flare\Events\ServerMessageEvent;
use App\Flare\Events\UpdateTopBarEvent;
use App\Flare\Events\UpdateCharacterSheetEvent;
use App\Game\Battle\Events\UpdateCharacterEvent;
use App\Game\Battle\Services\CharacterService;

class UpdateCharacterListener
{

    private $characterService;

    public function __construct(CharacterService $characterService) {
        $this->characterService = $characterService;
    }

    /**
     * Handle the event.
     *
     * @param  \App\Game\Battle\UpdateCharacterEvent  $event
     * @return void
     */
    public function handle(UpdateCharacterEvent $event)
    {
        $xp = $event->character->xp + $event->monster->xp;

        if ($xp >= $event->character->xp_next) {
            $this->characterService->levelUpCharacter($event->character);

            $event->character->refresh();

            event(new ServerMessageEvent($event->character->user, 'level_up'));
            event(new UpdateTopBarEvent($event->character));
            event(new UpdateCharacterSheetEvent($event->character));

        } else {
            // If not assign the xp and gold to the character as well as the time out.

            $event->character->xp    = $xp;
            $event->character->gold += $event->monster->gold;

            $event->character->save();

            $event->character->refresh();

            event(new UpdateTopBarEvent($event->character));
            event(new UpdateCharacterSheetEvent($event->character));
        }
    }
}
