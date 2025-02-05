<?php

namespace Tests\Feature\Game\Battle;

use App\Flare\Values\ItemEffectsValue;
use App\Flare\Values\MapNameValue;
use App\Flare\Values\MaxCurrenciesValue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use App\Flare\Events\ServerMessageEvent;
use App\Flare\Events\UpdateTopBarEvent;
use App\Flare\Services\BuildMonsterCacheService;
use App\Game\Battle\Values\MaxLevel;
use App\Game\Core\Events\GoldRushCheckEvent;
use App\Game\Core\Events\AttackTimeOutEvent;
use App\Game\Core\Events\CharacterIsDeadBroadcastEvent;
use App\Game\Core\Events\DropsCheckEvent;
use App\Game\Core\Events\ShowTimeOutEvent;
use App\Game\Core\Events\UpdateTopBarBroadcastEvent;
use Tests\TestCase;
use Tests\Traits\CreateRace;
use Tests\Traits\CreateClass;
use Tests\Traits\CreateCharacter;
use Tests\Traits\CreateUser;
use Tests\Traits\CreateRole;
use Tests\Traits\CreateMonster;
use Tests\Traits\CreateItem;
use Tests\Traits\CreateSkill;
use Tests\Setup\Monster\MonsterFactory;
use Tests\Traits\CreateItemAffix;
use Tests\Setup\Character\CharacterFactory;

class BattleControllerApiTest extends TestCase
{
    use RefreshDatabase,
        CreateUser,
        CreateRole,
        CreateRace,
        CreateClass,
        CreateCharacter,
        CreateMonster,
        CreateItem,
        CreateSkill,
        CreateItemAffix;

    private $user;

    private $character;

    private $monster;

    private $purgatory;

    public function setUp(): void {
        parent::setUp();

        $this->purgatory = $this->createGameMap(['name' => MapNameValue::PURGATORY, 'default' => false]);

        $this->createGameMap([
            'name'    => 'Surface',
            'path'    => 'path',
            'default' => true,
        ]);

        $this->character = (new CharacterFactory)->createBaseCharacter()
                                                 ->givePlayerLocation()
                                                 ->equipStartingEquipment()
                                                 ->assignFactionSystem();

        $this->monster   = (new MonsterFactory)->buildMonster();

        $this->createItemAffix([
            'name'                 => 'Sample',
            'base_damage_mod'      => '0.10',
            'type'                 => 'prefix',
            'description'          => 'Sample',
            'base_healing_mod'     => '0.10',
            'str_mod'              => '0.10',
            'dur_mod'              => '0.10',
            'dex_mod'              => '0.10',
            'chr_mod'              => '0.10',
            'int_mod'              => '0.10',
            'skill_name'           => null,
            'skill_training_bonus' => null,
        ]);

        $this->createItemAffix([
            'name'                 => 'Sample',
            'base_damage_mod'      => '0.10',
            'type'                 => 'suffix',
            'description'          => 'Sample',
            'base_healing_mod'     => '0.10',
            'str_mod'              => '0.10',
            'dur_mod'              => '0.10',
            'dex_mod'              => '0.10',
            'chr_mod'              => '0.10',
            'int_mod'              => '0.10',
            'skill_name'           => null,
            'skill_training_bonus' => null,
        ]);
    }

    public function tearDown(): void {
        parent::tearDown();

        $this->character = null;
        $this->monster   = null;
    }

    public function testCanGetActions() {

        $this->createMonster([
            'game_map_id' => $this->character->getCharacter(false)->map->gameMap->id,
        ]);

        resolve(BuildMonsterCacheService::class)->buildCache();

        $user     = $this->character->getUser();

        $response = $this->actingAs($user)
                         ->json('GET', '/api/actions', [
                             'user_id' => $user->id
                         ])
                         ->response;

        $content   = json_decode($response->content());

        $character = $this->character->getCharacter(false);

        $this->assertEquals(200, $response->status());
        $this->assertNotEmpty($content->monsters);
        $this->assertEquals($character->name, $content->character->name);
    }

