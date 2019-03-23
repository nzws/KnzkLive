const config = require('../../config');

class mastodon_components {
  static post(value, option = {}, visibility = 'public') {
    const optiondata = {
      status: value,
      visibility: visibility
    };

    if (option.cw) {
      optiondata.spoiler_text = option.cw;
    }
    if (option.in_reply_to_id) {
      optiondata.in_reply_to_id = option.in_reply_to_id;
    }
    if (option.media_ids) {
      optiondata.media_ids = option.media_ids;
    }
    if (option.sensitive) {
      optiondata.sensitive = option.sensitive;
    }
    fetch('https://' + config.domain + '/api/v1/statuses', {
      headers: {
        'content-type': 'application/json',
        Authorization: 'Bearer ' + config.tipknzk_token
      },
      method: 'POST',
      body: JSON.stringify(optiondata)
    })
      .then(function(response) {
        if (response.ok) {
          return response.json();
        } else {
          console.warn('NG:POST:SERVER');
          return null;
        }
      })
      .then(function(json) {
        if (json) {
          if (json.id) {
            console.log('OK:POST');
          } else {
            console.warn('NG:POST:' + json);
          }
        }
      });
  }
}

module.exports = mastodon_components;
