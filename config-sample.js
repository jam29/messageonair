// Points d'entrée de l'appli : Répertoires et URLs
var app_name = '151124_ADF';
var server = '192.168.16.200';
var webprotocol = 'http://';
var web_server_url = webprotocol + server;
var root_dir = 'D:/';
var web_root_dir = root_dir + 'xampp/htdocs/messageonair/' + app_name + '/';
var nodejs_root_dir = root_dir + 'nodejs/' + app_name + '/';
var url_static_root = '/' + app_name + '/';
var url_nodejs_root = '/';

// Database
var dbhost     = 'localhost';
var dbuser     = 'preview';
var dbpassword = 'netdirect14';
var dbname     = 'preview';

// PORT number
var PORT = 3014;

// Mail

module.exports.app_name = app_name;
module.exports.server = server;
module.exports.webprotocol = webprotocol;
module.exports.web_server_url = web_server_url;
module.exports.root_dir = root_dir;
module.exports.web_root_dir = web_root_dir;
module.exports.nodejs_root_dir = nodejs_root_dir;
module.exports.url_static_root = url_static_root;
module.exports.url_nodejs_root = url_nodejs_root;

module.exports.PORT = PORT;

module.exports.dbhost = dbhost;
module.exports.dbuser = dbuser;
module.exports.dbpassword = dbpassword;
module.exports.dbname = dbname;
