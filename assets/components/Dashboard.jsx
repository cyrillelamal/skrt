import React from 'react';
import axios from 'axios';
import {UserSearch} from "./UserSearch";
import {ConversationPreview} from './ConversationPreview';

export class Dashboard extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            username: '', // Search
            conversations: [],
            curConversation: null,
        };

        this.setUsername = this.setUsername.bind(this);
        this.setCurConversation = this.setCurConversation.bind(this);
    }

    componentDidMount() {
        axios.get('/api/conversations')
            .then(res => this.setState({conversations: res.data}))
            .catch(err => console.error(err));
    }

    setUsername(username) {
        this.setState({username});

        this.initiateConversation([username]);
    }

    setCurConversation(id) {
        this.setState(curState => {
            if (curState.curConversation === id) {
                return {};
            } else {
                axios.get('/api/messages', {
                    params: {
                        conversation_id: id,
                    }
                }).then(res => {
                    console.log(res, res.data)
                }).catch(err => {
                    console.error(err)
                })
                
                return {
                    curConversation: id
                }
            }
        });
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

    render() {
        console.log(this.state)

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
                                    setCurConversation={this.setCurConversation}
                                />
                            ))}
                            </div>
                        </div>
                        <div className="tile is-parent">
                            <div className="tile is-child box">
                                messages
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        );
    }
}
