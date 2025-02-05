<?php

namespace App\Flare\Handlers;


use App\Flare\Builders\Character\AttackDetails\CharacterAffixInformation;
use App\Flare\Builders\CharacterInformationBuilder;
use App\Flare\Handlers\AttackHandlers\CanHitHandler;
use App\Flare\Handlers\AttackHandlers\EntrancingChanceHandler;
use App\Flare\Handlers\AttackHandlers\ItemHandler;
use App\Flare\Values\AttackTypeValue;
use App\Game\Adventures\Traits\CreateBattleMessages;

class MonsterAttackHandler {

    use CreateBattleMessages;

    private $characterInformationBuilder;

    private $characterAffixInformation;

    private $entrancingChanceHandler;

    private $itemHandler;

    private $canHitHandler;

    private $characterHealth;

    private $monsterHealth;

    private $battleLogs = [];

    private $isMonsterVoided = false;

    public function __construct(
        CharacterInformationBuilder $characterInformationBuilder,
        CharacterAffixInformation   $characterAffixInformation,
        EntrancingChanceHandler     $entrancingChanceHandler,
        ItemHandler                 $itemHandler,
        CanHitHandler               $canHitHandler,
    ) {
        $this->characterInformationBuilder = $characterInformationBuilder;
        $this->characterAffixInformation    = $characterAffixInformation;
        $this->entrancingChanceHandler     = $entrancingChanceHandler;
        $this->canHitHandler               = $canHitHandler;
        $this->itemHandler                 = $itemHandler;
    }

    public function setHealth(int $monsterHealth, int $characterHealth): MonsterAttackHandler {
        $this->monsterHealth   = $monsterHealth;
        $this->characterHealth = $characterHealth;

        return $this;
    }

    public function setMonsterVoided(bool $isMonsterVoided): MonsterAttackHandler {
        $this->isMonsterVoided = $isMonsterVoided;

        return $this;
    }

    public function getMonsterHealth(): int {
        return $this->monsterHealth;
    }

    public function getCharacterHealth(): int {
        return $this->characterHealth;
    }

    public function getBattleLogs(): array {
        return $this->battleLogs;
    }

    public function resetLogs() {
        $this->battleLogs = [];
    }

    public function doAttack($attacker, $defender, string $attackType, bool $isDefenderVoided = false) {

        $monsterAttack = explode('-', $attacker->attack_range);
        $monsterAttack = rand($monsterAttack[0], $monsterAttack[1]);

        if ($this->entrancingChanceHandler->entrancedEnemy($attacker, $defender, $isDefenderVoided, $this->isMonsterVoided)) {
            $this->battleLogs = $this->entrancingChanceHandler->getBattleLogs();

            $this->entrancingChanceHandler->resetLogs();

            $this->characterHealth -= $monsterAttack;

            $message = $attacker->name . ' hits for: ' . number_format($monsterAttack);
            $this->battleLogs = $this->addMessage($message, 'enemy-action-fired');

            $this->useItems($attacker, $defender, $attackType, $isDefenderVoided);

            return;
        } else {
            $this->battleLogs = $this->entrancingChanceHandler->getBattleLogs();
        }

        if ($this->canHitHandler->canHit($attacker, $defender, $isDefenderVoided)) {
            if ($this->blockedAttack($monsterAttack, $defender, $attackType, $isDefenderVoided)) {
                $message          = 'You blocked the enemy\'s attack!';
                $this->battleLogs = $this->addMessage($message, 'action-fired', $this->battleLogs);

                $this->useItems($attacker, $defender, $attackType, $isDefenderVoided);

                return;
            }

            $this->characterHealth -= $monsterAttack;

            $message = $attacker->name . ' hits for: ' . number_format($monsterAttack);

            $this->battleLogs = $this->addMessage($message, 'enemy-action-fired');

            $this->useItems($attacker, $defender, $attackType, $isDefenderVoided);

             return;
        }

        $message          = $attacker->name . ' Missed!';
        $this->battleLogs = $this->addMessage($message, 'info-damage', $this->battleLogs);

        $this->useItems($attacker, $defender, $attackType, $isDefenderVoided);

        $this->defenderAttemptToHeal($defender, $attacker, $isDefenderVoided);

    }

    protected function defenderAttemptToHeal($defender, $attacker, bool $isDefenderVoided = false) {
        if ($this->characterHealth <= 0) {
            $this->attemptToResurrect($defender, $attacker, $isDefenderVoided);
        } else if (!$isDefenderVoided){
            $this->useLifeStealingAffixes($attacker, $defender);
        }
    }

    private function attemptToResurrect($defender, $attacker, bool $isDefenderVoided = false) {
        $resChance = $this->characterInformationBuilder->setCharacter($defender)->fetchResurrectionChance();

        $dc = 100 - 100 * $resChance;

        if (rand(1, 100) > $dc) {
            $this->characterHealth = 1;

            $message = 'You are pulled back from the void and given one health!';
            $this->battleLogs = $this->addMessage($message, 'action-fired', $this->battleLogs);

            if (!$isDefenderVoided) {
                $this->useLifeStealingAffixes($attacker, $defender);
            }
        }
    }

