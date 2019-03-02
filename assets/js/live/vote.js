const kit = require('../components/kanzakit');
const toast = require('../components/toast');
const api = require('../components/api');

class vote {
  static vote(id) {
    $('#prop_vote').hide();

    api
      .request('client/vote/add', 'GET', {
        id: config.live.id,
        type: id
      })
      .catch(error => {
        $('#prop_vote').show();
      });
  }

  static create() {
    const vote = [
      kit.elemId('open_vote1'),
      kit.elemId('open_vote2'),
      kit.elemId('open_vote3'),
      kit.elemId('open_vote4')
    ];
    const title = kit.elemId('open_vote_title');

    if (confirm('投票を開始します。\nよろしいですか？')) {
      api
        .request('client/live/vote', 'POST', {
          title: title.value,
          vote1: vote[0].value,
          vote2: vote[1].value,
          vote3: vote[2].value,
          vote4: vote[3].value,
          is_post: kit.elemId('vote_ispost').checked ? 1 : 0
        })
        .then(json => {
          if (json['success']) {
            $('#enqueteModal').modal('hide');
            $('#open_enquete_btn').hide();
            $('#close_enquete_btn').show();
            title.value = '';
            vote[0].value = '';
            vote[1].value = '';
            vote[2].value = '';
            vote[3].value = '';
          } else {
            toast.new(
              'エラーが発生しました。データベースに問題が発生している可能性があります。',
              '.bg-danger'
            );
          }
        });
    }
  }

  static close() {
    if (!config.live.is_broadcaster) return false;

    if (confirm('投票を終了します。\nよろしいですか？')) {
      api
        .request('client/live/vote', 'POST', {
          end: true
        })
        .then(json => {
          if (json['success']) {
            $('#open_enquete_btn').show();
            $('#close_enquete_btn').hide();
          } else {
            toast.new(
              'エラーが発生しました。データベースに問題が発生している可能性があります。',
              '.bg-danger'
            );
          }
        });
    }
  }
}

module.exports = vote;
