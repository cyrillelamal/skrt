import React from 'react';
import axios from 'axios';
import {UserSearch} from "./UserSearch";
import {Conversation} from './Conversation';

export class Dashboard extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            username: '', // Search
            conversations: [],
        };

        this.setUsername = this.setUsername.bind(this);
    }

    componentDidMount() {
        axios.get('/api/conversations')
            .then(res => this.setState({conversations: res.data}))
            .catch(err => {
                console.error(err);
            });
    }

    setUsername(username) {
        this.setState({username});

        this.initiateConversation([username]);
    }

    initiateConversation(usernames) {
        axios.post('/api/conversations/', {usernames})
            .then(res => {
                this.setState(curState => {
                    return {
                        conversations: [res.data, ...curState.conversations]
                    };
                });
            })
            .catch(err => {
                console.error(err);
            })
    }

    render() {
        return (
            <main className="section">
                <div className="container is-fluid">
                    <div className="tile is-ancestor">
                        <div className="tile is-4 is-vertical is-parent">
                            <div className="tile is-child box">
                                <UserSearch setUsername={this.setUsername}/>
                            </div>
                            <div className="tile is-child box">{this.state.conversations.map(conversation => (
                                <Conversation key={conversation.id} conversation={conversation}/>
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
