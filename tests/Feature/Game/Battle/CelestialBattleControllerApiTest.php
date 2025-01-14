<?php

namespace Tests\Feature\Game\Battle;

use App\Flare\Builders\CharacterAttackInformation;
use App\Flare\Builders\CharacterInformationBuilder;
use Illuminate\Support\Facades\Event;
use Mockery;
use App\Flare\Models\CelestialFight;
use App\Flare\Services\BuildMonsterCacheService;
use App\Flare\Values\NpcTypes;
use App\Game\Battle\Values\CelestialConjureType;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Setup\Character\CharacterFactory;
use Tests\Traits\CreateCelestials;
use Tests\Traits\CreateItem;
use Tests\Traits\CreateItemAffix;
use Tests\Traits\CreateMonster;
use Tests\Traits\CreateNpc;

class CelestialBattleControllerApiTest extends TestCase {

    use RefreshDatabase, CreateMonster, CreateNpc, CreateCelestials, CreateItem, CreateItemAffix;

    private $character = null;

    public function setUp(): void {
        parent::setUp();

        $this->character = (new CharacterFactory)->createBaseCharacter()->givePlayerLocation()->updateCharacter([
            'gold'      => 999999999,
            'gold_dust' => 999999999,
        ]);
    }

    public function testGetAListOfCelestialMonsters() {
        $this->createMonster([
            'is_celestial_entity' => true,
            'gold_cost'           => 1000,
            'gold_dust_cost'      => 1000,
        ]);

        $character = $this->character->getCharacter(false);

        $response = $this->actingAs($character->user)
                         ->json('GET', '/api/celestial-beings/' . $character->id)
                         ->response;

        $content = json_decode($response->content());

        $this->assertEquals(200, $response->status());
        $this->assertCount(1, $content->celestial_monsters);
        $this->assertTrue($content->character_gold > 1);
        $this->assertTrue($content->character_gold_dust > 1);
    }

    public function testCanConjureCelestial() {
        $this->createNpc([
           'type' => NpcTypes::SUMMONER
        ]);

        $monster = $this->createMonster([
            'is_celestial_entity' => true,
            'gold_cost'           => 1000,
            'gold_dust_cost'      => 1000,
        ]);

        $character = $this->character->getCharacter(true);

        $response = $this->actingAs($character->user)
            ->json('POST', '/api/conjure/' . $character->id, [
                'monster_id' => $monster->id,
                'type'       => 'public'
            ])
            ->response;

        $this->assertEquals(200, $response->status());
        $this->assertTrue(CelestialFight::all()->isNotEmpty());
    }

    public function testCannotConjureCelestialWhenRequestIsWrong() {
        $this->createNpc([
            'type' => NpcTypes::SUMMONER
        ]);

        $monster = $this->createMonster([
            'is_celestial_entity' => true,
            'gold_cost'           => 1000,
            'gold_dust_cost'      => 1000,
        ]);

        $character = $this->character->getCharacter(false);

        $response = $this->actingAs($character->user)
            ->json('POST', '/api/conjure/' . $character->id, [
                'monster_id' => $monster->id,
                'type'       => 'sample'
            ])
            ->response;

        $this->assertEquals(422, $response->status());
        $this->assertFalse(CelestialFight::all()->isNotEmpty());
    }

    public function testCannotConjureCelestialWhenPrivateCelestialExists() {
        $this->createNpc([
            'type' => NpcTypes::SUMMONER
        ]);

        $monster = $this->createMonster([
            'is_celestial_entity' => true,
            'gold_cost'           => 1000,
            'gold_dust_cost'      => 1000,
        ]);

        $character = $this->character->getCharacter(false);

        $this->createCelestialFight([
            'monster_id'        => $monster->id,
            'character_id'      => $character->id,
            'conjured_at'       => now(),
            'x_position'        => 16,
            'y_position'        => 16,
            'damaged_kingdom'   => false,
            'stole_treasury'    => false,
            'weakened_morale'   => false,
            'current_health'    => 1000,
            'max_health'        => 1000,
            'type'              => CelestialConjureType::PRIVATE,
        ]);

        $response = $this->actingAs($character->user)
            ->json('POST', '/api/conjure/' . $character->id, [
                'monster_id' => $monster->id,
                'type'       => 'private'
            ])
            ->response;

        $this->assertEquals(200, $response->status());

        // There should only be one, you cannot have multiple private conjurations.
        $this->assertTrue(CelestialFight::count() === 1);
    }

