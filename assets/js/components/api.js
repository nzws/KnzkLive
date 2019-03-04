const toast = require('./toast');

class api {
  static request(url, method = 'GET', body = {}, header = {}) {
    if (!header['content-type'])
      header['content-type'] = 'application/x-www-form-urlencoded';
    if (method === 'POST') body['csrf_token'] = config['csrf_token'];

    return new Promise((resolve, reject) => {
      fetch(
        config.endpoint +
          url +
          config.suffix +
          (body && method !== 'POST' ? '?' + api.buildQuery(body) : ''),
        {
          headers: header,
          method,
          credentials: 'include',
          body: method === 'POST' ? api.buildQuery(body) : null
        }
      )
        .then(response => {
          if (config['is_debug'])
            console.log('[Knzk-Debug] API Response', response);
          if (response.ok) {
            return response.json();
          } else {
            throw response;
          }
        })
        .then(json => {
          if (json['error']) {
            toast.new(json['error'], '.bg-warning');
            reject(json);
            return;
          }
          if (config['is_debug'])
            console.log('[Knzk-Debug] API Response received', json);
          resolve(json);
        })
        .catch(error => {
          console.error(error);
          toast.new(
            'サーバーと通信中にエラーが発生しました。通信環境が正常かお確かめください。',
            '.bg-danger'
          );
          reject(error);
        });
    });
  }

  static buildQuery(data) {
    let body = '';
    let key;
    for (key in data) {
      body += `${key}=${encodeURIComponent(data[key])}&`;
    }
    body += 'd=' + new Date().getTime();
    return body;
  }
}

module.exports = api;
