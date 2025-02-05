<?php

namespace Tests\Unit\Game\Adventure\Services;

use DB;
use Mail;
use Str;
use Cache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Event;
use App\Flare\Calculators\DropCheckCalculator;
use App\Flare\Calculators\GoldRushCheckCalculator;
use App\Game\Adventures\Services\AdventureService;
use App\Game\Adventures\Mail\AdventureCompleted;
use Mockery;
use Tests\Setup\AdventureSetup;
use Tests\Setup\MockRewardBuilder;
use Tests\TestCase;
use Tests\Traits\CreateUser;
use Tests\Traits\CreateAdventure;
use Tests\Traits\CreateMonster;
use Tests\Traits\CreateItemAffix;
use Tests\Traits\CreateGameSkill;
use Tests\Traits\CreateItem;
use Tests\Setup\Character\CharacterFactory;

class AdventureServiceTest extends TestCase
{
    use RefreshDatabase,
        CreateUser,
        CreateAdventure,
        CreateMonster,
        CreateGameSkill,
        CreateItemAffix,
        CreateItem;

    public function setUp(): void {
        parent::setUp();

        $this->createItemAffix();

        Queue::fake();
        Event::fake();
    }

    public function testProcessAdventureCharacterLives()
    {

        $this->assertTrue(true);

        $item      = $this->createItem(['name' => 'Item Name']);

        $character = (new CharacterFactory)->createBaseCharacter()
                                        ->givePlayerLocation()
                                        ->updateCharacter(['can_move' => false])
                                        ->levelCharacterUp(100)
                                        ->inventoryManagement()
                                        ->giveItem($item, true, 'right-hand')
                                        ->getCharacterFactory()
                                        ->updateSkill('Accuracy', [
                                            'level' => 10,
                                            'xp_towards' => 10,
                                            'currently_training' => true
                                        ])
                                        ->updateSkill('Dodge', [
                                            'level' => 10
                                        ])
                                        ->updateSkill('Looting', [
                                            'level' => 10
                                        ]);

        $adventure = $this->createNewAdventure();

        $character = $character->assignFactionSystem()->createAdventureLog($adventure)->getCharacter(true);


        $adventureService = resolve(AdventureService::class);

        $adventureService->setCharacter($character)->setAdventure($adventure)->setName(Str::random(8));

        for ($i = 1; $i <= $adventure->levels; $i++) {
            $adventureService->processAdventure($i, $adventure->levels, 'attack');
        }


        $character = $character->refresh();

        $this->assertFalse($character->is_dead);
        $this->assertTrue($character->adventureLogs->isNotEmpty());
        $this->assertTrue($character->adventureLogs->first()->complete);
        $this->assertTrue(!empty($character->adventureLogs->first()->rewards));
        $this->assertTrue(!empty($character->adventureLogs->first()->logs));

    }

    public function testProcessAdventureTookTooLong()
    {

        $item      = $this->createItem(['name' => 'Item Name']);

        $character = (new CharacterFactory)->createBaseCharacter()
            ->givePlayerLocation()
            ->updateCharacter(['can_move' => false])
            ->levelCharacterUp(100)
            ->inventoryManagement()
            ->giveItem($item)
            ->getCharacterFactory()
            ->updateSkill('Accuracy', [
                'level' => 10,
                'xp_towards' => 10,
                'currently_training' => true
            ])
            ->updateSkill('Dodge', [
                'level' => 10
            ])
            ->updateSkill('Looting', [
                'level' => 10
            ]);

        $adventure = $this->createNewAdventure();

        $character = $character->assignFactionSystem()->createAdventureLog($adventure)->getCharacter(true);

        $adventureService = resolve(AdventureService::class);

        $adventureService->setCharacter($character)->setAdventure($adventure)->setName(Str::random(8));

        for ($i = 1; $i <= $adventure->levels; $i++) {
            $adventureService->processAdventure($i, $adventure->levels, 'defend');
        }

        $character->refresh();

        $this->assertFalse($character->is_dead);
        $this->assertTrue($character->adventureLogs->isNotEmpty());
        $this->assertFalse($character->adventureLogs->first()->complete);
        $this->assertTrue($character->adventureLogs->first()->took_to_long);
        $this->assertTrue(empty($character->adventureLogs->first()->rewards));
        $this->assertTrue(!empty($character->adventureLogs->first()->logs));
    }

