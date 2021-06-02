import React, {Component} from 'react';
import { withTranslation } from 'react-i18next';

import axios from 'axios';

import { Table, message } from 'antd';

class Demands extends Component {
    constructor(props){
        super(props);
        this.state = {
            loading: true,
            columns: [
                {
                    title: this.props.t('common.name'),
                    dataIndex: 'name'
                },
                {
                    title: this.props.t('common.address'),
                    dataIndex: 'address',
                },
                {
                    title: this.props.t('actions._'),
                    dataIndex: 'actions'
                }
            ],
            data: []
        }        
    }

    componentDidMount() {
        axios.get(
            window.mfwApp.urls.demand.list,
            {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }
        ).then(res => {
            if (res.data.success) {
                this.setState({
                    loading: false,
                    data: res.data.data
                });
            } else {
                message.error(this.props.t(res.data.error));
                this.setState({loading: false})
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
        return (
        <React.Fragment>
            <Table
                columns={this.state.columns}
                rowKey={record => record.id}
                dataSource={this.state.data}
                loading={this.state.loading}
            />
        </React.Fragment>
        )
    }
}

export default withTranslation()(Demands);