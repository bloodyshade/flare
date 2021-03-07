<?php

namespace App\Game\Maps\Values;

use App\Flare\Models\Character;
use Illuminate\Support\Facades\Storage;

class MapTileValue {

    /**
     * Get the tile color from the current map.
     * 
     * @param Character $character
     * @param int $xPosition
     * @param int $yPosition
     * @return string
     */
    public function getTileColor(Character $character, int $xPosition, int $yPosition): string {
        $contents            = Storage::disk('maps')->get($character->map->gameMap->path);

        $this->imageResource = imagecreatefromstring($contents);

        $rgb                 = imagecolorat($this->imageResource, $xPosition, $yPosition);

        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;
        
        return $r.$g.$b;
    }

    /**
     * Is the current tile a water tile?
     * 
     * @param int $color
     * @return bool
     */
    public function isWaterTile(int $color): bool {
        // These repersent water:
        $invalidColors = [
            115217255, 114217255, 112219255, 112217247, 106222255, 117217251, 115223255
        ];

        return in_array($color, $invalidColors);
    }
}