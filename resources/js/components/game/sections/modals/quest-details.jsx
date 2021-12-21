import React, {Fragment} from 'react';
import {Modal, Button, Tabs, Tab} from 'react-bootstrap';
import ItemName from "../../../marketboard/components/item-name";

export default class QuestDetails extends React.Component {

  constructor(props) {
    super(props);
  }

  getNPCCommands(npc) {
    return npc.commands.map((command) => command.command).join(', ');
  }

  formatNumber(number) {
    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
  }

  renderPlaneAccessRequirements(map) {
    if (map.map_required_item !== null) {
      return (
        <Fragment>
          <dt>Required to access</dt>
          <dd><ItemName item={map.map_required_item} /></dd>

          {
            map.map_required_item.required_monster !== null ?
              <Fragment>
                <dt>Which requires you to fight (first)</dt>
                <dd>{map.map_required_item.required_monster.name}</dd>
                <dt>Who resides on plane</dt>
                <dd>{map.map_required_item.required_monster.game_map.name}</dd>
                {this.renderPlaneAccessRequirements(map.map_required_item.required_monster.game_map)}
              </Fragment>
            : null
          }
        </Fragment>
      );
    }

    return null;
  }

  renderLocations(locations) {
    return locations.map((location) => {
      return  <Fragment>
        <dl>
          <dt>By Going to</dt>
          <dd>{location.name}</dd>
          <dt>Which is at (X/Y)</dt>
          <dd>{location.x}/{location.y}</dd>
          <dt>On Plane</dt>
          <dd>{location.map.name}</dd>
          {this.renderPlaneAccessRequirements(location.map)}
        </dl>
      </Fragment>
    });
  }

