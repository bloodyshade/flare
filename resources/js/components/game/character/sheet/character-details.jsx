import React, {Fragment} from 'react';
import {Card, Col, Row, Tabs, Tab} from 'react-bootstrap';
import AlertInfo from "../../components/base/alert-info";

export default class CharacterDetails extends React.Component {

  constructor(props) {
    super(props);

    this.state = {
      loading: true,
      baseData: {},
    }

    this.updateBaseDetails = Echo.private('update-character-base-stats-' + this.props.userId);
    this.topBar            = Echo.private('update-top-bar-' + this.props.userId);
  }

  componentDidMount() {
    axios.get('/api/character-base-data/' + this.props.characterId).then((result) => {
      this.setState({
        baseData: result.data.base_info,
        loading: false,
      });
    }).catch((err) => {
      this.setState({loading: false});
      if (err.hasOwnProperty('response')) {
        const response = err.response;

        if (response.status === 401) {
          return location.reload()
        }

        if (response.status === 429) {
          return this.props.openTimeOutModal()
        }
      }
    });

    this.updateBaseDetails.listen('Game.Core.Events.UpdateBaseCharacterInformation', (event) => {
      this.setState({
        baseData: event.baseStats,
      });
    });

    this.topBar.listen('Game.Core.Events.UpdateTopBarBroadcastEvent', (event) => {
      let baseData = JSON.parse(JSON.stringify(this.state.baseData));

      baseData.xp  = event.characterSheet.xp;
      baseData.xp_next = event.characterSheet.xp_next;

      this.setState({
        baseData: baseData,
      });
    });
  }

  buildEachTab(attackData, voided) {
    const tabs = [];

    const attackDataKeys = Object.keys(attackData)
      .filter(key => voided ? !key.includes('voided') : key.includes('voided'));

    for (const key in attackData) {
      if (attackDataKeys.includes(key)) {
        tabs.push(
          <Tab eventKey={key}
               title={key.replace(/_/g, " ").replace(/(^\w{1})|(\s{1}\w{1})/g, match => match.toUpperCase())}
               tabClassName="mt-4"
          >
            <div className="mt-4">
              <Row>
                <Col xs={12} sm={12} md={12} lg={6}>
                  <h4>Attack Data</h4>
                  <hr />
                  <dl>
                    {this.renderAttackData(attackData[key])}
                  </dl>
                </Col>
                <Col xs={12} sm={12} md={12} lg={6}>
                  <h4>Affix Attack Data</h4>
                  <hr />
                  <dl>
                    {this.renderAttackData(attackData[key].affixes)}
                  </dl>
                </Col>
              </Row>
            </div>
          </Tab>
        );
      }
    }

    return tabs;
  }

  renderAttackData(attackData) {

    const data = [];

    for (const key in attackData) {
      if (key !== 'affixes' && key !== 'name') {
        data.push(
          <Fragment>
            <dt>{key.replace(/_/g, " ").replace(/(^\w{1})|(\s{1}\w{1})/g, match => match.toUpperCase())}</dt>
            <dd>{typeof attackData[key] === 'boolean' ? (attackData[key] ? 'Yes' : 'No') : this.formatNumber(attackData[key])}</dd>
          </Fragment>
        )
      }
    }

    return data;
  }

  formatNumber(number) {
    return parseFloat(number).toFixed(2).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
  }

