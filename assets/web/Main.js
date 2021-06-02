import React, {Component} from 'react';
import { Switch, Route, Redirect, withRouter } from 'react-router-dom';

import { Spin, message } from 'antd';

import axios from 'axios';

import Demands from '@app/web/Demands';

class Main extends Component {
    constructor(props){
        super(props);
        this.state = {loading: true};
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
            if (res.data.success) {
                window.mfwApp.urls = JSON.parse(res.data.urls);
                this.setState({
                    loading: false
                });
            } else {
                message.error(this.props.t(res.data.error));
                this.setState({
                    loading: false
                });
            }
        }).catch(error => {
            if (error.response) {
                this.setState({
                    loading: false,
                    errorCode: error.response.status
                });
            } else {
                message.error(error.toString());
                this.setState({
                    loading: false
                });
            }
        });
    }
    
    render() {
        return this.state.loading === true ? (<Spin/>) : (
        <React.Fragment>
            <Switch>
                <Route path="/" component={Demands} />
                <Route path="/demands" component={Demands} />
            </Switch>
        </React.Fragment>
        )
    }
}

export default withRouter(Main);