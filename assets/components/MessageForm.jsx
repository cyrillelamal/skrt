import React from 'react';
import axios from 'axios';

export class MessageForm extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            body: '',
        };

        this.textareaRef = React.createRef();

        this.handleSubmit = this.handleSubmit.bind(this);
        this.handleChange = this.handleChange.bind(this);
    }

    handleSubmit(event) {
        event.preventDefault();

        if (this.state.body.trim() === '') {
            return null;
        }

        axios.post('/api/messages/', {
            conversation_id: this.props.conversationId,
            body: this.state.body,
        })
            .then(res => this.props.appendMessage(res.data))
            .catch(err => console.error(err));

        this.setState({body: ''});
        this.textareaRef.current.focus();
    }

    handleChange(event) {
        this.setState({body: event.target.value});
    }

    render() {
        return (
            <article className="media">
                <div className="media-content">
                    <form onSubmit={this.handleSubmit}>
                        <div className="field">
                            <p className="control">
                                <textarea
                                    name="body" id="body"
                                    className="textarea" rows="3"
                                    value={this.state.body}
                                    onChange={this.handleChange}
                                    ref={this.textareaRef}
                                    placeholder="Write a message..."
                                />
                            </p>
                        </div>
                        <div className="field">
                            <div className="control">
                                <button type="submit" className="button is-primary">Send</button>
                            </div>
                        </div>
                    </form>
                </div>
            </article>
        );
    }
}