    public function testCanGetActionsWithSkills() {

        $user     = $this->character->getUser();

        $this->createMonster([
            'game_map_id' => $this->character->getCharacter(false)->map->gameMap->id,
        ]);

        resolve(BuildMonsterCacheService::class)->buildCache();

        $response = $this->actingAs($user)
                         ->json('GET', '/api/actions', [
                             'user_id' => $user->id
                         ])
                         ->response;

        $content   = json_decode($response->content());
        $character = $this->character->getCharacter(false);

        $this->assertEquals(200, $response->status());
        $this->assertNotEmpty($content->monsters);
        $this->assertEquals($character->name, $content->character->name);
    }

    public function testWhenNotLoggedInCannotGetActions() {
        $response = $this->json('GET', '/api/actions', [
                             'user_id' => 1
                         ])
                         ->response;

        $this->assertEquals(401, $response->status());
    }

    public function testBattleResultsCharacterIsDead() {
        $user      = $this->character->getUser();
        $character = $this->character->getCharacter(false);

        $response = $this->actingAs($user)
                         ->json('POST', '/api/battle-results/' . $character->id, [
                             'is_character_dead' => true
                         ])
                         ->response;

        $this->assertEquals(200, $response->status());

    }

    public function testBattleResultsMonsterIsDead() {

        $user      = $this->character->getUser();
        $character = $this->character->getCharacter(false);
        $monster   = $this->monster->getMonster();

        $currentGold = $character->gold;

        $response = $this->actingAs($user)
                         ->json('POST', '/api/battle-results/' . $character->id, [
                             'is_defender_dead' => true,
                             'defender_type'    => 'monster',
                             'monster_id'       => $monster->id,
                         ])
                         ->response;

        $this->assertEquals(200, $response->status());

        $this->assertTrue($currentGold !== $this->character->getCharacter(false)->gold);
    }

    public function testBattleResultsMonsterIsDeadPlayerGetsNoFactionPointsMaxed() {

        $user      = $this->character->getUser();
        $character = $this->character->getCharacter(false);
        $monster   = $this->monster->getMonster();

        $character->factions()->first()->update(['current_level' => 5, 'maxed' => true]);

        $currentGold = $character->gold;

        $response = $this->actingAs($user)
            ->json('POST', '/api/battle-results/' . $character->id, [
                'is_defender_dead' => true,
                'defender_type'    => 'monster',
                'monster_id'       => $monster->id,
            ])
            ->response;

        $this->assertEquals(200, $response->status());

        $character = $character->refresh();

        $this->assertTrue($currentGold !== $character->gold);
        $this->assertTrue($character->factions->first()->current_points === 0);
    }

    public function testBattleResultsMonsterIsDeadPlayerGetsTenFactionPointsWithQuestItem() {

        $user      = $this->character->getUser();
        $character = $this->character->getCharacter(false);
        $monster   = $this->monster->getMonster();

        $character->inventory->slots()->create([
            'inventory_id' => $character->inventory->id,
            'item_id'      => $this->createItem(['type' => 'quest', 'effect' => ItemEffectsValue::FACTION_POINTS])->id
        ]);

        $character->factions()->first()->update(['current_level' => 2]);

        $currentGold = $character->gold;

        $character = $character->refresh();

        $response = $this->actingAs($user)
            ->json('POST', '/api/battle-results/' . $character->id, [
                'is_defender_dead' => true,
                'defender_type'    => 'monster',
                'monster_id'       => $monster->id,
            ])
            ->response;

        $this->assertEquals(200, $response->status());

        $character = $character->refresh();

        $this->assertTrue($currentGold !== $character->gold);
        $this->assertTrue($character->factions->first()->current_points === 10);
    }

    public function testBattleResultsMonsterIsDeadPlayerMaxesFaction() {

        $user      = $this->character->getUser();
        $character = $this->character->getCharacter(false);
        $monster   = $this->monster->getMonster();

        $character->inventory->slots()->create([
            'inventory_id' => $character->inventory->id,
            'item_id'      => $this->createItem(['type' => 'quest', 'effect' => ItemEffectsValue::FACTION_POINTS])->id
        ]);

        $character->factions()->first()->update(['current_level' => 4, 'current_points' => 8000, 'points_needed' => 8000]);

        $currentGold = $character->gold;

        $response = $this->actingAs($user)
            ->json('POST', '/api/battle-results/' . $character->id, [
                'is_defender_dead' => true,
                'defender_type'    => 'monster',
                'monster_id'       => $monster->id,
            ])
            ->response;

        $this->assertEquals(200, $response->status());

        $character = $character->refresh();

        $this->assertTrue($currentGold !== $character->gold);
        $this->assertTrue($character->factions->first()->maxed);
    }