    private function useLifeStealingAffixes($attacker, $defender) {
        $affixes = $this->characterAffixInformation->setCharacter($defender);
        $handler = $this->itemHandler->setCharacterHealth($this->characterHealth)->setMonsterHealth($this->monsterHealth);
        $info    = $this->characterInformationBuilder->setCharacter($defender);

        $canResist  = $info->canAffixesBeResisted();
        $damage     = $affixes->findLifeStealingAffixes(true);

        if ($damage > $this->characterHealth) {
            $damage = $this->characterHealth;
        }

        $handler->useLifeStealingAffixes($attacker, $damage, $canResist);

        $this->monsterHealth   = $handler->getMonsterHealth();
        $this->characterHealth = $handler->getCharacterHealth();

        $this->battleLogs = [...$this->battleLogs, ...$handler->getBattleMessages()];

        $this->itemHandler->resetLogs();
    }

    protected function useItems($attacker, $defender, string $attackType, bool $isDefenderVoided = false) {

        if (!$this->isMonsterVoided) {
            $itemHandler = $this->itemHandler->setCharacterHealth($this->characterHealth)
                                             ->setMonsterHealth($this->monsterHealth);

            $itemHandler->useArtifacts($attacker, $defender);

            $this->characterHealth = $itemHandler->getCharacterHealth();
            $this->monsterHealth = $itemHandler->getMonsterHealth();

            $this->useAffixes($attacker, $defender);
        }

        if ($this->canHitHandler->canCast($attacker, $defender, $isDefenderVoided)) {
            $itemHandler        = $this->itemHandler->setCharacterHealth($this->characterHealth);
            $defenderReduction  = $this->characterInformationBuilder
                                       ->setCharacter($defender)
                                       ->getTotalDeduction('spell_evasion');

            $monsterSpellDamage = rand(1, $attacker->max_spell_damage);


            if ($this->blockedAttack($monsterSpellDamage, $defender, $attackType, $isDefenderVoided)) {
                $message = 'You managed to block the enemy\'s spells with your armour!';
                $this->battleLogs = $this->addMessage($message, 'action-fired', $this->battleLogs);
            } else {
                if ($defenderReduction > 1.0) {
                    $message = 'You evaded the enemy\'s spells!';
                    $this->battleLogs = $this->addMessage($message, 'action-fired', $this->battleLogs);

                    return;
                } else {
                    $dc   = 100 - 100 * $defenderReduction;
                    $roll = rand(1, 100);

                    if ($roll > $dc) {
                        $message = 'You evaded the enemy\'s spells!';
                        $this->battleLogs = $this->addMessage($message, 'action-fired', $this->battleLogs);

                        return;
                    }
                }

                $itemHandler->castSpell($attacker, $defender, $monsterSpellDamage);

                $this->characterHealth = $itemHandler->getCharacterHealth();

                $this->battleLogs      = [...$this->battleLogs, ...$itemHandler->getBattleMessages()];

                $itemHandler->resetLogs();
            }


        } else {
            $message = 'The enemy fails to cast their damaging spells!';
            $this->battleLogs = $this->addMessage($message, 'info-damage', $this->battleLogs);
        }

        $this->heal($attacker, $defender);
    }

    protected function useAffixes($attacker, $defender) {
        if ($attacker->max_affix_damage > 0) {
            $defenderReduction = $this->characterInformationBuilder
                                      ->setCharacter($defender)
                                      ->getTotalDeduction('affix_damage_reduction');
            $damage            = rand(1, $attacker->max_affix_damage);

            if ($defenderReduction > 0) {
                $message = 'Your rings negate some of the enemy\'s enchantment damage.';
                $this->battleLogs = $this->addMessage($message, 'enemy-action-fired', $this->battleLogs);

                $damage -= ceil($damage * $defenderReduction);
            }

            $message = $attacker->name . '\'s enchantments glow, lashing out for: ' . number_format($damage);
            $this->battleLogs = $this->addMessage($message, 'enemy-action-fired', $this->battleLogs);

            $this->characterHealth -= $damage;
        }
    }

    protected function heal($attacker, $defender) {
        if ($attacker->healing_percentage > 0) {
            $defenderReduction = $this->characterInformationBuilder
                ->setCharacter($defender)
                ->getTotalDeduction('healing_reduction');
            $healing            = $attacker->dur * $attacker->max_healing;

            if ($defenderReduction > 0) {
                $healing -= ceil($healing * $defenderReduction);
            }

            if ($healing >= 1) {
                $message = 'Your rings negate some of the enemy\'s healing power.';
                $this->battleLogs = $this->addMessage($message, 'action-fired', $this->battleLogs);

                $message = $attacker->name . '\'s healing spells wash over them for: ' . number_format($healing);
                $this->battleLogs = $this->addMessage($message, 'action-fired', $this->battleLogs);

                $this->monsterHealth += $healing;
            } else {
                $message = 'Your rings negate all of the enemy\'s healing power.';
                $this->battleLogs = $this->addMessage($message, 'action-fired', $this->battleLogs);
            }
        }
    }

    protected function blockedAttack(int $monsterAttack, $defender, string $attackType, bool $isDefenderVoided = false): bool {
        $info        = $this->characterInformationBuilder->setCharacter($defender);
        $isFighter   = $defender->classType()->isFighter();
        $ac          = $info->buildDefence($isDefenderVoided);
        $defenderStr = $isDefenderVoided ? $defender->str : $info->statMod('str');

        if ($attackType === AttackTypeValue::DEFEND || $attackType === AttackTypeValue::VOIDED_DEFEND) {
            if ($isFighter) {
                $defenderStr += $defenderStr * .15;
            } else {
                $defenderStr += $defenderStr * .05;
            }

            $ac = $ac + $defenderStr;

            if ($monsterAttack < $ac) {
                 return true;
            }

            $chance = $ac / $monsterAttack;
            $dc     = 100 - 100 * $chance;

            return rand(1, 100) > $dc;
        }

        return $monsterAttack < $ac;
    }
}
