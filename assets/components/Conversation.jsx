import React from 'react';
import {MessageForm} from "./MessageForm";
import {Message} from "./Message";

// noinspection JSUnresolvedVariable
export class Conversation extends React.Component {
    render() {
        if (!this.props.messages) {
            return (
                <h1 className="title">Start messaging</h1>
            );
        }

        return (
            <div className="block">
                <h2 className="subtitle">
                    {this.props.title}
                </h2>{this.props.messages.map(message => (
                <Message
                    key={message.id}
                    {...message}
                />
            ))}
                <hr/>
                <MessageForm
                    conversationId={this.props.id}
                    appendMessage={this.props.appendMessage}
                />
            </div>
        );
    }
}
