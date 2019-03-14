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

    $('#toot').keydown(({ keyCode, ctrlKey, metaKey }) => {
      if (keyCode === 13) {
        knzk.live.comment.post();
      }
    });
    $('[data-toggle="popover"]').popover();
  },
  comment: require('./live/comment'),
  share: require('./live/share'),
  vote: require('./live/vote'),
  live: require('./live/live'),
  admin: require('./live/admin'),
  item: require('./live/item')
};
