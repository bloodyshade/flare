<?php

namespace App\Game\Maps\Services;

use App\Flare\Cache\CoordinatesCache;
use App\Flare\Events\ServerMessageEvent;
use App\Flare\Events\UpdateTopBarEvent;
use App\Flare\Models\CelestialFight;
use App\Flare\Models\Character;
use App\Flare\Models\GameMap;
use App\Flare\Models\Item;
use App\Flare\Models\Kingdom;
use App\Flare\Models\Location;
use App\Flare\Models\Npc;
use App\Flare\Services\BuildMonsterCacheService;
use App\Flare\Transformers\CharacterAttackTransformer;
use App\Flare\Transformers\CharacterSheetBaseInfoTransformer;
use App\Flare\Values\ItemEffectsValue;
use App\Flare\Values\NpcTypes;
use App\Game\Battle\Services\ConjureService;
use App\Game\Core\Events\UpdateAttackStats;
use App\Game\Core\Events\UpdateBaseCharacterInformation;
use App\Game\Core\Traits\ResponseBuilder;
use App\Game\Maps\Events\MoveTimeOutEvent;
use App\Game\Maps\Events\UpdateActionsBroadcast;
use App\Game\Maps\Events\UpdateMapDetailsBroadcast;
use App\Game\Maps\Events\UpdateMonsterList;
use App\Game\Maps\Services\Common\CanPlayerMassEmbezzle;
use App\Game\Maps\Services\Common\LiveCharacterCount;
use App\Game\Maps\Values\MapTileValue;
use App\Game\Maps\Values\MapPositionValue;
use App\Game\Messages\Events\GlobalMessageEvent;
use App\Game\Messages\Events\ServerMessageEvent as GameServerMessageEvent;
use Illuminate\Support\Facades\Cache;
use League\Fractal\Manager;

class MovementService {

    use ResponseBuilder, LiveCharacterCount, CanPlayerMassEmbezzle;

    /**
     * @var array $portDetails
     */
    private $portDetails = [];

    /**
     * @var array $adventureDetails
     */
    private $adventureDetails = [];

    /**
     * @var array $kingdomData
     */
    private $kingdomData = [];

    private $npcKingdoms = [];

    private $celestialEntities = [];

    /**
     * @var PortService $portService
     */
    private $portService;

    /**
     * @var MapTileValue $mapTileValue
     */
    private $mapTile;

    /**
     * @var CharacterAttackTransformer $characterAttackTransformer
     */
    private $characterAttackTransformer;

    /**
     * @var CoordinatesCache $coordinatesCache
     */
    private $coordinatesCache;

    /**
     * @var MapPositionValue $mapPositionValue
     */
    private $mapPositionValue;

    /**
     * @var TraverseService $traverseService
     */
    private $traverseService;

    /**
     * @var ConjureService $conjureService
     */
    private $conjureService;

    /**
     * @var BuildMonsterCacheService $buildMonsterCacheService
     */
    private $buildMonsterCacheService;

    /**
     * @var Manager $manager
     */
    private $manager;

    private const CHANCE_FOR_CELESTIAL_TO_SPAWN = 1000000;

    /**
     * Constructor
     *
     * @param PortService $portService
     * @return void
     */
    public function __construct(PortService $portService,
                                MapTileValue $mapTile,
                                CharacterSheetBaseInfoTransformer $characterAttackTransformer,
                                CoordinatesCache $coordinatesCache,
                                MapPositionValue $mapPositionValue,
                                TraverseService $traverseService,
                                ConjureService $conjureService,
                                BuildMonsterCacheService $buildMonsterCacheService,
                                Manager $manager)
    {
        $this->portService                = $portService;
        $this->mapTile                    = $mapTile;
        $this->characterAttackTransformer = $characterAttackTransformer;
        $this->coordinatesCache           = $coordinatesCache;
        $this->mapPositionValue           = $mapPositionValue;
        $this->traverseService            = $traverseService;
        $this->conjureService             = $conjureService;
        $this->buildMonsterCacheService   = $buildMonsterCacheService;
        $this->manager                    = $manager;
    }