    public function testCannotConjureCelestialWhenPublicCelestialExists() {
        $this->createNpc([
            'type' => NpcTypes::SUMMONER
        ]);

        $monster = $this->createMonster([
            'is_celestial_entity' => true,
            'gold_cost'           => 1000,
            'gold_dust_cost'      => 1000,
        ]);

        $character = $this->character->getCharacter(false);

        $this->createCelestialFight([
            'monster_id'        => $monster->id,
            'character_id'      => null,
            'conjured_at'       => now(),
            'x_position'        => 16,
            'y_position'        => 16,
            'damaged_kingdom'   => false,
            'stole_treasury'    => false,
            'weakened_morale'   => false,
            'current_health'    => 1000,
            'max_health'        => 1000,
            'type'              => CelestialConjureType::PUBLIC,
        ]);

        $response = $this->actingAs($character->user)
            ->json('POST', '/api/conjure/' . $character->id, [
                'monster_id' => $monster->id,
                'type'       => 'public'
            ])
            ->response;

        $this->assertEquals(200, $response->status());

        // There should only be one, you cannot have multiple private conjurations.
        $this->assertTrue(CelestialFight::count() === 1);
    }

    public function testCannotConjureCelestialTooPoor() {
        $this->createNpc([
            'type' => NpcTypes::SUMMONER
        ]);

        $monster = $this->createMonster([
            'is_celestial_entity' => true,
            'gold_cost'           => 1000000,
            'gold_dust_cost'      => 1000000,
        ]);

        $character = $this->character->getCharacter(false);

        $character->update([
            'gold' => 0,
            'gold_dust' => 0,
        ]);

        $character = $character->refresh();

        $response = $this->actingAs($character->user)
            ->json('POST', '/api/conjure/' . $character->id, [
                'monster_id' => $monster->id,
                'type'       => 'public'
            ])
            ->response;

        $this->assertEquals(200, $response->status());
        $this->assertTrue(CelestialFight::all()->isEmpty());
    }

    public function testGetCelestialFight() {
        $this->createNpc([
            'type' => NpcTypes::SUMMONER
        ]);

        $monster = $this->createMonster([
            'is_celestial_entity' => true,
            'gold_cost'           => 1000,
            'gold_dust_cost'      => 1000,
        ]);

        $character = $this->character->getCharacter(false);

        $celestialFight = $this->createCelestialFight([
            'monster_id'        => $monster->id,
            'character_id'      => null,
            'conjured_at'       => now(),
            'x_position'        => 16,
            'y_position'        => 16,
            'damaged_kingdom'   => false,
            'stole_treasury'    => false,
            'weakened_morale'   => false,
            'current_health'    => 1000,
            'max_health'        => 1000,
            'type'              => CelestialConjureType::PUBLIC,
        ]);

        $response = $this->actingAs($character->user)
            ->json('GET', '/api/celestial-fight/'.$character->id.'/' . $celestialFight->id, [
                'attack_type' => 'attack'
            ])
            ->response;

        $content = json_decode($response->content());

        $this->assertEquals(200, $response->status());

        $this->assertObjectHasAttribute('fight', $content);
    }


    public function testCannotGetCelestialFightWhenDead() {
        $this->createNpc([
            'type' => NpcTypes::SUMMONER
        ]);

        $monster = $this->createMonster([
            'is_celestial_entity' => true,
            'gold_cost'           => 1000,
            'gold_dust_cost'      => 1000,
        ]);

        $character = $this->character->updateCharacter([
            'is_dead' => true
        ])->getCharacter(false);

        $celestialFight = $this->createCelestialFight([
            'monster_id'        => $monster->id,
            'character_id'      => null,
            'conjured_at'       => now(),
            'x_position'        => 16,
            'y_position'        => 16,
            'damaged_kingdom'   => false,
            'stole_treasury'    => false,
            'weakened_morale'   => false,
            'current_health'    => 1000,
            'max_health'        => 1000,
            'type'              => CelestialConjureType::PUBLIC,
        ]);

        $response = $this->actingAs($character->user)
            ->json('GET', '/api/celestial-fight/'.$character->id.'/' . $celestialFight->id, [
                'attack_type' => 'attack'
            ])
            ->response;

        $content = json_decode($response->content());

        $this->assertEquals(200, $response->status());

        $this->assertCount(0, $content);
    }