    public function testProcessAdventureCharacterLivesAndOnline()
    {

        $item      = $this->createItem(['name' => 'Item Name']);

        $character = (new CharacterFactory)->createBaseCharacter()
            ->givePlayerLocation()
            ->updateCharacter(['can_move' => false])
            ->levelCharacterUp(100)
            ->inventoryManagement()
            ->giveItem($item, true, 'right-hand')
            ->getCharacterFactory()
            ->updateSkill('Accuracy', [
                'level' => 10,
                'xp_towards' => 10,
                'currently_training' => true
            ])
            ->updateSkill('Dodge', [
                'level' => 10
            ])
            ->updateSkill('Looting', [
                'level' => 10
            ]);

        $adventure = $this->createNewAdventure();

        $character = $character->assignFactionSystem()->createAdventureLog($adventure)->getCharacter(true);

        DB::table('sessions')->insert([[
            'id'           => '1',
            'user_id'      => $character->user->id,
            'ip_address'   => '1',
            'user_agent'   => '1',
            'payload'      => '1',
            'last_activity'=> 1602801731,
        ]]);

        $adventureService = resolve(AdventureService::class);

        $adventureService->setCharacter($character)->setAdventure($adventure)->setName(Str::random(8));

        for ($i = 1; $i <= $adventure->levels; $i++) {
            $adventureService->processAdventure($i, $adventure->levels, 'attack');
        }


        $character->refresh();

        $this->assertEmpty($adventureService->getLogInformation());

        $this->assertFalse($character->is_dead);
        $this->assertTrue($character->adventureLogs->isNotEmpty());
        $this->assertTrue($character->adventureLogs->first()->complete);
        $this->assertTrue(!empty($character->adventureLogs->first()->rewards));
        $this->assertTrue(!empty($character->adventureLogs->first()->logs));

    }

    public function testProcessAdventureWithMultipleLevels()
    {

        $item      = $this->createItem(['name' => 'Item Name']);

        $character = (new CharacterFactory)->createBaseCharacter()
            ->givePlayerLocation()
            ->updateCharacter(['can_move' => false])
            ->levelCharacterUp(100)
            ->inventoryManagement()
            ->giveItem($item, true, 'right-hand')
            ->getCharacterFactory()
            ->updateSkill('Accuracy', [
                'level' => 10,
                'xp_towards' => 10,
                'currently_training' => true
            ])
            ->updateSkill('Dodge', [
                'level' => 10
            ])
            ->updateSkill('Looting', [
                'level' => 10
            ]);

        $adventure = $this->createNewAdventure(null, null, 5);

        $character = $character->assignFactionSystem()->createAdventureLog($adventure)->getCharacter(true);

        $character->map->gameMap->update([
            'drop_chance_bonus' => 0.01
        ]);

        $character = $character->refresh();

        $adventureService = resolve(AdventureService::class);

        $adventureService->setCharacter($character)->setAdventure($adventure)->setName(Str::random(8));

        for ($i = 1; $i <= $adventure->levels; $i++) {
            $adventureService->processAdventure($i, $adventure->levels, 'attack');
        }

        $character = $character->refresh();

        $this->assertEquals(5, $character->adventureLogs->first()->last_completed_level);
    }

    public function testProcessAdventureWithMultipleLevelsMultipleEnemies()
    {

        $item      = $this->createItem(['name' => 'Item Name']);

        $character = (new CharacterFactory)->createBaseCharacter()
            ->givePlayerLocation()
            ->updateCharacter(['can_move' => false])
            ->levelCharacterUp(100)
            ->inventoryManagement()
            ->giveItem($item, true, 'right-hand')
            ->getCharacterFactory()
            ->updateSkill('Accuracy', [
                'level' => 10,
                'xp_towards' => 10,
                'currently_training' => true
            ])
            ->updateSkill('Dodge', [
                'level' => 10
            ])
            ->updateSkill('Looting', [
                'level' => 10
            ]);

        $adventure = $this->createNewAdventure(null, null, 5);

        $character = $character->assignFactionSystem()->createAdventureLog($adventure)->getCharacter(true);

        $adventureService = resolve(AdventureService::class);

        $adventureService->setCharacter($character)->setAdventure($adventure)->setName(Str::random(8));

        for ($i = 1; $i <= $adventure->levels; $i++) {
            $adventureService->processAdventure($i, $adventure->levels, 'attack');
        }

        $character = $character->refresh();

        $this->assertEquals(5, $character->adventureLogs->first()->last_completed_level);
    }

