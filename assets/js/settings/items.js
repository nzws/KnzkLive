const kit = require('../components/kanzakit');
const toast = require('../components/toast');
const api = require('../components/api');

class items {
  static remove(id, type) {
    if (confirm(`よろしいですか？`)) {
      api
        .request('client/settings/items', 'POST', {
          id: id
        })
        .then(json => {
          if (json.success) {
            const elem = kit.elemId(`${type}_${id}`);
            if (elem) kit.elemRemove(elem);

            const slot = kit.elemId(`${type}_slot`);
            slot.textContent = parseInt(slot.textContent) + 1;

            const alert = kit.elemId(`${type}_alert`);
            if (alert) kit.elemRemove(alert);

            const bt = kit.elemId(`${type}_bt`);
            if (bt) {
              bt.classList.remove('btn-warning');
              bt.classList.add('btn-primary');
              bt.textContent = '追加';
            }

            toast.new('削除しました。', '.bg-success');
          } else {
            toast.new(
              'エラーが発生しました。データベースに問題が発生している可能性があります。',
              '.bg-danger'
            );
          }
        });
    }
  }

  static playVoice(file_name) {
    const audio = new Audio(config.storage_url + 'voice/' + file_name);
    audio.volume = 1;
    audio.muted = 0;
    audio.play();
  }
}

module.exports = items;
