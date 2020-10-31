import React from 'react';
import axios from 'axios';

export class MessageForm extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            body: '',
        };

        this.handleSubmit = this.handleSubmit.bind(this);
        this.handleChange = this.handleChange.bind(this);
    }

    handleSubmit(event) {
        event.preventDefault();

        axios.post('/api/messages/', {
            conversation_id: this.props.conversationId,
            body: this.state.body,
        })
            .then(res => console.log(res))
            .catch(err => console.error(err));

        this.setState({body: ''});
    }

    handleChange(event) {
        this.setState({body: event.target.value});
    }

    render() {
        return (
            <form onSubmit={this.handleSubmit}>
                <div className="field">
                    <p className="control">
                        <textarea
                            name="body" id="body"
                            className="textarea"
                            value={this.state.body}
                            onChange={this.handleChange}
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
        );
    }
}