    /**
     * Update the characters position.
     *
     * Only updates the character position if the character can walk on water
     *
     * @param Character $character
     * @param array $params
     * @return array
     */
    public function updateCharacterPosition(Character $character, array $params): array {
        $xPosition      = $params['character_position_x'];
        $yPosition      = $params['character_position_y'];
        $mapTileColor   = $this->mapTile->getTileColor($character, $xPosition, $yPosition);
        $lockedLocation = Location::where('x', $xPosition)->where('y', $yPosition)->where('game_map_id', $character->map->game_map_id)->where('required_quest_item_id', true)->first();

        if ($this->mapTile->isWaterTile((int) $mapTileColor)) {
            if ($this->mapTile->canWalkOnWater($character, $xPosition, $yPosition)) {
                return $this->moveCharacter($character, $params, $lockedLocation);
            } else {
                return $this->errorResult('cannot walk on water.');
            }
        }

        if ($this->mapTile->isDeathWaterTile((int) $mapTileColor)) {

            if ($this->mapTile->canWalkOnDeathWater($character, $xPosition, $yPosition)) {
                return $this->moveCharacter($character, $params, $lockedLocation);
            } else {
                return $this->errorResult('cannot walk on death water.');
            }
        }

        if ($this->mapTile->isMagma((int) $mapTileColor)) {
            if ($this->mapTile->canWalkOnMagma($character, $xPosition, $yPosition)) {
                return $this->moveCharacter($character, $params, $lockedLocation);
            } else {
                return $this->errorResult('cannot walk on magma.');
            }
        }

        if ($this->mapTile->isPurgatoryWater((int) $mapTileColor)) {
            return $this->errorResult('You would slip away into the void if you tried to go that way, child!');
        }

        if (!is_null($lockedLocation)) {
            $item = Item::where('id', $lockedLocation->required_quest_item_id)->first();
            $slot = $character->inventory->slots()->where('item_id', $item->id)->first();

            if (is_null($slot)) {
                return $this->errorResult('Cannot enter this location without a ' . $item->name);
            }
        }

        return $this->moveCharacter($character, $params, $lockedLocation);
    }

    /**
     * Lets the players traverse from one plane to another.
     *
     * @param int $mapId
     * @param Character $character
     * @return array
     */
    public function updateCharacterPlane(int $mapId, Character $character): array {
        if (!$this->traverseService->canTravel($mapId, $character)) {
            return $this->errorResult('You are missing a required item to travel to that plane.');
        }

        $this->traverseService->travel($mapId, $character);

        $character = $character->refresh();

        $xPosition = $character->map->character_position_x;
        $yPosition = $character->map->character_position_y;

        $lockedLocation = Location::where('x', $xPosition)->where('y', $yPosition)->where('game_map_id', $character->map->game_map_id)->where('required_quest_item_id', true)->first();

        return $this->successResult([
            'lockedLocationType' => is_null($lockedLocation) ? null : $lockedLocation->type
        ]);
    }