    public function testBattleResultsMonsterIsDeadPlayerMaxesFactionButInventoryIsFull() {

        $user      = $this->character->getUser();
        $character = $this->character->getCharacter(false);
        $monster   = $this->monster->getMonster();

        $character->inventory->slots()->create([
            'inventory_id' => $character->inventory->id,
            'item_id'      => $this->createItem(['type' => 'quest', 'effect' => ItemEffectsValue::FACTION_POINTS])->id
        ]);

        $character->update(['max_inventory' => 1]);

        $character->factions()->first()->update(['current_level' => 4, 'current_points' => 8000, 'points_needed' => 8000]);

        $character = $character->refresh();

        $currentGold = $character->gold;

        $response = $this->actingAs($user)
            ->json('POST', '/api/battle-results/' . $character->id, [
                'is_defender_dead' => true,
                'defender_type'    => 'monster',
                'monster_id'       => $monster->id,
            ])
            ->response;

        $this->assertEquals(200, $response->status());

        $character = $character->refresh();

        $this->assertTrue($currentGold !== $character->gold);
        $this->assertTrue($character->factions->first()->maxed);
    }

    public function testBattleResultsMonsterIsDeadPlayerGetsCopperCoins() {

        $user      = $this->character->getUser();
        $character = $this->character->getCharacter(false);
        $monster   = $this->createMonster(['game_map_id' => $this->purgatory->id]);

        $character->inventory->slots()->create([
            'inventory_id' => $character->inventory->id,
            'item_id'      => $this->createItem(['type' => 'quest', 'effect' => ItemEffectsValue::GET_COPPER_COINS])->id
        ]);

        $character = $character->refresh();

        $currentGold = $character->gold;

        $response = $this->actingAs($user)
            ->json('POST', '/api/battle-results/' . $character->id, [
                'is_defender_dead' => true,
                'defender_type'    => 'monster',
                'monster_id'       => $monster->id,
            ])
            ->response;

        $this->assertEquals(200, $response->status());

        $character = $character->refresh();

        $this->assertTrue($currentGold !== $character->gold);
        $this->assertTrue($character->copper_coins > 0);
    }

    public function testBattleResultsMonsterIsDeadPlayerMaxesFactionAndGoldCaps() {

        $user      = $this->character->getUser();
        $character = $this->character->getCharacter(false);
        $monster   = $this->monster->getMonster();

        $character->inventory->slots()->create([
            'inventory_id' => $character->inventory->id,
            'item_id'      => $this->createItem(['type' => 'quest', 'effect' => ItemEffectsValue::FACTION_POINTS])->id
        ]);

        $character->update(['gold' => 1999999999999]);

        $character->factions()->first()->update(['current_level' => 4, 'current_points' => 8000, 'points_needed' => 8000]);

        $currentGold = $character->gold;

        $response = $this->actingAs($user)
            ->json('POST', '/api/battle-results/' . $character->id, [
                'is_defender_dead' => true,
                'defender_type'    => 'monster',
                'monster_id'       => $monster->id,
            ])
            ->response;

        $this->assertEquals(200, $response->status());

        $character = $character->refresh();

        $this->assertTrue($currentGold !== $character->gold);
        $this->assertTrue($character->factions->first()->maxed);
        $this->assertEquals(MaxCurrenciesValue::MAX_GOLD, $character->gold);
    }

