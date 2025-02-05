<?php

namespace App\Flare\Transformers;

use App\Flare\Models\GameBuilding;
use App\Flare\Models\GameBuildingUnit;
use App\Flare\Models\GameUnit;
use App\Flare\Models\PassiveSkill;
use App\Game\Kingdoms\Values\BuildingActions;
use App\Game\Kingdoms\Values\KingdomMaxValue;
use App\Game\Kingdoms\Values\UnitCosts;
use App\Game\PassiveSkills\Values\PassiveSkillTypeValue;
use League\Fractal\TransformerAbstract;
use App\Flare\Models\Kingdom;
use Illuminate\Support\Collection;

class KingdomTransformer extends TransformerAbstract {

    protected $defaultIncludes = [
        'buildings', 'recruitable_units'
    ];

    /**
     * Gets the response data for the character sheet
     *
     * @param Character $character
     * @return mixed
     */
    public function transform(Kingdom $kingdom) {
        return [
            'id'                 => $kingdom->id,
            'character_id'       => $kingdom->character_id,
            'game_map_id'        => $kingdom->game_map_id,
            'name'               => $kingdom->name,
            'color'              => $kingdom->color,
            'max_stone'          => $kingdom->max_stone,
            'max_wood'           => $kingdom->max_wood,
            'max_clay'           => $kingdom->max_clay,
            'max_iron'           => $kingdom->max_iron,
            'current_stone'      => $kingdom->current_stone,
            'current_wood'       => $kingdom->current_wood,
            'current_clay'       => $kingdom->current_clay,
            'current_iron'       => $kingdom->current_iron,
            'current_population' => $kingdom->current_population,
            'max_population'     => $kingdom->max_population,
            'x_position'         => $kingdom->x_position,
            'y_position'         => $kingdom->y_position,
            'current_morale'     => $kingdom->current_morale,
            'max_morale'         => $kingdom->max_morale,
            'treasury'           => $kingdom->treasury,
            'gold_bars'          => $kingdom->gold_bars,
            'building_queue'     => $kingdom->buildingsQueue,
            'unit_queue'         => $kingdom->unitsQueue,
            'current_units'      => $kingdom->units,
            'unit_movement'      => $kingdom->unitsMovementQueue,
            'treasury_defence'   => $kingdom->treasury / KingdomMaxValue::MAX_TREASURY,
            'passive_defence'    => $kingdom->fetchDefenceBonusFromPassive(),
            'unit_cost_reduction'       => $kingdom->fetchUnitCostReduction(),
            'building_cost_reduction'   => $kingdom->fetchBuildingCostReduction(),
            'iron_cost_reduction'       => $kingdom->fetchIronCostReduction(),
            'population_cost_reduction' => $kingdom->fetchPopulationCostReduction(),
            'can_access_bank'           => $this->canAccessGoblinCoinBank($kingdom),
            'treasury_defence'          => $kingdom->fetchTreasuryDefenceBonus(),
            'walls_defence'             => $kingdom->getWallsDefence(),
            'gold_bars_defence'         => $kingdom->fetchGoldBarsDefenceBonus(),
            'defence_bonus'             => $kingdom->fetchKingdomDefenceBonus(),
            'unit_time_reduction'       => $this->fetchTimeReductionBonus($kingdom, 'unit_time_reduction'),
            'building_time_reduction'   => $this->fetchTimeReductionBonus($kingdom, 'building_time_reduction'),
        ];
    }

    protected function includeBuildings(Kingdom $kingdom) {
        return $this->collection($kingdom->buildings, resolve(KingdomBuildingTransformer::class));
    }

    protected function includeRecruitableUnits(Kingdom $kingdom) {
        $buildings = $kingdom->buildings()->whereHas('gameBuilding', function($query) {
            return $query->where('trains_units', true);
        })->get();

        $collection = new Collection;

        foreach($buildings as $building) {
            if ($building->is_locked) {
                continue;
            }

            $units = GameBuildingUnit::where('game_building_id', $building->gameBuilding->id)
                                     ->where('required_level', '<=', $building->level)
                                     ->get();


            foreach($units as $unit) {
                $unit = GameUnit::find($unit->game_unit_id);

                $collection->push($unit);
            }
        }

        $collection = $collection->transform(function($unit) use($kingdom) {
            $kingdomUnitInfo = $kingdom->units()->where('game_unit_id', $unit->id)->first();

            $unit->kingdom_current_amount = !is_null($kingdomUnitInfo) ? $kingdomUnitInfo->amount : 0;
            $unit->kingdom_max_amount     = $kingdom->max_population;
            $unit->max_recruitable        = number_format(KingdomMaxValue::MAX_UNIT);
            $unit->can_recruit_more       = is_null($kingdomUnitInfo) || $kingdomUnitInfo->amount < KingdomMaxValue::MAX_UNIT;
            $unit->cost_per_unit          = (new UnitCosts($unit->name))->fetchCost();

            return $unit;
        });

        return $this->collection($collection, resolve(UnitTransformer::class));
    }

    protected function canAccessGoblinCoinBank(Kingdom $kingdom): bool {
        $building = $kingdom->buildings->filter(function($building) {
            return $building->name === BuildingActions::GOBLIN_COIN_BANK;
        })->first();

        if (is_null($building)) {
            return false;
        }

        return !$building->is_locked && BuildingActions::canAccessGoblinBank($building);
    }

    protected function fetchTimeReductionBonus(Kingdom $kingdom, string $timeReductionAttribute): float {
        $character = $kingdom->character;

        if (is_null($character)) {
            return 0.0;
        }

        $skill = $character->skills->filter(function($skill) {
            return $skill->type()->effectsKingdom();
        })->first();

        return $skill->{$timeReductionAttribute};
    }
}