    /**
     * Process the area.
     *
     * sets the kingdom data for a specific area.
     *
     * This includes if you are the owner, can settle, can manage or can attack.
     *
     * @param Character $character
     * @return void
     */
    public function processArea(Character $character): void {
        $location = Location::where('x', $character->x_position)
                            ->where('y', $character->y_position)
                            ->where('game_map_id', $character->map->game_map_id)
                            ->first();

        if (!is_null($location)) {
            $this->processLocation($location, $character);

            if (!is_null($location->enemy_strength_type)) {
                $this->updateActions($character, $location->name);

                event(new GameServerMessageEvent($character->user, 'You have entered: ' . $location->name . '. Monsters here are much stronger.
                Special location enemy strength is also effected by the planes monster strength if you on Shadow Planes or Lower.
                Remember, if you are here to get quest items, they will not drop if you are auto battling. Gear will matter here.
                There are quests you can do for Voidance and Devoidance Quest items which make your time here much easier.
                Locations such as these can drop special quest items. Check your quest section under: Plane Quests (on the map) -> All quests.
                If you need further help, click Help I\'m stuck at the top or the Discord button to join discord and ask for help in #help.'));
            }
        } else {
            $this->updateActions($character, null, $character->map->gameMap->name);
        }

        $this->npcKingdoms       = Kingdom::select('x_position', 'y_position', 'npc_owned')
                                          ->whereNull('character_id')
                                          ->where('npc_owned', true)
                                          ->where('game_map_id', $character->map->game_map_id)
                                          ->get()
                                          ->toArray();

        $celestialEntity = CelestialFight::with('monster')->join('monsters', function($join) use($character) {
            $join->on('monsters.id', 'celestial_fights.monster_id')
                 ->where('x_position', $character->x_position)
                 ->where('y_position', $character->y_position)
                 ->where('monsters.game_map_id', $character->map->gameMap->id);
        })->select('celestial_fights.*')->first();

        if (!is_null($celestialEntity)) {
            $this->celestialEntities[] = $celestialEntity->toArray();
        }

        $kingdom = Kingdom::where('x_position', $character->x_position)
                          ->where('y_position', $character->y_position)
                          ->where('game_map_id', $character->map->game_map_id)
                          ->first();

        $canAttack       = false;
        $canSettle       = false;
        $canManage       = false;
        $kingdomToAttack = [];

        if (!is_null($kingdom)) {
            if (!is_null($kingdom->character_id)) {
                if ($kingdom->character->id !== $character->id) {
                    $canAttack = true;

                    $kingdomToAttack = [
                        'id' => $kingdom->id,
                        'x_position' => $kingdom->x_position,
                        'y_position' => $kingdom->y_position,
                    ];
                } else {
                    $canManage = true;

                    $kingdom->updateLastWalked();
                }
            } else {
                $canAttack = true;

                $kingdomToAttack = [
                    'id' => $kingdom->id,
                    'x_position' => $kingdom->x_position,
                    'y_position' => $kingdom->y_position,
                ];
            }
        } else if (is_null($location)) {
            $canSettle = true;
        }

        $owner           = null;
        $canMassEmbezzle = false;

        if (!is_null($kingdom)) {
            if (!is_null($kingdom->character_id)) {
                $owner = $kingdom->character->name;

                $canMassEmbezzle = $this->canMassEmbezzle($kingdom->character, $canManage);

            } else {
                $owner = Npc::where('type', NpcTypes::KINGDOM_HOLDER)->first()->name . ' (NPC)';
            }
        }

        $this->kingdomData = [
            'owner'             => $owner,
            'can_attack'        => $canAttack,
            'can_manage'        => $canManage,
            'can_settle'        => $canSettle,
            'kingdom_to_attack' => $kingdomToAttack,
            'can_mass_embezzle' => $canMassEmbezzle,
        ];
    }

    /**
     * Process the location for ports and adventures as well as drops.
     *
     * @param Location $location | null
     * @param Character $character
     * @param PortService $portService
     * @return void
     */
    public function processLocation(Location $location, Character $character): void {
        if ($location->is_port) {
            $this->portDetails = $this->portService->getPortDetails($character, $location);
        }

        $this->giveQuestReward($location, $character);

        if ($location->adventures->isNotEmpty()) {
            $this->adventureDetails = $location->adventures->where('published', true)->toArray();
        }
    }

    /**
     * Send off the movement timeout.
     *
     * Sets the character's ability to move to false.
     * Sets the can move again to 10 seconds from now.
     *
     * Sends off the broadcast event to update the front end.
     *
     * @param Character $character
     * @return void
     */
    public function updateCharacterMovementTimeOut(Character $character) {
        $character->update([
            'can_move'          => false,
            'can_move_again_at' => now()->addSeconds(10),
        ]);

        event(new MoveTimeOutEvent($character));
    }