    public function testProcessAdventureWithMultipleLevelsAndGetQuestItem()
    {
        $item = $this->createItem([
            'name' => 'Apples',
            'type' => 'quest',
        ]);

        $monster = $this->createMonster([
            'quest_item_id'          => $item->id,
            'quest_item_drop_chance' => 0.05,
        ]);

        $character = (new CharacterFactory)->createBaseCharacter()
            ->givePlayerLocation()
            ->levelCharacterUp(10)
            ->updateCharacter(['can_move' => false])
            ->inventoryManagement()
            ->giveItem($this->createItem([
                'name' => 'Sample Item',
                'base_damage' => 11600,
            ]), true, 'right-hand')
            ->equipLeftHand('Sample Item')
            ->getCharacterFactory()
            ->updateSkill('Accuracy', [
                'level' => 10,
                'xp_towards' => 10,
                'currently_training' => true
            ])
            ->updateSkill('Dodge', [
                'level' => 10
            ])
            ->updateSkill('Looting', [
                'level' => 10
            ]);

        $adventure = $this->createNewAdventure(null, $monster, 5);

        $character = $character->assignFactionSystem()->createAdventureLog($adventure)->getCharacter(true);

        $dropCheckCalculator = Mockery::mock(DropCheckCalculator::class)->makePartial();

        $this->app->instance(DropCheckCalculator::class, $dropCheckCalculator);

        $dropCheckCalculator->shouldReceive('fetchDropCheckChance')->andReturn(true);

        $dropCheckCalculator->shouldReceive('fetchQuestItemDropCheck')->andReturn(true);

        $adventureService = resolve(AdventureService::class);

        $adventureService->setCharacter($character)->setAdventure($adventure)->setName(Str::random(8));

        for ($i = 1; $i <= $adventure->levels; $i++) {
            $adventureService->processAdventure($i, $adventure->levels, 'attack');
        }

        $this->assertTrue(true);

        $character = $character->refresh();

        $this->assertEquals(5, $character->adventureLogs->first()->last_completed_level);

        foreach ($character->adventureLogs->first()->rewards as $key => $value) {
            if ($key === 'items') {
                $this->assertNotFalse(array_search($item->id, array_column($value, 'id')));
            }
        }
    }

    public function testProcessAdventureWithMultipleLevelsAndCannotReceiveQuestItem()
    {

        $item = $this->createItem([
            'name' => 'Apples',
            'type' => 'quest',
        ]);

        $monster = $this->createMonster([
            'quest_item_id'          => $item->id,
            'quest_item_drop_chance' => 0.05,
        ]);

        $character = (new CharacterFactory)->createBaseCharacter()
            ->givePlayerLocation()
            ->levelCharacterUp(10)
            ->updateCharacter(['can_move' => false])
            ->inventoryManagement()
            ->giveItem($this->createItem([
                'name' => 'Sample Item',
                'base_damage' => 11600,
            ]), true, 'right-hand')
            ->equipLeftHand('Sample Item')
            ->getCharacterFactory()
            ->updateSkill('Accuracy', [
                'level' => 10,
                'xp_towards' => 10,
                'currently_training' => true
            ])
            ->updateSkill('Dodge', [
                'level' => 10
            ])
            ->updateSkill('Looting', [
                'level' => 10
            ])
            ->inventoryManagement()
            ->giveItem($item)
            ->getCharacterFactory();

        $adventure = $this->createNewAdventure(null, $monster, 5);

        $character = $character->assignFactionSystem()->createAdventureLog($adventure)->getCharacter(true);

        $dropCheckCalculator = Mockery::mock(DropCheckCalculator::class)->makePartial();

        $this->app->instance(DropCheckCalculator::class, $dropCheckCalculator);

        $dropCheckCalculator->shouldReceive('fetchDropCheckChance')->andReturn(true);

        $dropCheckCalculator->shouldReceive('fetchQuestItemDropCheck')->andReturn(true);

        $adventureService = resolve(AdventureService::class);

        $adventureService->setCharacter($character)->setAdventure($adventure)->setName(Str::random(8));

        for ($i = 1; $i <= $adventure->levels; $i++) {
            $adventureService->processAdventure($i, $adventure->levels, 'attack');
        }

        $character = $character->refresh();

        $this->assertEquals(5, $character->adventureLogs->first()->last_completed_level);

        foreach ($character->adventureLogs->first()->rewards as $key => $value) {
            if ($key === 'items') {
                $this->assertFalse(array_search($item->id, array_column($value, 'id')));
            }
        }
    }

