import React from 'react';
import Embezzle from './embezzle';
import {Alert, OverlayTrigger, Tooltip, Tabs, Tab} from 'react-bootstrap';
import Deposit from "./deposit";
import Population from "./population";
import AlertInfo from "../../../components/base/alert-info";
import GoblinCoinBank from "./goblin-coin-bank";

const renderDeposit = (props) => (
  <Tooltip id="button-tooltip" {...props}>
    Deposit Gold into kingdom. This will increase your morale by 15%. The more gold you have the higher your defence.
    Having 2 billion gold gives defences 100% extra defence. Treasury is increased per hour based on gold, your Lust for Gold skill
    bonus and the level of your keep. Check help docs, under kingdoms for more info.
  </Tooltip>
);

const renderWithdraw = (props) => (
  <Tooltip id="button-tooltip" {...props}>
    By removing any amount of gold you will lose 15% morale each time you embezzle. Your kingdom will also lose the added defence
    bonus by the amount of gold you embezzle. Remember having gold increases defence.
  </Tooltip>
);

const renderHelp = (props) => (
  <Tooltip id="button-tooltip" {...props}>
    This comes from a combination of your wall defence, which is level / 30. Your Kingdom Management Passive Skill buff,
    the amount of treasury you have divided by the kingdom's max treasury (2 billion) and the amount of gold bars you have (maximum of 1000, at 2 billion each)
    which can be purchased from the Goblin Coin Bank.
  </Tooltip>
);

const renderPopulation = (props) => (
  <Tooltip id="button-tooltip" {...props}>
    A kingdom can buy more people. These people will show up instantly. However, be cautioned. If you have more people than you are allowed at the
    Hourly reset, your kingdom treasury will lose 10,000 gold PER person over the cap. Buying people is only done to increase your military.
  </Tooltip>
);

export default class KingdomInfo extends React.Component {

  constructor(props) {
    super(props);

    this.state = {
      showEmbezzle: false,
      showDeposit: false,
      showSuccess: false,
      successMessage: null,
    }
  }

  getCurrentMorale() {
    return (this.props.kingdom.current_morale * 100).toFixed(2);
  }

  getTreasury() {
    if (this.props.kingdom.treasury === null) {
      return 0;
    }

    return this.props.kingdom.treasury.toLocaleString('en-US', {maximumFractionDigits: 0});
  }

  getResourceIncrease(type) {
    const building = this.props.kingdom.buildings.filter((b) => b[type] !== 0);

    if (_.isEmpty(building)) {
      return 0;
    }

    return Math.ceil(building[0][type] + building[0][type] * this.props.kingdom.building_cost_reduction);
  }

  getTotalMoraleIncreasePerHour() {
    let currentMoraleIncrease = 0;

    const buildings = this.props.kingdom.buildings;

    buildings.forEach((building) => {
      if (building.current_durability !== 0) {
        currentMoraleIncrease += building.morale_increase;
      }
    });

    return (currentMoraleIncrease * 100).toFixed(2);
  }

  getTotalMoraleDecreasePerHour() {
    let currentMoraleDecrease = 0;

    const buildings = this.props.kingdom.buildings;

    buildings.forEach((building) => {
      if (building.current_durability === 0) {
        currentMoraleDecrease += building.morale_decrease;
      }
    });

    return (currentMoraleDecrease * 100).toFixed(2);
  }

  showBuyPeople() {
    this.setState({
      showBuyPeople: true,
    });
  }

  closeBuyPeople() {
    this.setState({
      showBuyPeople: false,
    });
  }

  showEmbezzle() {
    this.setState({
      showEmbezzle: true,
    });
  }

  closeEmbezzle() {
    this.setState({
      showEmbezzle: false,
    });
  }

  formatNumber(number) {
    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
  }

  embezzledSuccess(amount) {
    this.setState({
      showSuccess: true,
      successMessage: 'Embezzled ' + this.formatNumber(amount) + ' gold from kingdom. The kingdoms morale has dropped by 15%.',
    });
  }

  showDeposit() {
    this.setState({
      showDeposit: true,
    });
  }

  closeDeposit() {
    this.setState({
      showDeposit: false,
    });
  }

  depositSuccess(amount) {
    this.setState({
      showSuccess: true,
      successMessage: 'Deposited ' + this.formatNumber(amount) + ' gold to the kingdom. The kingdoms morale has increased by 15% and your defence bonus has also been increased.',
    });
  }

  peoplePurchasedSuccess(amount) {
    this.setState({
      showSuccess: true,
      successMessage: 'Purchased: ' + this.formatNumber(amount) + ' People.',
    })
  }

  closeSuccess() {
    this.setState({
      showSuccess: false,
      successMessage: null
    })
  }

