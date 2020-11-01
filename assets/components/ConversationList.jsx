import React from 'react';
import {ConversationPreview} from "./ConversationPreview";

export class ConversationList extends React.Component {
    render() {
        return (
            <div className="block">
                <nav className="panel">
                    <p className="panel-heading">
                        Your conversations
                    </p>{
                    this.props.conversations.map(conversation => (
                        <div key={conversation.id} className="panel-block p-0">
                            <ConversationPreview
                                setConversation={this.props.setConversation}
                                {...conversation}
                            />
                        </div>
                    ))
                }
                </nav>
            </div>
        );
    }
}
