import React from 'react';
import axios from 'axios';
import {UserSearch} from "./UserSearch";
import {Conversation} from "./Conversation";
import {ConversationList} from "./ConversationList";

export class Dashboard extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            conversations: [],
            conversation: {},
        };

        this.initiateConversation = this.initiateConversation.bind(this);
        this.setConversation = this.setConversation.bind(this);
        this.appendMessage = this.appendMessage.bind(this);

        this.eventSource = null;
    }

    componentDidMount() {
        axios.get('/api/conversations')
            .then(res => this.setState({conversations: res.data}))
            .catch(err => console.error(err));

        axios.get('/api/discovery')
            .then(res => {
                this.subscribeToMercure(res.data);
                sessionStorage.setItem('userId', res.data.id)
            })
            .catch(() => localStorage.setItem('userId', null));
    }

    componentWillUnmount() {
        if (this.eventSource) {
            this.eventSource.close();
        }
    }

    subscribeToMercure({mercure_publish_url, topic}) {
        const url = new URL(mercure_publish_url);
        url.searchParams.append('topic', topic);

        this.eventSource = new EventSource(url.toString(), {withCredentials: true});
        this.eventSource.onmessage = message => this.handleMercureMessage(message);
    }

    handleMercureMessage(message) {
        const data = JSON.parse(message.data);

        this.setState(state => {
            const conversations = state.conversations.map(c => c.id === data.id ? data : c.id);

            let newState = {conversations};

            if (state.conversation.id === data.id) {
                const messages = [...state.conversation.messages, ...data.messages];

                const conversation = Object.assign({}, data);
                conversation.messages = messages;

                Object.assign(newState, {conversation});
            }

            return newState;
        });
    }

    appendMessage(message) {
        this.setState(state => {
            const messages = [...state.conversation.messages, message];

            const conversation = Object.assign({}, state.conversation);
            conversation.messages = messages;

            return {conversation};
        });
    }

    initiateConversation(usernames) {
        axios.post('/api/conversations/', {usernames})
            .then(res => {
                this.setState(state => {
                    return {conversations: [res.data, ...state.conversations]};
                });
            })
            .catch(err => console.error(err));
    }

    setConversation(id) {
        this.setState(state => {
            if (state.conversation.id !== id) {
                this.fetchConversation(id);
            }

            return {};
        });
    }

    fetchConversation(id, offset = 0, limit = 25) {
        axios.get(`/api/conversations/${id}`, {
            params: {offset, limit}
        }).then(res => {
            this.setState({conversation: res.data});
        }).catch(reason => {
            console.error(reason);
        });
    }

    render() {
        return (
            <main className="section">
                <div className="container is-fluid">
                    <div className="columns">
                        <div className="column is-4">
                            <UserSearch
                                initiateConversation={this.initiateConversation}
                            />
                            <ConversationList
                                setConversation={this.setConversation}
                                conversations={this.state.conversations}
                            />
                        </div>
                        <div className="column is-8">
                            <Conversation
                                appendMessage={this.appendMessage}
                                {...this.state.conversation}
                            />
                        </div>
                    </div>
                </div>
            </main>
        );
    }
}
