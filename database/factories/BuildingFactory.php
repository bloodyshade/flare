<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Flare\Models\Building;

class BuildingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Building::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'game_building_id'  => null,
            'kingdoms_id'       => null,
            'level'             => null,
            'current_defence'   => 0,
            'curent_durability' => 0,
            'max_defence'       => 0,
            'max_durability'    => 0,
        ];
    }
}