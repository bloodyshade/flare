<?php

namespace Tests\Unit\Game\Adventure\Jobs;

use App\Flare\Services\BuildCharacterAttackTypes;
use App\Game\Adventures\Builders\RewardBuilder;
use Cache;
use App\Game\Adventures\Jobs\AdventureJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Setup\Character\CharacterFactory;
use Tests\Traits\CreateAdventure;
use Tests\Traits\CreateGameSkill;
use Tests\Traits\CreateItem;
use Tests\Traits\CreateItemAffix;

class AdventureJobTest extends TestCase
{
    use RefreshDatabase, CreateAdventure, CreateGameSkill, CreateItem, CreateItemAffix;


    public function testAdventureJob()
    {

        $this->createItem();
        $this->createItemAffix();

        $character = (new CharacterFactory)->createBaseCharacter()
                ->givePlayerLocation()
                ->levelCharacterUp(5)
                ->inventoryManagement()
                ->giveItem($this->createItem([
                    'name' => 'Sample Item',
                    'base_damage' => 11600,
                ]))
                ->equipLeftHand('Sample Item')
                ->getCharacterFactory()
                ->updateSkill('Accuracy', [
                    'level' => 999
                ])
                ->updateSkill('Casting Accuracy', [
                    'level' => 999
                ]);

        $adventure = $this->createNewAdventure();

        $character = $character->assignFactionSystem()->getCharacter(true);

        $character->adventureLogs()->create([
            'character_id'         => $character->id,
            'adventure_id'         => $adventure->id,
            'complete'             => false,
            'in_progress'          => true,
            'took_to_long'         => false,
            'last_completed_level' => null,
            'logs'                 => null,
            'rewards'              => null,
            'created_at'           => null,
        ]);

        Cache::put('character_'.$character->id.'_adventure_'.$adventure->id, 'Sample');

        AdventureJob::dispatch($character->refresh(), $adventure, 'attack', 'Sample', 1);

        $this->assertTrue($character->refresh()->adventureLogs()->first()->complete);
    }

    public function testBailWhenJobNameDoesNotExist()
    {

        $this->createItem();
        $this->createItemAffix();

        $character = (new CharacterFactory)->createBaseCharacter()->givePlayerLocation()->levelCharacterUp(5)->getCharacter(false);

        $adventure = $this->createNewAdventure();

        $character->adventureLogs()->create([
            'character_id'         => $character->id,
            'adventure_id'         => $adventure->id,
            'complete'             => false,
            'in_progress'          => true,
            'took_to_long'         => false,
            'last_completed_level' => null,
            'logs'                 => null,
            'rewards'              => null,
            'created_at'           => null,
        ]);

        AdventureJob::dispatch($character->refresh(), $adventure, 'attack', 'Sample', 1);

        $this->assertFalse($character->refresh()->adventureLogs()->first()->complete);
    }


}
