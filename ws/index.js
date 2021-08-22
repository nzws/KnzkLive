const app = require('express')();
const http = require('http').Server(app);
const WebSocket = require('ws');
const bodyParser = require('body-parser');
const os = require('os');
const lastUpdate = {
  detect: null,
  tipknzk: null,
  streaming: null
};
const liveTimerPool = {};

const config = require('../config');
const mysql = require('mysql');
const db = mysql.createPool({
  host: config.db.host,
  port: config.db.port,
  user: config.db.user,
  password: config.db.pass,
  database: config.db.name
});

app.get('/', function (req, res) {
  res.send('ok');
});

app.get('/health', function (req, res) {
  res.send(JSON.stringify(lastUpdate));
});

app.use(
  bodyParser.urlencoded({
    extended: true
  })
);
app.use(bodyParser.json());

app.post('/send_comment', function (req, res) {
  console.log('[KnzkLive WebSocket] Send Comment', req.body);
  send(
    req.body.live_id,
    JSON.stringify({
      event: 'update',
      payload: JSON.stringify(req.body),
      is_knzklive: true
    })
  );
  res.end();
});

app.post('/delete_comment', function (req, res) {
  console.log('[KnzkLive WebSocket] Delete Comment', req.body);
  send(
    req.body.live_id,
    JSON.stringify({
      event: 'delete',
      payload: req.body.delete_id,
      is_knzklive: true
    })
  );
  res.end();
});

app.post('/send_prop', function (req, res) {
  console.log('[KnzkLive WebSocket] Send prop', req.body);
  send(
    req.body.live_id,
    JSON.stringify({
      event: 'prop',
      payload: JSON.stringify(req.body.mode === 'update_status' ? {} : req.body),
      is_knzklive: true
    })
  );

  if (req.body.mode === 'update_status') {
    if (liveTimerPool[req.body.live_id]) {
      clearTimeout(liveTimerPool[req.body.live_id]);
      liveTimerPool[req.body.live_id] = null;
    }

    if (req.body.result.status === 1) {
      liveTimerPool[req.body.live_id] = setTimeout(() => {
        exec(
          `php ${__dirname}/../knzkctl job:stop_live ${req.body.live_id}`,
          (err, stdout, stderr) => {
            if (err) {
              console.log(err);
            }
          }
        );
      }, 15 * 60 * 1000);
    } else if (req.body.result.status === 2) {
      const dir = os.tmpdir();

      const liveRtmp = `rtmp://${req.body.result.rtmp_server}/live/${req.body.live_id}stream`;
      const thumbnailPath = `${dir}/knzklive-thumbnail-${req.body.live_id}.png`;

      exec(
        `ffmpeg -y -i ${liveRtmp} -ss 1 -vframes 1 -s 960x540 -f image2 ${thumbnailPath}`,
        (err, stdout, stderr) => {
          if (err) {
            console.log(err);
            return;
          }
          exec(
            `php ${__dirname}/../knzkctl job:send_thumbnail ${req.body.live_id} ${dir}`,
            (err, stdout, stderr) => {
              console.log(err, stdout, stderr);
            }
          );
          console.log('thumbnail taken');
        }
      );
    }
  }

  res.end();
});

app.post('/update_conf', function (req, res) {
  console.log('[KnzkLive WebSocket] Update conf', req.body);
  const b = req.body;
  if (b.mode === 'add') {
    //add
    if (b.type === 'hashtag') {
      //hashtag
      if (b.value !== 'default') {
        conf.hashtag.push(b.value);
      } else {
        conf.hashtag.push('knzklive_' + b.live_id);
        conf.hashtag_id['knzklive_' + b.live_id] = b.live_id;
      }

      if (b.da_token) startDAConnect(b.da_token, b.live_id);
      if (b.sl_token) startSLConnect(b.sl_token, b.live_id);
    } else {
      //user
      conf.acct.push(b.value);
    }
    conf.hashtag = Array.from(new Set(conf.hashtag));
    conf.acct = Array.from(new Set(conf.acct));
  } else {
    //del
    if (b.type === 'hashtag') {
      const index = conf.hashtag.indexOf(b.value);
      if (index !== -1) {
        conf.hashtag.splice(index, 1);
      }

      if (b.da_token) closeDAConnect(b.da_token);
      if (b.sl_token) closeSLConnect(b.sl_token);
    }
  }
  res.end();
});

app.post('/v2', function (req, res) {
  console.log('[KnzkLive WebSocket] v2 migrate API', req.body);
  const b = req.body;
  if (b.mode === 'add-origin') {
    db.query(
      'INSERT INTO `live_slot` (`id`, `used`, `max`, `server`, `server_ip`, `is_testing`) VALUES (NULL, 0, 1, ?, ?, 0)',
      [b.domain, b.ipv4],
      error => {
        if (error) throw error;
        console.log('[Add origin]', b);
      }
    );
  } else if (b.mode === 'remove-origin') {
    db.query(
      'DELETE FROM live_slot WHERE server_ip = ?',
      b.ipv4,
      error => {
        if (error) throw error;
        console.log('[Delete origin]', b);
      }
    );
  }
  res.end();
});

