module.exports = {
  ready: function() {
    this.comment.check_limit();

    setInterval(this.live.watch, 5000);
    setInterval(this.live.update_watch, 20000);
    setInterval(this.live.date, 1000);

    $('#toot').keydown(function(e) {
      if (e.keyCode === 13 && (e.ctrlKey || e.metaKey)) {
        this.comment.post();
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
