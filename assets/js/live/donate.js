const kit = require('../components/kanzakit');

class donate {
  static add(data) {
    kit.elemId('comment').classList.add('have_donation');
    $('#donators').show();
    $('#donators').prepend(
      `<span class="badge badge-pill donator" onclick="live.live.userDropdown(this, null, '${
        data.account.acct
      }', '${data.account.url}')" id="donate_${data.id}" style="background:${
        data.color
      }"><img src="${
        data.account.avatar_url
      }" height="30" width="30" class="rounded-circle avatar"/> ${data.amount}${
        data.currency
      }</span>`
    );
    config.dn[data.id] = data;

    const datet = parseInt(
      new Date(data.ended_at).getTime() - new Date().getTime()
    );
    setTimeout(() => {
      donate.delete(data.id);
    }, datet);
  }

  static delete(id) {
    kit.elemRemove(kit.elemId(`donate_${id}`));
    delete config.dn[id];
    if (Object.keys(config.dn).length <= 0) {
      const dn = kit.elemId('donators');
      if (dn) $(dn).hide();
      kit.elemId('comment').classList.remove('have_donation');
    }
  }
}

module.exports = donate;
