import {random} from "lodash";

export default class CanEntranceEnemy {

  constructor() {
    this.battleMessages = [];
  }

  canEntranceEnemy(attackType, defender, type) {
    let canEntrance   = false;
    const chance      = attackType.affixes.entrancing_chance;
    defender          = defender.monster;

    if (attackType.affixes.entrancing_chance > 0.0) {
      const cantResist     = attackType.affixes.cant_be_resisted;
      const canBeEntranced = random(1, 100) > (100 - (100 * chance));

      if (cantResist || canBeEntranced) {
        this.addMessage('The enemy is dazed by your enchantments!');

        canEntrance = true;
      } else if (canBeEntranced) {
        const dc = 100 - (100 * defender.affix_resistance);

        if (dc <= 0 || random(0, 100) > dc) {
          this.addMessage('The enemy is resists your entrancing enchantments!');

        } else {
          this.addMessage('The enemy is dazed by your enchantments!');

          canEntrance = true;
        }
      } else {
        this.addMessage('The enemy is resists your entrancing enchantments!');
      }
    }

    return canEntrance;
  }

  monsterCanEntrance(attacker, defender) {
    let canEntrance = false;
    const dc        = this.monsterChanceDC(attacker, defender);
    const chance    = random(1, this.monsterMaxRoll(defender));

    if (dc > chance) {
      this.addMessage('You resist the alluring entrancing enchantments on your enemy!');
    } else {
      this.addMessage(attacker.name + ' has trapped you in a trance like state with their enchantments!');

      canEntrance = true;
    }

    return canEntrance;
  }

  monsterChanceDC(attacker, defender) {
    if (defender.class === 'Heretic' || defender.class === 'Prophet') {
      const baseDc = (defender.focus * 0.05);

      return baseDc - (baseDc * attacker.entrancing_chance);
    }

    return 100 - 100 * attacker.entrancing_chance;
  }

  monsterMaxRoll(defender) {
    if (defender.class === 'Heretic' || defender.class === 'Prophet') {
      return (defender.focus * 0.05);
    }

    return 100;
  }

  addMessage(message) {
    this.battleMessages.push({message: message, class: 'info-damage'});
  }

  getBattleMessages() {
    return this.battleMessages;
  }
}