  render() {
    return (
      <>
        <Modal show={this.props.show} onHide={this.props.questDetailsClose}>
          <Modal.Header closeButton>
            <Modal.Title>{this.props.quest.name}</Modal.Title>
          </Modal.Header>
          <Modal.Body>
            <Tabs defaultActiveKey="npc-info" id="map-quest">
              <Tab eventKey="npc-info" title="NPC Info">
                <div className="mt-3">
                  <dl>
                    <dt>Name</dt>
                    <dd>{this.props.quest.npc.name}</dd>
                    <dt>How to message</dt>
                    <dd><code>{this.props.quest.npc.text_command_to_message}</code></dd>
                    <dt>Available Commands</dt>
                    <dd>{this.getNPCCommands(this.props.quest.npc)}</dd>
                    <dt>Coordinates (X/Y)</dt>
                    <dd>{this.props.quest.npc.x_position} / {this.props.quest.npc.y_position}</dd>
                    <dt>Must be at same location?</dt>
                    <dd>{this.props.quest.npc.must_be_at_same_location ? 'Yes' : 'No'}</dd>
                  </dl>
                  <hr />
                  <h3 className="tw-font-light">Info</h3>
                  <p>
                    Use the chat box to communicate with the NPC by private messaging them. The Available Commands section outlines accepted commands you can
                    message them. Some NPC's might require you to be at their location. This means you have to physically be at the same coordinates before
                    you message the npc to complete the quest.
                  </p>
                </div>
              </Tab>
              <Tab eventKey="required-info" title="Required to complete">
                <p>
                  Below you will find all the requirements of this quest. Once you have met them, make sure to check the NPC
                  tab to see where to go and what command to use to interact with them.
                </p>
                <p>
                  If this NPC only accepts currency, you should probably do the quests in order,
                  specially if they have another quest where the currency is the same and of higher requirement.
                </p>
                <hr />
                <div className="mt-3">
                  <dl>
                    {
                      this.props.quest.gold_cost !== null ?
                        <Fragment>
                          <dt>Gold Cost:</dt>
                          <dd>{this.formatNumber(this.props.quest.gold_cost)}</dd>
                        </Fragment>
                      :
                        null
                    }
                    {
                      this.props.quest.gold_dust_cost !== null ?
                        <Fragment>
                          <dt>Gold Dust Cost:</dt>
                          <dd>{this.formatNumber(this.props.quest.gold_dust_cost)}</dd>
                        </Fragment>
                        :
                        null
                    }
                    {
                      this.props.quest.shard_cost !== null ?
                        <Fragment>
                          <dt>Shards Cost:</dt>
                          <dd>{this.formatNumber(this.props.quest.shard_cost)}</dd>
                        </Fragment>
                        :
                        null
                    }
                    {
                      this.props.quest.item_id !== null ?
                        <Fragment>
                          <dt>Required Item:</dt>
                          <dd><ItemName item={this.props.quest.item} /></dd>
                        </Fragment>
                        :
                        null
                    }
                  </dl>
                  {
                    this.props.quest.item_id !== null ?
                      <Fragment>
                        <hr />
                        <h3 className="tw-font-light">Quest Requires Item</h3>
                        <hr />
                        <p>This quest requires you to hand in item. Below you will find relevant details as to how to obtain the item
                        you need.</p>
                        <dl>
                          {
                            this.props.quest.required_item_monster !== null ?
                              <Fragment>
                                <dt>Obtained by killing</dt>
                                <dd>{this.props.quest.required_item_monster.name}</dd>
                                <dt>Resides on plane</dt>
                                <dd>{this.props.quest.required_item_monster.game_map.name}</dd>
                                {this.renderPlaneAccessRequirements(this.props.quest.required_item_monster.game_map)}
                              </Fragment>
                            : null
                          }

                          {
                            this.props.quest.item.required_quest !== null ?
                              <Fragment>
                                <dt>Obtained by completing</dt>
                                <dd>{this.props.quest.item.required_quest.name}</dd>
                                <dt>Which belongs to (NPC)</dt>
                                <dd>{this.props.quest.item.required_quest.npc.real_name}</dd>
                                <dt>Who is on the plane of</dt>
                                <dd>{this.props.quest.item.required_quest.npc.game_map.name}</dd>
                                <dt>At coordinates (X/Y)</dt>
                                <dd>{this.props.quest.item.required_quest.npc.x_position} / {this.props.quest.item.required_quest.npc.y_position}</dd>
                              </Fragment>
                              : null
                          }
                        </dl>
                        {
                          this.props.quest.item.locations.length > 0 ?
                            <Fragment>
                              <hr />
                              <h3 className="tw-font-light">Locations</h3>
                              <p>Locations that will give you the item, just for visiting.</p>
                              <hr />
                              {this.renderLocations(this.props.quest.item.locations)}
                            </Fragment>
                            : null
                        }
                      </Fragment>
                    : null
                  }
                </div>
              </Tab>
              <Tab eventKey="reward-info" title="Reward">
                <div className="mt-3">
                  <p>Upon completing this quest, buy speaking to the NPC and entering the command, once you have
                  met the required objectives you will be rewarded with the following.</p>
                  <hr />
                  <dl>
                    {
                      this.props.quest.reward_xp !== null ?
                        <Fragment>
                          <dt>XP Reward</dt>
                          <dd>{this.formatNumber(this.props.quest.reward_xp)}</dd>
                        </Fragment>
                      : null
                    }
                    {
                      this.props.quest.reward_gold !== null ?
                        <Fragment>
                          <dt>Gold Reward</dt>
                          <dd>{this.formatNumber(this.props.quest.reward_gold)}</dd>
                        </Fragment>
                        : null
                    }
                    {
                      this.props.quest.reward_gold_dust !== null ?
                        <Fragment>
                          <dt>Gold Dust Reward</dt>
                          <dd>{this.formatNumber(this.props.quest.reward_gold_dust)}</dd>
                        </Fragment>
                        : null
                    }
                    {
                      this.props.quest.reward_shards !== null ?
                        <Fragment>
                          <dt>Shards Reward</dt>
                          <dd>{this.formatNumber(this.props.quest.reward_shards)}</dd>
                        </Fragment>
                        : null
                    }
                    {
                      this.props.quest.unlocks_skill ?
                        <Fragment>
                          <dt>Unlocks New Skill</dt>
                          <dd>{this.props.quest.unlocks_skill_name}</dd>
                        </Fragment>
                        : null
                    }
                    {
                      this.props.quest.reward_item !== null ?
                        <Fragment>
                          <dt>Item reward</dt>
                          <dd>
                            <a href={"/items/" + this.props.quest.reward_item.id} target="_blank">
                              <ItemName item={this.props.quest.reward_item} /> <i
                              className="fas fa-external-link-alt"></i>
                            </a>
                          </dd>
                        </Fragment>
                        : null
                    }
                  </dl>
                </div>
              </Tab>
            </Tabs>
          </Modal.Body>
          <Modal.Footer>
            <Button variant="secondary" onClick={this.props.questDetailsClose}>
              Close
            </Button>
          </Modal.Footer>
        </Modal>
      </>
    );
  }
}