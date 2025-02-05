<?php

namespace App\Game\Skills\Services;

use App\Flare\Models\Inventory;
use App\Game\Core\Events\CharacterInventoryDetailsUpdate;
use App\Game\Core\Events\CharacterInventoryUpdateBroadCastEvent;
use App\Game\Core\Events\UpdateQueenOfHeartsPanel;
use App\Game\Core\Services\RandomEnchantmentService;
use App\Game\Skills\Events\UpdateCharacterEnchantingList;
use App\Game\Skills\Values\SkillTypeValue;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use App\Flare\Builders\CharacterInformationBuilder;
use App\Flare\Events\ServerMessageEvent;
use App\Flare\Events\UpdateSkillEvent;
use App\Flare\Models\Character;
use App\Flare\Models\GameSkill;
use App\Flare\Models\InventorySlot;
use App\Flare\Models\Item;
use App\Flare\Models\ItemAffix;
use App\Flare\Models\Skill;
use App\Game\Core\Events\CraftedItemTimeOutEvent;
use App\Game\Core\Traits\ResponseBuilder;
use App\Game\Skills\Services\Traits\UpdateCharacterGold;
use App\Game\Messages\Events\ServerMessageEvent as GameServerMessageEvent;
use Illuminate\Support\Facades\Log;

class EnchantingService {

    use ResponseBuilder, UpdateCharacterGold;

    /**
     * @var CharacterInformationBuilder $characterInformationBuilder;
     */
    private $characterInformationBuilder;

    /**
     * @var EnchantItemService $enchantItemService
     */
    private $enchantItemService;

    /**
     * @var RandomEnchantmentService
     */
    private $randomEnchantmentService;

    /**
     * @var bool $sentToEasyMessage
     */
    private $sentToEasyMessage = false;

    /**
     * Only set if the affix to be applied was too easy to enchant.
     *
     * @var bool $wasTooEasy
     */
    private $wasTooEasy = false;

    /**
     * Constructor
     *
     * @param CharacterInformationBuilder $characterInformationBuilder
     * @param EnchantItemService $enchantItemService
     * @return void
     */
    public function __construct(CharacterInformationBuilder $characterInformationBuilder,
                                EnchantItemService $enchantItemService,
                                RandomEnchantmentService $randomEnchantmentService)
    {

        $this->characterInformationBuilder = $characterInformationBuilder;
        $this->enchantItemService          = $enchantItemService;
        $this->randomEnchantmentService    = $randomEnchantmentService;
    }

    /**
     * Fetches the affixes for a character.
     *
     * Only returns that which the player has the skill level and intelligence for.
     *
     * @param Character $character
     * @return array
     */
    public function fetchAffixes(Character $character): array {
        $characterInfo   = $this->characterInformationBuilder->setCharacter($character);;
        $enchantingSkill = $this->getEnchantingSkill($character);

        return [
            'affixes'             => $this->getAvailableAffixes($characterInfo, $enchantingSkill),
            'character_inventory' => array_values($this->fetchCharacterInventory($character)),
        ];
    }

    /**
     * Does the cost supplied actually match the actual cost?
     *
     * @param array $enchantmentIds
     * @param int $itemId
     * @return bool
     */
    public function getCostOfEnchantment(array $enchantmentIds, int $itemId): bool {
        $itemAffixes   = ItemAffix::findMany($enchantmentIds);
        $itemToEnchant = Item::find($itemId);

        if (is_null($itemAffixes)) {
            return false;
        }

        if (is_null($itemToEnchant)) {
            return false;
        }

        $cost = $itemAffixes->sum('cost');

        foreach ($itemAffixes as $itemAffix) {
            if (!is_null($itemToEnchant->{'item_' . $itemAffix->type . '_id'})) {
                $cost += 1000;
            }
        }

        return $cost;
    }

    /**
     * Enchant an item.
     *
     * Attempts to enchant an item with the supplied affixes and slot.
     *
     * The params passed in must be the request params coming back from the request.
     *
     * The array returned contains the the status and the details, either a list of
     * the characters inventory and their affixes they can enchant or a error message.
     *
     * eg, ['message' => '', 'status' => 422] or
     * ['affixes' => Collection, 'character_inventory' => [...], 'status' => 200]
     *
     * @param Character $character
     * @param array $params
     * @param InventorySlot $slot
     * @return array
     */
    public function enchant(Character $character, array $params, InventorySlot $slot): void {
        $characterInfo   = $this->characterInformationBuilder->setCharacter($character);
        $enchantingSkill = $this->getEnchantingSkill($character);

        if ($params['cost'] > $character->gold) {
            new GameServerMessageEvent($character->user, 'Not Enough Gold.');

            return;
        }

        $this->updateCharacterGold($character, $params['cost']);

        $this->attachAffixes($params['affix_ids'], $slot, $enchantingSkill, $character);

        $this->enchantItemService->updateSlot($slot);

        $this->updateCharacterAffixList($character, $characterInfo, $enchantingSkill);

        event(new CharacterInventoryUpdateBroadCastEvent($character->user, 'inventory'));

        event(new CharacterInventoryDetailsUpdate($character->user));
    }