  render() {
    return (
      <>
        {
          this.state.showSuccess ?
            <div className="mb-2 mt-2">
              <Alert variant="success" onClose={this.closeSuccess.bind(this)} dismissible>
                {this.state.successMessage}
              </Alert>
            </div>
            : null
        }
        <Tabs defaultActiveKey="core-info" id="kingdom-info-tabs">
          <Tab eventKey="core-info" title="Basic Information">
            <div className="row mt-3">
              <div className="col-md-6 mb-3">
                <dl>
                  <dt><strong>Population</strong> <OverlayTrigger
                    placement="bottom"
                    delay={{ show: 250, hide: 400 }}
                    overlay={renderPopulation}
                  >
                    <button className="btn btn-success btn-sm mr-2" onClick={this.showBuyPeople.bind(this)}>
                      <i className="ra ra-gold-bar"></i>
                    </button>

                  </OverlayTrigger>:

                  </dt>
                  <dd>{this.formatNumber(this.props.kingdom.current_population)} / {this.formatNumber(this.props.kingdom.max_population)}</dd>
                  <dt><strong>Morale</strong>:</dt>
                  <dd>{this.getCurrentMorale()}%</dd>
                  <dt>
                    <strong>Treasury</strong> <OverlayTrigger
                    placement="bottom"
                    delay={{ show: 250, hide: 400 }}
                    overlay={renderDeposit}
                  >
                    <button className="btn btn-success btn-sm mr-1" onClick={this.showDeposit.bind(this)}>
                      <i className="ra ra-gold-bar"></i>
                    </button>

                  </OverlayTrigger>
                    <OverlayTrigger
                      placement="bottom"
                      delay={{ show: 250, hide: 400 }}
                      overlay={renderWithdraw}
                    >
                      <button className="btn btn-danger btn-sm" onClick={this.showEmbezzle.bind(this)}>
                        <i className="ra ra-gold-bar"></i>
                      </button>

                    </OverlayTrigger> :
                  </dt>
                  <dd>{this.getTreasury()}</dd>
                  <dt>Gold Bars (Purchased from Goblin Bank):</dt>
                  <dd>{this.props.kingdom.gold_bars}</dd>
                </dl>
              </div>
              <div className="col-md-6">
                <h4>Information</h4>
                <hr />
                <p>
                  This is the base information of your kingdom. You can see your current treasury, current population and
                  current morale.
                </p>
                <p>
                  Red buttons means to withdraw, while green means to deposit or purchase. Hovering over
                  them will give you information on what they do.
                </p>
              </div>
            </div>
          </Tab>
          <Tab eventKey="resource-info" title="Resource Information">
            <div className="row mt-3">
              <div className="col-md-4 mb-3">
                <h4 className="tw-font-light">Current Resources</h4>
                <hr />
                <dl>
                  <dt><strong>Wood</strong>:</dt>
                  <dd>{this.formatNumber(this.props.kingdom.current_wood)} / {this.formatNumber(this.props.kingdom.max_wood)}</dd>
                  <dt><strong>Clay</strong>:</dt>
                  <dd>{this.formatNumber(this.props.kingdom.current_clay)} / {this.formatNumber(this.props.kingdom.max_clay)}</dd>
                  <dt><strong>Stone</strong>:</dt>
                  <dd>{this.formatNumber(this.props.kingdom.current_stone)} / {this.formatNumber(this.props.kingdom.max_stone)}</dd>
                  <dt><strong>Iron</strong>:</dt>
                  <dd>{this.formatNumber(this.props.kingdom.current_iron)} / {this.formatNumber(this.props.kingdom.max_iron)}</dd>
                  <dt><strong>Population</strong>:</dt>
                  <dd>{this.formatNumber(this.props.kingdom.current_population)} / {this.formatNumber(this.props.kingdom.max_population)}</dd>
                </dl>
              </div>
              <div className="col-md-4 mb-3">
                <h4 className="tw-font-light">Hourly Resource Gain</h4>
                <hr />
                <dl>
                  <dt><strong>Wood Increase/hr</strong>:</dt>
                  <dd>{this.getResourceIncrease('wood_increase')} (+{(this.props.kingdom.building_cost_reduction * 100).toFixed(0)}%)</dd>
                  <dt><strong>Clay Increase/hr</strong>:</dt>
                  <dd>{this.getResourceIncrease('clay_increase')} (+{(this.props.kingdom.building_cost_reduction * 100).toFixed(0)}%)</dd>
                  <dt><strong>Stone Increase/hr</strong>:</dt>
                  <dd>{this.getResourceIncrease('stone_increase')} (+{(this.props.kingdom.building_cost_reduction * 100).toFixed(0)}%)</dd>
                  <dt><strong>Iron Increase/hr</strong>:</dt>
                  <dd>{this.getResourceIncrease('iron_increase')} (+{(this.props.kingdom.building_cost_reduction * 100).toFixed(0)}%)</dd>
                  <dt><strong>Population Increase/hr</strong>:</dt>
                  <dd>{this.getResourceIncrease('population_increase')} (+{(this.props.kingdom.building_cost_reduction * 100).toFixed(0)}%)</dd>
                </dl>
                <p className="mt-2">The additional percentage values come from you training: Building Management <a href="/information/passive-skills">Passive</a>.</p>
              </div>
              <div className="col-md-4">
                <h4>Information</h4>
                <hr />
                <p>This section details how many resources your kingdom currently has and how many you gain per hour.</p>
                <p>Your kingdom gains resources once per hour, the higher the level your resource buildings the more resources
                your kingdoms can have and gain per hour.</p>
                <p>
                  Resources are used to upgrade buildings, rebuild buildings and recruit units. Of course you can always use Gold
                  to purchase building levels and units, Assuming you have the gold.
                </p>
              </div>
            </div>
          </Tab>
          <Tab eventKey="defence-morale" title="Morale and Defence">
            <div className="row mt-4">
              <div className="col-md-6 mb-3">
                <dl>
                  <dt><strong>Morale Increase/hr</strong>:</dt>
                  <dd>{this.getTotalMoraleIncreasePerHour()}%</dd>
                  <dt><strong>Morale Decrease/hr</strong>:</dt>
                  <dd>{this.getTotalMoraleDecreasePerHour()}%</dd>
                  <dt><strong>Current Defence Bonus</strong> <OverlayTrigger
                    placement="bottom"
                    delay={{ show: 250, hide: 400 }}
                    overlay={renderHelp}
                  >
                    <i className="far fa-question-circle"></i>

                  </OverlayTrigger>:</dt>
                  <dd>
                    <span>{(this.props.kingdom.defence_bonus * 100).toFixed(2)}%</span>
                  </dd>
                </dl>
                <hr />
                <h4 className="tw-font-light">Current Defence Bonus Break Down</h4>
                <hr />
                <AlertInfo icon={"fas fa-question-circle"} title={"Attn!"}>
                  <p>
                    When cannons attack your kingdom, your Current Defence Bonus will be capped at 45% when facing those units.
                    The logic here is a cannon ball can rip through anything, stone, iron, clay, wood. It can rip through it all.
                  </p>
                  <p>
                    It doesn't matter if the attacking force has sent other units, your defence only drops for these units. Other units,
                    your defence will rebound back up to what it currently is.
                  </p>
                </AlertInfo>
                <dl>
                  <dt>Wall Bonus: </dt>
                  <dd>{(this.props.kingdom.walls_defence * 100).toFixed(2)}%</dd>
                  <dt>Treasury Bonus: </dt>
                  <dd>{(this.props.kingdom.treasury_defence * 100).toFixed(2)}%</dd>
                  <dt>Passive (Kingdom Management) Bonus: </dt>
                  <dd>{(this.props.kingdom.passive_defence * 100).toFixed(2)}%</dd>
                  <dt>Gold Bars (Goblin Coin Bank) Bonus: </dt>
                  <dd>{(this.props.kingdom.gold_bars_defence * 100).toFixed(2)}%</dd>
                </dl>
              </div>
              <div className="col-md-6">
                <h4>Information</h4>
                <hr />
                <p>This section details your morale increase/decrease per hour.</p>
                <p>Many things can effect the increase/decrease, such as specific buildings
                with a durability of 0, will decrease morale per hour. You can read about this in the
                  <a href="/information/kingdoms"> kingdoms section</a> of the help docs.</p>
                <p>Treasury, such as depositing will increase the morale by 15%. Rebuilding destroyed buildings will
                also increase morale.</p>
                <p>Your defence bonus comes from the total treasury you have divided by the maximum. There is also a
                 <a href="/information/passive-skills"> Passive Skill</a> that you can train to increase this an additional 25%</p>
              </div>
            </div>
          </Tab>
          <Tab eventKey="goblin-coin-bank" title="Goblin Bank">
            {
              this.props.kingdom.can_access_bank ?
                <GoblinCoinBank
                  kingdom={this.props.kingdom}
                  characterId={this.props.characterId}
                  characterGold={this.props.characterGold}
                />
              :
                <div className="mt-2">
                  <AlertInfo icon={'fas fa-question-circle'} title="Info">
                    You need to unlock the Goblin Coin Bank Passive skill and level the building to level 5 or higher before you
                    are able to convert your gold to gold bars.
                  </AlertInfo>
                </div>
            }
          </Tab>
        </Tabs>

        <Embezzle
          show={this.state.showEmbezzle}
          close={this.closeEmbezzle.bind(this)}
          morale={this.props.kingdom.current_morale}
          treasury={this.props.kingdom.treasury}
          kingdomId={this.props.kingdom.id}
          embezzledSuccess={this.embezzledSuccess.bind(this)}
        />
        <Deposit
          show={this.state.showDeposit}
          close={this.closeDeposit.bind(this)}
          morale={this.props.kingdom.current_morale}
          treasury={this.props.kingdom.treasury}
          kingdomId={this.props.kingdom.id}
          depositSuccess={this.depositSuccess.bind(this)}
          characterGold={this.props.characterGold}
        />
        <Population
          show={this.state.showBuyPeople}
          close={this.closeBuyPeople.bind(this)}
          kingdomId={this.props.kingdom.id}
          purchasedSuccess={this.peoplePurchasedSuccess.bind(this)}
          characterGold={this.props.characterGold}
          currentPopulation={this.props.kingdom.current_population}
        />
      </>
    )
  }
}
