import React, {Component} from 'react';
import { withTranslation } from 'react-i18next';

import axios from 'axios';

import { Table, message, Button, Modal, Row, Col } from 'antd';

class Demands extends Component {
    constructor(props){
        super(props);
        this.createPochtaOrder = this.createPochtaOrder.bind(this);
        this.closeErrors = this.closeErrors.bind(this);
        this.getDemands = this.getDemands.bind(this);
        this.nextDemands = this.nextDemands.bind(this);
        this.state = {
            loading: true,
            errors: [],
            showErrors: false,
            offset: 0,
            errorColumns: [
                {
                    title: this.props.t('demand.errors.columns.code'),
                    dataIndex: 'code'
                },
                {
                    title: this.props.t('demand.errors.columns.description'),
                    dataIndex: 'description'
                },
                {
                    title: this.props.t('demand.errors.columns.details'),
                    dataIndex: 'details'
                }
            ],            
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
                                  className="mfw-table-button-link">{this.props.t('demand.russianMail')}
                                </Button>;
                    }
                }
            ],
            data: []
        }        
    }

    componentDidMount() {
        this.getDemands();
    }
    
    getDemands() {
        axios({
            url: window.mfwApp.urls.demand.list+'\\'+this.state.offset,
            method: 'get',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(res => {
            if (res.data.success) {
                this.setState({
                    loading: false,
                    data: res.data.demands,
                    offset: res.data.nextOffset
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
        axios({
            url: window.mfwApp.urls.demand.createPochtaOrder,
            data: row,
            method: 'post',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            }
        }).then(res => {
            if (res.data.success) {
                if (res.data.mailResult.errors != undefined) {
                    this.setState({errors: res.data.mailResult.errors[0]['error-codes'], showErrors: true});
                } else {
                    alert('УСПЕШНО!!!!!');
                }
/*                this.setState({
                    loading: false,
                    data: res.data.demands
                });*/
            } else {
                var error = this.props.t(res.data.error);
                if (res.data.args != undefined) {
                    Object.keys(res.data.args).forEach(function(key){
                        error = error.replace('{'+key+'}', res.data.args[key]);
                    });
                }
                message.error(error);
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
    
    nextDemands() {
        this.setState(state => {return {offset: state.offset+10, loading: true}});
        this.getDemands();
    }
    
    closeErrors() {
        this.setState({showErrors: false});
    }
    
    render() {
        return (
        <React.Fragment>
            <Table
                columns={this.state.columns}
                rowKey={record => record.num}
                dataSource={this.state.data}
                loading={this.state.loading}
                pagination={false}
            />
            { this.state.loading ? null :
            <Row justify="end">
                <Button className="mfw-mt-1 mfw-mr-1" onClick={this.nextDemands}>{this.props.t('common.next.10')}</Button>
            </Row>
            }
            <Modal 
               title={this.props.t('common.errors')} 
               visible={this.state.showErrors} 
               onCancel={this.closeErrors}
               width="50vw"
               footer1={null}
               footer1={[
                <Button key="back" onClick={this.closeErrors}>{this.props.t('common.close')}</Button>
              ]}>
                <Table columns={this.state.errorColumns}
                    rowKey={record => record.code}
                    dataSource={this.state.errors}
                    size="small"
                    pagination={false}
                    scroll={{y: 240}}/>
            </Modal>                    
        </React.Fragment>
        )
    }
}

export default withTranslation()(Demands);