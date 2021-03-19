<?php

namespace App\Game\Kingdoms\Handlers;

use App\Flare\Models\KingdomBuilding;
use App\Flare\Models\Kingdom;
use Illuminate\Database\Eloquent\Collection;

class SiegeHandler {

    public function attack(Kingdom $defender, array $siegeUnits) {

        foreach ($siegeUnits as $index => $unitInfo) {
            $unitInfo = $this->primaryAttack($defender, $unitInfo);
            
            if ($unitInfo['amount'] > 0) {
                if ($unitInfo['fall_back'] === 'KingdomBuildings') {
                    $unitInfo = $this->attackKingdomBuildings($defender, $unitInfo);
                } else {
                    $unitInfo = $this->fallBackAttack($defender, $unitInfo);
                }
                
            }

            if ($unitInfo['amount'] > 0) {
                $unitInfo = $this->unitAttack($defender, $unitInfo);
            }

            $siegeUnits[$index] = $unitInfo;
        }

        return $siegeUnits;
    }

    protected function primaryAttack(Kingdom $defender, array $unitInfo): array {
        $primaryTarget = $defender->buildings->where('name', $unitInfo['primary_target'])->first();
        
        if (is_null($primaryTarget)) {
            return $unitInfo;
        }
        
        if ($this->hasKingdomBuildingFallen($primaryTarget)) {
            return $unitInfo;
        }

        return $this->attackTarget($primaryTarget, $unitInfo);
    }

    protected function fallBackAttack(Kingdom $defender, array $unitInfo): array {
        $fallBackTarget = $defender->buildings->where('name', $unitInfo['fall_back'])->first();

        if (is_null($fallBackTarget)) {
            return $unitInfo;
        }
        
        if ($this->hasKingdomBuildingFallen($fallBackTarget)) {
            return $unitInfo;
        }

        return $this->attackTarget($fallBackTarget, $unitInfo);
    }

    protected function attackKingdomBuildings(Kingdom $defender, array $unitInfo): array {
        $buildings = $defender->buildings->where('is_walls', false);

        return $this->attackAllKingdomBuildings($defender, $buildings, $unitInfo);
    }

    protected function unitAttack(Kingdom $defender, array $unitInfo) {
        $totalDefenderAttack  = 0;
        $totalDefenderDefence = 0;
        $totalDefenderTypes   = 0;

        $totalAttack          = $unitInfo['total_attack'];
        $totalDefence         = $unitInfo['total_defence']; 

        foreach ($defender->units as $unit) {
            $totalDefenderAttack  += $unit->amount * $unit->gameUnit->attack;
            $totalDefenderDefence += $unit->amount * $unit->gameUnit->defence;
            $totalDefenderTypes   += 1;
        }

        if ($totalDefenderAttack === 0) {
            return $unitInfo;
        }

        if ($totalAttack > $totalDefenderDefence) {
            $totalDefenderPercentageLost = $this->calculatePerentageLost($totalAttack, $totalDefenderDefence);
            $totalAttackersLost          = $this->calculatePerentageLost($totalAttack, $totalDefenderDefence, true);

            $this->updateDefenderUnits($defender, $totalDefenderPercentageLost);
            $newUnitTotal = $this->getNewUnitTotal($unitInfo['amount'], $totalAttackersLost);

            $unitInfo['amount'] = $newUnitTotal > 0 ? $newUnitTotal : 0;
        } else {
            $totalDefenderPercentageLost = $this->calculatePerentageLost($totalDefenderAttack, $totalDefence, true);
            $totalAttackersLost          = $this->calculatePerentageLost($totalDefenderAttack, $totalDefence);

            $this->updateDefenderUnits($defender, $totalDefenderPercentageLost);
            $newUnitTotal = $this->getNewUnitTotal($unitInfo['amount'], $totalAttackersLost);

            $unitInfo['amount'] = $newUnitTotal > 0 ? $newUnitTotal : 0;
        }
        
        return $unitInfo;
    }

    private function attackTarget(KingdomBuilding $target, array $unitInfo): array {
        $totalAttack = $unitInfo['total_attack'];

        if ($totalAttack > $target->current_defence) {
            $totalPercentageUnitsLost      = $this->calculatePerentageLost($totalAttack, $target->current_defence, true);
            $totalPercentageDurabilityLost = $this->calculatePerentageLost($totalAttack, $target->current_defence);

            $this->updateKingdomBuilding($target, $totalPercentageDurabilityLost);

            $newUnitTotal       = $this->getNewUnitTotal($unitInfo['amount'], $totalPercentageUnitsLost);
            $unitInfo['amount'] = $newUnitTotal > 0 ? $newUnitTotal : 0;
        } else {
            
            $totalPercentageUnitsLost = $this->calculatePerentageLost($totalAttack, $target->current_defence, true);

            $this->updateKingdomBuilding($target, 0.01);
            
            $newUnitTotal       = $this->getNewUnitTotal($unitInfo['amount'], $totalPercentageUnitsLost);
            $unitInfo['amount'] = $newUnitTotal > 0 ? $newUnitTotal : 0;
        }

        return $unitInfo;
    }