    /**
     * Teleport the player to a specified location.
     *
     * The array that is returned is for the response of the controller.
     *
     * @param Character $character
     * @param int $x
     * @param int $y
     * @param int $cost
     * @param int $time
     * @param int $timeout
     * @return array
     */
    public function teleport(Character $character, int $x, int $y, int $cost, int $timeout, bool $pctCommand = false): array {
        $canTeleportToWater      = $this->mapTile->canWalkOnWater($character, $x, $y);
        $canTeleportToDeathWater = $this->mapTile->canWalkOnDeathWater($character, $x, $y);
        $canTeleportToMagma      = $this->mapTile->canWalkOnMagma($character, $x, $y);
        $lockedLocation          = Location::where('x', $x)->where('y', $y)->where('game_map_id', $character->map->game_map_id)->whereNotNull('required_quest_item_id')->first();

        if (!$canTeleportToWater && $this->mapTile->isWaterTile($this->mapTile->getTileColor($character, $x, $y))) {
            $item = Item::where('effect', ItemEffectsValue::WALK_ON_WATER)->first();

            return $this->errorResult('Cannot teleport to water locations without a ' . $item->name);
        }

        if (!$canTeleportToDeathWater && $this->mapTile->isDeathWaterTile($this->mapTile->getTileColor($character, $x, $y))) {
            $item = Item::where('effect', ItemEffectsValue::WALK_ON_DEATH_WATER)->first();

            return $this->errorResult('Cannot teleport to Death Water locations without a ' . $item->name);
        }

        if (!$canTeleportToMagma && $this->mapTile->isMagma($this->mapTile->getTileColor($character, $x, $y))) {
            $item = Item::where('effect', ItemEffectsValue::WALK_ON_MAGMA)->first();

            return $this->errorResult('Cannot teleport to magma locations without a ' . $item->name);
        }

        if ($this->mapTile->isPurgatoryWater($this->mapTile->getTileColor($character, $x, $y))) {
            return $this->errorResult('You would slip away into the void if you tried to go that way, child!');
        }

        if (!is_null($lockedLocation)) {
            $item = Item::where('id', $lockedLocation->required_quest_item_id)->first();
            $slot = $character->inventory->slots()->where('item_id', $item->id)->first();

            if (is_null($slot)) {
                return $this->errorResult('Cannot enter this location without a ' . $item->name);
            }
        }

        if ($character->gold < $cost) {
            return $this->errorResult('Not enough gold.');
        }

        $coordinates = $this->coordinatesCache->getFromCache();

        if (!in_array($x, $coordinates['x']) && !in_array($x, $coordinates['y'])) {
            return $this->errorResult('Invalid coordinates');
        }

        $this->attemptConjure($character);

        $character->map->update([
            'character_position_x' => $x,
            'character_position_y' => $y,
            'position_x'           => $this->mapPositionValue->fetchXPosition($x, $character->map->position_x),
            'position_y'           => $this->mapPositionValue->fetchYPosition($y),
        ]);

        $character = $character->refresh();

        $this->processArea($character);

        $this->teleportCharacter($character, $timeout, $cost, $pctCommand);

        return $this->successResult([
            'lockedLocationType' => is_null($lockedLocation) ? null : $lockedLocation->type,
        ]);
    }

    /**
     * Set sail.
     *
     * Moves the character from one port to another assuming they can.
     *
     * @param Character $character
     * @param Location $location
     * @param array $params
     * @return array
     */
    public function setSail(Character $character, Location $location, array $params): array {
        $fromPort = Location::where('id', $params['current_port_id'])->where('is_port', true)->first();

        if (is_null($fromPort)) {
            return $this->errorResult('Invalid port to set sail from.');
        }

        if ($character->gold < $params['cost']) {
            return $this->errorResult('You don\'t have the gold');
        }

        $matches = $this->portService->doesMatch($character, $fromPort, $location, $params['time_out_value'], $params['cost']);

        if (!$matches) {
            return $this->errorResult('The port you are trying to go doesn\'t exist.');
        }

        $this->moveCharacterToNewPort($character, $location, $params['time_out_value']);

        $character = $character->refresh();

        $this->attemptConjure($character);

        $celestialEntity = CelestialFight::with('monster')->where('x_position', $character->x_position)
            ->where('y_position', $character->y_position)
            ->first();


        return $this->successResult([
            'character_position_details' => $character->map,
            'port_details'               => $this->portService->getPortDetails($character, $location),
            'adventure_details'          => $location->adventures->isNotEmpty() ? $location->adventures : [],
            'celestial_entities'         => !is_null($celestialEntity) ? [$celestialEntity] : [],
        ]);
    }

    /**
     * Get the port details
     *
     * @return array
     */
    public function portDetails(): array {
        return $this->portDetails;
    }

    /**
     * Get the adventure details
     *
     * @return array
     */
    public function adventureDetails(): array {
        return $this->adventureDetails;
    }

    /**
     * Get the kingdom data
     *
     * @return array
     */
    public function kingdomDetails(): array {
        return $this->kingdomData;
    }

    /**
     * Gets the NPC owned kingdoms.
     *
     * @return array
     */
    public function npcOwnedKingdoms(): array {
        return $this->npcKingdoms;
    }

    /**
     * Get celestials
     *
     * @return array
     */
    public function celestialEntities(): array {
        return $this->celestialEntities;
    }

    /**
     * Can conjure Celestials?
     *
     * @return bool
     */
    public function canConjure() {

        $needed = self::CHANCE_FOR_CELESTIAL_TO_SPAWN - 1;

        if (Cache::has('celestial-spawn-rate')) {
            $needed = $needed - ($needed * Cache::get('celestial-spawn-rate'));
        }

        return rand(1, self::CHANCE_FOR_CELESTIAL_TO_SPAWN) > $needed;
    }

