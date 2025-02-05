<?php

namespace App\Game\Adventures\Controllers\Api;

use App\Game\Adventures\Requests\AdventureEmbarkRequest;
use Cache;
use App\Http\Controllers\Controller;
use App\Flare\Events\UpdateTopBarEvent;
use App\Flare\Models\Adventure;
use App\Flare\Models\Character;
use App\Game\Adventures\Events\EmbarkOnAdventureEvent;
use App\Game\Adventures\Events\UpdateAdventureLogsBroadcastEvent;

class AdventureController extends Controller {

    public function __construct() {
        $this->middleware('is.character.dead')->except('getLogs');
        $this->middleware('is.character.adventuring')->except(['cancelAdventure', 'getLogs']);
    }

    public function getLogs() {
        return response()->json(auth()->user()->character->adventureLogs, 200);
    }

    public function adventure(AdventureEmbarkRequest $request, Character $character, Adventure $adventure) {
        $character->update([
            'can_attack'    => false,
            'can_move'      => false,
            'can_craft'     => false,
            'can_adventure' => false,
        ]);

        $adventureLog = $character->adventureLogs()->create([
            'character_id'     => $character->id,
            'adventure_id'     => $adventure->id,
            'in_progress'      => true,
            'over_flow_set_id' => $request->has('over_flow_set') ? $request->over_flow_set : null,
        ]);

        $character->update([
            'current_adventure_id' => $adventureLog->id,
        ]);

        $character = $character->refresh();

        event(new EmbarkOnAdventureEvent($character, $adventure, $request->attack_type));

        event(new UpdateTopBarEvent($character));

        return response()->json([
            'message'                => 'Adventure has started!',
            'adventure_completed_at' => $character->can_adventure_again_at,
        ], 200);
    }

    public function cancelAdventure(Character $character, Adventure $adventure) {
        $character->update([
            'can_attack'             => true,
            'can_move'               => true,
            'can_craft'              => true,
            'can_adventure'          => true,
            'can_adventure_again_at' => null,
            'current_adventure_id'   => null,
        ]);

        $adventureLog = $character->adventureLogs
                                  ->where('adventure_id', $adventure->id)
                                  ->where('in_progress', true)
                                  ->first();

        $adventureLog->update([
            'in_progress' => false,
            'rewards'     => null,
        ]);

        Cache::forget('character_'.$character->id.'_adventure_'.$adventure->id);

        event(new UpdateAdventureLogsBroadcastEvent($character->refresh()->adventureLogs, $character->user, true));

        event(new UpdateTopBarEvent($character));

        $adventureLog->delete();

        return response()->json([
            'message'        => 'Adventure canceled.',
        ], 200);
    }
}