    public function testProcessAdventureWithMultipleLevelsWithNoDrops()
    {

        $character = (new CharacterFactory)->createBaseCharacter()
                                        ->givePlayerLocation()
                                        ->inventoryManagement()
                                        ->giveItem($this->createItem([
                                            'name' => 'Sample Item',
                                            'base_damage' => 600,
                                        ]), true, 'right-hand')
                                        ->equipLeftHand('Sample Item')
                                        ->getCharacterFactory()
                                        ->levelCharacterUp(10)
                                        ->updateCharacter(['can_move' => false])
                                        ->updateSkill('Accuracy', [
                                            'level' => 10,
                                            'xp_towards' => 10,
                                            'currently_training' => true
                                        ]);

        $adventure = $this->createNewAdventure(null, null, 5);

        $character = $character->assignFactionSystem()->createAdventureLog($adventure)->getCharacter(true);

        $dropCheckCalculator = Mockery::mock(DropCheckCalculator::class)->makePartial();

        $this->app->instance(DropCheckCalculator::class, $dropCheckCalculator);

        $dropCheckCalculator->shouldReceive('fetchDropCheckChance')->andReturn(false);

        $goldRushChange = Mockery::mock(GoldRushCheckCalculator::class)->makePartial();

        $this->app->instance(GoldRushCheckCalculator::class, $goldRushChange);

        $goldRushChange->shouldReceive('fetchGoldRushChance')->andReturn(false);

        $adventureService = resolve(AdventureService::class);

        $adventureService->setCharacter($character)->setAdventure($adventure)->setName(Str::random(8));

        for ($i = 1; $i <= $adventure->levels; $i++) {
            $adventureService->processAdventure($i, $adventure->levels, 'attack');
        }

        $character = $character->refresh();

        $this->assertEquals(5, $character->adventureLogs->first()->last_completed_level);
    }

    public function testProcessAdventureWithMultipleLevelsNotTrainingSkills()
    {

        $character = (new CharacterFactory)->createBaseCharacter()
                                        ->givePlayerLocation()
                                        ->inventoryManagement()
                                        ->giveItem($this->createItem([
                                            'name' => 'Sample Item',
                                            'base_damage' => 600,
                                        ]), true, 'right-hand')
                                        ->equipLeftHand('Sample Item')
                                        ->getCharacterFactory()
                                        ->levelCharacterUp(10)
                                        ->updateCharacter(['can_move' => false])
                                        ->updateSkill('Accuracy', [
                                            'level' => 10,
                                            'xp_towards' => 10,
                                            'currently_training' => true
                                        ])
                                        ->updateSkill('Dodge', [
                                            'level' => 10
                                        ])
                                        ->updateSkill('Looting', [
                                            'level' => 10
                                        ]);

        $adventure = $this->createNewAdventure(null, null, 5);

        $character = $character->assignFactionSystem()->createAdventureLog($adventure)->getCharacter(true);

        $adventureService = resolve(AdventureService::class);

        $adventureService->setCharacter($character)->setAdventure($adventure)->setName(Str::random(8));

        for ($i = 1; $i <= $adventure->levels; $i++) {
            $adventureService->processAdventure($i, $adventure->levels, 'attack');
        }

        $character = $character->refresh();

        $this->assertEquals(5, $character->adventureLogs->first()->last_completed_level);
    }

