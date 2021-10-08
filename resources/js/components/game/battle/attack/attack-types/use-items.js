import {random} from 'lodash';
import DamageAffixes from "./enchantments/damage-affixes";
import LifeStealingAffixes from "./enchantments/life-stealing-affixes";

export default class UseItems {

  constructor(defender, monsterCurrentHealth, characterCurrentHealth) {
    this.monsterCurrentHealth   = monsterCurrentHealth;
    this.characterCurrentHealth = characterCurrentHealth;
    this.defender               = defender;
    this.battleMessages         = [];
  }

  useItems(attackData, attackerClass) {
    if (attackerClass === 'Vampire') {
      this.lifeStealingAffixes(attackData, false);
      this.lifeStealingAffixes(attackData, true);
    }

    const damageAffixes = new DamageAffixes(this.characterCurrentHealth, this.monsterCurrentHealth);

    damageAffixes.fireDamageAffixes(attackData.affixes, this.defender);

    this.characterCurrentHealth = damageAffixes.getCharacterHealth();
    this.monsterCurrentHealth          = damageAffixes.getMonsterHealth();

    this.battleMessages = [...this.battleMessages, damageAffixes.getBattleMessages()];

    this.useArtifacts(attackData, this.defender, 'player');
    this.ringDamage(attackData, this.defender, 'player');
  }

  getBattleMessage() {
    return this.battleMessages;
  }

  getCharacterCurrentHealth() {
    return this.characterCurrentHealth;
  }

  getMonsterCurrentHealth() {
    return this.monsterCurrentHealth;
  }

  lifeStealingAffixes(attackData, stacking) {
    const lifeStealingAffixes = new LifeStealingAffixes(this.characterCurrentHealth, this.monsterCurrentHealth)

    lifeStealingAffixes.affixesLifeStealing(attackData.affixes, this.defender, stacking);

    this.characterCurrentHealth = lifeStealingAffixes.getCharacterHealth();
    this.monsterCurrentHealth   = lifeStealingAffixes.getMonsterHealth();

    this.battleMessages = [...this.battleMessages, lifeStealingAffixes.getBattleMessages()];
  }

  useArtifacts(attacker, defender, type) {
    if (type == 'player') {
      if (attacker.artifact_damage !== 0) {
        this.addMessage('Your artifacts glow before the enemy!');

        this.artifactDamage(attacker, defender, type);

      }
    } else {
      if (attacker.artifact_damage !== 0) {
        this.addMessage('The enemies artifacts glow brightly!');

        this.artifactDamage(attacker, defender, type);
      }
    }
  }

  artifactDamage(attacker, defender, type) {

    if (type === 'player') {

      const dc        = 100 - (100 * defender.artifact_annulment);
      let totalDamage = attacker.artifact_damage;

      if (dc <= 0 || random(1, 100) > dc) {
        this.addMessage(attacker.name + '\'s Artifacts are annulled!');

        return;
      }

      this.monsterCurrentHealth = this.monsterCurrentHealth - totalDamage;

      this.addActionMessage(attacker.name + ' artifacts hit for: ' + this.formatNumber(totalDamage));
    }

    if (type === 'monster') {
      const dc        = 100 - (100 * defender.artifact_annulment);
      let totalDamage = attacker.artifact_damage;

      if (dc <= 0 || random(1, 100) > dc) {
        this.addMessage(attacker.name + '\'s Artifacts are annulled!');

        return;
      }

      this.characterCurrentHealth = this.characterCurrentHealth - totalDamage;

      this.addEnemyActionMessage(attacker.name + ' artifacts hit for: ' + this.formatNumber(totalDamage));
    }
  }

  ringDamage(attacker, defender, type) {
    if (type === 'player' && attacker.ring_damage > 0) {
      this.monsterCurrentHealth = this.monsterCurrentHealth - attacker.ring_damage;

      this.addActionMessage('Your rings hit for: ' + this.formatNumber(attacker.ring_damage));
    }
  }

  formatNumber(number) {
    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
  }

  addMessage(message) {
    this.battleMessages.push({message: message, class: 'info-damage'});
  }

  addActionMessage(message) {
    this.battleMessages.push({message: message, class: 'action-fired'});
  }

  addEnemyActionMessage(message) {
    this.battleMessages.push({message: message, class: 'enemy-action-fired'});
  }
}