    private function attackAllKingdomBuildings(Kingdom $defender, Collection $targets, $unitInfo): array {
        $totalAttack  = $unitInfo['total_attack'];
        $totalDefence = $unitInfo['total_defence'];

        $defenderSiegeUnits        = $this->getDefenderSiegeUnits($defender);
        $defenderSiegeUnitsAttack  = $this->defenderSiegeUnitsAttack($defenderSiegeUnits);
        $defenderKingdomBuildingsDefence  = $this->getKingdomBuildingsTotalDefence($targets);

        if (!$defenderSiegeUnits->isEmpty()) {
            $defenderSiegeUnitsAttack = $this->defenderSiegeUnitsAttack($defenderSiegeUnits);
        }

        if ($totalAttack > $defenderKingdomBuildingsDefence) {
            $totalPercentageDurabilityLost = $this->calculatePerentageLost($totalAttack, $defenderKingdomBuildingsDefence);
            
            $this->updateAllKingdomBuildings($targets, $totalPercentageDurabilityLost);

            if ($defenderSiegeUnitsAttack !== 0) {
                $totalPercentageOfAttackersLost = $this->calculatePerentageLost($defenderSiegeUnitsAttack, $totalDefence);

                $newUnitTotal       = $this->getNewUnitTotal($unitInfo['amount'], $totalPercentageOfAttackersLost);
                $unitInfo['amount'] = $newUnitTotal > 0 ? $newUnitTotal : 0;
            }
        } else {
            if ($defenderSiegeUnitsAttack !== 0) {
                $totalPercentageOfAttackersLost = $this->calculatePerentageLost($defenderSiegeUnitsAttack, $totalDefence);

                $newUnitTotal       = $this->getNewUnitTotal($unitInfo['amount'], $totalPercentageOfAttackersLost);
                $unitInfo['amount'] = $newUnitTotal > 0 ? $newUnitTotal : 0;
            }
        }

        return $unitInfo;
    }

    private function calculatePerentageLost(int $totalAttack, int $totalDefence, bool $flipped = false): float {
        if (!$flipped) {
            return ($totalAttack / $totalDefence);
        }

        return  ($totalDefence / $totalAttack);
    }

    private function updateKingdomBuilding(KingdomBuilding $building, float $durabilityPercentageLost) {
        $durability = ceil($building->current_durability - ($building->current_durability * $durabilityPercentageLost));

        $building->update([
            'current_durability' => $durability < 0 ? 0 : $durability,
        ]);
    }

    private function updateAllKingdomBuildings(Collection $buildings, float $percentageOfDurabilityLost) {
        $buildingsStillStanding = $buildings->where('current_durability', '!=', 0)->all();
        $totalKingdomBuildings         = count($buildingsStillStanding);
        $percentageLost         = ($percentageOfDurabilityLost / $totalKingdomBuildings);

        foreach ($buildingsStillStanding as $building) {
            $newDurability = $building->current_durability - ($building->current_durability * $percentageLost);

            $building->update([
                'current_durability' => $newDurability > 0 ? $newDurability : 0,
            ]);
        }
    }

    private function updateDefenderUnits(Kingdom $defender, float $percentageOfUnitsLost) {
      foreach ($defender->units as $unit) {
          $newAmount = $this->getNewUnitTotal($unit->amount, $percentageOfUnitsLost);

          $unit->update([
              'amount' => $newAmount > 0 ? $newAmount : 0,
          ]);
      }
    }

    private function getNewUnitTotal(int $totalUnits, float $percentageOfUnitsLost) {
        return ceil($totalUnits - ($totalUnits * $percentageOfUnitsLost));
    }

    private function hasKingdomBuildingFallen(KingdomBuilding $building): bool {
        return $building->durabilityPercentageLost > 0;
    }

    private function getDefenderSiegeUnits(Kingdom $defender) {
        return $defender->units()->join('game_units', function($join) {
            $join->on('game_units.id', 'kingdom_units.game_unit_id')
                 ->where('siege_weapon', true)
                 ->where('defender', true);
        })->get();
    }

    private function defenderSiegeUnitsAttack(Collection $siegeUnits): int {
        $totalAttack = 0;

        foreach ($siegeUnits as $siegeUnit) {
            $totalAttack += $siegeUnit->gameUnit->attack * $siegeUnit->amount;
        }

        return $totalAttack;
    }

    private function getKingdomBuildingsTotalDefence(Collection $buildings): int {
        $totalDefence = 0;

        foreach ($buildings as $building) {
            if (!$this->hasKingdomBuildingFallen($building)) {
                $totalDefence += $building->current_defence;
            }
        }

        return $totalDefence;
    }
}