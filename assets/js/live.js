const common_comment = require('./comment_loader');

module.exports = {
  ready() {
    this.comment.check_limit();
    common_comment.getNgs();
    this.live.watch();
    this.live.update_watch();

    setInterval(this.live.watch, 5000);
    setInterval(this.live.update_watch, 20000);
    setInterval(this.live.date, 1000);

    $('#toot').keydown(e => {
      if (e.keyCode === 13 && (e.ctrlKey || e.metaKey)) {
        knzk.live.comment.post();
      }
    });
  },
  comment: require('./live/comment'),
  share: require('./live/share'),
  vote: require('./live/vote'),
  live: require('./live/live'),
  admin: require('./live/admin'),
  item: require('./live/item')
};
