import React from 'react';
import moment from 'moment';
import {MessageForm} from "./MessageForm";

export class Conversation extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            messages: [],
        };
    }

    getIntendClass(userId) {
        return Number(userId) === Number(localStorage.getItem('userId'))
            ? 'pl-5'
            : 'pr-5';
    }

    // addMessage(message) {
    //     this
    // }

    render() {
        if (!this.props.conversation) {
            return (
                <h1 className="title p-5">Start messaging</h1>
            );
        }

        const conversation = this.props.conversation;
        const messages = conversation.messages;

        return (
            <div className="block p-3">
                <h2 className="subtitle">{conversation.title}</h2>
                <hr/>
                {messages.map(message => (
                    <article key={message.id} className={`media ${this.getIntendClass(message.creator.id)}`}>
                        <div className="media-content">
                            <div className="content">
                                <p>
                                    <strong>{message.creator.username}</strong>
                                    <small>{moment(message.created_at).format('DD/MM/YYYY HH:mm:ss')}</small>
                                    <br/>
                                    {message.body}
                                </p>
                            </div>
                        </div>
                    </article>
                ))}
                <article className="media">
                    <div className="media-content">
                        <MessageForm
                            conversationId={this.props.conversation.id}
                            // addMessage={this.addMessage}
                        />
                    </div>
                </article>
            </div>
        );
    }
}
