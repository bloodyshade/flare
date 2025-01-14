<?php

namespace App\Flare\Handlers;

use App\Flare\Builders\Character\AttackDetails\CharacterAttackBuilder;
use App\Flare\Builders\CharacterInformationBuilder;
use App\Flare\Handlers\AttackHandlers\CanHitHandler;
use App\Flare\Handlers\AttackHandlers\CastAndAttackHandler;
use App\Flare\Handlers\AttackHandlers\CastHandler;
use App\Flare\Handlers\AttackHandlers\EntrancingChanceHandler;
use App\Flare\Handlers\AttackHandlers\ItemHandler;
use App\Game\Adventures\Traits\CreateBattleMessages;

class WeaponAndMagicAttackBase {

    use CreateBattleMessages;

    private $characterAttackBuilder;

    private $entrancingChanceHandler;

    private $attackExtraActionHandler;

    private $castHandler;

    private $itemHandler;

    private $canHitHandler;

    private $characterHealth;

    private $monsterHealth;

    private $dmgReduction = 0.0;

    private $battleLogs = [];

    public function __construct(
        CharacterAttackBuilder $characterAttackBuilder,
        EntrancingChanceHandler $entrancingChanceHandler,
        AttackExtraActionHandler $attackExtraActionHandler,
        CastHandler $castHandler,
        ItemHandler $itemHandler,
        CanHitHandler $canHitHandler,
    ) {
        $this->characterAttackBuilder    = $characterAttackBuilder;
        $this->entrancingChanceHandler   = $entrancingChanceHandler;
        $this->attackExtraActionHandler  = $attackExtraActionHandler;
        $this->castHandler               = $castHandler;
        $this->itemHandler               = $itemHandler;
        $this->canHitHandler             = $canHitHandler;
    }

    public function setCharacterHealth(int $characterHealth): WeaponAndMagicAttackBase {
        $this->characterHealth = $characterHealth;

        return $this;
    }

    public function setMonsterHealth(int $monsterHealth): WeaponAndMagicAttackBase {
        $this->monsterHealth = $monsterHealth;

        return $this;
    }

    public function setDmgReduction(float $reduction): WeaponAndMagicAttackBase {
        $this->dmgReduction = $reduction;

        $this->castHandler  = $this->castHandler->setDmgReduction($reduction);

        return $this;
    }

    public function getCharacterHealth(): int {
        return $this->characterHealth;
    }

    public function getMonsterHealth(): int {
        return $this->monsterHealth;
    }

    public function getBattleMessages(): array {
        return $this->battleLogs;
    }

    public function resetLogs() {
        $this->battleLogs = [];
    }

    public function castAndThenAttack($attacker, $defender, string $attackType) {

        $this->characterAttackBuilder = $this->characterAttackBuilder->setCharacter($attacker);
        $characterInfo                = $this->characterAttackBuilder->getInformationBuilder()->setCharacter($attacker);
        $voided                       = $this->isAttackVoided($attackType);
        $totalSpellDamage             = $characterInfo->getTotalSpellDamage($voided);
        $totalHealing                 = $characterInfo->buildHealFor($voided);

        $totalWeaponDamage            = $this->characterAttackBuilder->getPositionalWeaponDamage('left-hand', $voided);

        if ($this->attackExtraActionHandler->canAutoAttack($characterInfo)) {
            $message = 'You dance along in the shadows, the enemy doesn\'t see you. Strike now!';

            $this->battleLogs = $this->addMessage($message, 'action-fired', $this->battleLogs);

            $this->doCastAttack($characterInfo, $defender, $voided);
            $this->doWeaponAttack($characterInfo, $totalWeaponDamage);

        } else if ($this->entrancingChanceHandler->entrancedEnemy($attacker, $defender, $voided)) {
            $this->battleLogs = [...$this->battleLogs, ...$this->entrancingChanceHandler->getBattleLogs()];

            $this->doCastAttack($characterInfo, $defender, $voided);
            $this->doWeaponAttack($characterInfo, $totalWeaponDamage);

            $this->entrancingChanceHandler->resetLogs();
        } else {
            $this->castSpells($attacker, $defender, $characterInfo, $totalSpellDamage, $totalHealing, $voided);
            $this->weaponAttack($attacker, $defender, $characterInfo, $totalWeaponDamage, $voided);
            $this->useItems($attacker, $defender, $voided);
        }
    }

