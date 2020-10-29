import 'bulma';

import React from 'react';
import ReactDom from 'react-dom';
import {Dashboard} from './components/Dashboard';

import axios from 'axios';

axios.defaults.withCredentials = true;

ReactDom.render(<Dashboard/>, document.getElementById('root'));