    public function testBattleResultsMonsterIsDeadNoXpMaxLevel() {
        Event::fake([
            ServerMessageEvent::class,
            DropsCheckEvent::class,
            GoldRushCheckEvent::class,
            AttackTimeOutEvent::class,
            UpdateTopBarBroadcastEvent::class,
        ]);

        $user      = $this->character->getUser();
        $character = $this->character->getCharacter(false);
        $monster   = $this->monster->getMonster();

        $character->update([
            'level' => MaxLevel::MAX_LEVEL,
            'xp'    => 0,
        ]);

        $character = $character->refresh();

        $currentGold = $character->gold;

        $response = $this->actingAs($user)
            ->json('POST', '/api/battle-results/' . $character->id, [
                'is_defender_dead' => true,
                'defender_type'    => 'monster',
                'monster_id'       => $monster->id,
            ])
            ->response;

        $this->assertEquals(200, $response->status());

        $this->assertTrue($currentGold !== $this->character->getCharacter(false)->gold);
        $this->assertEquals(0, $this->character->getCharacter(false)->xp);
    }

    public function testBattleResultsWhenCharacterCannotAttack() {
        Event::fake([
            ServerMessageEvent::class,
            DropsCheckEvent::class,
            GoldRushCheckEvent::class,
            AttackTimeOutEvent::class,
            UpdateTopBarBroadcastEvent::class,
        ]);

        $user      = $this->character->getUser();
        $character = $this->character->getCharacter(false);
        $monster   = $this->monster->getMonster();

        $character->update([
            'can_attack' => false,
        ]);

        $character = $character->refresh();

        $response = $this->actingAs($user)
            ->json('POST', '/api/battle-results/' . $character->id, [
                'is_defender_dead' => true,
                'defender_type'    => 'monster',
                'monster_id'       => $monster->id,
            ])
            ->response;

        $this->assertEquals(429, $response->status());
    }

    public function testBattleResultsWhenCharacterAlreadyDead() {
        Event::fake([
            ServerMessageEvent::class,
            DropsCheckEvent::class,
            GoldRushCheckEvent::class,
            AttackTimeOutEvent::class,
            UpdateTopBarBroadcastEvent::class,
        ]);

        $user      = $this->character->getUser();
        $character = $this->character->getCharacter(false);
        $monster   = $this->monster->getMonster();


        $character->update([
            'is_dead' => true,
        ]);

        $character = $character->refresh();

        $response = $this->actingAs($user)
            ->json('POST', '/api/battle-results/' . $character->id, [
                'is_defender_dead' => true,
                'defender_type'    => 'monster',
                'monster_id'       => $monster->id,
            ])
            ->response;

        $this->assertEquals(422, $response->status());
    }

    public function testBattleResultsMonsterIsDeadAndCharacterLevelUp() {
        Event::fake([
            ServerMessageEvent::class,
            DropsCheckEvent::class,
            GoldRushCheckEvent::class,
            AttackTimeOutEvent::class,
            UpdateTopBarBroadcastEvent::class,
        ]);

        $character = $this->character->updateCharacter(['xp' => 99])->getCharacter(false);
        $user      = $this->character->getUser();
        $monster   = $this->monster->getMonster();

        $response = $this->actingAs($user)
                         ->json('POST', '/api/battle-results/' . $character->id, [
                             'is_defender_dead' => true,
                             'defender_type'    => 'monster',
                             'monster_id'       => $monster->id,
                         ])
                         ->response;

        $character = $this->character->getCharacter(false);

        $this->assertEquals(200, $response->status());
        $this->assertEquals(2, $character->level);
        $this->assertEquals(0, $character->xp);
    }

    public function testBattleResultsMonsterIsDeadAndCharacterGainedItem() {
        Event::fake([
            ServerMessageEvent::class,
            GoldRushCheckEvent::class,
            AttackTimeOutEvent::class,
            UpdateTopBarBroadcastEvent::class,
        ]);

        $character   = $this->character->updateSkill('Looting', ['level' => 999])->getCharacter(false);
        $user        = $this->character->getUser();
        $monster     = $this->monster->getMonster();

        $currentGold = $character->gold;

        $response = $this->actingAs($user)
                         ->json('POST', '/api/battle-results/' . $character->id, [
                             'is_defender_dead' => true,
                             'defender_type' => 'monster',
                             'monster_id' => $monster->id,
                         ])
                         ->response;

        $character = $this->character->getCharacter(false);

        $this->assertEquals(200, $response->status());
        $this->assertTrue($currentGold !== $character->gold);
        $this->assertTrue(count($character->inventory->slots) > 1);
    }

