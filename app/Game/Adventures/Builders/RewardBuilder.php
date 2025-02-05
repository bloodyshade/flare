<?php

namespace App\Game\Adventures\Builders;

use App\Flare\Builders\RandomItemDropBuilder;
use App\Flare\Models\Adventure;
use App\Flare\Models\Character;
use App\Flare\Models\Item;
use App\Flare\Models\ItemAffix;
use App\Flare\Models\Monster;
use App\Flare\Models\Skill;
use App\Game\Battle\Services\BattleDrop;
use Facades\App\Flare\Calculators\XPCalculator;
use Facades\App\Flare\Calculators\SkillXPCalculator;
use Facades\App\Flare\Calculators\DropCheckCalculator;
use Facades\App\Flare\Calculators\GoldRushCheckCalculator;

class RewardBuilder {

    private $battleDrop;

    public function __construct(BattleDrop $battleDrop) {
        $this->battleDrop = $battleDrop;
    }

    /**
     * Fetch the Xp Reward
     *
     * @param Monster $monster
     * @param int $characterLevel
     * @param float $xpReduction | 0.0
     */
    public function fetchXPReward(Monster $monster, int $characterLevel, float $xpReduction = 0.0) {
        return XPCalculator::fetchXPFromMonster($monster, $characterLevel, $xpReduction);
    }

    /**
     * Fetch the skill xp reward
     *
     * @param Skill $skill
     * @param Adventure $adventure
     */
    public function fetchSkillXPReward(Skill $skill, Adventure $adventure) {
        return SkillXPCalculator::fetchSkillXP($skill, $adventure);
    }

    /**
     * Fetch the drops.
     *
     * @param Monster $monster
     * @param Character $character
     * @param Adventure $adventure
     * @return mixed Item | null
     */
    public function fetchDrops(Monster $monster, Character $character, Adventure $adventure, float $gameMapBonus): ?Item {

        $lootingChance = $character->skills->where('name', '=', 'Looting')->first()->skill_bonus;

        $battleDrop = $this->battleDrop->setAdventure($adventure)
                           ->setGameMapBonus($gameMapBonus)
                           ->setLootingChance($lootingChance)
                           ->setMonster($monster);

        $hasDrop = DropCheckCalculator::fetchDropCheckChance($monster, $lootingChance, $gameMapBonus, $adventure);

        return $battleDrop->handleDrop($character, $hasDrop, true);
    }

    /**
     * Fetches the quest drop from a monster.
     *
     * @param Monster $monster
     * @param Character $character
     * @param Adventure $adventure
     * @param array $rewards
     * @return mixed|null
     */
    public function fetchQuestItemFromMonster(Monster $monster, Character $character, Adventure $adventure, array $rewards, float $gameMapBonus) {
        if (!is_null($monster->questItem)) {
            $lootingChance = $character->skills->where('name', '=', 'Looting')->first()->skill_bonus;

            $hasDrop = DropCheckCalculator::fetchQuestItemDropCheck($monster, $lootingChance, $gameMapBonus, $adventure);

            $hasItem = $character->inventory->slots->filter(function($slot) use ($monster) {
                return $slot->item_id === $monster->questItem->id;
            })->all();

            if ($hasDrop && empty($hasItem) && $this->questItemNotInRewards($monster->questItem->id, $rewards['items'])) {
                return $monster->questItem;
            }

            return null;
        }
    }

    /**
     * Fetches a gold rush.
     *
     * If a gold rush is not possible, we return the monsters gold.
     *
     * @param Monster $monster
     * @param Character $character
     * @param Adventure $adventure
     * @return int
     */
    public function fetchGoldRush(Monster $monster, Character $character, Adventure $adventure, float $gameMapBonus = 0.0): int {

        $hasGoldRush = GoldRushCheckCalculator::fetchGoldRushChance($monster, $gameMapBonus, $adventure);

        if ($hasGoldRush) {
            return $monster->gold + ($character->gold * 0.03);
        }

        return $monster->gold;
    }

    /**
     * Make sure the quest item is not already in the list of item rewards.
     *
     * @param int $id
     * @param array $rewardItems
     * @return bool
     *
     * @codeCoverageIgnore
     */
    protected function questItemNotInRewards(int $id, array $rewardItems): bool {
        if (empty($rewardItems)) {
             return true;
        }

        $has = false;

        foreach ($rewardItems as $item) {

            if ($item['id'] === $id) {
                $has = true;
            }
        }

        return $has;
    }
}
