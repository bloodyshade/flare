<?php

namespace App\Game\Battle\Services;

use App\Game\Core\Events\CharacterInventoryDetailsUpdate;
use Facades\App\Flare\Cache\CoordinatesCache;
use App\Flare\Models\CelestialFight;
use App\Flare\Models\Character;
use App\Flare\Models\CharacterInCelestialFight;
use App\Flare\Services\FightService;
use App\Flare\Values\MaxCurrenciesValue;
use App\Game\Battle\Events\UpdateCelestialFight;
use App\Game\Battle\Handlers\BattleEventHandler;
use App\Game\Battle\Jobs\BattleAttackHandler;
use App\Game\Core\Events\AttackTimeOutEvent;
use App\Game\Core\Events\CharacterInventoryUpdateBroadCastEvent;
use App\Game\Core\Traits\ResponseBuilder;
use App\Game\Messages\Events\GlobalMessageEvent;
use App\Game\Messages\Events\ServerMessageEvent;

class CelestialFightService {

    use ResponseBuilder;

    private $battleEventHandler;

    public function __construct(BattleEventHandler $battleEventHandler) {
        $this->battleEventHandler = $battleEventHandler;
    }

    public function joinFight(Character $character, CelestialFight $celestialFight): CharacterInCelestialFight {
        $characterInCelestialFight = CharacterInCelestialFight::where('character_id', $character->id)->first();

        $totalHealth = $character->getInformation()->buildHealth();

        if ($totalHealth > 1000000000000) {
            $totalHealth = 1000000000000;
        }

        if (is_null($characterInCelestialFight)) {
            $characterInCelestialFight = CharacterInCelestialFight::create([
                'celestial_fight_id'      => $celestialFight->id,
                'character_id'            => $character->id,
                'character_max_health'    => $totalHealth,
                'character_current_health'=> $totalHealth,
            ]);
        } else {
            if (now()->diffInMinutes($characterInCelestialFight->updated_at) > 5) {
                $characterInCelestialFight = $this->updateCharacterInFight($character, $characterInCelestialFight);
            }

            if ($character->getInformation()->buildHealth() !== $characterInCelestialFight->character_current_health) {
                $characterInCelestialFight = $this->updateCharacterInFight($character, $characterInCelestialFight);
            }
        }

        return $characterInCelestialFight;
    }

    public function fight(Character $character, CelestialFight $celestialFight, CharacterInCelestialFight $characterInCelestialFight, string $attackType): array {
        $fightService = resolve(FightService::class, [
            'character' => $character,
            'monster'   => $celestialFight->monster,
        ])->setAttackTimes(1)->setCelestialFightHealth($celestialFight->current_health);

        $fightService->processFight($character, $celestialFight->monster, $attackType);

        $logInfo         = $fightService->getBattleMessages();
        $monsterHealth   = $fightService->getMonsterHealth();
        $characterHealth = $fightService->getCharacterHealth();

        $celestialFight->update([
            'current_health' => $monsterHealth
        ]);

        $characterInCelestialFight->update([
            'character_current_health' => $characterHealth
        ]);


        if ($characterHealth <= 0) {
            $this->battleEventHandler->processDeadCharacter($character);

            $characterInCelestialFight = $characterInCelestialFight->refresh();
            $celestialFight            = $celestialFight->refresh();

            event(new UpdateCelestialFight($celestialFight, false));

            $logInfo[] = [
                'message' => 'You have died during the fight! Death has come for you!',
                'class'   => 'enemy-action-fired',
            ];

            $this->moveCelestial($character, $celestialFight);

            return $this->successResult([
                'fight' => [
                    'character' =>[
                        'max_health'     => $characterInCelestialFight->character_max_health,
                        'current_health' => $characterInCelestialFight->character_current_health,
                    ],
                    'monster' => [
                        'max_health'     => $celestialFight->max_health,
                        'current_health' => $celestialFight->current_health,
                    ]
                ],
                'logs' => $logInfo
            ]);
        }

        if ($monsterHealth <= 0) {
            $newShards = $character->shards + $celestialFight->monster->shards;

            if ($newShards >= MaxCurrenciesValue::MAX_SHARDS) {
                $newShards = MaxCurrenciesValue::MAX_SHARDS;
            }

            $character->update([
                'shards' => $newShards
            ]);

            BattleAttackHandler::dispatch($character->id, $celestialFight->monster_id)->onQueue('default_long');

            event(new GlobalMessageEvent($character->name . ' has slain the '.$celestialFight->monster->name.'! They have been rewarded with a godly gift!'));

            event(new ServerMessageEvent($character->user, 'You received: ' . $celestialFight->monster->shards . ' shards! Shards can only be used in Alchemy.'));

            event(new ServerMessageEvent($character->user, 'Your additional rewards (XP and so on...) are processing and will be with you shortly.'));

            CharacterInCelestialFight::where('celestial_fight_id', $celestialFight->id)->delete();

            $celestialFight->delete();

            event(new UpdateCelestialFight(null, true));

            return $this->successResult([
                'battle_over' => true,
                'logs'        => $logInfo,
            ]);
        }

        event(new AttackTimeOutEvent($character));

        return $this->successResult([
            'fight' => [
                'character' =>[
                    'max_health'     => $characterInCelestialFight->character_max_health,
                    'current_health' => $characterInCelestialFight->character_current_health,
                ],
                'monster' => [
                    'max_health'     => $celestialFight->max_health,
                    'current_health' => $celestialFight->current_health,
                ]
            ],
            'logs' => $logInfo,
        ]);
    }

    public function revive(Character $character) {
        $character = $this->battleEventHandler->processRevive($character);

        $characterInCelestialFight = CharacterInCelestialFight::where('character_id', $character->id)->first();
        $celestialFight            = CelestialFight::find($characterInCelestialFight->celestial_fight_id);

        return $this->successResult([
            'fight' => [
                'character' =>[
                    'max_health'     => $characterInCelestialFight->character_max_health,
                    'current_health' => $characterInCelestialFight->character_current_health,
                ],
                'monster' => [
                    'max_health'     => $celestialFight->max_health,
                    'current_health' => $celestialFight->current_health,
                ]
            ],
        ]);
    }

    protected function updateCharacterInFight(Character $character, CharacterInCelestialFight $characterInCelestialFight) {
        $totalHealth = $character->getInformation()->buildHealth();

        if ($totalHealth > 1000000000000) {
            $totalHealth = 1000000000000;
        }

        $characterInCelestialFight->update([
            'character_max_health'    => $totalHealth,
            'character_current_health'=> $totalHealth,
        ]);

        return $characterInCelestialFight->refresh();
    }

    protected function moveCelestial(Character $character, CelestialFight $celestialFight) {
        $monster              = $celestialFight->monster;
        $healthRange          = explode('-', $monster->health_range);
        $currentMonsterHealth = rand($healthRange[0], $healthRange[1]);

        $celestialFight->update([
            'x_position'      => CoordinatesCache::getFromCache()['x'][rand(CoordinatesCache::getFromCache()['x'][0], (count(CoordinatesCache::getFromCache()['x']) - 1))],
            'y_position'      => CoordinatesCache::getFromCache()['y'][rand(CoordinatesCache::getFromCache()['y'][0], (count(CoordinatesCache::getFromCache()['y']) - 1))],
            'current_health'  => $currentMonsterHealth,
        ]);

        event(new GlobalMessageEvent($character->name . ' Has caused: ' . $monster->name . ' to flee to the far ends of Tlessa (use /pct or /pc to find the new coordinates).'));
    }
}