    public function attackAndThenCast($attacker, $defender, string $attackType) {

        $this->characterAttackBuilder = $this->characterAttackBuilder->setCharacter($attacker);
        $characterInfo                = $this->characterAttackBuilder->getInformationBuilder()->setCharacter($attacker);
        $voided                       = $this->isAttackVoided($attackType);
        $totalSpellDamage             = $characterInfo->getTotalSpellDamage($voided);
        $totalHealing                 = $characterInfo->buildHealFor($voided);

        $totalWeaponDamage            = $this->characterAttackBuilder->getPositionalWeaponDamage('left-hand', $voided);

        if ($this->attackExtraActionHandler->canAutoAttack($characterInfo)) {
            $message = 'You dance along in the shadows, the enemy doesn\'t see you. Strike now!';

            $this->battleLogs = $this->addMessage($message, 'action-fired', $this->battleLogs);

            $this->doCastAttack($characterInfo, $defender, $voided);
            $this->doWeaponAttack($characterInfo, $totalWeaponDamage);

        } else if ($this->entrancingChanceHandler->entrancedEnemy($attacker, $defender, $voided)) {
            $this->battleLogs = [...$this->battleLogs, ...$this->entrancingChanceHandler->getBattleLogs()];

            $this->doCastAttack($characterInfo, $defender, $voided);
            $this->doWeaponAttack($characterInfo, $totalWeaponDamage);

            $this->entrancingChanceHandler->resetLogs();
        } else {
            $this->castSpells($attacker, $defender, $characterInfo, $totalSpellDamage, $totalHealing, $voided);
            $this->weaponAttack($attacker, $defender, $characterInfo, $totalWeaponDamage, $voided);
            $this->useItems($attacker, $defender, $voided);
        }

        $this->weaponAttack($attacker, $defender, $characterInfo, $totalWeaponDamage, $voided);
        $this->castSpells($attacker, $defender, $characterInfo, $totalSpellDamage, $totalHealing, $voided);
        $this->useItems($attacker, $defender, $voided);
    }

    public function weaponAttack($attacker, $defender, CharacterInformationBuilder $characterInformationBuilder, int $totalWeaponDamage, bool $voided) {
        $canHit = $this->canHitHandler->canHit($attacker, $defender, $voided);

        if ($canHit) {
            if (!$this->isBlocked($defender, $totalWeaponDamage)) {
                $this->doWeaponAttack($characterInformationBuilder, $totalWeaponDamage);
            } else {
                $message          = $defender->name . ' Blocked your attack!';
                $this->battleLogs = $this->addMessage($message, 'enemy-action-fired', $this->battleLogs);
            }
        } else {
            $message          = 'You missed with your weapon(s)!';
            $this->battleLogs = $this->addMessage($message, 'enemy-action-fired', $this->battleLogs);
        }
    }

    public function castSpells($attacker, $defender, CharacterInformationBuilder $characterInfo, int $totalSpellDamage, int $totalHealing, bool $voided) {
        if ($totalSpellDamage > 0) {
            $canHit        = $this->canHitHandler->canCast($attacker, $defender, $voided);

            if ($canHit) {
                if (!$this->isBlocked($defender, $totalSpellDamage)) {
                    $this->doCastAttack($characterInfo, $defender, $voided);
                } else {
                    $message          = $defender->name . ' Blocked your damaging spell!';
                    $this->battleLogs = $this->addMessage($message, 'enemy-action-fired', $this->battleLogs);
                }
            } else {
                $message          = 'Your damage spell missed!';
                $this->battleLogs = $this->addMessage($message, 'enemy-action-fired', $this->battleLogs);
            }
        } else if ($totalHealing > 0) {
            $this->doHealing($characterInfo, $voided);
        }

        $this->entrancingChanceHandler->resetLogs();
    }

    protected function doHealing(CharacterInformationBuilder $characterInfo, bool $voided = false) {
        $this->characterHealth = $this->castHandler->setMonsterHealth($this->monsterHealth)
            ->fireOffHealingSpells($characterInfo, $this->characterHealth, $voided);

        $this->monsterHealth   = $this->castHandler->getMonsterHealth();

        $logs = $this->castHandler->getBattleMessages();

        $this->battleLogs = [...$this->battleLogs, ...$logs];

        $this->castHandler->resetLogs();
    }

    protected function doCastAttack(CharacterInformationBuilder $characterInfo, $defender, bool $voided = false) {
        $this->castHandler->setMonsterHealth($this->monsterHealth)
            ->setCharacterHealth($this->characterHealth)
            ->castDamageSpells($characterInfo, $defender, $voided);

        $this->monsterHealth = $this->castHandler->getMonsterHealth();
        $this->characterHealth = $this->castHandler->getCharacterHealth();

        $logs = $this->castHandler->getBattleMessages();

        $this->battleLogs = [...$this->battleLogs, ...$logs];

        $this->castHandler->resetLogs();
    }

    protected function doWeaponAttack(CharacterInformationBuilder $characterInformationBuilder, int $damage) {
        $this->monsterHealth = $this->attackExtraActionHandler->positionalWeaponAttack($characterInformationBuilder, $this->monsterHealth, $damage);

        $logs = $this->attackExtraActionHandler->getMessages();

        $this->battleLogs = [...$this->battleLogs, ...$logs];

        $this->attackExtraActionHandler->resetMessages();
    }

    protected function isBlocked($defender, $damage): bool {
        return $damage < $defender->ac;
    }

    protected function isAttackVoided(string $attackType): bool {
        return str_contains($attackType, 'voided');
    }

    protected function useItems($attacker, $defender, bool $voided = false) {
        $itemHandler = $this->itemHandler->setCharacterHealth($this->characterHealth)
            ->setMonsterHealth($this->monsterHealth);

        $itemHandler->useItems($attacker, $defender, $voided);

        $this->characterHealth = $itemHandler->getCharacterHealth();
        $this->monsterHealth   = $itemHandler->getMonsterHealth();
        $this->battleLogs      = [...$this->battleLogs, ...$itemHandler->getBattleMessages()];

        $itemHandler->resetLogs();
    }
}
