import React, {Component} from 'react';
import { Redirect } from 'react-router-dom';
import {Form, Button, Input, Modal, message} from 'antd';
import { withTranslation } from 'react-i18next';
import axios from 'axios';

class Login extends Component {
    constructor(props){
        super(props);
        this.login = this.login.bind(this);
        this.state = {}
    }
    
    login = (values) => {
        var params = new URLSearchParams();
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
                message.error(this.props.t(res.data.error));
            }
        }).catch(error => {
            message.error(error.toString());
        });
    };    

    render() {
        return (
            this.state.redirect ? (
                <Redirect to={this.state.redirect}/>
            ) : (
                <Modal visible={true} closable={false} footer={null}>
                    <Form labelCol={{span: 8 }}
                          wrapperCol={{ span: 16 }}
                          name="basic"
                          onFinish={this.login}>
                        <Form.Item label={this.props.t('login._')}
                          name="_username"
                          rules={[{ required: true, message: this.props.t('login.errors.login_message')}]}>
                            <Input />
                        </Form.Item>
                        <Form.Item label={this.props.t('login.password')}
                           name="_password"
                           rules={[{ required: true, message: this.props.t('login.errors.password_message')}]}>
                            <Input.Password/>
                        </Form.Item>
                        <Form.Item wrapperCol={{ offset: 8, span: 16 }}>
                            <Button type="primary" htmlType="submit">{this.props.t('login._')}</Button>
                        </Form.Item>
                    </Form> 
                </Modal>
            )
        )
    }
}

export default withTranslation()(Login);