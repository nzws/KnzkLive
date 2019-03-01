class donate {
  static add(data) {
    $('#donators').show();
    $('#donators').prepend(
      `<span class="badge badge-pill donator" onclick="userDropdown(this, null, '${
        data['account']['acct']
      }', '${data['account']['url']}')" id="donate_${
        data['id']
      }" style="background:${data['color']}"><img src="${
        data['account']['avatar_url']
      }" height="30" width="30" class="rounded-circle avatar"/> ${
        data['amount']
      }${data['currency']}</span>`
    );
    config.dn[data['id']] = data;

    const datet = parseInt(
      new Date(data['ended_at']).getTime() - new Date().getTime()
    );
    setTimeout(function() {
      this.delete(data['id']);
    }, datet);
  }
}

module.exports = donate;
