import React, {Component} from 'react';
import { Switch, Route, withRouter } from 'react-router-dom';

import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';

import {ConfigProvider, message} from 'antd';
import axios from 'axios';
import ruRU from 'antd/lib/locale/ru_RU';

import ru from '@app/translations/ru.json';
import Login from '@app/web/Login';
import Main from '@app/web/Main';

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
        return (
            <ConfigProvider locale={ruRU}> 
                <Switch>
                    <Route path="/login" component={Login} />
                    <Route path="/" component={Main} />
                </Switch>
            </ConfigProvider>
        )
    }
}

export default withRouter(App);