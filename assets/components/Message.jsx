import React from 'react';
import moment from 'moment';

// noinspection JSUnresolvedVariable
export class Message extends React.Component {
    getPaddingClass() {
        return Number(localStorage.getItem('userId')) === this.props.creator.id
            ? 'pl-6'
            : 'pr-6';
    }

    render() {
        const createdAt = moment(this.props.created_at).format('YYYY/MM/DD HH:mm:ss');

        return (
            <article className={`media ${this.getPaddingClass()}`}>
                <div className="media-content">
                    <div className="content">
                        <p>
                            <strong>{this.props.creator.username}</strong>&nbsp;
                            <small>{createdAt}</small>
                            <br/>
                            {this.props.body}
                        </p>
                    </div>
                </div>
            </article>
        );
    }
}
