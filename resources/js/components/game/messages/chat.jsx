import React from 'react';
import cloneDeep from 'lodash/cloneDeep';
import {getServerMessage} from '../helpers/server_message'
import {isEmpty} from 'lodash';
import {DateTime} from "luxon";

export default class Chat extends React.Component {

  constructor(props) {
    super(props);

    this.state = {
      messages: [],
      message: '',
      user: {},
    }

    this.echo = Echo.join('chat');
    this.globalMessage = Echo.join('global-message');
    this.serverMessages = Echo.private('server-message-' + this.props.userId);
    this.privateMessages = Echo.private('private-message-' + this.props.userId);
  }

  componentDidMount() {

    axios.get('/api/user-chat-info/' + this.props.userId).then((result) => {
      this.setState({
        user: result.data.user,
      });
    }).catch((err) => {
      if (err.hasOwnProperty('response')) {
        const response = err.response;

        if (response.status === 401  || response.status === 429) {
          return location.reload();
        }
      }
    });

    axios.get('/api/last-chats/').then((result) => {
      let messages = [];

      result.data.forEach((message) => {
        let newMessage = message;

        newMessage['from_god'] = this.isGod(message.user);

        messages.push(newMessage);
      });

      this.setState({
        messages: messages
      });

    }).catch((err) => {
      if (err.hasOwnProperty('response')) {
        const response = err.response;

        if (response.status === 401  || response.status === 429) {
          return location.reload();
        }
      }
    });

    this.echo.listen('Game.Messages.Events.MessageSentEvent', (event) => {

      const message = event.message;
      message['user'] = event.user;
      message['name'] = event.name;
      message['from_god'] = this.isGod(event.user);
      message['x'] = event.x;
      message['y'] = event.y;
      message['map'] = event.mapName;

      const messages = cloneDeep(this.state.messages);

      messages.unshift(message);

      this.setState({
        messages: messages
      }, () => {
        if (!this.isGod(event.user)) {
          this.props.updateChatTabIcon(false);
        }
      });
    });

    this.globalMessage.listen('Game.Messages.Events.GlobalMessageEvent', (event) => {
      const messages = cloneDeep(this.state.messages);

      messages.unshift({
        message: event.message,
        type: 'global-message',
        id: Math.random().toString(36).substring(7),
      });

      this.setState({
        messages: messages,
      }, () => {
        if (!this.isGod(event.user)) {
          this.props.updateChatTabIcon(false);
        }
      });
    });

    this.serverMessages.listen('Game.Messages.Events.ServerMessageEvent', (event) => {
      if (event.npc) {
        const messages = cloneDeep(this.state.messages);

        const message = {
          message: event.message,
          type: 'server-message',
          user: event.user,
          user_id: event.user.id,
          id: Math.random().toString(36).substring(7),
          is_npc: event.npc,
          isLink: event.isLink,
          link: event.link,
          event_id: event.id,
        };

        messages.unshift(message);

        if (messages.length > 1000) {
          messages.length = 500; // Remove the last 500 messages to clear lag.
        }

        const user = cloneDeep(this.state.user);

        user.is_silenced = event.user.is_silenced;
        user.can_talk_again_at = event.user.can_speak_again_at;

        this.setState({
          messages: messages,
          user: user,
        }, () => {

          this.props.updateChatTabIcon(false);
        });
      }
    });

    this.privateMessages.listen('Game.Messages.Events.PrivateMessageEvent', (event) => {
      const messages = cloneDeep(this.state.messages);
      const message = {
        message: event.message,
        type: 'private-message',
        user: event.user,
        user_id: event.user.id,
        from: event.from,
        id: Math.random().toString(36).substring(7),
      };

      messages.unshift(message);

      this.setState({
        messages: messages
      }, () => {
        this.props.updateChatTabIcon(false);
      });
    });
  }

  isGod(user) {
    if (typeof user === 'undefined') {
      return false;
    }

    if (!user.hasOwnProperty('roles')) {
      return false;
    }

    if (isEmpty(user.roles)) {
      return false;
    }

    return user.roles.filter(r => r.name === 'Admin').length > 0
  }

  buildErrorMessage(customMessage) {
    const messages = cloneDeep(this.state.messages);

    const message = {
      message: customMessage,
      type: 'error-message',
      user_id: this.props.userId,
      id: Math.random().toString(36).substring(7),
    };

    messages.unshift(message);

    this.setState({
      messages: messages,
    });
  }

  componentWillUnMount() {
    Echo.leave('chat');
  }

  messageUser(e) {
    const name = e.target.getAttribute('data-name');

    this.setState({
      message: '/m ' + name + ': '
    }, () => {
      this.chatInput.focus();
    });
  }

  fetchLocationInfo(message) {
    return (
      <span>[{message.map} | {message.x}/{message.y}]</span>
    );
  }

