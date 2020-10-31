import React from 'react';
import moment from 'moment';

export class ConversationPreview extends React.Component {
    constructor(props) {
        super(props);

        const {conversation} = props;

        this.state = {
            id: conversation.id,
            updated_at: conversation.updated_at,
            empty: conversation.empty,
            title: conversation.title,
            messages: conversation.messages,
        };

        this.handleClick = this.handleClick.bind(this);
    }

    handleClick(event) {
        event.preventDefault();

        this.props.setCurConversation(this.state.id);
    }

    render() {
        const updatedAt = moment(this.state.updated_at).format('DD/MM/YYYY HH:mm:ss');

        return (
            <article className="media m-0 p-0" onClick={this.handleClick}>
                <div className="media-content p-3">
                    <div className="content">
                        <p>
                            <strong>{this.state.title}</strong> <small>{updatedAt}</small><br/>{this.state.empty ? (
                            <>Empty</>
                        ) : (
                            <>{this.state.messages[0].body}</>
                        )}
                        </p>
                    </div>
                </div>
            </article>
        );
    }
}
