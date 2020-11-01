import React from 'react';
import {MessageForm} from "./MessageForm";
import {Message} from "./Message";

export class Conversation extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            messages: [],
        };

        this.appendMessage = this.appendMessage.bind(this);
    }

    appendMessage(message) {
        this.setState(state => {
            const messages = state.messages.length === 0
                ? [...this.props.messages, message]
                : [...state.messages, message];

            return {messages};
        });
    }

    render() {
        if (Object.keys(this.props) < 1) {
            return (
                <h1 className="title">Start messaging</h1>
            );
        }

        const messages = this.state.messages.length === 0
            ? this.props.messages
            : this.state.messages;

        return (
            <div className="block">
                <h2 className="subtitle">
                    {this.props.title}
                </h2>{messages.map(message => (
                <Message
                    key={message.id}
                    {...message}
                />
            ))}
                <hr/>
                <MessageForm
                    conversationId={this.props.id}
                    appendMessage={this.appendMessage}
                />
            </div>
        );
    }
}