  renderMessages() {
    const elements = [];

    if (this.state.messages.length > 0) {

      this.state.messages.map((message) => {
        if (message.user_id === this.props.userId && message.type === 'server-message') {
          if (message.is_npc) {
            elements.push(
              <li key={message.id + '_server-message'}>
                <div className="npc-message">{message.message}</div>
              </li>
            )
          }
        } else if(message.type === 'global-message') {
          elements.push(
            <li key={message.id + '_global-message'}>
              <div className="global-message">
                {message.message}
              </div>
            </li>
          )
        } else if (message.user_id === this.props.userId && message.type === 'private-message') {
          elements.push(
            <li key={message.id + '_private-message'}>
              <div className="private-message">
                <strong onClick={this.messageUser.bind(this)}
                        data-name={message.from}>{message.from}</strong>: {message.message}
              </div>
            </li>
          )
        } else if (message.user_id === this.props.userId && message.type === 'drop-message') {
          elements.push(
            <li key={message.id + 'drop-message'}>
              <div className="drop-message">{message.message}</div>
            </li>
          )
        } else if (message.user_id === this.props.userId && message.type === 'error-message') {
          elements.push(
            <li key={message.id + 'error-message'}>
              <div className="error-message">{message.message}</div>
            </li>
          )
        } else if (message.from_god) {
          elements.push(
            <li key={message.id + '_god-message'}>
              <div className="god-message">
                <div className="god-message"><strong>The Creator</strong>: {message.message}</div>
              </div>
            </li>
          )
        } else if (message.type === 'private-message-sent') {
          elements.push(
            <li key={message.id + '_private-message-sent'}>
              <div className="private-message-sent">
                <div className="private-message-sent">{message.message}</div>
              </div>
            </li>
          )
        } else {
          elements.push(
            <li key={message.id}>
              <div className="message" style={{'color': message.color}}>
                {message.x !== null && message.y !== null ? this.fetchLocationInfo(message) : null} <strong
                onClick={this.messageUser.bind(this)}
                data-name={message.name}>{message.name}</strong>: {message.message}
              </div>
            </li>
          )
        }
      });
    }

    return elements;
  }

  postMessage() {
    if (this.state.user.is_silenced) {
      this.setState({
        message: ''
      });

      const dt = DateTime.fromISO(this.state.user.can_talk_again_at).toLocaleString(DateTime.TIME_WITH_SHORT_OFFSET);

      return this.buildErrorMessage('You cannot talk again until: ' + dt);
    }

    const message = this.state.message.replace(/(<([^>]+)>)/ig, "");

    this.setState({
      message: ''
    });

    axios.post('api/public-message', {
      message: message
    }).catch((error) => {
      if (error.hasOwnProperty('response')) {
        const response = error.response;

        if (response.status === 429) {
          getServerMessage('chatting_to_much');
        }

        if (response.status === 401) {
          location.reload();
        }
      }
    });
  }

  postPrivateMessage() {
    if (this.state.user.is_silenced) {
      this.setState({
        message: ''
      });

      return this.buildErrorMessage('You cannot talk again until: ' + this.state.user.can_talk_again_at);
    }

    const messageData = this.state.message.match(/^\/m\s+(\w+[\w| ]*):\s*(.*)/)

    if (messageData == null) {
      getServerMessage('invalid_command');
      return;
    }

    const message = {
      message: 'Sent to ' + messageData[1] + ': ' + messageData[2],
      type: 'private-message-sent',
      id: Math.random().toString(36).substring(7),
    }

    const messages = cloneDeep(this.state.messages);

    messages.unshift(message);

    this.setState({
      messages: messages,
      message: '',
    });

    axios.post('/api/private-message', {
      user_name: messageData[1],
      message: messageData[2],
    }).catch((error) => {
      if (error.hasOwnProperty('response')) {
        const response = error.response;

        if (response.status === 429) {
          getServerMessage('chatting_to_much');
        }

        if (response.status === 401) {
          location.reload();
        }
      }
    });
  }

  publicEntity(teleportTo) {
    if (this.state.user.is_silenced) {
      return this.buildErrorMessage('You cannot talk again until: ' + this.state.user.can_talk_again_at);
    }

    this.setState({
      message: ''
    });

    axios.post('/api/public-entity/', {
      attempt_to_teleport: teleportTo,
    }).catch((err) => {
      if (err.hasOwnProperty('response')) {
        const response = err.response;

        if (response.status === 401) {
          return location.reload;
        }
      }
    });
  }



  handleKeyPress(e) {
    if (e.key === 'Enter') {
      e.target.value = '';

      if (this.state.message.length < 1) {
        getServerMessage('message_length_0');
      } else if (this.state.message.length > 140) {
        getServerMessage('message_length_max');
      } else {
        this.handleChat();
      }
    }
  }

  handleOnClick() {
    if (this.state.message.length < 1) {
      getServerMessage('message_length_0');
    } else if (this.state.message.length > 140) {
      getServerMessage('message_length_max');
    } else {
      this.handleChat();
    }
  }

  handleChat() {
    const celestialCommand = this.state.message.includes('/pc') || this.state.message.includes('/pct');

    if (celestialCommand) {
      this.publicEntity(this.state.message.includes('/pct'))
    } else if (this.state.message.includes('/m')) {
      this.postPrivateMessage()
    } else {
      this.postMessage();
    }
  }

  handleOnChange(e) {
    this.setState({
      message: e.target.value
    });
  }

  render() {
    return (
      <div className="card p-2 pr-4">
        <div className="card-body">
          <div className="chat">
            <div className="row">
              <div className="col-md-11">
                <input
                  type="text"
                  className="form-control input-sm"
                  value={this.state.message}
                  onChange={this.handleOnChange.bind(this)}
                  onKeyPress={this.handleKeyPress.bind(this)}
                  ref={(input) => {
                    this.chatInput = input;
                  }}
                />
              </div>

              <div className="col-md-1 message-button">
                <button className="btn btn-primary" onClick={this.handleOnClick.bind(this)}>Send</button>
              </div>
            </div>

            <div className="row">
              <div className="col-md-12">
                <div className="chat-box mt-3">
                  <ul> {this.renderMessages()}</ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    )

    return null;
  }
}
