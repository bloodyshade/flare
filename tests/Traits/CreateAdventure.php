<?php

namespace Tests\Traits;

use App\Flare\Models\Adventure;
use App\Flare\Models\AdventureFloorDescriptions;
use App\Flare\Models\AdventureLog;
use App\Flare\Models\Character;
use App\Flare\Models\GameMap;
use App\Flare\Models\Location;
use App\Flare\Models\Monster;
use Database\Factories\AdventureFloorDescriptionFactory;
use Illuminate\Support\Str;

trait CreateAdventure {

    use CreateItem, CreateMonster, CreateLocation, CreateGameMap;

    public function createNewAdventure(Location $location = null, Monster $monster = null, int $levels = 1, string $name = 'Sample', bool $published = true, bool $withFloorDescriptions = false): Adventure {

        $gameMap = GameMap::first();

        if (is_null($gameMap)) {
            $gameMap = $this->createGameMap();
        }

        $adventure = Adventure::factory()->create([
            'name'             => $name,
            'description'      => 'Sample description',
            'reward_item_id'   => $this->createItem([
                'name'        => 'Item Name',
                'type'        => 'weapon',
                'base_damage' => 1,
                'cost'        => 1,
            ]),
            'levels'           => $levels,
            'time_per_level'   => 1,
            'gold_rush_chance' => 0.10,
            'item_find_chance' => 0.10,
            'skill_exp_bonus'  => 0.10,
            'published'        => $published,
            'location_id'      => !is_null($location) ? $location->id : $this->createLocation([
                'name'                  => 'Sample',
                'game_map_id'           => $gameMap->id,
                'quest_reward_item_id'  => null,
                'description'           => 'bla',
                'is_port'               => false,
                'enemy_strength_type'   => null,
                'x'                     => 16,
                'y'                     => 16,
            ])->id,
        ]);

        if (is_null($monster)) {
            $monster = $this->createMonsterForAdventure();
        }

        $adventure->monsters()->attach($monster);

        if ($withFloorDescriptions) {
            for ($i = 1; $i <= $levels; $i++) {
                $this->createFloorDescription($adventure->id, Str::random(10));
            }
        }

        return $adventure;
    }

    public function createNewAdventureWithManyMonsters(int $monsters = 1, int $levels = 1, string $name = 'Sample', bool $published = true, bool $withFloorDescriptions = false): Adventure {

        $adventure = Adventure::factory()->create([
            'name'             => $name,
            'description'      => 'Sample description',
            'reward_item_id'   => $this->createItem([
                'name'        => 'Item Name',
                'type'        => 'weapon',
                'base_damage' => 1,
                'cost'        => 1,
            ]),
            'levels'           => $levels,
            'time_per_level'   => 1,
            'gold_rush_chance' => 0.10,
            'item_find_chance' => 0.10,
            'skill_exp_bonus'  => 0.10,
            'published'        => $published,
        ]);

        for($i = 0; $i <= $monsters; $i++) {
            $adventure->monsters()->attach($this->createMonsterForAdventure());
        }

        if ($withFloorDescriptions) {
            for ($i = 1; $i <= $levels; $i++) {
                $this->createFloorDescription($adventure->id, Str::random(10));
            }
        }

        return $adventure;
    }

    public function createLog(
        Character $character,
        Adventure $adventure,
        bool $inProgress = false,
        int $lastCompletedLevel = 1
    ): AdventureLog {

        return AdventureLog::factory()->create([
            'character_id'         => $character->id,
            'adventure_id'         => $adventure->id,
            'complete'             => false,
            'in_progress'          => $inProgress,
            'last_completed_level' => $lastCompletedLevel,
            'logs'                 => null,
        ]);
    }

    protected function createMonsterForAdventure(): Monster {
        return $this->createMonster(
            [
                'name' => Str::random(10),
            ]
        );
    }

    protected function createFloorDescription(int $adventureId, string $description) {
        AdventureFloorDescriptions::factory()->create([
            'adventure_id' => $adventureId,
            'description'  => $description,
        ]);
    }
}
