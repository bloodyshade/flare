import CanEntranceEnemy from "../attack/attack-types/enchantments/can-entrance-enemy";
import CanHitCheck from "../attack/attack-types/can-hit-check";
import AttackType from "../attack/attack-type";
import UseItems from "../attack/attack-types/use-items";
import {random} from "lodash";

export default class MonsterAttack {

  constructor(attacker, defender, currentCharacterHealth, currentMonsterHealth) {
    this.attacker               = attacker;
    this.defender               = defender;
    this.currentCharacterHealth = currentCharacterHealth;
    this.currentMonsterHealth   = currentMonsterHealth;
    this.battleMessages         = [];
  }

  doAttack(previousAttackType, isCharacterVoided, isMonsterVoided) {
    const monster = this.attacker.getMonster();
    let damage    = this.attacker.attack();

    if (this.entrancesEnemy(monster, this.defender, isCharacterVoided, isMonsterVoided)) {
      if (this.canDoCritical(monster)) {
        this.addMessage(monster.name + ' grows enraged and lashes out with all fury! (Critical Strike!)')

        damage = damage * 2;
      }

      this.currentCharacterHealth = this.currentCharacterHealth - damage;

      this.addActionMessage(monster.name + ' hits for: ' + this.formatNumber(damage));

      this.useItems(monster, isCharacterVoided, isMonsterVoided);

      this.fireOffHealing(monster);

      return this.setState()
    } else {
      if (this.canHit(monster, this.defender)) {

        if (this.isBlocked(previousAttackType, this.defender, damage, isCharacterVoided)) {
          this.addMessage('The enemy\'s attack was blocked!');

          this.useItems(monster, isCharacterVoided, isMonsterVoided, previousAttackType);

          this.fireOffHealing(monster);

          return this.setState()
        }

        if (this.canDoCritical(monster)) {
          this.addMessage(monster.name + ' grows enraged and lashes out with all fury! (Critical Strike!)')

          damage = damage * 2;
        }

        this.currentCharacterHealth = this.currentCharacterHealth - damage;

        this.addActionMessage(monster.name + ' hits for: ' + this.formatNumber(damage));

        this.useItems(monster, isCharacterVoided, isMonsterVoided, previousAttackType);

        this.fireOffHealing(monster);

        return this.setState()

      } else {
        this.addActionMessage(monster.name + ' missed!');

        this.useItems(monster, isCharacterVoided, isMonsterVoided, previousAttackType);

        this.fireOffHealing(monster);

        return this.setState();
      }
    }
  }

  setState() {
    return {
      characterCurrentHealth: this.currentCharacterHealth,
      monsterCurrentHealth: this.currentMonsterHealth,
      battleMessages: this.battleMessages,
    }
  }

  entrancesEnemy(attacker, defender, isCharacterVoided, isMonsterVoided) {

    if (isMonsterVoided) {
      return false;
    }

    const canEntrance = new CanEntranceEnemy();

    if (canEntrance.monsterCanEntrance(attacker, defender, isCharacterVoided)) {
      this.battleMessages = [...this.battleMessages, ...canEntrance.getBattleMessages()]

      return true;
    }

    this.battleMessages = [...this.battleMessages, ...canEntrance.getBattleMessages()]

    return false;
  }

  canHit(attacker, defender) {
    const canHit = new CanHitCheck()

    if (canHit.canMonsterHit(attacker, defender, this.battleMessages)) {
      return true;
    }

    return false;
  }

  isBlocked(attackType, defender, damage, isCharacterVoided) {
    if (AttackType.DEFEND === attackType || AttackType.VOIDED_DEFEND === attackType) {
      const defenderAC = defender.attack_types[attackType].defence;


      if (damage < defenderAC) {
        return true
      }

      const chanceToBlock = defenderAC / damage;

      const dc = 100 - 100 * chanceToBlock

      return random(1, 100) > dc;
    }

    if (isCharacterVoided) {
      return damage < defender.voided_ac;
    }

    return damage < defender.ac;
  }

