<?php
$error_reporting = false;

$trusted_users = array();

// sqlite, mysql or mysqli
$sql_type = "sqlite";

$sql_host = "localhost";
$sql_user = "mysqluser";
$sql_pass = "mysqlpw";
$sql_dtba = "mysqldb";

$jabber_server = "jabberserver";
$jabber_port = "5222";
$jabber_ssl = false;
$jabber_username = "jabberuser";
$jabber_password = "jabberpw";

$uname = posix_uname();

$jabber_version_name = "Yoda Jabber/MUC bot";
$jabber_version_version = shell_exec('git rev-parse HEAD');
$jabber_version_os = $uname['sysname'];
$jabber_resource = $uname['nodename'];
$jabber_priority = 5;
$jabber_enable_logging = true;
$jabber_log_filename = "logs/xmpp.log";

$online_msg = "foo";

$command_prefix = "!";
?>