    public function testBattleIsOverAndGetsFactionItem() {
        Event::fake([
            ServerMessageEvent::class,
            GoldRushCheckEvent::class,
            AttackTimeOutEvent::class,
            UpdateTopBarBroadcastEvent::class,
        ]);

        $this->createItem();
        $this->createItemAffix();

        $character   = $this->character->getCharacter(false);

        $character->factions()->update([
            'current_points' => 499
        ]);

        $character   = $character->refresh();
        $user        = $this->character->getUser();
        $monster     = $this->monster->getMonster();

        $currentGold = $character->gold;

        $response = $this->actingAs($user)
            ->json('POST', '/api/battle-results/' . $character->id, [
                'is_defender_dead' => true,
                'defender_type' => 'monster',
                'monster_id' => $monster->id,
            ])
            ->response;

        $character = $this->character->getCharacter(false);

        $this->assertEquals(200, $response->status());
        $this->assertTrue($currentGold !== $character->gold);
        $this->assertTrue(count($character->inventory->slots) > 1);

        $this->assertNotNull($character->factions()->where('points_needed', 2000)->first());
    }

    public function testBattleIsOverAndGetsFactionItemForMaxFactionLevel() {
        Event::fake([
            ServerMessageEvent::class,
            GoldRushCheckEvent::class,
            AttackTimeOutEvent::class,
            UpdateTopBarBroadcastEvent::class,
        ]);

        $this->createItem();
        $this->createItemAffix();

        $character   = $this->character->getCharacter(false);

        $character->factions()->update([
            'current_level'  => 4,
            'current_points' => 7999,
        ]);

        $character   = $character->refresh();
        $user        = $this->character->getUser();
        $monster     = $this->monster->getMonster();

        $currentGold = $character->gold;

        $response = $this->actingAs($user)
            ->json('POST', '/api/battle-results/' . $character->id, [
                'is_defender_dead' => true,
                'defender_type' => 'monster',
                'monster_id' => $monster->id,
            ])
            ->response;

        $character = $this->character->getCharacter(false);

        $this->assertEquals(200, $response->status());
        $this->assertTrue($currentGold !== $character->gold);
        $this->assertTrue(count($character->inventory->slots) > 1);

        $this->assertNotNull($character->factions()->where('maxed', true)->first());
    }

    public function testBattleResultsMonsterIsDeadAndCharacterGainedItemFromShadowPlane() {
        Event::fake([
            ServerMessageEvent::class,
            GoldRushCheckEvent::class,
            AttackTimeOutEvent::class,
            UpdateTopBarBroadcastEvent::class,
        ]);

        $character   = $this->character->updateSkill('Looting', ['level' => 100])->getCharacter(false);
        $user        = $this->character->getUser();
        $monster     = $this->monster->getMonster();

        $monster->update([
            'max_level' => 40,
            'game_map_id' => $this->createGameMap([
                'name' => 'Shadow Plane'
            ])->id,
        ]);

        $this->character->assignFactionSystem();

        $monster = $monster->refresh();

        $currentGold = $character->gold;

        $response = $this->actingAs($user)
            ->json('POST', '/api/battle-results/' . $character->id, [
                'is_defender_dead' => true,
                'defender_type' => 'monster',
                'monster_id' => $monster->id,
            ])
            ->response;

        $character = $this->character->getCharacter(false);

        $this->assertEquals(200, $response->status());
        $this->assertTrue($currentGold !== $character->gold);
        $this->assertTrue(count($character->inventory->slots) > 1);
    }

    public function testBattleResultsMonsterIsDeadAndCharacterGainedQuestItem() {
        Event::fake([
            ServerMessageEvent::class,
            GoldRushCheckEvent::class,
            AttackTimeOutEvent::class,
            UpdateTopBarBroadcastEvent::class,
        ]);

        $character   = $this->character->updateSkill('Looting', ['level' => 10000000])->getCharacter(false);
        $user        = $this->character->getUser();
        $monster     = $this->monster->getMonster();

        $item = $this->createItem([
            'name' => 'quest item',
            'type' => 'quest',
        ]);


        $monster->update([
            'quest_item_id' => $item->id,
            'quest_item_drop_chance' => 1.00,
        ]);

        $monster = $monster->refresh();

        $currentGold = $character->gold;

        $response = $this->actingAs($user)
            ->json('POST', '/api/battle-results/' . $character->id, [
                'is_defender_dead' => true,
                'defender_type' => 'monster',
                'monster_id' => $monster->id,
            ])
            ->response;


        $character = $character->refresh();

        $found = $character->inventory->slots->filter(function($slot) use ($item) {
            return $slot->item->name === $item->name;
        })->all();

        $this->assertEquals(200, $response->status());
        $this->assertTrue($currentGold !== $character->gold);
        $this->assertTrue(count($character->inventory->slots) > 1);
        $this->assertNotEmpty($found);
    }

