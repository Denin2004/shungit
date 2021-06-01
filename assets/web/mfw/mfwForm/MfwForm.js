import React, {Component} from 'react';

import { Form, message } from 'antd';

import axios from 'axios';
import { withTranslation } from 'react-i18next';

import MfwFormWidget from '@app/mfw/mfwForm/MfwFormWidget';

class MfwForm extends Component {
    constructor(props){
        super(props);
        this.closeError = this.closeError.bind(this);
        this.finish = this.finish.bind(this);
        var parsed = this.props.parsed ? this.props.parsed : [];
        React.Children.toArray(this.props.children).map((child) => {
            this.findWidget(child, parsed);
        });
        this.state = {
            parsed: parsed
        }
    }

    closeError() {
        this.setState({error: ''});
    }

    finish(values) {
        axios({
            method: this.props.mfwForm.method,
            url: this.props.mfwForm.action,
            data: values
        }).then(res => {
            if (res.data.success) {
                this.props.success(res.data);
            } else {
                message.error(this.props.t(res.data.error));
            }
        }).catch(error => {
            message.error(error.toString());
        });
    }

    findWidget(child, parsed) {
        const children = React.Children.toArray(child.props ? (child.props.children ? child.props.children : []) : []);
        if ((child.props)&&(child.props.element)&&(child.props.element.id)) {
            parsed.push(child.props.element.id);
        }
        children.map(ch => {
            this.findWidget(ch, parsed);
        });
    }

    render() {
        return (
            <Form {...this.props.formProps} onFinish={this.finish}>
                {this.props.children}
                {Object.keys(this.props.mfwForm.elements).map(key => {
                    if (this.state.parsed.indexOf(this.props.mfwForm.elements[key].id) === -1) {
                        return (
                            <MfwFormWidget key={key} element={this.props.mfwForm.elements[key]}/>
                        )
                    }
                })}
            </Form>
        )
    }
}

export default withTranslation()(MfwForm);