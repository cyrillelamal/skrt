import React from 'react';
import moment from 'moment';

export class ConversationPreview extends React.Component {
    constructor(props) {
        super(props);

        const {conversation} = props;

        this.conversation = conversation;

        this.handleClick = this.handleClick.bind(this);
    }

    handleClick(event) {
        event.preventDefault();

        this.props.setConversation(this.conversation.id);
    }

    render() {
        const updatedAt = moment(this.conversation.updated_at).format('DD/MM/YYYY HH:mm:ss');

        return (
            <article className="media m-0 p-0 has-grab-cursor" onClick={this.handleClick}>
                <div className="media-content p-3">
                    <div className="content">
                        <p>
                            <strong>{this.conversation.title}</strong>
                            <small>{updatedAt}</small><br/>{this.conversation.empty ? (
                            <>Empty</>
                        ) : (
                            <>{this.conversation.messages[0].body}</>
                        )}
                        </p>
                    </div>
                </div>
            </article>
        );
    }
}