    public function testBattleResultsMonsterIsDeadAndCharacterDidNotGainQuestItem() {
        Event::fake([
            ServerMessageEvent::class,
            GoldRushCheckEvent::class,
            AttackTimeOutEvent::class,
            UpdateTopBarBroadcastEvent::class,
        ]);

        $character   = $this->character->updateSkill('Looting', ['level' => 0])->getCharacter(false);
        $user        = $this->character->getUser();
        $monster     = $this->monster->getMonster();


        $item = $this->createItem([
            'name' => 'quest item',
            'type' => 'quest',
        ]);


        $monster->update([
            'quest_item_id' => $item->id,
            'quest_item_drop_chance' => 0.0,
            'drop_check' => 0.0,
        ]);

        $monster = $monster->refresh();

        $currentGold = $character->gold;

        $response = $this->actingAs($user)
            ->json('POST', '/api/battle-results/' . $character->id, [
                'is_defender_dead' => true,
                'defender_type' => 'monster',
                'monster_id' => $monster->id,
            ])
            ->response;

        $character = $character->refresh();

        $found = $character->inventory->slots->filter(function($slot) use ($item) {
            return $slot->item->name === $item->name;
        })->all();

        $this->assertEquals(200, $response->status());
        $this->assertTrue($currentGold !== $character->gold);
        $this->assertEmpty($found);
    }

    public function testBattleResultsMonsterIsDeadAndCharacterGainedQuestItemMonsterDropChanceIsMax() {
        Event::fake([
            ServerMessageEvent::class,
            GoldRushCheckEvent::class,
            AttackTimeOutEvent::class,
            UpdateTopBarBroadcastEvent::class,
        ]);

        $character   = $this->character->updateSkill('Looting', ['level' => 1])->getCharacter(false);
        $user        = $this->character->getUser();
        $monster     = $this->monster->getMonster();

        $itemId = $this->createItem([
            'name' => 'quest item',
            'type' => 'quest',
        ])->id;

        $monster->update([
            'quest_item_id' => $itemId,
            'quest_item_drop_chance' => 1.00,
        ]);

        $monster = $monster->refresh();

        $currentGold = $character->gold;

        $response = $this->actingAs($user)
            ->json('POST', '/api/battle-results/' . $character->id, [
                'is_defender_dead' => true,
                'defender_type' => 'monster',
                'monster_id' => $monster->id,
            ])
            ->response;

        $character = $character->refresh();

        $found = $character->inventory->slots->filter(function($slot) use ($itemId) {
            return $slot->item_id === $itemId;
        })->all();

        $this->assertEquals(200, $response->status());
        $this->assertTrue($currentGold !== $character->gold);
        $this->assertTrue(count($character->inventory->slots) > 1);
        $this->assertNotEmpty($found);
    }

    public function testBattleResultsCharacterCannotPickUpItem() {
        Event::fake([
            ServerMessageEvent::class,
            GoldRushCheckEvent::class,
            AttackTimeOutEvent::class,
            UpdateTopBarBroadcastEvent::class,
        ]);

        $character   = $this->character->updateSkill('Looting', ['level' => 100])
                                       ->updateCharacter(['inventory_max' => 0])
                                       ->getCharacter(false);
        $user        = $this->character->getUser();
        $monster     = $this->monster->getMonster();

        $currentGold = $character->gold;

        $response = $this->actingAs($user)
                         ->json('POST', '/api/battle-results/' . $character->id, [
                             'is_defender_dead' => true,
                             'defender_type' => 'monster',
                             'monster_id' => $monster->id,
                         ])
                         ->response;

        $character = $this->character->getCharacter(false);

        $this->assertEquals(200, $response->status());
        $this->assertTrue($currentGold !== $character->gold);
        $this->assertTrue(count($character->inventory->slots) === 1);
    }

