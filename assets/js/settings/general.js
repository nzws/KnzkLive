const moment = require('moment');

const api = require('../components/api');
const kit = require('../components/kanzakit');

class general {
  static pointHistLoad() {
    if (!config.hist_page) config.hist_page = 1;
    api
      .request('client/settings/point_hist', 'GET', { page: config.hist_page })
      .then(json => {
        if (json[0]) {
          let html = '';
          json.forEach(data => {
            data.type =
              data.type === 'toot'
                ? 'トゥート/コメント'
                : data.type === 'user'
                ? 'チケット/プレゼント'
                : data.type === 'live'
                ? '配信'
                : 'その他';

            html += `<tr><td>${data.created_at}</td><td>${data.point}</td><td>${
              data.type
            }</td><td>${kit.escape(data.data)}</td></tr>`;
          });
          kit.elemId('point_hist').innerHTML += html;
          config.hist_page++;
        } else {
          kit.elemId('point_hist_bt').classList.add('invisible');
        }
      });
  }

  static loadMoment() {
    moment.locale('ja');
    Array.prototype.forEach.call(
      document.getElementsByClassName('momentjs'),
      object => {
        const date = object.dataset.time;
        if (!date) return;
        object.textContent =
          object.dataset.type === 'fromNow' ? moment(date).fromNow() : null;
      }
    );
  }
}

module.exports = general;
