<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use Faker\Generator as Faker;
use Illuminate\Support\Str;
use App\Flare\Models\ArtifactProperty;


/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(ArtifactProperty::class, function (Faker $faker) {
    return [
        'item_id'         => null,
        'name'            => null,
        'base_damage_mod' => null,
        'description'     => null,
    ];
});