const ws = new WebSocket.Server({ server: http });

ws.on('connection', function (c, req) {
  console.log(new Date() + ' Connected.');
  c.url = req.url;
  c.on('message', function (message) {
    c.send(JSON.stringify({ event: 'pong' }));
  });
});

function send(liveId, message) {
  ws.clients.forEach(function (c) {
    if (c.url === '/api/streaming/live/' + liveId) {
      try {
        c.send(message);
      } catch (e) {
        //
      }
    }
  });
}

http.listen(3000, function () {
  console.log('[KnzkLive WebSocket] listening on *:3000');
});

// donation-alerts
const daData = {};
const socketio = require('socket.io-client');
function startDAConnect(token, live_id) {
  if (daData[token]) {
    console.log('[Worker Donate] already connected');
    return false;
  }

  daData[token] = socketio('wss://socket.donationalerts.ru:443', {
    reconnection: true,
    reconnectionDelayMax: 5000,
    reconnectionDelay: 1000
  });

  daData[token].on('connect', function () {
    daData[token].emit('add-user', { token: token, type: 'minor' });
  });

  daData[token].on('donation', function (msg) {
    const data = JSON.parse(msg);
    if (data['_is_test_alert']) {
      donateTest(live_id);
    } else {
      donateRun(data['username'], live_id, data['amount'], data['currency']);
    }
  });
}

function closeDAConnect(token) {
  daData[token].close();
  daData[token] = null;
}

// stream-labs
const slData = {};
function startSLConnect(token, live_id) {
  if (slData[token]) {
    console.log('[Worker Donate-sl] already connected');
    return false;
  }

  slData[token] = socketio(`https://sockets.streamlabs.com?token=${token}`, {
    transports: ['websocket'],
    reconnection: true,
    reconnectionDelayMax: 5000,
    reconnectionDelay: 1000
  });

  slData[token].on('event', function (eventData) {
    if (eventData.type === 'donation') {
      const msg = eventData.message[0];
      if (!msg) return false;

      if (msg.isTest) {
        donateTest(live_id);
      } else {
        donateRun(msg.from, live_id, msg.amount, msg.currency);
      }
    }
  });
}

function closeSLConnect(token) {
  slData[token].close();
  slData[token] = null;
}

function donateTest(live_id) {
  exec(
    `php ${__dirname}/../knzkctl job:donate ${live_id} testing`,
    (err, stdout, stderr) => {
      if (err) {
        console.log(err);
      }
    }
  );
}

function donateRun(username, live_id, amount, currency) {
  if (username.indexOf('knzklive_') === -1) return;
  username = parseInt(username.replace('knzklive_', ''));
  if (!username) return;

  db.query(
    'SELECT * FROM `users` WHERE id = ?',
    username,
    function (error, results, fields) {
      if (error) throw error;
      const user_id = results[0] ? results[0]['id'] : 0;
      exec(
        `php ${__dirname}/../knzkctl job:donate ${live_id} ${user_id} ${parseInt(
          amount
        )} ${currency}`,
        (err, stdout, stderr) => {
          if (err) {
            console.log(err);
          }
        }
      );
    }
  );
}

const WebSocketClient = require('websocket').client;
const striptags = require('striptags');
const exec = require('child_process').exec;
const fetch = require('node-fetch');
let conf = {
  hashtag: [],
  acct: [],
  hashtag_id: {}
};

db.getConnection(function (err, connection) {
  if (err) {
    console.error('[DBERROR]', err);
    db.end(function () {
      process.exit();
    });
  } else {
    connection.release();
  }
});

db.query(
  'SELECT * FROM `live` WHERE is_live = 1 OR is_live = 2',
  function (error, results, fields) {
    if (error) throw error;
    for (let item of results) {
      if (item['custom_hashtag']) {
        conf.hashtag.push(item['custom_hashtag']);
      } else {
        conf.hashtag.push('knzklive_' + item['id']);
        conf.hashtag_id['knzklive_' + item['id']] = item['id'];
      }
    }
    console.log('[Worker Hashtag]', conf.hashtag, conf.hashtag_id);
  }
);

db.query(
  'SELECT * FROM `users` WHERE twitter_id IS NULL',
  function (error, results, fields) {
    if (error) throw error;
    for (let item of results) {
      conf.acct.push(item['acct']);
    }
    console.log('[Worker Users]', conf.acct);
  }
);

db.query(
  'SELECT * FROM `users` WHERE live_current_id != 0',
  function (error, results, fields) {
    if (error) throw error;
    for (let item of results) {
      const misc = JSON.parse(item['misc']);
      if (misc['donation_alerts_token'])
        startDAConnect(misc['donation_alerts_token'], item['live_current_id']);
      if (misc['streamlabs_token'])
        startSLConnect(misc['streamlabs_token'], item['live_current_id']);
    }
    console.log('[Worker Donate]');
  }
);

