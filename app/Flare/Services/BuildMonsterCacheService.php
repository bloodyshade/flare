<?php

namespace App\Flare\Services;

use App\Flare\Models\Location;
use App\Flare\Values\LocationEffectValue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Collection as DBCollection;
use Illuminate\Support\Collection as IlluminateCollection;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use App\Flare\Models\GameMap;
use App\Flare\Models\Monster;
use App\Flare\Transformers\MonsterTransfromer;

class BuildMonsterCacheService {

    private $manager;

    private $monster;

    public function __construct(Manager $manager, MonsterTransfromer $monster) {
        $this->manager            = $manager;
        $this->monster            = $monster;
    }

    public function buildCache() {
        $monstersCache = [];

        foreach (GameMap::all() as $gameMap) {
            $monsters =  new Collection(
                Monster::where('published', true)
                    ->where('is_celestial_entity', false)
                    ->where('game_map_id', $gameMap->id)
                    ->orderBy('max_level', 'asc')->get(),
                $this->monster
            );

            $monstersCache[$gameMap->name] = $this->manager->createData($monsters)->toArray();
        }

        foreach (Location::whereNotNull('enemy_strength_type')->get() as $location) {
            $monsters = Monster::where('published', true)
                               ->where('is_celestial_entity', false)
                               ->where('game_map_id', $location->map->id)
                               ->orderBy('max_level', 'asc')->get();

            switch ($location->enemy_strength_type) {
                case LocationEffectValue::INCREASE_STATS_BY_HUNDRED_THOUSAND:
                    $monsters = $this->transformMonsterForLocation($monsters, 100000, 0.05);
                    break;
                case LocationEffectValue::INCREASE_STATS_BY_ONE_MILLION:
                    $monsters = $this->transformMonsterForLocation($monsters, 1000000, 0.10);
                    break;
                case LocationEffectValue::INCREASE_STATS_BY_TEN_MILLION:
                    $monsters = $this->transformMonsterForLocation($monsters, 10000000, 0.25);
                    break;
                case LocationEffectValue::INCREASE_STATS_BY_HUNDRED_MILLION:
                    $monsters = $this->transformMonsterForLocation($monsters, 100000000, 0.50);
                    break;
                case LocationEffectValue::INCREASE_STATS_BY_ONE_BILLION:
                    $monsters = $this->transformMonsterForLocation($monsters, 1000000000, 0.70);
                    break;
                default:
                    break;
            }

            $monsters = new Collection($monsters, $this->monster);

            $monstersCache[$location->name] = $this->manager->createData($monsters)->toArray();
        }

        Cache::put('monsters', $monstersCache);
    }

    protected function transformMonsterForLocation(DBCollection $monsters, int $increaseStatsBy, float $increasePercentageBy): IlluminateCollection {
        return $monsters->transform(function($monster) use ($increaseStatsBy, $increasePercentageBy) {
            $monster->str                    += $increaseStatsBy;
            $monster->dex                    += $increaseStatsBy;
            $monster->agi                    += $increaseStatsBy;
            $monster->dur                    += $increaseStatsBy;
            $monster->chr                    += $increaseStatsBy;
            $monster->int                    += $increaseStatsBy;
            $monster->ac                     += $increaseStatsBy;
            $monster->spell_evasion          += $increasePercentageBy;
            $monster->artifact_annulment     += $increasePercentageBy;
            $monster->affix_resistance       += $increasePercentageBy;
            $monster->healing_percentage     += $increasePercentageBy;
            $monster->entrancing_chance      += $increasePercentageBy;
            $monster->devouring_light_chance += $increasePercentageBy;
            $monster->accuracy               += $increasePercentageBy;
            $monster->casting_accuracy       += $increasePercentageBy;
            $monster->dodge                  += $increasePercentageBy;
            $monster->criticality            += $increasePercentageBy;

            return $monster;
        });
    }
}