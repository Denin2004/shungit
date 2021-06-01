import React from 'react';
import ReactDOM from 'react-dom';
import { BrowserRouter as Router } from 'react-router-dom';

import 'antd/dist/antd.css';

import App from '@app/web/App';

ReactDOM.render(
    <div className="App">
        <Router>
            <App/>
        </Router>
    </div>,
    document.getElementById('root')
);
