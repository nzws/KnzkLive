import $ from 'jquery';
window.$ = $;

import 'popper.js';
import 'bootstrap';
import '../scss/index.scss';

import turbolinks from 'turbolinks';
turbolinks.start();
document.addEventListener('turbolinks:load', () => {
  if (window.ready) window.ready();
});

window.knzk = {
  live: require('./live'),
  comment_loader: require('./comment_loader'),
  comment_viewer: require('./comment_viewer'),
  live_embed: require('./live_embed'),
  settings: require('./settings')
};
