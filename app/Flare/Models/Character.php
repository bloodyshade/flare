<?php

namespace App\Flare\Models;

use App\Flare\Builders\Character\AttackDetails\CharacterHealthInformation;
use App\Flare\Builders\CharacterInformationBuilder;
use App\Flare\Values\CharacterClassValue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Flare\Models\Traits\WithSearch;
use Database\Factories\CharacterFactory;

class Character extends Model
{

    use HasFactory, WithSearch;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'name',
        'damage_stat',
        'game_race_id',
        'game_class_id',
        'current_adventure_id',
        'inventory_max',
        'can_attack',
        'can_move',
        'can_craft',
        'can_adventure',
        'is_dead',
        'can_move_again_at',
        'can_attack_again_at',
        'can_craft_again_at',
        'can_adventure_again_at',
        'can_settle_again_at',
        'force_name_change',
        'spell_evasion',
        'artifact_annulment',
        'is_attack_automation_locked',
        'is_mass_embezzling',
        'is_npc',
        'is_test',
        'level',
        'xp',
        'xp_next',
        'str',
        'dur',
        'dex',
        'chr',
        'int',
        'agi',
        'focus',
        'ac',
        'gold',
        'gold_dust',
        'shards',
        'copper_coins',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'inventory_max'               => 'integer',
        'current_adventure_id'        => 'integer',
        'can_attack'                  => 'boolean',
        'can_move'                    => 'boolean',
        'can_craft'                   => 'boolean',
        'can_adventure'               => 'boolean',
        'is_dead'                     => 'boolean',
        'force_name_change'           => 'boolean',
        'is_npc'                      => 'boolean',
        'is_test'                     => 'boolean',
        'is_attack_automation_locked' => 'boolean',
        'can_move_again_at'           => 'datetime',
        'can_attack_again_at'         => 'datetime',
        'can_craft_again_at'          => 'datetime',
        'can_adventure_again_at'      => 'datetime',
        'can_settle_again_at'         => 'datetime',
        'level'                       => 'integer',
        'xp'                          => 'integer',
        'xp_next'                     => 'integer',
        'str'                         => 'integer',
        'dur'                         => 'integer',
        'dex'                         => 'integer',
        'chr'                         => 'integer',
        'int'                         => 'integer',
        'agi'                         => 'integer',
        'focus'                       => 'integer',
        'ac'                          => 'integer',
        'gold'                        => 'integer',
        'gold_dust'                   => 'integer',
        'shards'                      => 'integer',
        'copper_coins'                => 'integer',
    ];

    public function race() {
        return $this->belongsTo(GameRace::class, 'game_race_id', 'id');
    }

    public function class() {
        return $this->belongsTo(GameClass::class, 'game_class_id', 'id');
    }

    public function skills() {
        return $this->hasMany(Skill::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function inventory() {
        return $this->hasOne(Inventory::class, 'character_id', 'id');
    }

    public function inventorySets() {
        return $this->hasMany(InventorySet::class, 'character_id', 'id');
    }

    public function factions() {
        return $this->hasMany(Faction::class, 'character_id', 'id');
    }

    public function map() {
        return $this->hasOne(Map::class);
    }

    public function getXPositionAttribute() {
        return $this->map->character_position_x;
    }

    public function getYPositionAttribute() {
        return $this->map->character_position_y;
    }

    public function getMapUrlAttribute() {
        return $this->map->gameMap->path;
    }

    public function getKingdomsCountAttribute() {
        return $this->kingdoms->count();
    }

    public function adventureLogs() {
        return $this->hasMany(AdventureLog::class);
    }

    public function notifications() {
        return $this->hasMany(Notification::class, 'character_id', 'id');
    }

    public function kingdoms() {
        return $this->hasMany(Kingdom::class, 'character_id', 'id');
    }

    public function kingdomAttackLogs() {
        return $this->hasMany(KingdomLog::class);
    }

    public function unitMovementQueues() {
        return $this->hasMany(UnitMovementQueue::class);
    }

    public function boons() {
        return $this->hasMany(CharacterBoon::class);
    }

    public function questsCompleted() {
        return $this->hasMany(QuestsCompleted::class);
    }

    public function currentAutomations() {
        return $this->hasMany(CharacterAutomation::class);
    }

    public function passiveSkills() {
        return $this->hasMany(CharacterPassiveSkill::class);
    }

    public function getXpAttribute($value) {
        return number_format($value, 2);
    }

    /**
     * Allows one to get specific information from a character.
     *
     * By returning the CharacterInformationBuilder class, we can allow you to get
     * multiple calculated sets of data.
     *
     * @return CharacterInformationBuilder
     */
    public function getInformation(): CharacterInformationBuilder {
        $info = resolve(CharacterInformationBuilder::class);

        return $info->setCharacter($this);
    }

    /**
     * Allows one to get health information about the character.
     *
     * @return CharacterHealthInformation
     */
    public function getHeathInformation(): CharacterHealthInformation {
        $healthInfo = resolve(CharacterHealthInformation::class);

        return $healthInfo->setCharacter($this);
    }

    /**
     * Returns the character class value.
     *
     * @return CharacterClassValue
     * @throws \Exception
     */
    public function classType(): CharacterClassValue {
        return new CharacterClassValue($this->class->name);
    }

    /**
     * Gets the inventory count.
     *
     * Excludes quest and equipped items.
     *
     * @return int
     */
    public function getInventoryCount(): int {
        $inventory = Inventory::where('character_id', $this->id)->first();

        return InventorySlot::select('inventory_slots.*')
                            ->where('inventory_slots.inventory_id', $inventory->id)
                            ->where('inventory_slots.equipped', false)
                            ->join('items', function($join) {
                                $join->on('items.id', '=', 'inventory_slots.item_id')
                                     ->where('items.type', '!=', 'quest');
                           })->count();
    }

    /**
     * Is the inventory full?
     *
     * @return bool
     */
    public function isInventoryFull(): bool {
        return $this->getInventoryCount() >= $this->inventory_max;
    }

    protected static function newFactory() {
        return CharacterFactory::new();
    }
}