  useItems(attacker, isCharacterVoided, isMonsterVoided, previousAttackType) {

    if (!isMonsterVoided) {
      const useItems = new UseItems(this.defender, this.currentMonsterHealth, this.currentCharacterHealth);

      useItems.useArtifacts(attacker, this.defender, 'monster');

      this.battleMessages = [...this.battleMessages, ...useItems.getBattleMessage()]

      this.currentCharacterHealth = useItems.getCharacterCurrentHealth();

      this.fireOffAffixes(attacker);
    }

    this.fireOffSpells(attacker, this.defender, isCharacterVoided, previousAttackType);
  }

  fireOffAffixes(attacker) {
    if (attacker.max_affix_damage > 0) {
      const defenderReduction = this.defender.affix_damage_reduction;
      let damage              = random(1, attacker.max_affix_damage);

      if (defenderReduction > 0) {
        damage = (damage - (damage * defenderReduction)).toFixed(2);

        this.addMessage('Your rings negate some of the enemy\'s enchantment damage.');
      }

      if (damage <= 0.0) {
        return;
      }

      this.currentCharacterHealth = this.currentCharacterHealth - damage;

      this.addActionMessage(attacker.name + '\'s enchantments glow, lashing out for: ' + this.formatNumber(damage));
    }
  }

  fireOffSpells(attacker, defender, isCharacterVoided, previousAttackType) {
    if (!this.canCastSpells(attacker, defender, isCharacterVoided)) {
      this.addActionMessage(attacker.name + '\'s Spells fizzle and fail to fire.');

      return;
    }

    if (attacker.spell_damage > 0) {
      const evasionChance = this.defender.spell_evasion;
      const dc            = Math.ceil(100 - (100 * evasionChance));
      const roll          = random(1, 100);

      if (evasionChance >= 1.0) {
        this.addHealingMessage('You evade the enemy\'s spells!');

        return;
      }

      if (roll > dc) {
        this.addHealingMessage('You evade the enemy\'s spells!');
      } else {
        if (this.isBlocked(previousAttackType, defender, damage, isCharacterVoided)) {
          this.addHealingMessage('You managed to block the enemy\'s spells with your armour!');

          return;
        }

        let damage = attacker.spell_damage;

        if (this.canDoCritical(attacker)) {
          this.addMessage(attacker.name + ' With a fury of hatred their spells fly viciously at you! (Critical Strike!)')

          damage = damage * 2;
        }

        this.currentCharacterHealth = this.currentCharacterHealth - damage

        this.addActionMessage(attacker.name + '\'s spells burst toward you doing: ' + this.formatNumber(damage));

        return;
      }
    }
  }

  canCastSpells(attacker, defender, isCharacterVoided) {
    const canHit = new CanHitCheck()

    if (canHit.canCast(attacker, defender, isCharacterVoided)) {
      return true;
    }

    return false;
  }

  fireOffHealing(attacker) {
    if (attacker.max_healing > 0) {
      const defenderHealingReduction = this.defender.healing_reduction;
      let healFor                    = Math.ceil(attacker.dur * attacker.max_healing);

      if (healFor < 0) {
        return;
      }

      if (this.canDoCritical(attacker)) {
        this.addMessage(attacker.name + ' Glows with renewed life! (Critical Healing!)')

        healFor = healFor * 2;
      }

      if (defenderHealingReduction > 0) {
        healFor = healFor - healFor * defenderHealingReduction;
        this.addMessage('Your rings negate some of the enemy\'s healing power.');
      }

      if (healFor > 1) {
        this.currentMonsterHealth = this.currentMonsterHealth + healFor;
        this.addHealingMessage(attacker.name + '\'s healing spells wash over them for: ' + this.formatNumber(healFor.toFixed(0)));
      } else {
        this.addMessage('Your rings negate all of the enemy\'s healing power.');
      }
    }
  }

  addMessage(message) {
    this.battleMessages.push({message: message, class: 'info-damage'});
  }

  addHealingMessage(message) {
    this.battleMessages.push({message: message, class: 'action-fired'});
  }

  addActionMessage(message) {
    this.battleMessages.push({message: message, class: 'enemy-action-fired'});
  }

  formatNumber(number) {
    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
  }

  canDoCritical(attacker) {
    const dc = 100 - 100 * attacker.criticality;

    return random(1, 100) > dc;
  }

}
