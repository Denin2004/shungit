import React, {Component} from 'react';
import { Switch, Route, withRouter } from 'react-router-dom';

import { Toast, ActivityIndicator, Flex } from 'antd-mobile';

import axios from 'axios';
import { withTranslation } from 'react-i18next';


class Main extends Component {
    constructor(props){
        super(props);
        this.state = {
            loading: true
        }
    }

    componentDidMount() {
        axios.get(
            '/config',
            {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }
        ).then(res => {
            window.mfwApp.urls = JSON.parse(res.data.urls);
            if (res.data.success) {
                this.setState({
                    loading: false
                });
            } else {
                Toast.fail(this.props.t(res.data.error));
            }
        }).catch(error => {
            if (error.response) {
                Toast.fail(window.mfwApp.axiosError(error.response));
            } else {
                Toast.fail(error.toString());
            }
        });
    }

    render() {
        return (
            this.state.loading ? (
            <div className="mfw-all-page">
                <Flex align="center" justify="center" className="mfw-h100" alignContent="center">
                    <ActivityIndicator
                      size="large"
                      text={this.props.t('common.loading')}/>
                </Flex>
            </div>
            ) : (
                <div>Main Mobile</div>
            )
        )
    }
}

export default withRouter(withTranslation()(Main));