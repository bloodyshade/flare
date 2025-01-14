<?php

namespace App\Flare\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Traits\HasRoles;
use App\Game\Messages\Models\Message;
use Database\Factories\UserFactory;

class User extends Authenticatable
{
    use Notifiable, HasRoles, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email',
        'password',
        'last_logged_in',
        'message_throttle_count',
        'can_speak_again_at',
        'is_silenced',
        'is_banned',
        'unbanned_at',
        'ip_address',
        'banned_reason',
        'un_ban_request',
        'adventure_email',
        'upgraded_building_email',
        'kingdoms_update_email',
        'rebuilt_building_email',
        'kingdom_attack_email',
        'unit_recruitment_email',
        'show_unit_recruitment_messages',
        'show_building_upgrade_messages',
        'show_kingdom_update_messages',
        'show_building_rebuilt_messages',
        'auto_disenchant',
        'disable_attack_type_popover',
        'auto_disenchant_amount',
        'timeout_until',
        'is_test',
        'will_be_deleted',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at'              => 'datetime',
        'can_speak_again_at'             => 'datetime',
        'last_logged_in'                 => 'datetime',
        'is_silenced'                    => 'boolean',
        'message_throttle_count'         => 'integer',
        'is_banned'                      => 'boolean',
        'unbanned_at'                    => 'datetime',
        'timeout_until'                  => 'datetime',
        'adventure_email'                => 'boolean',
        'is_test'                        => 'boolean',
        'upgraded_building_email'        => 'boolean',
        'kingdoms_update_email'          => 'boolean',
        'rebuilt_building_email'         => 'boolean',
        'kingdom_attack_email'           => 'boolean',
        'unit_recruitment_email'         => 'boolean',
        'show_unit_recruitment_messages' => 'boolean',
        'show_building_upgrade_messages' => 'boolean',
        'show_kingdom_update_messages'   => 'boolean',
        'show_building_rebuilt_messages' => 'boolean',
        'auto_disenchant'                => 'boolean',
        'disable_attack_type_popover'    => 'boolean',
        'will_be_deleted'                => 'boolean',
    ];

    public function character() {
        return $this->hasOne(Character::class);
    }

    public function messages() {
        return $this->hasMany(Message::class);
    }

    protected static function newFactory() {
        return UserFactory::new();
    }
}
