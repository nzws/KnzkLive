module.exports = {
  ready: function() {
    this.comment.check_limit();
  },
  comment: require('./live/comment'),
  share: require('./live/share')
};
