<?php

namespace Tests\Feature\Game\Quests\Controllers;

use App\Flare\Models\Npc;
use App\Flare\Values\NpcCommandTypes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Flare\Values\NpcTypes;
use Tests\Setup\Character\CharacterFactory;
use Tests\TestCase;
use Tests\Traits\CreateItem;
use Tests\Traits\CreateNpc;
use Tests\Traits\CreateQuest;

class QuestsControllerTest extends TestCase
{
    use RefreshDatabase, CreateQuest, CreateNpc, CreateItem;

    private $character;

    private $completedQuest;

    public function setUp(): void {
        parent::setUp();

        $this->character = (new CharacterFactory)->createBaseCharacter()->givePlayerLocation();

        $this->completedQuest = $this->createCompletedQuest([
            'character_id' => $this->character->getCharacter()->id,
            'quest_id'     => $this->createQuest([
                'name'   => 'Sample',
                'item_id' => $this->createItem()->id,
                'npc_id' => $this->createNpc([
                    'type'        => NpcTypes::QUEST_GIVER,
                    'game_map_id' => $this->character->getCharacter()->map->game_map_id,
                ])->id,
                'unlocks_skill'   => false,
            ])->id,
        ]);

        Npc::first()->commands()->create([
            'npc_id'       => Npc::first()->id,
            'command'      => 'Command',
            'command_type' => NpcCommandTypes::QUEST,
        ]);
    }

    public function testVisitCompleteQuestsPage() {
        $character = $this->character->getCharacter();

        $this->actingAs($character->user)->visitRoute('completed.quests', [
            'user' => $character->user->id,
        ])->see('Completed Quests');
    }

    public function testVisitCompletedQuestPage() {
        $character = $this->character->getCharacter();

        $this->actingAs($character->user)->visitRoute('completed.quest', [
            'character'       => $character->id,
            'questsCompleted' => $this->completedQuest->id,
        ])->see($this->completedQuest->quest->name);
    }
}