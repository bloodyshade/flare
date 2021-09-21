import React, {Fragment} from 'react';
import ItemName from "../../../../marketboard/components/item-name";
import {Alert, Card} from "react-bootstrap";
import ReactDatatable from "@ashvin27/react-datatable";
import SaveAsSetModal from "../modals/save-as-set-modal";
import EquippedSectionButton from "./equipped-section-button";

export default class EquippedSection extends React.Component {

  constructor(props) {
    super(props);

    this.equipped_Config = {
      key_column: 'slot_id',
      page_size: 10,
      length_menu: [10, 25, 50, 75],
      show_pagination: true,
      pagination: 'advance',
      hideSizePerPage: true,
    }

    this.equipped_headers = [
      {
        key: "affix_name",
        text: "Name",
        sortable: true,
        cell: row => <div>
          <a href={'/items/' + row.id} target="_blank">
            <ItemName item={row} useAffixName={true} />
          </a>
        </div>
      },
      {
        key: "type",
        text: "Type",
        sortable: true,
      },
      {
        key: 'base_damages',
        text: 'Base Damage',
        sortable: true,
        cell: row => <div>{row.base_damage !== null ? row.base_damage : 0}</div>
      },
      {
        key: 'base_ac',
        text: 'Base AC',
        sortable: true,
        cell: row => <div>{row.base_ac !== null ? row.base_ac : 0}</div>
      },
      {
        key: 'base_healing',
        text: 'Base Healing',
        sortable: true,
        cell: row => <div>{row.base_healing !== null ? row.base_healing : 0}</div>
      },
      {
        name: "actions",
        text: "Actions",
        cell: row => <Fragment>
          <EquippedSectionButton
            characterId={this.props.characterId}
            item={row}
            findEquippedSlotId={this.findEquippedSlotId.bind(this)}
            setSuccessMessage={this.setSuccessMessage.bind(this)}
            setErrorMessage={this.setErrorMessage.bind(this)}
            hasSetEquipped={typeof this.props.equipped === 'object' && !Array.isArray(this.props.equipped)}
            loading={this.state.loading}
          />
        </Fragment>
      },
    ];

    this.state = {
      successMessage: null,
      errorMessage: null,
      manageOpenSaveAsSet: false,
      loading: false,
      equippedItems: [],
    }
  }

  componentDidMount() {
    console.log(this.formatDataForTable());
    this.setState({
      equippedItems: this.formatDataForTable()
    })
  }

  componentDidUpdate(prevProps, prevState, snapshot) {
    const equippedItems = this.formatDataForTable()

    if (this.state.equippedItems.length !== equippedItems.length) {
      this.setState({
        equippedItems: equippedItems,
      });
    }
  }

  clearSuccessMessage() {
    this.setState({
      successMessage: null,
    })
  }

  clearErrorMessage() {
    this.setState({
      errorMessage: null,
    })
  }

  setSuccessMessage(message) {
    this.setState({
      successMessage: message,
    })
  }

  setErrorMessage(message) {
    this.setState({
      errorMessage: message,
    })
  }

  formatDataForTable() {
    if (Array.isArray(this.props.equipped)) {
      return this.props.equipped.map((e) => {
        e.item['slot_id'] = e.id;

        return e.item;
      });
    }

    if (typeof this.props.equipped === 'object') {
      return this.props.equipped.slots.map((s) => {
        s.item['slot_id'] = s.id;

        return s.item;
      });
    }
  }

  findEquippedSlotId(itemId) {
    if (Array.isArray(this.props.equipped)) {
      const foundObject =  this.props.equipped.filter((e) => e.item.id === itemId);

      if (foundObject.length > 0) {
        return foundObject[0].id;
      }
    }

    if (typeof this.props.equipped === 'object') {
      const foundObject = this.props.equipped.slots.filter((s) => s.item.id === itemId);

      if (foundObject.length > 0) {
        return foundObject[0].id;
      }
    }

    return null;
  }

  unequipAll() {
    this.setState({loading: true, errorMessage: null, successMessage: null});
    axios.post('/api/character/'+this.props.characterId+'/inventory/unequip-all', {
      is_set_equipped: typeof this.props.equipped === 'object' && !Array.isArray(this.props.equipped),
    }).then((result) => {
      this.setState({loading: false});
      this.setSuccessMessage(result.data.message)
    }).catch((error) => {
      this.setState({loading: false});
      if (error.hasOwnProperty('response')) {
        const response = error.response;

        if (response.status === 401) {
          return location.reload()
        }

        if (response.status === 429) {
          return window.location.replace('/game');
        }

        if (response.data.hasOwnProperty('message')) {
          this.setErrorMessage(response.data.message)
        }

        if (response.data.hasOwnProperty('error')) {
          this.setErrorMessage(response.data.error)
        }
      }
    })
  }

  manageOpenSaveAsSet() {
    this.setState({
      openSaveAsSet: !this.state.openSaveAsSet
    })
  }

  render() {
    return(
      <Card>
        <Card.Body>

          <div className="alert alert-info mt-2">
            <p>
              You may choose to save currently equipped items to a set that is not currently equipped.
              To do this click Save as set when you are happy with the items you have equipped.
              These items will still be equipped to you, you will just now have a set equipped.
            </p>
          </div>

          {
            typeof this.props.equipped === 'object' && !Array.isArray(this.props.equipped) ?
              <div className="alert alert-warning mt-2 mb-3">
                You currently have a set equipped. Equipping any other item, or set will replace this set completely.
                <strong>You cannot mix and match sets or sets with non set items. It's one or the other.</strong>

              </div>
            : null
          }

          {
            this.state.successMessage !== null ?
              <div className="mb-3">
                <Alert variant="success" onClose={this.clearSuccessMessage.bind(this)} dismissible>
                  {this.state.successMessage}
                </Alert>
              </div>
              : null
          }

          {
            this.state.errorMessage !== null ?
              <div className="mb-3">
                <Alert variant="danger" onClose={this.clearErrorMessage.bind(this)} dismissible>
                  {this.state.errorMessage}
                </Alert>
              </div>
              : null
          }
          <hr />
          <button className='btn btn-danger mr-2'
                  onClick={this.unequipAll.bind(this)}
                  disabled={this.props.equipped.length === 0 || this.state.loading}
          >
            Unequip All
          </button>
          <button className='btn btn-primary mr-2'
                  onClick={this.manageOpenSaveAsSet.bind(this)}
                  disabled={
                    typeof this.props.equipped === 'object' && !Array.isArray(this.props.equipped) ||
                      this.props.equipped.length === 0 || this.state.loading
                  }
          >
            Save as set
          </button>
          <hr />
          {
            this.state.loading ?
              <div className="progress loading-progress mt-2 mb-2" style={{position: 'relative'}}>
                <div className="progress-bar progress-bar-striped indeterminate">
                </div>
              </div>
              : null
          }
          <ReactDatatable
            config={this.equipped_Config}
            records={this.state.equippedItems}
            columns={this.equipped_headers}
          />
          {
            this.state.openSaveAsSet ?
              <SaveAsSetModal
                characterId={this.props.characterId}
                sets={this.props.usableSets}
                open={this.state.openSaveAsSet}
                close={this.manageOpenSaveAsSet.bind(this)}
                setSuccessMessage={this.setSuccessMessage.bind(this)}
              />
            : null
          }
        </Card.Body>
      </Card>
    )
  }


}