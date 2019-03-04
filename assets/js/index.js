import '../scss/index.scss';

const live = require('./live');
const comment_loader = require('./comment_loader');
const comment_viewer = require('./comment_viewer');
const live_embed = require('./live_embed');

window.knzk = {
  live,
  comment_loader,
  comment_viewer,
  live_embed
};