    public function testBattleResultsMonsterIsDeadAndCharacterGainedGoldRush() {
        Event::fake([
            ServerMessageEvent::class,
            DropsCheckEvent::class,
            AttackTimeOutEvent::class,
            UpdateTopBarBroadcastEvent::class,
        ]);

        $character   = $this->character->updateSkill('Looting', ['level' => 100])
                                       ->getCharacter(false);
        $user        = $this->character->getUser();
        $monster     = $this->monster->getMonster();

        $response = $this->actingAs($user)
                         ->json('POST', '/api/battle-results/' . $character->id, [
                             'is_defender_dead' => true,
                             'defender_type' => 'monster',
                             'monster_id' => $monster->id,
                         ])
                         ->response;

        $character = $this->character->getCharacter(false);

        $this->assertEquals(200, $response->status());
        $this->assertNotEquals(0, $character->gold);
    }

    public function testCharacterGetsFullXPWhenMonsterMaxLevelIsHigherThenCharacterLevel() {
        Event::fake([
            ServerMessageEvent::class,
            DropsCheckEvent::class,
            GoldRushCheckEvent::class,
            ShowTimeOutEvent::class,
            UpdateTopBarBroadcastEvent::class,
        ]);

        $character = $this->character->getCharacter(false);
        $monster   = $this->monster->updateMonster(['max_level' => 5])->getMonster();
        $user      = $this->character->getUser();

        $response = $this->actingAs($user)
                         ->json('POST', '/api/battle-results/' . $character->id, [
                             'is_defender_dead' => true,
                             'defender_type' => 'monster',
                             'monster_id' => $monster->id,
                         ])
                         ->response;

        $this->assertEquals(200, $response->status());

        $character = $this->character->getCharacter(false);

        $this->assertEquals(10, $character->xp);
    }

    public function testCharacterGetsOneThirdXPWhenMonsterMaxLevelIsLowerThenCharacterLevel() {
        Event::fake([
            ServerMessageEvent::class,
            DropsCheckEvent::class,
            GoldRushCheckEvent::class,
            ShowTimeOutEvent::class,
            UpdateTopBarBroadcastEvent::class,
        ]);

        $character = $this->character->updateCharacter(['level' => 500])->getCharacter(false);
        $monster   = $this->monster->updateMonster(['max_level' => 5])->getMonster();
        $user      = $this->character->getUser();

        $response = $this->actingAs($user)
                         ->json('POST', '/api/battle-results/' . $character->id, [
                             'is_defender_dead' => true,
                             'defender_type' => 'monster',
                             'monster_id' => $monster->id,
                         ])
                         ->response;

        $this->assertEquals(200, $response->status());

        $this->assertEquals(3.0, $this->character->getCharacter(false)->xp);
    }

    public function testCharacterSeesErrorForUnknownType() {
        Event::fake([
            ServerMessageEvent::class,
            DropsCheckEvent::class,
            GoldRushCheckEvent::class,
            ShowTimeOutEvent::class,
            UpdateTopBarBroadcastEvent::class,
        ]);

        $character = $this->character->updateCharacter(['level' => 500])->getCharacter(false);
        $monster   = $this->monster->updateMonster(['max_level' => 5])->getMonster();
        $user      = $this->character->getUser();

        $response = $this->actingAs($user)
                         ->json('POST', '/api/battle-results/' . $character->id, [
                             'is_defender_dead' => true,
                             'defender_type' => 'apple-sauce',
                             'monster_id' => $monster->id,
                         ])
                         ->response;

        $this->assertEquals(422, $response->status());
        $this->assertEquals('Could not find type of defender.', json_decode($response->content())->message);
    }

    public function testWhenNotLoggedInCannotAccessBattleResults() {

        $response = $this->json('POST', '/api/battle-results/1')
                         ->response;

        $this->assertEquals(401, $response->status());
    }

