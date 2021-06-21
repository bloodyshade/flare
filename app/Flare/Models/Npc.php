<?php

namespace App\Flare\Models;

use Illuminate\Database\Eloquent\Model;
use App\Flare\Models\Traits\WithSearch;

class Npc extends Model {

    use WithSearch;

    protected $table = 'npcs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'type',
        'game_map_id',
        'moves_around_map',
        'must_be_at_same_location',
        'text_command_to_message',
        'x_position',
        'y_position',
    ];

    protected $casts = [
        'moves_around_map'         => 'boolean',
        'must_be_at_same_location' => 'boolean',
        'x_position'               => 'integer',
        'x_position'               => 'integer',
    ];

    public function gameMap() {
        return $this->belongsTo(GameMap::class, 'game_map_id', 'id');
    }

    public function commands() {
        return $this->hasMany(NpcCommand::class, 'npc_id', 'id');
    }
}