    public function testCannotGetCelestialFightWhenAdventuring() {
        $this->createNpc([
            'type' => NpcTypes::SUMMONER
        ]);

        $monster = $this->createMonster([
            'is_celestial_entity' => true,
            'gold_cost'           => 1000,
            'gold_dust_cost'      => 1000,
        ]);

        $character = $this->character->updateCharacter([
            'can_adventure' => false
        ])->getCharacter(false);

        $celestialFight = $this->createCelestialFight([
            'monster_id'        => $monster->id,
            'character_id'      => null,
            'conjured_at'       => now(),
            'x_position'        => 16,
            'y_position'        => 16,
            'damaged_kingdom'   => false,
            'stole_treasury'    => false,
            'weakened_morale'   => false,
            'current_health'    => 1000,
            'max_health'        => 1000,
            'type'              => CelestialConjureType::PUBLIC,
        ]);

        $response = $this->actingAs($character->user)
            ->json('GET', '/api/celestial-fight/'.$character->id.'/' . $celestialFight->id, [
                'attack_type' => 'attack'
            ])
            ->response;

        $content = json_decode($response->content());

        $this->assertEquals(200, $response->status());

        $this->assertCount(0, $content);
    }

    public function testCanAttackCelestial() {
        $this->createItem();
        $this->createItemAffix();

        $this->createNpc([
            'type' => NpcTypes::SUMMONER
        ]);

        $monster = $this->createMonster([
            'is_celestial_entity' => true,
            'gold_cost'           => 1000,
            'gold_dust_cost'      => 1000,
        ]);

        $character = $this->character->levelCharacterUp(50)->getCharacter(false);

        $celestialFight = $this->createCelestialFight([
            'monster_id'        => $monster->id,
            'character_id'      => null,
            'conjured_at'       => now(),
            'x_position'        => 16,
            'y_position'        => 16,
            'damaged_kingdom'   => false,
            'stole_treasury'    => false,
            'weakened_morale'   => false,
            'current_health'    => 1000,
            'max_health'        => 1000,
            'type'              => CelestialConjureType::PUBLIC,
        ]);

        $response = $this->actingAs($character->user)
            ->json('POST', '/api/attack-celestial/'.$character->id.'/' . $celestialFight->id, [
                'attack_type' => 'attack'
            ])
            ->response;

        $this->assertEquals(200, $response->status());
    }

    public function testCanAttackCelestialWithHighHealth() {
        $this->createItem();
        $this->createItemAffix();

        $this->createNpc([
            'type' => NpcTypes::SUMMONER
        ]);

        $monster = $this->createMonster([
            'is_celestial_entity' => true,
            'gold_cost'           => 1000,
            'gold_dust_cost'      => 1000,
        ]);

        $celestialFight = $this->createCelestialFight([
            'monster_id'        => $monster->id,
            'character_id'      => null,
            'conjured_at'       => now(),
            'x_position'        => 16,
            'y_position'        => 16,
            'damaged_kingdom'   => false,
            'stole_treasury'    => false,
            'weakened_morale'   => false,
            'current_health'    => 1000,
            'max_health'        => 1000,
            'type'              => CelestialConjureType::PUBLIC,
        ]);

        $character = $this->character->levelCharacterUp(50)->getCharacter(false);

        $characterInfo = Mockery::spy(CharacterInformationBuilder::class);

        $characterInfo->shouldReceive('buildHealth')->andReturn(	1000000000000000000);

        $response = $this->actingAs($character->user)
            ->json('POST', '/api/attack-celestial/'.$character->id.'/' . $celestialFight->id, [
                'attack_type' => 'attack'
            ])
            ->response;

        $this->assertEquals(200, $response->status());
    }

