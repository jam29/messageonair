var mysql  = require('mysql');
var conf   = require('../config.js');
//var logger = require('./logger');

module.exports = {
  "query": query
};

// Internal connection pool
var pool = mysql.createPoolCluster({
  "canRetry": true
});

// Add main connection
pool.add({
  host     : conf.dbhost,
  user     : conf.dbuser,
  password : conf.dbpassword,
  database : conf.dbname
});

// Test connection
pool.getConnection(function (err, _connection) {
  if (err) {
    throw new Error("MySQL connection error: " + err);
    process.exit(1);
  }

  //logger.info("MySQL connection OK");
  _connection.release();
});

// Public API
// query(sql: string [, params: Array], cb: Function)
//   => cb(err, rows, fields)
function query (sql, params, cb) {
  if (typeof params === 'function') {
    cb = params;
    params = [];
  }

  pool.getConnection(function (err, connection) {
    if (err) {
      return cb(err);
    }

    connection.query(sql, params, function (err, rows, fields) {
      connection.release();

      cb(err, rows, fields);
    });
  });
}
