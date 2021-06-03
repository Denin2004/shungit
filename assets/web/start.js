import React from 'react';
import ReactDOM from 'react-dom';
import { BrowserRouter as Router } from 'react-router-dom';

import '@app/web/less/app.less';

import App from '@app/web/App';

ReactDOM.render(
    <div className="App">
        <Router>
            <App/>
        </Router>
    </div>,
    document.getElementById('root')
);