    public function testCannotAttackCelestialWhenDead() {
        $this->createNpc([
            'type' => NpcTypes::SUMMONER
        ]);

        $monster = $this->createMonster([
            'is_celestial_entity' => true,
            'gold_cost'           => 1000,
            'gold_dust_cost'      => 1000,
        ]);

        $character = $this->character->updateCharacter([
            'is_dead' => true
        ])->getCharacter(false);

        $celestialFight = $this->createCelestialFight([
            'monster_id'        => $monster->id,
            'character_id'      => null,
            'conjured_at'       => now(),
            'x_position'        => 16,
            'y_position'        => 16,
            'damaged_kingdom'   => false,
            'stole_treasury'    => false,
            'weakened_morale'   => false,
            'current_health'    => 1000,
            'max_health'        => 1000,
            'type'              => CelestialConjureType::PUBLIC,
        ]);

        $response = $this->actingAs($character->user)
            ->json('POST', '/api/attack-celestial/'.$character->id.'/' . $celestialFight->id, [
                'attack_type' => 'attack'
            ])
            ->response;

        $content = json_decode($response->content());

        $this->assertEquals(200, $response->status());

        $this->assertCount(0, $content);
    }

    public function testCannotAttackCelestialWhenAdventuring() {
        $this->createNpc([
            'type' => NpcTypes::SUMMONER
        ]);

        $monster = $this->createMonster([
            'is_celestial_entity' => true,
            'gold_cost'           => 1000,
            'gold_dust_cost'      => 1000,
        ]);

        $character = $this->character->updateCharacter([
            'can_adventure' => false
        ])->getCharacter(false);

        $celestialFight = $this->createCelestialFight([
            'monster_id'        => $monster->id,
            'character_id'      => null,
            'conjured_at'       => now(),
            'x_position'        => 16,
            'y_position'        => 16,
            'damaged_kingdom'   => false,
            'stole_treasury'    => false,
            'weakened_morale'   => false,
            'current_health'    => 1000,
            'max_health'        => 1000,
            'type'              => CelestialConjureType::PUBLIC,
        ]);

        $response = $this->actingAs($character->user)
            ->json('POST', '/api/attack-celestial/'.$character->id.'/' . $celestialFight->id, [
                'attack_type' => 'attack'
            ])
            ->response;

        $content = json_decode($response->content());

        $this->assertEquals(200, $response->status());

        $this->assertCount(0, $content);
    }

    public function testReviveInCelestialFight() {

        $this->createNpc([
            'type' => NpcTypes::SUMMONER
        ]);

        $monster = $this->createMonster([
            'is_celestial_entity' => true,
            'gold_cost'           => 1000,
            'gold_dust_cost'      => 1000,
        ]);

        resolve(BuildMonsterCacheService::class)->buildCache();

        $character = $this->character->getCharacter(true);

        $character->update(['is_dead' => true]);

        $character = $character->refresh();

        $celestialFight = $this->createCelestialFight([
            'monster_id'        => $monster->id,
            'character_id'      => null,
            'conjured_at'       => now(),
            'x_position'        => 16,
            'y_position'        => 16,
            'damaged_kingdom'   => false,
            'stole_treasury'    => false,
            'weakened_morale'   => false,
            'current_health'    => 1000,
            'max_health'        => 1000,
            'type'              => CelestialConjureType::PUBLIC,
        ]);

        $this->createCharacterInCelestialFight([
            'character_id'             => $character->id,
            'celestial_fight_id'       => $celestialFight->id,
            'character_max_health'     => $character->getInformation()->buildHealth(),
            'character_current_health' => 0,
        ]);

        $response = $this->actingAs($character->user)
            ->json('POST', '/api/celestial-revive/'.$character->id, [
                'attack_type' => 'attack'
            ])
            ->response;

        $content = json_decode($response->content());

        $this->assertEquals(200, $response->status());

        $this->assertEquals(2839, $content->fight->character->current_health);
    }
}
