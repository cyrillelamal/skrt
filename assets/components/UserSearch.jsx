import React from 'react';
import axios from 'axios';

export class UserSearch extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            username: '',
            inputClass: 'is-info',
            users: [],
            hideUsers: false,
        };

        this.inputRef = React.createRef(); // Focus after selection from the list of users.

        this.handleSubmit = this.handleSubmit.bind(this);
        this.handleChange = this.handleChange.bind(this);
        this.showUsers = this.showUsers.bind(this);
        this.hideUsers = this.hideUsers.bind(this);
        this.selectUser = this.selectUser.bind(this);

        this.timeout = null;
    }

    componentWillUnmount() {
        clearTimeout(this.timeout);
    }

    handleSubmit(event) {
        event.preventDefault();

        this.props.setUsername(this.state.username);
    }

    handleChange(event) {
        const username = event.target.value;

        if (this.timeout) {
            clearTimeout(this.timeout);
        }

        this.timeout = setTimeout(() => this.loadUsers(username), 510);

        const inputClass = username.length < 4 ? 'is-info' : 'is-success';

        this.setState({username, inputClass});
    }

    loadUsers(username) {
        if (username.length > 3) {
            axios.get(`/api/users/`, {
                params: {username}
            })
                .then(res => this.setState({users: res.data, hideUsers: false}))
                .catch(() => this.setState({users: [], inputClass: 'is-danger'}));
        }
    }

    showUsers() {
        this.setState({hideUsers: false});
    }

    hideUsers(event) {
        event.preventDefault();

        this.setState({hideUsers: true});
    }

    selectUser(event) {
        event.preventDefault();

        const username = event.target.text.trim();

        this.props.setUsername(username);

        this.setState({username, hideUsers: true});

        this.inputRef.current.focus();
    }

    render() {
        return (
            <div className="block">
                <form onSubmit={this.handleSubmit}>
                    <input
                        ref={this.inputRef}
                        value={this.state.username}
                        onChange={this.handleChange}
                        onClick={this.showUsers}
                        className={`input ${this.state.inputClass} is-expanded`}
                        type="text" placeholder="Find a user"
                    />
                </form>
                <nav className="panel">
                    {!this.state.hideUsers && this.state.users.map(user => (
                        <a href="#" key={user.id} className="panel-block" onClick={this.selectUser}>{user.username}</a>
                    ))}
                    {!this.state.hideUsers && this.state.users.length > 0 && (
                        <div className="panel-block">
                            <button onClick={this.hideUsers} className="button is-link is-outlined is-fullwidth">Hide
                            </button>
                        </div>
                    )}
                </nav>
            </div>
        );
    }
}