function reConnect($type = 'worker') {
  console.log('サーバとの接続が切れました。30秒後にリトライします...', $type);
  setTimeout(function () {
    if ($type === 'worker') StartWorker();
    else StartTIPKnzk();
  }, 30000);
}

function StartWorker() {
  const client = new WebSocketClient();

  client.on('connectFailed', function (error) {
    console.log('Connect Error: ' + error.toString());
    reConnect();
  });

  client.on('connect', function (connection) {
    console.log('WebSocket Client Connected');

    connection.on('error', function (error) {
      console.log('Connection Error: ' + error.toString());
      reConnect();
    });

    connection.on('close', function () {
      reConnect();
    });

    connection.on('message', function (message) {
      try {
        if (message.type !== 'utf8') return;

        lastUpdate.detect = Date.now();
        const ord = JSON.parse(message.utf8Data);
        const json = JSON.parse(ord.payload);
        if (ord.event !== 'update') return;
        if (json.visibility !== 'public' && json.visibility !== 'unlisted')
          return;
        if (json.reblog) return;
        if (json.account.username === json.account.acct)
          json.account.acct = json.account.acct + '@' + config.domain;

        if (!json.tags || !json.tags[0]) return;

        json.tags.forEach(i => {
          if (conf.hashtag.indexOf(i.name) === -1) return;

          const sql =
            'UPDATE `live` SET `comment_count` = `comment_count` + 1 ' +
            (conf.hashtag_id[i.name]
              ? 'WHERE `id` = ?'
              : 'WHERE `custom_hashtag` = ?');

          const value = conf.hashtag_id[i.name]
            ? conf.hashtag_id[i.name]
            : i.name;

          db.query(sql, value, error => {
            if (error) throw error;
            console.log('[Detect Hashtag] ' + i.name);
          });

          if (conf.acct.indexOf(json.account.acct) === -1) return;

          db.query(
            'UPDATE `users` SET `point_count_today_toot` = `point_count_today_toot` + 2 WHERE acct = ?',
            json.account.acct,
            error => {
              if (error) throw error;
              console.log('[Detect User]', json.account.acct);
            }
          );
        });
      } catch (e) {
        console.log('[Worker Error]', e);
      }
    });
  });

  const url =
    'wss://' +
    config.domain +
    '/api/v1/streaming/?stream=public' +
    (config.worker_token ? `&access_token=${config.worker_token}` : '');

  client.connect(url);
}

function StartTIPKnzk() {
  if (!config.tipknzk_token) {
    console.log('[TIPKnzk]', 'トークンがないため無効化しました。');
    return;
  }

  const client = new WebSocketClient();

  client.on('connectFailed', function (error) {
    console.log('Connect Error: ' + error.toString());
    reConnect('TIPKnzk');
  });

  client.on('connect', function (connection) {
    console.log('WebSocket Client Connected');

    connection.on('error', function (error) {
      console.log('Connection Error: ' + error.toString());
      reConnect('TIPKnzk');
    });

    connection.on('close', function () {
      reConnect('TIPKnzk');
    });

    connection.on('message', function (message) {
      try {
        if (message.type !== 'utf8') return;
        lastUpdate['tipknzk'] = Date.now();
        const ord = JSON.parse(message.utf8Data);
        let json = JSON.parse(ord.payload);
        if (ord.event !== 'notification' || json['type'] !== 'mention') return;
        json = json['status'];
        let to_acct = '';
        json.mentions.forEach(i => {
          if (i.acct.toLowerCase() === config.tipknzk_acct.toLowerCase())
            return;
          if (i.acct === i.username)
            to_acct =
              i.acct === i.username ? to_acct + '@' + config.domain : i.acct;
        });

        if (!to_acct) return;
        if (json.account.username === json.account.acct)
          json.account.acct = json.account.acct + '@' + config.domain;

        const data = striptags(json.content).split(' ');
        if (data[1] === 'tip') {
          exec(
            `php ${__dirname}/../knzkctl job:tipknzk ${parseInt(data[3])} ${
              json['account']['acct']
            } ${to_acct}`,
            (err, stdout) => {
              if (err) {
                console.log(err);
              }
              post(
                '@' + json.account.acct + ' ' + stdout,
                {
                  in_reply_to_id: json['id']
                },
                json['visibility']
              );
            }
          );
        }
      } catch (e) {
        console.log('[TIPKnzk Error]', e);
      }
    });
  });

  client.connect(
    'wss://' +
      config.domain +
      '/api/v1/streaming/?stream=user&access_token=' +
      config.tipknzk_token
  );
}

StartWorker();
StartTIPKnzk();

function post(value, option = {}, visibility = 'public') {
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
    .then(response => {
      if (response.ok) {
        return response.json();
      } else {
        console.warn('NG:POST:SERVER');
        return null;
      }
    })
    .then(json => {
      if (!json) return;
      if (json['id']) {
        console.log('OK:POST');
      } else {
        console.warn('NG:POST:' + json);
      }
    });
}
