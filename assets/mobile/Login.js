import React, {Component} from 'react';
import { Redirect } from 'react-router-dom';

import { createForm } from 'rc-form';

import { List, InputItem, Toast, Button, WhiteSpace } from 'antd-mobile';

import { withTranslation } from 'react-i18next';
import axios from 'axios';


class Login extends Component {
    constructor(props){
        super(props);
        this.login = this.login.bind(this);
        this.state = {
            errors: {
                _username: false,
                _password: false
            }
        };
    }

    errorsToast(errors) {
        return  <React.Fragment>{errors.map(error => {return <div key={error}>{error}</div>})}</React.Fragment>
    }

    login() {
        this.props.form.validateFields((error) => {
            if (!error) {
                var params = new URLSearchParams(),
                    values = this.props.form.getFieldsValue();
                Object.keys(values).map(key => {
                     params.append(key, values[key]);
                });
                axios({url: '/login_check',
                    method: 'post',
                    data: params,
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }).then(res => {
                    if (res.data.success) {
                        this.setState({redirect: res.data.redirect})
                    } else {
                        Toast.fail(this.props.t(res.data.error));
                    }
                }).catch(error => {
                    Toast.fail(error.toString());
                });
            } else {
                this.setState(state => {
                    var errorText = [];
                    Object.keys(state.errors).map(key => {
                        state.errors[key] = false;
                    });
                    Object.keys(error).map(field => {
                        state.errors[field] = true;
                        error[field].errors.map(i => {
                            errorText.push(i.message);
                        })
                    });
                    Toast.fail(this.errorsToast(errorText));
                    return state;
                });
            }
        });
    };
    
    render() {
        return (
            this.state.redirect ? (
                <Redirect to={this.state.redirect}/>
            ) : (
                <div>
                    <WhiteSpace size="lg"/>
                    <List>
                        <InputItem
                           {...this.props.form.getFieldProps(
                                '_username',
                                {
                                    rules: [
                                        {
                                            required: true,
                                            message: this.props.t('login.errors.login_message')
                                        }
                                    ]
                                }
                            )}
                            type="text"
                           placeholder={this.props.t('login.errors.login_message')}
                           error={this.state.errors._username}
                           clear>{this.props.t('login._')}</InputItem>
                        <InputItem
                           {...this.props.form.getFieldProps(
                                '_password',
                                {
                                    rules: [
                                        {
                                            required: true,
                                            message: this.props.t('login.errors.password_message')
                                        }
                                    ]
                                }
                            )}
                           type="password"
                           error={this.state.errors._password}
                           placeholder={this.props.t('login.errors.password_message')}
                           onKeyDown={(e) => {if (e.key == 'Enter'){this.login()}}}
                           clear>{this.props.t('login.password')}</InputItem>
                        <List.Item>
                            <Button type="primary" size="small" inline onClick={this.login}>{this.props.t('login._')}</Button>
                        </List.Item>
                    </List>
                </div>
            )
        )
    }
}

export default withTranslation()(createForm()(Login));