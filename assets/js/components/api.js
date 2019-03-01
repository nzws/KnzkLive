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
          method: method,
          credentials: 'include',
          body: method === 'POST' ? api.buildQuery(body) : null
        }
      )
        .then(function(response) {
          if (config['is_debug'])
            console.log('[Knzk-Debug] API Response', response);
          if (response.ok) {
            return response.json();
          } else {
            throw response;
          }
        })
        .then(function(json) {
          if (json['error']) {
            toastr.warn(json['error'], 'エラー');
            reject(error);
            return;
          }
          if (config['is_debug'])
            console.log('[Knzk-Debug] API Response received', json);
          resolve(json);
        })
        .catch(function(error) {
          console.error(error);
          toastr.error(
            'サーバーと通信中にエラーが発生しました。<br>通信環境が正常かお確かめください。',
            'エラー'
          );
          reject(error);
        });
    });
  }

  static buildQuery(data) {
    let body = '',
      key;
    for (key in data) {
      body += `${key}=${encodeURIComponent(data[key])}&`;
    }
    body += 'd=' + new Date().getTime();
    return body;
  }
}

module.exports = api;
