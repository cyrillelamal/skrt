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
        this.fetchConversation = this.fetchConversation.bind(this);
        this.prependMessage = this.prependMessage.bind(this);

        this.eventSource = null;
        this.audioLink = '';
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

        this.audioLink = document.getElementById('audio-notification').dataset.link;
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
                const conversation = Object.assign({}, data);
                conversation.messages = [...data.messages, ...state.conversation.messages];

                Object.assign(newState, {conversation});
            }

            const audio = new Audio(this.audioLink);
            audio.play();

            return newState;
        });
    }

    prependMessage(message) {
        this.setState(state => {
            const conversation = Object.assign({}, state.conversation);
            conversation.messages = [message, ...state.conversation.messages];

            const offset = 1 + Number(sessionStorage.getItem('conversationOffset'));
            sessionStorage.setItem('conversationOffset', String(offset));

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

    fetchConversation(id, offset = 0, limit = 10) {
        sessionStorage.setItem('conversationOffset', String(offset));

        axios.get(`/api/conversations/${id}`, {
            params: {offset, limit}
        }).then(res => {
            this.setState(state => {
                if (offset === 0) {
                    return {conversation: res.data};
                }

                const conversation = Object.assign({}, state.conversation);
                conversation.messages = [...state.conversation.messages, ...res.data.messages];

                return {conversation};
            });
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
                                appendMessage={this.prependMessage}
                                fetchCOnversation={this.fetchConversation}
                                {...this.state.conversation}
                            />
                        </div>
                    </div>
                </div>
            </main>
        );
    }
}
