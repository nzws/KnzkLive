const app = require('express')();
const http = require('http').Server(app);
const io = require('socket.io')(http);
const bodyParser = require('body-parser');

app.get('/', function(req, res) {
  res.send('ok');
});

app.use(bodyParser.urlencoded({
  extended: true
}));
app.use(bodyParser.json());

app.post('/send_comment', function(req, res) {
  console.log('[KnzkLive WebSocket] Send Comment', req.body);
  io.emit('knzklive_comment_' + req.body.live_id, req.body);
  res.end();
});

app.post('/send_prop', function(req, res) {
  console.log('[KnzkLive WebSocket] Send prop', req.body);
  io.emit('knzklive_prop_' + req.body.live_id, req.body);
  res.end();
});

app.post('/update_conf', function(req, res) {
  console.log('[KnzkLive WebSocket] Update conf', req.body);
  const b = req.body;
  if (b.mode === "add") { //add
    if (b.type === "hashtag") { //hashtag
      if (b.value) {
        conf.hashtag.push(b.value);
      } else {
        conf.hashtag.push("knzklive_" + b.live_id);
        conf.hashtag_id["knzklive_" + b.live_id] = b.live_id;
      }
    } else { //user
      conf.acct.push(b.value);
    }
    conf.hashtag = Array.from(new Set(conf.hashtag));
    conf.acct = Array.from(new Set(conf.acct));
  } else { //del
    if (b.type === "hashtag") {
      const index = conf.hashtag.indexOf(b.value);
      if (index !== -1) {
        conf.hashtag.splice(index, 1);
      }
    }
  }
  res.end();
});

io.on('connection', function(socket) {
  console.log('[KnzkLive WebSocket] connected');
});

http.listen(3000, function() {
  console.log('[KnzkLive WebSocket] listening on *:3000');
});

const WebSocketClient = require('websocket').client;
const mysql = require('mysql');
const config = require('../config');
let conf = {
  "hashtag": [],
  "acct": [],
  "hashtag_id": {}
};

const db = mysql.createPool({
  host: config.db.host,
  port: config.db.port,
  user: config.db.user,
  password: config.db.pass,
  database: config.db.name
});

db.getConnection(function (err, connection) {
  if (err) {
    console.error("[DBERROR]", err);
    db.end(function () {
      process.exit();
    });
  } else {
    connection.release();
  }
});

db.query('SELECT * FROM `live` WHERE is_live = 1 OR is_live = 2', function (error, results, fields) {
  if (error) throw error;
  for (let item of results) {
    if (item["custom_hashtag"]) {
      conf.hashtag.push(item["custom_hashtag"]);
    } else {
      conf.hashtag.push("knzklive_" + item["id"]);
      conf.hashtag_id["knzklive_" + item["id"]] = item["id"];
    }
  }
  console.log("[Worker Hashtag]", conf.hashtag, conf.hashtag_id);
});

db.query('SELECT * FROM `users` WHERE twitter_id IS NULL', function (error, results, fields) {
  if (error) throw error;
  for (let item of results) {
    conf.acct.push(item["acct"]);
  }
  console.log("[Worker Users]", conf.acct);
});

function reConnect() {
  console.log('サーバとの接続が切れました。30秒後にリトライします...');
  setTimeout(function () {
    StartWorker();
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
      console.log("Connection Error: " + error.toString());
      reConnect();
    });

    connection.on('close', function () {
      reConnect();
    });

    connection.on('message', function (message) {
      try {
        if (message.type === 'utf8') {
          const ord = JSON.parse(message.utf8Data);
          const json = JSON.parse(ord.payload);
          if (ord.event === "update") {
            if (json['visibility'] !== 'public' && json['visibility'] !== 'unlisted') return;
            if (json['reblog']) return;
            if (json["account"]["username"] === json["account"]["acct"]) json["account"]["acct"] = json["account"]["acct"] + "@" + config.domain;

            if (conf.acct.indexOf(json["account"]["acct"]) !== -1) {
              console.log("[Detect User]", json["account"]["acct"]);
            }

            if (json["tags"] && json["tags"][0]) {
              for (let i of json["tags"]) {
                if (conf.hashtag.indexOf(i["name"]) !== -1) {
                  const sql = 'UPDATE `live` SET `comment_count` = `comment_count` + 1 ' + (conf.hashtag_id[i["name"]] ? "WHERE `id` = ?" : "WHERE `custom_hashtag` = ?");
                  const value = conf.hashtag_id[i["name"]] ? conf.hashtag_id[i["name"]] : i["name"];
                  db.query(sql, value, function (error, results, fields) {
                    if (error) throw error;
                    console.log("[Detect Hashtag] " + i["name"]);
                  });
                }
              }
            }
          }
        }
      } catch (e) {
        console.log("[Worker Error]", e);
      }
    });
  });

  client.connect("wss://" + config.domain + "/api/v1/streaming/?stream=public");
}

StartWorker();