    /**
     * Update character actions.
     *
     * @param int $mapId
     * @param Character $character
     */
    protected function updateActions(Character $character, string $locationName = null, string $gameMapName = null ) {
        $user      = $character->user;

        $character = new \League\Fractal\Resource\Item($character, $this->characterAttackTransformer);

        if (!is_null($gameMapName)) {
            $monsters  = $this->buildMonsterCacheService->fetchMonsterCache($gameMapName);
        } else {
            $monsters  = $this->buildMonsterCacheService->fetchMonsterCache($locationName);
        }

        $character = $this->manager->createData($character)->toArray();

        broadcast(new UpdateMonsterList($monsters, $user));

        event(new UpdateBaseCharacterInformation($user, $character));
    }

    /**
     * Moves the character to the new location.
     *
     *
     * @param Character $character
     * @param array $params
     * @return array
     */
    protected function moveCharacter(Character $character, array $params, ?Location $lockedLocation): array {
        $character->map->update($params);

        $character = $character->refresh();

        $this->attemptConjure($character);

        $this->processArea($character);

        $this->updateCharacterMovementTimeOut($character);

        $kingdomDetails = $this->kingdomDetails();
        $canEmbezzle    = false;

        if (isset($kingdomDetails['can_manage'])) {
            $canEmbezzle = $this->canMassEmbezzle($character, $kingdomDetails['can_manage']);
        }

        return $this->successResult([
            'port_details'       => $this->portDetails(),
            'adventure_details'  => $this->adventureDetails(),
            'kingdom_details'    => $kingdomDetails,
            'celestials'         => $this->celestialEntities(),
            'characters_on_map'  => $this->getActiveUsersCountForMap($character),
            'can_mass_embezzle'  => $canEmbezzle,
            'lockedLocationType' => is_null($lockedLocation) ? null : $lockedLocation->type,
        ]);
    }


    /**
     * Attempt to conjure Celestial to any plane on Movement, Teleport or Set Sail
     *
     * @param Character $character
     */
    protected function attemptConjure(Character $character) {
        if ($this->canConjure()) {
            $this->conjureService->movementConjure($character);
        }
    }

    /**
     * Teleport the character to a new location.
     *
     * @param Character $character
     * @param int timeout
     * @param int $cost
     */
    protected function teleportCharacter(Character $character, int $timeout, int $cost, bool $pctCommand = false) {
        $character->update([
            'can_move'          => $timeout === 0 ? true : false,
            'gold'              => $character->gold - $cost,
            'can_move_again_at' => $timeout === 0 ? null : now()->addMinutes($timeout),
        ]);

        $character = $character->refresh();

        if ($timeout !== 0) {
            event(new MoveTimeOutEvent($character, $timeout, true));
        }
        event(new UpdateTopBarEvent($character));

        event(new UpdateMapDetailsBroadcast($character->map()->with('gameMap')->first(), $character->user, $this, false, $pctCommand));
    }

    /**
     * Give the quest reward item
     *
     * @param Location $location
     * @param Character $character
     * @param void
     */
    protected function giveQuestReward(Location $location, Character $character): void {
        if (!is_null($location->questRewardItem)) {
            $item = $character->inventory->slots->filter(function($slot) use ($location) {
                return $slot->item_id === $location->questRewardItem->id;
            })->first();

            if (is_null($item)) {
                if ($character->isInventoryFull()) {
                    event(new ServerMessageEvent($character->user, 'inventory_full'));
                } else {
                    $character->inventory->slots()->create([
                        'inventory_id' => $character->inventory->id,
                        'item_id'      => $location->questRewardItem->id,
                    ]);

                    $questItem = $location->questRewardItem;

                    if (!is_null($questItem->effect)) {
                        $message = $character->name . ' has found: ' . $questItem->affix_name;

                        broadcast(new GlobalMessageEvent($message));
                    }

                    event(new ServerMessageEvent($character->user, 'found_item', $questItem->affix_name));
                }
            }
        }
    }

    /**
     * Move the character to the new port.
     *
     * @param Character $character
     * @param Location $location
     * @param int $timeOutValue
     * @return void
     */
    protected function moveCharacterToNewPort(Character $character, Location $location, int $timeOutValue): void {
        $character = $this->portService->setSail($character, $location);

        $this->giveQuestReward($location, $character);

        $character = $character->refresh();

        event(new MoveTimeOutEvent($character, $timeOutValue, true));
        event(new UpdateTopBarEvent($character));
    }


}
