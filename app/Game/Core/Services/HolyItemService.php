<?php

namespace App\Game\Core\Services;

use App\Game\Core\Events\CraftedItemTimeOutEvent;
use App\Game\Messages\Events\GlobalMessageEvent;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Collection as DBCollection;
use App\Flare\Events\UpdateTopBarEvent;
use App\Flare\Models\Character;
use App\Flare\Models\Inventory;
use App\Flare\Models\InventorySlot;
use App\Flare\Models\Item;
use App\Flare\Values\ItemHolyValue;
use App\Game\Core\Events\CharacterInventoryDetailsUpdate;
use App\Game\Core\Events\CharacterInventoryUpdateBroadCastEvent;
use App\Game\Core\Traits\ResponseBuilder;

class HolyItemService {

    use ResponseBuilder;

    public function fetchSmithingItems(Character $character): array {
        $slots = $this->getSlots($character);

        return $this->successResult([
            'items'   => $this->fetchValidItems($slots)->values(),
            'alchemy' => $this->fetchAlchemyItems($slots)->values(),
        ]);
    }

    public function applyOil(Character $character, array $params): array {
        event(new CraftedItemTimeOutEvent($character));

        $inventory = Inventory::where('character_id', $character->id)->first();

        $itemSlot    = InventorySlot::where('inventory_id', $inventory->id)->where('item_id', $params['item_id'])->first();
        $alchemySlot = InventorySlot::where('inventory_id', $inventory->id)->where('item_id', $params['alchemy_item_id'])->first();

        if (!$this->validateCost($itemSlot->item, $alchemySlot->item, $params['gold_dust_cost'])) {
            event(new GlobalMessageEvent($character->name . ' has been caught cheating. The Purgatory Smith lets out a loud roar. Tell them to stop cheating!'));

            return $this->errorResult('Error: Cost does not match.');
        }

        if (!$this->canApplyAdditionalStacks($itemSlot->item)) {
            return $this->errorResult('Error: No stacks left.');
        }

        $character->update([
            'gold_dust' => $character->gold_dust - $params['gold_dust_cost'],
        ]);

        $this->applyStack($itemSlot, $alchemySlot);

        $character = $character->refresh();

        event(new UpdateTopBarEvent($character));

        event(new CharacterInventoryUpdateBroadCastEvent($character->user, 'inventory'));

        event(new CharacterInventoryDetailsUpdate($character->user));

        return $this->fetchSmithingItems($character);
    }

    protected function validateCost(Item $item, Item $alchemyItem, int $originalAmount): bool {
        $baseCost  = $item->holy_stacks * 10000;
        $totalCost = $baseCost * $alchemyItem->holy_level;

        return $totalCost === $originalAmount;
    }

    protected function canApplyAdditionalStacks(Item $item): bool {
        $stacksLeft = $item->holy_stacks - $item->holy_stacks_applied;

        return $stacksLeft > 0;
    }

    protected function applyStack(InventorySlot $itemSlot, InventorySlot $alchemyItemSlot): InventorySlot {
        $holyItemEffect = new ItemHolyValue($alchemyItemSlot->item->holy_level);

        if ($itemSlot->item->appliedHolyStacks->isEmpty()) {
            $newItem = $itemSlot->item->duplicate();

            $newItem->update([
                'market_sellable' => true,
            ]);

            $newItem->appliedHolyStacks()->create([
                'item_id'                  => $newItem->id,
                'devouring_darkness_bonus' => $holyItemEffect->getRandomDevoidanceIncrease(),
                'stat_increase_bonus'      => $holyItemEffect->getRandomStatIncrease() / 100,
            ]);

            $inventory = Inventory::find($itemSlot->inventory_id);

            $itemSlot->delete();

            $alchemyItemSlot->delete();

            return $inventory->slots()->create([
                'inventory_id' => $inventory->id,
                'item_id'      => $newItem->id,
            ]);
        }

        $alchemyItemSlot->delete();

        $itemSlot->item->appliedHolyStacks()->create([
            'item_id'                  => $itemSlot->item->id,
            'devouring_darkness_bonus' => $holyItemEffect->getRandomDevoidanceIncrease(),
            'stat_increase_bonus'      => $holyItemEffect->getRandomStatIncrease() / 100,
        ]);

        return $itemSlot->refresh();
    }

    protected function fetchAlchemyItems(DBCollection $slots): Collection {
        return $slots->filter(function($slot) {
            return $slot->item->can_use_on_other_items;
        });
    }

    protected function fetchValidItems(DBCollection $slots): Collection {
        return $slots->filter(function($slot) {
            return ($slot->item->holy_stacks - $slot->item->holy_stacks_applied) > 0;
        });
    }

    protected function getSlots(Character $character): DBCollection {
        $inventory = Inventory::where('character_id', $character->id)->first();

        return InventorySlot::where('inventory_slots.inventory_id', $inventory->id)->where('inventory_slots.equipped', false)->get();
    }
}
