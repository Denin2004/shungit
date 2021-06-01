import React, {Component} from 'react';
import { Switch, Route, withRouter } from 'react-router-dom';
import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';

import moment from 'moment-timezone';

import 'antd-mobile/dist/antd-mobile.css';

import ru from '@app/translations/ru.json';
import Login from '@app/mobile/Login';
import Main from '@app/mobile/Main';

window.mfwApp.axiosError = function(response) {
    switch (response.status) {
        case 403:
            return i18n.t(response.data.error);
    }
    return response.statusText;
};

i18n.use(initReactI18next) // passes i18n down to react-i18next
    .init({
        resources: {
            ru: {
                translation: ru
            }
        },
        lng: 'ru',
        fallbackLng: 'ru',        
        interpolation: {
            escapeValue: false
        }
    });

class App extends Component {
    constructor(props){
        super(props);
    }

    render() {
        console.log('mobile');
        return (
             <Switch>
                <Route path="/login" component={Login} />
                <Route peth="/" component={Main} />
            </Switch>
        )
    }
}

export default withRouter(App);