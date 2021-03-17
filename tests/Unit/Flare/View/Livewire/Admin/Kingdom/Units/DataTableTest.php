<?php

namespace Tests\Unit\Flare\View\Livewire\Admin\Kingdom\Units;

use Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Flare\View\Livewire\Admin\Kingdoms\Units\DataTable;
use Tests\TestCase;
use Tests\Traits\CreateGameKingdomBuilding;
use Tests\Traits\CreateGameUnit;

class DataTableTest extends TestCase
{
    use RefreshDatabase, CreateGameUnit, CreateGameKingdomBuilding;

    public function setUp(): void {
        parent::setUp();
    }

    public function testTheComponentLoads()
    {
        $this->createGameUnit();

        Livewire::test(DataTable::class)
            ->assertSee('Sample Unit')
            ->set('search', 'Sample Unit')
            ->assertSee('Sample Unit')
            ->set('search', 'Sample 8nit')
            ->assertDontSee('Sample Unit');
    }

    public function testTheComponentLoadsWithKingdomBuilding() {
        $gameKingdomBuilding = $this->createGameKingdomBuilding();

        $gameKingdomBuilding->units()->create([
            'game_building_id' => $gameKingdomBuilding->id,
            'game_unit_id'     => $this->createGameUnit()->id,
            'required_level'   => 1,
        ]);

        Livewire::test(DataTable::class, [
            'building' => $gameKingdomBuilding->refresh(),
        ])
        ->assertSee('Sample Unit')
        ->set('search', 'Sample Unit')
        ->assertSee('Sample Unit')
        ->set('search', 'Sample 8nit')
        ->assertDontSee('Sample Unit');
    }
}