    public function testProcessAdventureCharacterDiesLoggedIn()
    {
        $monster = $this->createMonster([
            'name' => 'Monster',
            'damage_stat' => 'str',
            'xp' => 10,
            'str' => 500,
            'dur' => 500,
            'dex' => 500,
            'chr' => 500,
            'int' => 500,
            'ac' => 500,
            'gold' => 1,
            'max_level' => 500,
            'health_range' => '999-9999',
            'attack_range' => '99-999',
            'drop_check' => 0.1,
            'devouring_light_chance' => 1.1,
        ]);

        $character = (new CharacterFactory)->createBaseCharacter()
            ->givePlayerLocation()
                                        ->updateCharacter(['can_move' => false, 'dur' => 1])
                                        ->updateSkill('Accuracy', [
                                            'level' => 0,
                                            'xp_towards' => 10,
                                            'currently_training' => true
                                        ])
                                        ->updateSkill('Dodge', [
                                            'level' => 0
                                        ])
                                        ->updateSkill('Looting', [
                                            'level' => 0
                                        ]);

        $adventure = (new AdventureSetup)->setMonster($monster)->createAdventure();

        $character = $character->assignFactionSystem()->createAdventureLog($adventure)->getCharacter(true);


        $this->actingAs($character->user);

        DB::table('sessions')->insert([[
            'id'           => '1',
            'user_id'      => $character->user->id,
            'ip_address'   => '1',
            'user_agent'   => '1',
            'payload'      => '1',
            'last_activity'=> 1602801731,
        ]]);

        $adventureService = resolve(AdventureService::class);

        $adventureService->setCharacter($character)->setAdventure($adventure)->setName(Str::random(8));

        for ($i = 1; $i <= $adventure->levels; $i++) {
            $adventureService->processAdventure($i, $adventure->levels, 'attack');
        }

        $character = $character->refresh();

        $this->assertTrue($character->is_dead);
        $this->assertTrue($character->adventureLogs->isNotEmpty());
        $this->assertFalse($character->adventureLogs->first()->complete);
        $this->assertEquals(1, $character->adventureLogs->first()->last_completed_level);
    }


    public function testProcessAdventureCharacterDiesNotLoggedIn()
    {
        $monster = $this->createMonster([
            'name' => 'Monster',
            'damage_stat' => 'str',
            'xp' => 10,
            'str' => 500,
            'dur' => 500,
            'dex' => 500,
            'chr' => 500,
            'int' => 500,
            'ac' => 500,
            'gold' => 1,
            'max_level' => 10,
            'health_range' => '999-9999',
            'attack_range' => '99-999',
            'drop_check' => 0.1,
            'devouring_light_chance' => 1.1
        ]);

        $character = (new CharacterFactory)->createBaseCharacter()
                                        ->givePlayerLocation()
                                        ->updateCharacter(['can_move' => false])
                                        ->updateSkill('Accuracy', [
                                            'level' => 0,
                                            'xp_towards' => 10,
                                            'currently_training' => true
                                        ])
                                        ->updateSkill('Dodge', [
                                            'level' => 0
                                        ])
                                        ->updateSkill('Looting', [
                                            'level' => 0
                                        ]);

        $adventure = (new AdventureSetup)->setMonster($monster)->createAdventure();

        $character = $character->assignFactionSystem()->createAdventureLog($adventure)->getCharacter(true);

        Mail::fake();

        $adventureService = resolve(AdventureService::class);

        $adventureService->setCharacter($character)->setAdventure($adventure)->setName(Str::random(8));

        for ($i = 1; $i <= $adventure->levels; $i++) {
            $adventureService->processAdventure($i, $adventure->levels, 'attack');
        }

        $character = $character->refresh();

        Mail::assertSent(AdventureCompleted::class, 1);

        $this->assertTrue($character->is_dead);
        $this->assertTrue($character->adventureLogs->isNotEmpty());
        $this->assertFalse($character->adventureLogs->first()->complete);
        $this->assertEquals(1, $character->adventureLogs->first()->last_completed_level);
    }
}
