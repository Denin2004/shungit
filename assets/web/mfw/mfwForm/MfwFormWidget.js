import React, {Component} from 'react';

import i18n from '@app/i18app';
import { withTranslation } from 'react-i18next';

import { Form, Input, Button, Select} from 'antd';

import MfwPeriod from '@app/mfw/mfwForm/MfwPeriod';
import MfwAutocomplete from '@app/mfw/mfwForm/MfwAutocomplete';

class MfwFormWidget extends Component {
    constructor(props){
        super(props);
        this.textElement = this.textElement.bind(this);
        this.hiddenElement = this.hiddenElement.bind(this);
        this.buttonElement = this.buttonElement.bind(this);
        this.periodElement = this.periodElement.bind(this);
        this.autocompleteElement = this.autocompleteElement.bind(this);
        this.widgetProps = this.widgetProps.bind(this);
    }
    
    widgetProps() {
        return this.props.widgetProps ? 
            (this.props.element.widgetProps ? {...this.props.element.widgetProps, ...this.props.widgetProps} : this.props.widgetProps) : 
            (this.props.element.widgetProps ? this.props.element.widgetProps : {});
    }

    textElement() {
        return (
            <Form.Item {...this.widgetProps()}>
                <Input/>
            </Form.Item>
        );
    };

    hiddenElement() {
        return (
            <Form.Item hidden={true} {...this.widgetProps()}>
                <Input type="hidden"/>
            </Form.Item>
        );
    };
    
    periodElement() {
        return (
            <MfwPeriod {...this.props}/>
        );
    }
    
    autocompleteElement() {
        return (
            <MfwAutocomplete {...this.props}/>
        );
    }    

    choiceElement() {
        const widgetProps = this.widgetProps();
        return (
            <Form.Item {...widgetProps}>
                <Select {...this.props.element.selectProps}>
                    {this.props.element.choices.map((choice) => {
                        return(
                      <Select.Option key={choice.value} value={choice.value}>{widgetProps.translate == 'true' ? 
                         this.props.t(choice.label) : choice.label}</Select.Option>
                    )})}
                </Select>
            </Form.Item>
        );
    };

    buttonElement() {
        return (
            <Form.Item {...this.widgetProps()}>
                <Button htmlType="submit" {...this.props.element.buttonProps}>{this.props.element.title}</Button>
            </Form.Item>
        );
    };

    render() {
        return (
            this[this.props.element.type+'Element']()
        )
    }
}

export default withTranslation()(MfwFormWidget);