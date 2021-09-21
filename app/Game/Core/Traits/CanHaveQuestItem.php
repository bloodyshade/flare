<?php

namespace App\Game\Core\Traits;

use App\Flare\Models\Character;
use App\Flare\Models\Item;
use App\Flare\Models\Quest;

Trait CanHaveQuestItem {

    public function canHaveItem(Character $character, Item $item): bool {
        $doesntHave = $character->inventory->slots->filter(function ($slot) use ($item) {
            return $slot->item_id === $item->id && $item->type === 'quest';
        })->isEmpty();

        $hasCompletedQuest = $character->questsCompleted->filter(function($questCompleted) use ($item) {
            return $questCompleted->quest->item_id === $item->id;
        })->isEmpty();
        
        if ($hasCompletedQuest) {
            return $doesntHave;
        }

        return false;
    }
}