  render() {

    if (this.state.loading) {
      return (
        <div className="progress loading-progress mt-2 mb-2" style={{position: 'relative'}}>
          <div className="progress-bar progress-bar-striped indeterminate">
          </div>
        </div>
      );
    }

    const sheet = this.state.baseData;

    const xpValue = sheet.xp / sheet.xp_next * 100;

    return (
      <Card>
        <Card.Body>
          <Row>
            <Col xs={12} sm={6}>
              <dl>
                <dt>Character Name:</dt>
                <dd>{sheet.name}</dd>
                <dt>Character Race:</dt>
                <dd>{sheet.race}</dd>
                <dt>Character Class:</dt>
                <dd>{sheet.class}</dd>
                <dt>Character Level:</dt>
                <dd>{sheet.level} / {sheet.max_level}</dd>
                <dt>Character XP:</dt>
                <dd>
                  <div className="progress level-bar mb-2">
                    <div className="progress-bar skill-bar" role="progressbar"
                         style={{width: xpValue + '%'}}
                         aria-valuenow={sheet.xp} aria-valuemin="0"
                         aria-valuemax={sheet.xp_next}
                    >
                      {Math.round(sheet.xp)}
                    </div>
                  </div>
                </dd>
              </dl>
            </Col>
            <Col xs={12} sm={6}>
              <dl>
                <dt>Max Health:</dt>

                <dd>{sheet.health}</dd>
                <dt>Attack:</dt>
                <dd>{sheet.attack}</dd>
                <dt>Heal For:</dt>
                <dd>{sheet.heal_for}</dd>
                <dt>AC:</dt>
                <dd>{sheet.ac}</dd>
              </dl>
            </Col>
          </Row>
          <hr />
          <Tabs defaultActiveKey="stats" id="character-stats">
            <Tab eventKey="stats" title="Stats">
              <Row>
                <Col xs={12} sm={12} md={12} lg={6} xl={6}>
                  <h4 className='mt-4'>Raw Stats</h4>
                  <hr />
                  <dl className="mt-4">
                    <dt>Strength:</dt>
                    <dd>{sheet.str}</dd>
                    <dt>Durability:</dt>
                    <dd>{sheet.dur}</dd>
                    <dt>Dexterity:</dt>
                    <dd>{sheet.dex}</dd>
                    <dt>Charisma:</dt>
                    <dd>{sheet.chr}</dd>
                    <dt>Intelligence:</dt>
                    <dd>{sheet.int}</dd>
                    <dt>Agility:</dt>
                    <dd>{sheet.agi}</dd>
                    <dt>Focus:</dt>
                    <dd>{sheet.focus}</dd>
                  </dl>
                </Col>
                <Col xs={12} sm={12} md={12} lg={6} xl={6}>
                  <h4 className='mt-4'>Modded Stats</h4>
                  <hr />
                  <dl className="mt-4">
                    <dt>Strength:</dt>
                    <dd>{sheet.str_modded}</dd>
                    <dt>Durability:</dt>
                    <dd>{sheet.dur_modded}</dd>
                    <dt>Dexterity:</dt>
                    <dd>{sheet.dex_modded}</dd>
                    <dt>Charisma:</dt>
                    <dd>{sheet.chr_modded}</dd>
                    <dt>Intelligence:</dt>
                    <dd>{sheet.int_modded}</dd>
                    <dt>Agility:</dt>
                    <dd>{sheet.agi_modded}</dd>
                    <dt>Focus:</dt>
                    <dd>{sheet.focus_modded}</dd>
                  </dl>
                </Col>
              </Row>

            </Tab>
            <Tab eventKey="misc-modifiers" title="Misc Modifiers">
              <Row>
                <Col xs={12} sm={12} md={12} lg={4} xl={4}>
                  <h4 className='mt-4'>Resist./Reduce./Res. %</h4>
                  <hr />
                  <dl className="mt-4">
                    <dt>Spell Evasion<sup>**</sup>:</dt>
                    <dd>{(sheet.spell_evasion * 100).toFixed(2)}%</dd>
                    <dt>Artifact Annulment<sup>**</sup>:</dt>
                    <dd>{(sheet.artifact_anull * 100).toFixed(2)}%</dd>
                    <dt>Enchantment Reduction Amount<sup>**</sup>:</dt>
                    <dd>{(sheet.affix_damage_red * 100).toFixed(2)}%</dd>
                    <dt>Healing Reduction Amount<sup>**</sup>:</dt>
                    <dd>{(sheet.affix_damage_red * 100).toFixed(2)}%</dd>
                    <dt>Resurrection Chance<sup>*</sup>:</dt>
                    <dd>{(sheet.res_chance * 100).toFixed(2)}%</dd>
                  </dl>
                  <p className="mt-4"><sup>*</sup> Only healing spells can affect this.</p>
                  <p className="mt-2"><sup>**</sup> Only affects enemies.</p>
                </Col>
                <Col xs={12} sm={12} md={12} lg={4} xl={4}>
                  <h4 className='mt-4'>Devouring %</h4>
                  <hr />
                  <dl className="mt-4">
                    <dt>Devouring Light:</dt>
                    <dd>{(sheet.devouring_light * 100).toFixed(0)}%</dd>
                    <dt>Devouring Darkness:</dt>
                    <dd>{(sheet.devouring_darkness * 100).toFixed(0)}%</dd>
                  </dl>
                  <p className="mt-4">For more information, please see <a href="/information/voidance">Voidance help</a>. </p>
                </Col>
                <Col xs={12} sm={12} md={12} lg={4} xl={4}>
                  <h4 className='mt-4'>Holy Bonuses</h4>
                  <hr />
                  <dl className="mt-4">
                    <dt>Overall Holy Bonus:</dt>
                    <dd>{(sheet.holy_bonus * 100).toFixed(2)}%</dd>
                    <dt>Holy Stacks</dt>
                    <dd>{sheet.current_stacks}/{sheet.max_holy_stacks}</dd>
                    <dt>Voidance Resistance Bonus:</dt>
                    <dd>{(sheet.devouring_resistance * 100).toFixed(2)}%</dd>
                    <dt>Devoidance Resistance Bonus:</dt>
                    <dd>{(sheet.devouring_resistance * 100).toFixed(2)}%</dd>
                    <dt>Bonus Attack %</dt>
                    <dd>{(sheet.holy_attack_bonus * 100).toFixed(2)}%</dd>
                    <dt>Bonus AC %</dt>
                    <dd>{(sheet.holy_ac_bonus * 100).toFixed(2)}%</dd>
                    <dt>Bonus Healing %</dt>
                    <dd>{(sheet.holy_healing_bonus * 100).toFixed(2)}%</dd>
                  </dl>
                  <p className="mt-4">For more information, please see <a href="/information/holy-items">Holy Items help</a>. </p>
                </Col>
              </Row>

            </Tab>
          </Tabs>
          <hr />
          <Row>
            <Col xs={12}>
              <Row>
                <Col xs={12} sm={12} md={12} lg={6} xl={6}>
                  <h4>Class Bonus</h4>
                  <hr />
                  <p>
                    {sheet.class_bonus.description}
                  </p>

                  <dl className="mt-2">
                    <dt>Type:</dt>
                    <dd>{sheet.class_bonus.type}</dd>
                    <dt>Base Chance:</dt>
                    <dd>{(sheet.class_bonus.base_chance * 100).toFixed(2)}%</dd>
                    <dt>Requirements:</dt>
                    <dd>{sheet.class_bonus.requires}</dd>
                  </dl>
                </Col>
                <Col xs={12} sm={12} md={12} lg={6} xl={6}>
                  <h4 className='mr-4'>Attack Info</h4>
                  <hr />
                  <dl className="mt-4">
                    <dt>Weapon Attack:</dt>
                    <dd>{sheet.weapon_attack}</dd>
                    <dt>Rings Attack:</dt>
                    <dd>{sheet.rings_attack}</dd>
                    <dt>Spell Damage:</dt>
                    <dd>{sheet.spell_damage}</dd>
                    <dt>Artifact Damage:</dt>
                    <dd>{sheet.artifact_damage}</dd>
                    <dt>Heal For:</dt>
                    <dd>{sheet.heal_for}</dd>
                  </dl>
                </Col>
              </Row>
            </Col>
          </Row>
        </Card.Body>
      </Card>
    )
  }
}
