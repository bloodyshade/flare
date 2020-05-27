<?php

namespace Tests\Unit\Flare\Builders;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreateUser;
use Tests\Traits\CreateItem;
use Tests\Setup\CharacterSetup;
use App\Flare\Builders\RandomItemDropBuilder;
use App\Flare\Models\Item;
use App\Flare\Models\ItemAffix;
use Tests\Traits\CreateItemAffix;

class RandomItemDropBuilderTest extends TestCase
{

    use RefreshDatabase,
        CreateItem,
        CreateItemAffix,
        CreateUser;

    private $character;

    public function setUp(): void {
        parent::setup();

        $this->createItem([
            'name' => 'Rusty Dagger',
            'type' => 'weapon',
        ]);

        $item = $this->createItem([
            'name' => 'Bloody Spear',
            'type' => 'weapon',
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
            'ac_mod'               => '0.10',
            'skill_name'           => null,
            'skill_training_bonus' => null,
        ]);

        $this->character = (new CharacterSetup)->setupCharacter($this->createUser())
                                               ->giveItem($item)
                                               ->equipLeftHand()
                                               ->setSkill('Looting', [
                                                   'looting_level' => 100,
                                                   'looting_bonus' => 100,
                                               ])
                                               ->getCharacter();
    }

    public function tearDown(): void {
        parent::tearDown();

        $this->character = null;
    }

    public function testCreatesRegularItem() {
        $randomItemGenerator = resolve(RandomItemDropBuilder::class)
                                    ->setItemAffixes(ItemAffix::all());

        $looting = $this->character->skills->where('name', 'Looting')->first();
        $looting->update([
            'skill_bonus' => -100
        ]);

        $item = $randomItemGenerator->generateItem($this->character);

        $this->assertNull($item->itemSuffix);
    }

    public function testCreateEnchantedItem() {
        $randomItemGenerator = resolve(RandomItemDropBuilder::class)
                                    ->setItemAffixes(ItemAffix::all());

        $looting = $this->character->skills->where('name', 'Looting')->first();
        $looting->update([
            'skill_bonus' => 100
        ]);

        $item = $randomItemGenerator->generateItem($this->character);

        $this->assertNotNull($item->itemSuffix);
    }
}