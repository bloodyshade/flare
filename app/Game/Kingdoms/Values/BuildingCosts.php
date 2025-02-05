<?php


namespace App\Game\Kingdoms\Values;


class BuildingCosts
{

    const KEEP             = 'Keep';
    const FARM             = 'Farm';
    const LUMBER_MILL      = 'Lumber Mill';
    const STONE_QUARRY     = 'Stone Quarry';
    const CLAY_PIT         = 'Clay Pit';
    const IRON_MINE        = 'Iron Mine';
    const WALLS            = 'Walls';
    const BARRACKS         = 'Barracks';
    const CHURCH           = 'Church';
    const SETTLERS_HALL    = 'Settler\'s Hall';
    const GOBLIN_COIN_BANK = 'Goblin Coin Bank';
    const CANNONEER_SHOP   = 'Cannoneer Shop';
    const CALVARY          = 'Calvary Training Grounds';

    protected static $values = [
        self::KEEP             => self::KEEP,
        self::FARM             => self::FARM,
        self::LUMBER_MILL      => self::LUMBER_MILL,
        self::STONE_QUARRY     => self::STONE_QUARRY,
        self::CLAY_PIT         => self::CLAY_PIT,
        self::IRON_MINE        => self::IRON_MINE,
        self::WALLS            => self::WALLS,
        self::BARRACKS         => self::BARRACKS,
        self::CHURCH           => self::CHURCH,
        self::SETTLERS_HALL    => self::SETTLERS_HALL,
        self::GOBLIN_COIN_BANK => self::GOBLIN_COIN_BANK,
        self::CANNONEER_SHOP   => self::CANNONEER_SHOP,
        self::CALVARY          => self::CALVARY,
    ];

    private $name;

    public function __construct(string $name) {
        if (!in_array($name, self::$values)) {
            throw new \Exception($name . ' does not exist.');
        }

        $this->name = $name;
    }

    public function fetchCost(): int {

        switch($this->name) {
            case self::KEEP:
                return 10000;
            case self::FARM:
                return 500;
            case self::LUMBER_MILL:
                return 250;
            case self::STONE_QUARRY:
                return 750;
            case self::CLAY_PIT:
                return 150;
            case self::IRON_MINE:
                return 1000;
            case self::WALLS:
                return 5000;
            case self::BARRACKS:
                return 2500;
            case self::CHURCH:
                return 3500;
            case self::SETTLERS_HALL:
                return 50000;
            case self::GOBLIN_COIN_BANK:
                return 100000;
            case self::CANNONEER_SHOP:
                return 150000;
            case self::CALVARY:
                return 15000;
            default:
                return 0;
        }
    }

    public function isKeep(): bool {
        return $this->name === self::KEEP;
    }

    public function isFarm(): bool {
        return $this->name === self::FARM;
    }

    public function isLumberMill(): bool {
        return $this->name === self::LUMBER_MILL;
    }

    public function isStoneQuarry(): bool {
        return $this->name === self::STONE_QUARRY;
    }

    public function isClayPit(): bool {
        return $this->name === self::CLAY_PIT;
    }

    public function isIronMine(): bool {
        return $this->name === self::IRON_MINE;
    }

    public function isWalls(): bool {
        return $this->name === self::WALLS;
    }

    public function isChurch(): bool {
        return $this->name === self::CHURCH;
    }

    public function isSettlersHall(): bool {
        return $this->name === self::SETTLERS_HALL;
    }

    public function isGoblinCoinBank(): bool {
        return $this->name === self::GOBLIN_COIN_BANK;
    }

    public function isCannoneerShop(): bool {
        return $this->name === self::CANNONEER_SHOP;
    }

    public function isCalvary(): bool {
        return $this->value === self::CALVARY;
    }

    public function isPerson(): bool {
        return $this->name === self::PERSON;
    }
}