    public function timeForEnchanting(Item $item) {

        if (!is_null($item->itemPrefix) && !is_null($item->itemSuffix)) {
            return 'triple';
        }

        if (!is_null($item->itemPrefix) || !is_null($item->itemSuffix)) {
            return 'double';
        }

        return null;
    }

    public function getSlotFromInventory(Character $character, int $slotId) {
        $inventory = Inventory::where('character_id', $character->id)->first();

        return InventorySlot::where('id', $slotId)->where('inventory_id', $inventory->id)->where('equipped', false)->first();
    }

    protected function updateCharacterAffixList(Character $character, CharacterInformationBuilder $characterInfo, Skill $enchantingSkill) {
        $user             = $character->user;
        $availableAffixes = $this->getAvailableAffixes($characterInfo, $enchantingSkill);
        $inventory        = $this->fetchCharacterInventory($character);

        event(new UpdateCharacterEnchantingList($user, $availableAffixes, $inventory));
    }

    protected function getEnchantingSkill(Character $character): Skill {
        $gameSkill = GameSkill::where('type', SkillTypeValue::ENCHANTING)->first();

        return Skill::where('character_id', $character->id)->where('game_skill_id', $gameSkill->id)->first();
    }

    protected function fetchCharacterInventory(Character $character): array {
        return $character->refresh()->inventory->slots->filter(function($slot) {
            if ($slot->item->type !== 'quest' && $slot->item->type !== 'alchemy' && !$slot->equipped) {
                if (!is_null($slot->item->item_prefix_id)) {
                    if (!$slot->item->itemPrefix->randomly_generated) {
                        return $slot->item->load('itemSuffix', 'itemPrefix')->toArray();
                    }
                } else {
                    return $slot;
                }

                if (!is_null($slot->item->item_suffix_id)) {
                    if (!$slot->item->itemSuffix->randomly_generated) {
                        return $slot->item->load('itemSuffix', 'itemPrefix')->toArray();
                    }
                } else {
                    return $slot;
                }
            }
        })->values()->toArray();
    }

    protected function getAvailableAffixes(CharacterInformationBuilder $builder, Skill $enchantingSkill): Collection {

        $currentInt = $builder->statMod('int');

        // If the current intelligence is over 1 billion,
        // set the max to 1 billion as enchanting will never go over this.
        if ($currentInt > 1000000000) {
            $currentInt = 1000000000;
        }

        return ItemAffix::select('name', 'cost', 'id', 'type')
                        ->where('int_required', '<=', $currentInt)
                        ->where('skill_level_required', '<=', $enchantingSkill->level)
                        ->where('randomly_generated', false)
                        ->orderBy('cost', 'asc')
                        ->get();
    }

    protected function attachAffixes(array $affixes, InventorySlot $slot, Skill $enchantingSkill, Character $character) {
        foreach ($affixes as $affixId) {
            $affix = ItemAffix::find($affixId);

            // Reset.
            $this->wasTooEasy = false;

            if ($enchantingSkill->level < $affix->skill_level_required) {
                event(new ServerMessageEvent($character->user, 'to_hard_to_craft'));

                return;
            }

            if ($enchantingSkill->level >= $affix->skill_level_trivial) {
                if (!$this->sentToEasyMessage) {
                    event(new ServerMessageEvent($character->user, 'to_easy_to_craft'));
                    $this->sentToEasyMessage = true;
                }

                $this->processedEnchant($slot, $affix, $character, $enchantingSkill, true);

                $this->wasTooEasy = true;
            }

            /**
             * If the affix wasn't too easy to attach, attempt to enchant with the difficulty check
             * in place.
             *
             * If we fail to do this then we return from the loop.
             */
            if (!$this->wasTooEasy) {
                if (!$this->processedEnchant($slot, $affix, $character, $enchantingSkill)) {
                    return;
                }
            }
        }
    }

    protected function processedEnchant(InventorySlot $slot, ItemAffix $affix, Character $character, Skill $enchantingSkill, bool $tooEasy = false) {
        $enchanted = $this->enchantItemService->attachAffix($slot->item, $affix, $enchantingSkill, $tooEasy);

        if ($enchanted) {
            $this->appliedEnchantment($slot, $affix, $character, $enchantingSkill, $tooEasy);
        } else {
            $this->failedToApplyEnchantment($slot, $affix, $character);

            return false;
        }

        return true;
    }

    protected function appliedEnchantment(InventorySlot $slot, ItemAffix $affix, Character $character, Skill $enchantingSkill, bool $tooEasy = false) {
        $message = 'Applied enchantment: '.$affix->name.' to: ' . $slot->item->refresh()->affix_name;

        event(new ServerMessageEvent($character->user, 'enchanted', $message));

        event(new UpdateQueenOfHeartsPanel($character->user, $this->randomEnchantmentService->fetchDataForApi($character)));

        if (!$tooEasy) {
            event(new UpdateSkillEvent($enchantingSkill));
        }
    }

    protected function failedToApplyEnchantment(InventorySlot $slot, ItemAffix $affix, Character $character) {
        $message = 'You failed to apply '.$affix->name.' to: ' . $slot->item->refresh()->affix_name . '. The item shatters before you. You lost the investment.';

        event(new ServerMessageEvent($character->user, 'enchantment_failed', $message));

        $this->enchantItemService->deleteSlot($slot);
    }
}
