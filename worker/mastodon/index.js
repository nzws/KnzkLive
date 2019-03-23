const components = require('./components');
const mysql = require('mysql');
const config = require('../../config');
let conf = {
  hashtag: [],
  acct: [],
  hashtag_id: {}
};

class mastodon {
  constructor() {
    const db = mysql.createPool({
      host: config.db.host,
      port: config.db.port,
      user: config.db.user,
      password: config.db.pass,
      database: config.db.name
    });

    db.getConnection(function(err, connection) {
      if (err) {
        console.error('[DBERROR]', err);
        db.end(function() {
          process.exit();
        });
      } else {
        connection.release();
      }
    });

    db.query('SELECT * FROM `live` WHERE is_live = 1 OR is_live = 2', function(
      error,
      results,
      fields
    ) {
      if (error) throw error;
      for (let item of results) {
        if (item.custom_hashtag) {
          conf.hashtag.push(item.custom_hashtag);
        } else {
          conf.hashtag.push('knzklive_' + item.id);
          conf.hashtag_id['knzklive_' + item.id] = item.id;
        }
      }
      console.log('[Worker Hashtag]', conf.hashtag, conf.hashtag_id);
    });

    db.query('SELECT * FROM `users` WHERE twitter_id IS NULL', function(
      error,
      results,
      fields
    ) {
      if (error) throw error;
      for (let item of results) {
        conf.acct.push(item.acct);
      }
      console.log('[Worker Users]', conf.acct);
    });
  }
}

module.exports = mastodon;
