<?php
define("SERVER",'192.168.16.200');
define("OPE",'151124_ADF');
define("PORT_NODE",3014);
define("WEBPROTOCOL", 'http://');

define("DBSERVER", '127.0.0.1');
define("DBUSER", 'preview');
define("DBPASSWORD", 'netdirect14');
define("DBNAME", 'preview');

define("WEB_SERVER_URL", WEBPROTOCOL.SERVER.':'.PORT_NODE);
//define("NODEJS_URL_PATH", '/nodejs/'.OPE);
define("NODEJS_URL_PATH", '/');
define("NODEJS_ROOT_URL", WEB_SERVER_URL.NODEJS_URL_PATH);
?>