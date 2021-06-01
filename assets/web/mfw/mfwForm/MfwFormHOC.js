import React from 'react';
import { Form } from 'antd';

const useWithForm = (Component) => {
    return (props) => {
        const [useForm] = Form.useForm();
        return <Component form={useForm} {...props} />;
    };
};

export default useWithForm;