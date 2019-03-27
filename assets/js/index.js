import $ from 'jquery';
import moment from 'moment';
import 'popper.js';
import 'bootstrap';
import '../scss/index.scss';
window.$ = $;
window.moment = moment;

window.knzk = {
  live: require('./live'),
  comment_loader: require('./comment_loader'),
  comment_viewer: require('./comment_viewer'),
  live_embed: require('./live_embed'),
  settings: require('./settings')
};
