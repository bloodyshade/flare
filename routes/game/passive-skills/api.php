<?php

Route::middleware([
    'auth',
    'is.player.banned',
    'is.character.who.they.say.they.are',
    'is.character.adventuring',
])->group(function() {
    Route::post('/train/passive/{characterPassiveSkill}/{character}', ['as' => 'train.passive.skill', 'uses' => 'Api\CharacterPassiveSkillController@trainSkill']);
    Route::post('/stop-training/passive/{characterPassiveSkill}/{character}', ['as' => 'stop.training.passive.skill', 'uses' => 'Api\CharacterPassiveSkillController@stopTraining']);
});