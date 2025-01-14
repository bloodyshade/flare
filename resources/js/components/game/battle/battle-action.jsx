import React from 'react';
import {Row, Col} from 'react-bootstrap';
import TimeOutBar from '../timeout/timeout-bar';
import {isEqual} from 'lodash';

export default class BattleAction extends React.Component {

  constructor(props) {
    super(props);

    this.state = {
      character: null,
      monster: 0,
      monsters: null,
      characterMaxHealth: 0,
      characterCurrentHealth: 0,
      monsterMaxHealth: 0,
      monsterCurrentHealth: 0,
      battleMessages: [],
      isLoading: true,
      canAttack: true,
      isDead: false,
      showMessage: false,
      timeRemaining: null,
      disableAttack: false,
      itemsToCraft: null,
      isAdventuring: false,
    }

    this.attackStats = Echo.private('update-character-attack-' + this.props.userId);
    this.adventureLogs = Echo.private('update-adventure-logs-' + this.props.userId);
    this.canAttack = Echo.private('show-timeout-bar-' + this.props.userId);
    this.updateCharacterStatus = Echo.private('update-character-status-' + this.props.userId);
    this.updateMonstersList    = Echo.private('update-monsters-list-' + this.props.userId);
  }

  componentDidMount() {
    this.setState({
      character: this.props.character,
      monsters: this.props.monsters,
      characterMaxHealth: this.props.character.health,
      characterCurrentHealth: this.props.character.health,
      isLoading: false,
      timeRemaining: this.props.character.can_attack_again_at,
      isDead: this.props.character.is_dead,
    });

    this.attackStats.listen('Game.Core.Events.UpdateAttackStats', (event) => {
      this.setState({character: event.character});
    });

    this.canAttack.listen('Game.Core.Events.ShowTimeOutEvent', (event) => {
      this.setState({canAttack: event.canAttack});
    });

    this.updateCharacterStatus.listen('Game.Battle.Events.UpdateCharacterStatus', (event) => {
      this.setState({
        canAttack: event.data.can_attack,
        showMessage: event.data.show_message,
        isAdventuring: !event.data.can_adventure,
        isDead: event.data.is_dead,
      }, () => {
        this.props.isCharacterDead(event.data.is_dead);
        this.props.isCharacterAdventuring(!event.data.can_adventure);
        this.props.canAttack(event.data.can_attack);

        if (!event.is_dead) {
          this.props.setMonster(this.state.monster !== 0  ? this.state.monster : null);
        }
      });
    });

    this.updateMonstersList.listen('Game.Maps.Events.UpdateMonsterList', (event) => {
      this.setState({
        monsters: event.monsters,
      }, () => {
        this.props.updateResetBattleAction(true);
      });
    });

    this.adventureLogs.listen('Game.Adventures.Events.UpdateAdventureLogsBroadcastEvent', (event) => {
      this.setState({
        isAdventuring: event.isAdventuring,
      }, () => {
        this.props.isCharacterAdventuring(event.isAdventuring)
      });
    });
  }

  componentDidUpdate(prevProps, prevState) {
    if (this.state.isDead !== prevState.isDead) {
      let character = _.cloneDeep(this.state.character);
      character.is_dead = this.props.isDead;

      this.setState({
        character: character,
      });
    }

    if (!isEqual(this.props.monsters, prevProps.monsters)) {
      this.setState({
        monsters: this.props.monsters,
      })
    }

    if (this.props.shouldReset) {
      this.setState({
        monster: 0,
      }, () => {
        this.props.updateResetBattleAction();
      })
    }
  }

  updateActions(event) {
    const monster = this.state.monsters.filter(monster => monster.id === parseInt(event.target.value))[0];

    this.setState({
      monster: monster,
      battleMessages: [],
    }, () => {
      this.props.setMonster(this.state.monster);
    });
  }

  fightAgain() {
    this.setState({
      battleMessages: [],
    }, () => {
      this.props.setMonster(this.state.monster);
    });
  }

  monsterOptions() {
    return this.state.monsters.map((monster) => {
      return <option value={monster.id} key={monster.id}>{monster.name}</option>
    });
  }

  monsterSelectDisabled() {
    if (this.state.isDead) {
      return true;
    }

    if (this.state.isAdventuring) {
      return true;
    }

    if (this.props.attackAutomationIsRunning) {
      return true;
    }

    return false;
  }

  againDisabled() {
    if (parseInt(this.state.monster) === 0) {
      return true;
    }

    if (this.state.character.is_dead) {
      return true;
    }

    if (this.state.isAdventuring) {
      return true;
    }

    if (!this.state.canAttack) {
      return true;
    }

    if (this.props.attackAutomationIsRunning) {
      return true;
    }

    return false;
  }

  renderActions() {
    let monsterId = 0;

    if (typeof this.state.monster !== 'undefined') {
      if (this.state.monster.hasOwnProperty('id')) {
        monsterId = this.state.monster.id
      }
    }

    return (
      <>
        {this.state.isAdventuring
          ?
          <div className="alert alert-warning" role="alert">
            You are currently adventuring and cannot fight any monsters or craft/enchant or manage kingdoms.
          </div>
          :
          null
        }
        <Row>
          <Col xs={12} sm={12} md={12} lg={6} xl={6}>
            <select className="form-control monster-select" id="monsters" name="monsters"
                    value={monsterId}
                    onChange={this.updateActions.bind(this)}
                    disabled={this.monsterSelectDisabled()}>
              <option value="" key="0">Please select a monster</option>
              {this.monsterOptions()}
            </select>
          </Col>
          <Col xs={3} sm={3} md={3} lg={3} xl={1}>
            <button className="btn btn-primary"
                    type="button"
                    disabled={this.againDisabled()}
                    onClick={this.fightAgain.bind(this)}
            >
              Again!
            </button>
          </Col>
          <Col xs={6} sm={6} md={6} lg={3} xl={3}>
            <div className="ml-4 mt-2">
              <TimeOutBar
                cssClass={'character-timeout'}
                readyCssClass={'character-ready'}
                timeRemaining={this.state.timeRemaining}
                channel={'show-timeout-bar-' + this.props.userId}
                eventClass={'Game.Core.Events.ShowTimeOutEvent'}
              />
            </div>
          </Col>
        </Row>
      </>
    )
  }

  render() {
    return (
      <>{this.state.isLoading ? null : this.renderActions()}</>
    )
  }
}
