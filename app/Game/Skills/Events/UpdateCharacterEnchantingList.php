<?php

namespace App\Game\Skills\Events;

use App\Game\Core\Services\CharacterInventoryService;
use Illuminate\Broadcasting\Channel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
Use App\Flare\Models\User;

class UpdateCharacterEnchantingList implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;


    public $availableAffixes;

    public $inventory;

    /**
     * @var User $users
     */
    public $user;

    /**
     * Create a new event instance.
     *
     * @param User $user
     * @param array $items
     */
    public function __construct(User $user, Collection $availableAffixes, array $inventory) {
        $this->user                  = $user;
        $this->availableAffixes      = $availableAffixes;
        $this->inventory             = $inventory;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('update-enchanting-list-' . $this->user->id);
    }
}