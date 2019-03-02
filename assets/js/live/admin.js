const api = require('../components/api');
const kit = require('../components/kanzakit');
const toast = require('../components/toast');

const live = require('./live');

class admin {
  static toggle(mode) {
    if (!config.live.is_broadcaster) return false;

    if (confirm('よろしいですか？')) {
      api
        .request('client/live/setting', 'POST', {
          type: mode
        })
        .then(json => {
          if (json['success']) {
            const elem = kit.elemId('admin_panel_' + mode + '_display');

            elem.classList.remove('off', 'on');
            elem.classList.add(json['result'] ? 'on' : 'off');

            if (kit.search(elem.className, 'btn-warning')) {
              elem.classList.add('btn-info');
              elem.classList.remove('btn-warning');
            } else {
              elem.classList.add('btn-warning');
              elem.classList.remove('btn-info');
            }
          }
        });
    }
  }

  static openListenerModal() {
    if (!config.live.is_broadcaster) return false;
    $('#listenerModal').modal('show');

    api.request('client/live/listener', 'GET').then(json => {
      if (json) {
        let html = '';
        for (let item of json) {
          item.name = kit.escape(item.name);
          html += `<tr><td><img src="${
            item.avatar_url
          }" width="25" height="25"/> <b>${item.name}</b> <small>@${
            item.acct
          }</small></td></tr>`;
        }
        kit.elemId('listener_list').innerHTML = html;
      }
    });
  }

  static openEditLive() {
    if (!config.live.is_broadcaster) return false;

    $('.live_info').addClass('invisible');
    $('.live_edit').removeClass('invisible');
  }

  static undoEditLive() {
    if (!config.live.is_broadcaster) return false;

    kit.elemId('edit_name').value = config.live.watch_data['name'];

    const parser = document.createElement('div');
    parser.innerHTML = config.live.watch_data['description'];
    kit.elemId('edit_desc').value = parser.textContent;

    $('.live_info').removeClass('invisible');
    $('.live_edit').addClass('invisible');
  }

  static editLive() {
    if (!config.live.is_broadcaster) return false;

    const name = kit.elemId('edit_name').value;
    const desc = kit.elemId('edit_desc').value;

    if (!name || !desc) {
      toast.new('エラー: タイトルか説明が入力されていません。', '.bg-warning');
      return;
    }

    api
      .request('client/edit_live', 'POST', {
        name: name,
        description: desc
      })
      .then(json => {
        $('.live_info').removeClass('invisible');
        $('.live_edit').addClass('invisible');
        live.watch();
      });
  }
}

module.exports = admin;
