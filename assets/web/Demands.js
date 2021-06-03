import React, {Component} from 'react';
import { withTranslation } from 'react-i18next';

import axios from 'axios';

import { Table, message, Button } from 'antd';

class Demands extends Component {
    constructor(props){
        super(props);
        this.createPochtaOrder = this.createPochtaOrder.bind(this);
        this.state = {
            loading: true,
            columns: [
                {
                    title: this.props.t('demand.num'),
                    dataIndex: 'num'
                },
                {
                    title: this.props.t('demand.agent'),
                    dataIndex: 'agent'
                },
                {
                    title: this.props.t('demand.recipient'),
                    dataIndex: 'recipient'
                },
                {
                    title: this.props.t('common.address'),
                    dataIndex: 'address'
                },
                {
                    title: this.props.t('common.currency'),
                    dataIndex: 'currency'
                },
                {
                    title: this.props.t('common.sum'),
                    dataIndex: 'sum',
                    align: 'right',
                    render: (text) => {
                        return text.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                    }
                },
                {
                    title: this.props.t('common.actions'),
                    dataIndex: 'actions',
                    render: (text, row) => {
                        return <Button
                                  type="link"
                                  onClick={() => this.createPochtaOrder(row)}
                                  className="mfw-table-button-link">{this.props.t('actions.cancel')}
                                </Button>;
                    }
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
                    data: res.data.demands
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
    
    createPochtaOrder(row) {
        console.log(row);
        axios.post(
            window.mfwApp.urls.demand.createPochtaOrder,
            {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                data: row
            }
        ).then(res => {
            if (res.data.success) {
                this.setState({
                    loading: false,
                    data: res.data.demands
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
                rowKey={record => record.num}
                dataSource={this.state.data}
                loading={this.state.loading}
            />
        </React.Fragment>
        )
    }
}

export default withTranslation()(Demands);