    public function testWhenCharacterIsDeadReturnFourOhOne() {
        Event::fake([CharacterIsDeadBroadcastEvent::class, UpdateTopBarEvent::class]);

        $character = $this->character->updateCharacter(['is_dead' => true])->getCharacter(false);

        $response = $this->json('POST', '/api/battle-revive/' . $character->id)
                         ->response;

        $this->assertEquals(401, $response->status());
    }

    public function testCharacterCannotFightWhenDead() {
        Event::fake([CharacterIsDeadBroadcastEvent::class, UpdateTopBarEvent::class]);

        $character = $this->character->updateCharacter(['is_dead' => true])->getCharacter(false);
        $monster   = $this->monster->updateMonster(['max_level' => 5])->getMonster();
        $user      = $this->character->getUser();

        $response = $this->actingAs($user)
                         ->json('POST', '/api/battle-results/' . $character->id, [
                             'is_defender_dead' => true,
                             'defender_type' => 'apple-sauce',
                             'monster_id' => $monster->id,
                         ])
                         ->response;

        $this->assertEquals("You are dead and must revive before trying to do that. Dead people can't do things.", json_decode($response->content())->error);
        $this->assertEquals(422, $response->status());
    }

    public function testWhenCharacterIsDead() {
        Event::fake([CharacterIsDeadBroadcastEvent::class, UpdateTopBarEvent::class]);

        resolve(BuildMonsterCacheService::class)->buildCache();

        $character = $this->character->updateCharacter(['is_dead' => true])->getCharacter(false);
        $user      = $this->character->getUser();

        $response = $this->actingAs($user)
                         ->json('POST', '/api/battle-revive/' . $character->id)
                         ->response;



        $this->assertEquals(200, $response->status());

        $this->assertFalse($this->character->getCharacter(false)->is_dead);
    }

    public function testSkillLevelUpFromFight() {

        $character = $this->character->updateSkill('Looting', [
            'xp'                 => 99,
            'xp_max'             => 100,
            'currently_training' => true
        ])->inventoryManagement()
        ->giveItem($this->createItem([
            'name' => 'Sample',
            'skill_name' => 'Looting',
            'skill_training_bonus' => 1.0,
            'type' => 'quest'
        ]))->giveItem($this->createItem([
            'name' => 'Sample',
            'skill_name' => 'Looting',
            'skill_training_bonus' => 1.0,
            'type' => 'quest'
        ]))->getCharacterFactory()->getCharacter(false);

        $user    = $this->character->getUser();
        $monster = $this->monster->getMonster();

        $response = $this->actingAs($user)
                         ->json('POST', '/api/battle-results/' . $character->id, [
                             'is_defender_dead' => true,
                             'defender_type' => 'monster',
                             'monster_id' => $monster->id,
                         ])
                         ->response;

        $this->assertEquals(200, $response->status());

        $skill = $this->character->getCharacter(false)->skills->filter(function($skill) {
            return $skill->name === 'Looting';
        })->first();

        $this->assertEquals(2, $skill->level);
    }

    public function testSkillDoesNotLevelUpFromFight() {
        $character = $this->character->updateSkill('Looting', [
            'level'              => 100,
            'xp'                 => 99,
            'xp_max'             => 100,
            'currently_training' => true
        ])->inventoryManagement()
        ->giveItem($this->createItem([
            'name' => 'Sample',
            'skill_name' => 'Looting',
            'skill_training_bonus' => 1.0,
            'type' => 'quest'
        ]))->giveItem($this->createItem([
            'name' => 'Sample',
            'skill_name' => 'Looting',
            'skill_training_bonus' => 1.0,
            'type' => 'quest'
        ]))->getCharacterFactory()->getCharacter(false);

        $user    = $this->character->getUser();
        $monster = $this->monster->getMonster();

        $response = $this->actingAs($user)
                         ->json('POST', '/api/battle-results/' . $character->id, [
                             'is_defender_dead' => true,
                             'defender_type' => 'monster',
                             'monster_id' => $monster->id,
                         ])
                         ->response;

        $this->assertEquals(200, $response->status());

        $skill = $this->character->getCharacter(false)->skills->filter(function($skill) {
            return $skill->name === 'Looting';
        })->first();

        // Skill Did Not Level Up:
        $this->assertEquals(100, $skill->level);
    }
}
