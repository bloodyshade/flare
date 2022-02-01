<?php

namespace App\Game\Battle\Handlers;


use App\Flare\Builders\RandomAffixGenerator;
use App\Flare\Models\Character;
use App\Flare\Models\Faction;
use App\Flare\Models\Item as ItemModel;
use App\Flare\Models\Monster;
use App\Flare\Values\MaxCurrenciesValue;
use App\Flare\Values\RandomAffixDetails;
use App\Game\Core\Events\CharacterInventoryUpdateBroadCastEvent;
use App\Game\Core\Values\FactionLevel;
use App\Game\Core\Values\FactionType;
use App\Game\Messages\Events\GlobalMessageEvent;
use App\Game\Messages\Events\ServerMessageEvent;

class FactionHandler {

    private $randomAffixGenerator;

    public function __construct(RandomAffixGenerator $randomAffixGenerator){
        $this->randomAffixGenerator = $randomAffixGenerator;
    }

    public function handleFaction(Character $character, Monster $monster) {
        $this->handleFactionPoints($character, $monster);
    }

    protected function handleFactionPoints(Character $character, Monster $monster) {
        $mapId   = $monster->gameMap->id;
        $mapName = $monster->gameMap->name;
        $faction = $character->factions()->where('game_map_id', $mapId)->first();

        $faction->current_points += FactionLevel::gatPointsPerLevel($faction->current_level);

        if ($faction->current_points > $faction->points_needed) {
            $faction->current_points = $faction->points_needed;
        }

        if ($faction->current_points === $faction->points_needed && !FactionLevel::isMaxLevel($faction->current_level, $faction->current_points)) {

            return $this->handleFactionLevelUp($character, $faction, $mapName);

        } else if (FactionLevel::isMaxLevel($faction->current_level, $faction->current_points) && !$faction->maxed) {

            return $this->handleFactionMaxedOut($character, $faction, $mapName);
        }

        $faction->save();
    }

    protected function handleFactionLevelUp(Character $character, Faction $faction, string $mapName) {
        event(new ServerMessageEvent($character->user, $mapName . ' faction has gained a new level!'));

        $faction = $this->updateFaction($faction);

        $this->rewardPlayer($character, $faction, $mapName, FactionType::getTitle($faction->current_level));

        event(new ServerMessageEvent($character->user, 'Achieved title: ' . FactionType::getTitle($faction->current_level) . ' of ' . $mapName));
    }

    protected function handleFactionMaxedOut(Character $character, Faction $faction, string $mapName) {
        event(new ServerMessageEvent($character->user, $mapName . ' faction has become maxed out!'));
        event(new GlobalMessageEvent($character->name . 'Has maxed out the faction for: ' . $mapName . ' They are considered legendary among the people of this land.'));

        $this->rewardPlayer($character, $faction, $mapName, FactionType::getTitle($faction->current_level));

        $faction->update([
            'maxed' => true,
        ]);
    }

    protected function updateFaction(Faction $faction): Faction {

        $newLevel = $faction->current_level + 1;

        $faction->update([
            'current_points' => 0,
            'current_level'  => $newLevel,
            'points_needed'  => FactionLevel::getPointsNeeded($newLevel),
            'title'          => FactionType::getTitle($newLevel)
        ]);

        return $faction->refresh();
    }

    protected function rewardPlayer(Character $character, Faction $faction, string $mapName, ?string $title = null) {
        $character = $this->giveCharacterGold($character, $faction->current_level);
        $item      = $this->giveCharacterRandomItem($character);

        event(new ServerMessageEvent($character->user, 'Achieved title: ' . $title . ' of ' . $mapName));

        if ($character->isInventoryFull()) {

            event(new ServerMessageEvent($character->user, 'You got no item as your inventory is full. Clear space for the next time!'));
        } else {

            $character->inventory->slots()->create([
                'inventory_id' => $character->inventory->id,
                'item_id'      => $item->id,
            ]);

            $character = $character->refresh();

            event(new CharacterInventoryUpdateBroadCastEvent($character->user));

            event(new ServerMessageEvent($character->user, 'Rewarded with (item with randomly generated affix(es)): ' . $item->affix_name));
        }
    }

    protected function giveCharacterGold(Character $character, int $factionLevel) {
        $gold = FactionLevel::getGoldReward($factionLevel);

        $characterNewGold = $character->gold + $gold;

        $cannotHave = (new MaxCurrenciesValue($characterNewGold, 0))->canNotGiveCurrency();

        if ($cannotHave) {
            $characterNewGold = MaxCurrenciesValue::MAX_GOLD;

            $character->gold = $characterNewGold;
            $character->save();

            event(new ServerMessageEvent($character->user, 'Received faction gold reward: ' . number_format($gold) . ' gold. You are now gold capped.'));

            return $character->refresh();
        }

        $character->gold += $gold;

        event(new ServerMessageEvent($character->user, 'Received faction gold reward: ' . number_format($gold) . ' gold.'));

        $character->save();

        return $character->refresh();
    }

    protected function giveCharacterRandomItem(Character $character) {
        $item = ItemModel::where('cost', '<=', RandomAffixDetails::BASIC)
            ->whereNull('item_prefix_id')
            ->whereNull('item_suffix_id')
            ->where('cost', '<=', 4000000000)
            ->inRandomOrder()
            ->first();


        $randomAffix = $this->randomAffixGenerator
            ->setCharacter($character)
            ->setPaidAmount(RandomAffixDetails::BASIC);

        $duplicateItem = $item->duplicate();

        $duplicateItem->update([
            'item_prefix_id' => $randomAffix->generateAffix('prefix')->id,
        ]);

        if (rand(1, 100) > 50) {
            $duplicateItem->update([
                'item_suffix_id' => $randomAffix->generateAffix('suffix')->id
            ]);
        }

        return $duplicateItem;
    }
}