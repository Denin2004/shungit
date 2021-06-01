import React from 'react';
import ReactDOM from 'react-dom';
import { BrowserRouter as Router } from 'react-router-dom';

import App from '@app/mobile/App';

import '@app/mobile/less/app.less';

ReactDOM.render(
    <div className="App">
        <Router>
            <App/>
        </Router>
    </div>,
    document.getElementById('root')
);
