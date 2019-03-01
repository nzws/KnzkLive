function buildCommentData(data, inst) {
  const acct =
    data['account']['acct'] !== data['account']['username']
      ? data['account']['acct'].replace(' (local)', '')
      : data['account']['username'] + '@' + inst;

  data['account']['display_name'] = escapeHTML(data['account']['display_name']);
  data['donator_color'] = check_donator(acct);
  return data;
}

function check_donator(acct) {
  let result = false;
  for (let item in config.dn) {
    if (config.dn[item] && config.dn[item]['account']['acct'] === acct) {
      const datet = parseInt(
        new Date(config.dn[item]['ended_at']).getTime() - new Date().getTime()
      );
      if (datet <= 0) {
        delete_donate(config.dn[item]['id']);
      } else {
        result = config.dn[item]['color'];
      }
      break;
    }
  }
  return result;
}

function check_data(data) {
  let result = true;
  for (let item of config.nw) {
    if (
      data['content'].indexOf(item) !== -1 ||
      data['account']['display_name'].indexOf(item) !== -1
    ) {
      result = false;
      break;
    }
  }
  let acct =
    data['account']['acct'] !== data['account']['username']
      ? data['account']['acct'].replace(' (local)', '')
      : data['account']['username'] + '@' + inst;
  if (config.nu.indexOf(acct) !== -1) {
    result = false;
  }
  return result;
}

function delete_donate(id) {
  $('#donate_' + id).remove();
  delete config.dn[id];
  if (Object.keys(config.dn).length <= 0) {
    const dn = elemId('donators');
    if (dn) $(dn).hide();
  }
}
