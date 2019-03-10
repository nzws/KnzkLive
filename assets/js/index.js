import $ from 'jquery';
window.$ = $;

import 'popper.js';
import 'bootstrap';
import 'flv.js';
import 'hls.js';
import 'handlebars/dist/handlebars';
import 'clipboard';
import '../scss/index.scss';

window.knzk = {
  live: require('./live'),
  comment_loader: require('./comment_loader'),
  comment_viewer: require('./comment_viewer'),
  live_embed: require('./live_embed')
};
