import React from 'react';
import axios from 'axios';
import {UserSearch} from "./UserSearch";
import {ConversationPreview} from './ConversationPreview';
import {Conversation} from "./Conversation";

export class Dashboard extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            username: '', // Search
            conversations: [],
            conversation: null,
        };

        this.setUsername = this.setUsername.bind(this);
        this.setConversation = this.setConversation.bind(this);
    }

    componentDidMount() {
        axios.get('/api/conversations')
            .then(res => this.setState({conversations: res.data}))
            .catch(err => console.error(err));

        axios.get('/api/users/reflect')
            .then(res => localStorage.setItem('userId', res.data.id))
            .catch(() => localStorage.setItem('userId', null));
    }

    setUsername(username) {
        this.setState({username});

        this.initiateConversation([username]);
    }

    initiateConversation(usernames) {
        axios.post('/api/conversations/', {usernames})
            .then(res => {
                this.setState(curState => {
                    return {conversations: [res.data, ...curState.conversations]};
                });
            })
            .catch(err => console.error(err));
    }

    setConversation(id) {
        this.setState(curState => {
            if (curState.conversation && curState.conversation.id === id) {
                return {};
            } else {
                axios.get(`/api/conversations/${id}`)
                    .then(res => this.setState({conversation: res.data}))
                    .catch(err => console.log(err));

                return {curConversationId: id};
            }
        });
    }

    render() {
        return (
            <main className="section">
                <div className="container is-fluid">
                    <div className="tile is-ancestor">
                        <div className="tile is-4 is-vertical is-parent">
                            <div className="tile is-child box">
                                <UserSearch
                                    setUsername={this.setUsername}
                                />
                            </div>
                            <div className="tile is-child box p-0">{this.state.conversations.map(conversation => (
                                <ConversationPreview
                                    key={conversation.id}
                                    conversation={conversation}
                                    setConversation={this.setConversation}
                                />
                            ))}
                            </div>
                        </div>
                        <div className="tile is-parent">
                            <div className="tile is-child box p-0">
                                <Conversation
                                    conversation={this.state.conversation}
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        );
    }
}
