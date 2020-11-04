import React from 'react';
import {MessageForm} from "./MessageForm";
import {Message} from "./Message";

// noinspection JSUnresolvedVariable
export class Conversation extends React.Component {
    constructor(props) {
        super(props);

        this.handleScroll = this.handleScroll.bind(this);
    }

    componentDidMount() {
        window.addEventListener('scroll', this.handleScroll);
    }

    componentWillUnmount() {
        window.removeEventListener('scroll', this.handleScroll);
    }

    handleScroll() {
        if ((window.innerHeight + window.pageYOffset) >= document.body.offsetHeight) {
            const limit = 10;
            const offset = Number(sessionStorage.getItem('conversationOffset')) + limit; // + limit

            this.props.fetchCOnversation(this.props.id, offset, limit);
        }
    }

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
                </h2>
                <hr/>
                <MessageForm
                    conversationId={this.props.id}
                    appendMessage={this.props.appendMessage}
                />
                {this.props.messages.map(message => (
                    <Message
                        key={message.id}
                        {...message}
                    />
                ))}
            </div>
        );
    }
}
