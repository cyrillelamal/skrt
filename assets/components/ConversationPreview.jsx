import React from 'react';
import moment from 'moment';

// noinspection JSUnresolvedVariable
export class ConversationPreview extends React.Component {
    constructor(props) {
        super(props);

        this.handleClick = this.handleClick.bind(this);
    }

    handleClick(event) {
        event.preventDefault();

        this.props.setConversation(this.props.id);
    }

    render() {
        const updatedAt = moment(this.props.updated_at).format('DD/MM/YYYY HH:mm:ss');

        return (
            <article className="media has-grab-cursor" onClick={this.handleClick}>
                <div className="media-content p-3">
                    <div className="content">
                        <p>
                            <strong>{this.props.title}</strong>&nbsp;
                            <small>{updatedAt}</small><br/>{this.props.messages.length === 0 ? (
                            <>Empty</>
                        ) : (
                            <>{this.props.messages[0].body}</>
                        )}
                        </p>
                    </div>
                </div>
            </article>
        );
    }
}
