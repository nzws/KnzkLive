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

    db.query('SELECT * FROM `users` WHERE live_current_id != 0', function(
      error,
      results,
      fields
    ) {
      if (error) throw error;
      for (let item of results) {
        const misc = JSON.parse(item['misc']);
        if (misc['donation_alerts_token'])
          startDAConnect(
            misc['donation_alerts_token'],
            item['live_current_id']
          );
        if (misc['streamlabs_token'])
          startSLConnect(misc['streamlabs_token'], item['live_current_id']);
      }
      console.log('[Worker Donate]');
    });
  }
}

module.exports = mastodon;
