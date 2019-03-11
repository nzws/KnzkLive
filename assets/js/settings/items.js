import kit from '../components/kanzakit';
import toast from '../components/toast';
import api from '../components/api';

class items {
  static remove(id, type) {
    if (confirm(`よろしいですか？`)) {
      api
        .request('client/items', 'POST', {
          id: id
        })
        .then(json => {
          if (json.success) {
            const elem = kit.elemId(`${type}_${id}`);
            if (elem) kit.elemRemove(elem);

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
