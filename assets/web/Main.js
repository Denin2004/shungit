import React, {Component} from 'react';
import { withTranslation } from 'react-i18next';

import { message, Button } from 'antd';

import axios from 'axios';

class Main extends Component {
    constructor(props){
        super(props);
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
    
    test() {
        console.log(window.mfwApp);
        axios.get(
            window.mfwApp.urls.test,
            {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }
        ).then(res => {
            if (res.data.success) {
            } else {
                message.error(this.props.t(res.data.error));
            }
        }).catch(error => {
            message.error(error.toString());
        });
    }
    
    render() {
        return (
        <React.Fragment>
            <Button onClick={this.test} >Test</Button>
        </React.Fragment>
        )
    }
}

export default withTranslation()(Main);