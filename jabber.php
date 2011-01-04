#!/usr/bin/php
<?php
$config = array ();

require "config.php";

if ($sql_type == "mysql")
	require "extlib/dbabstraction/mysql.php";
elseif ($sql_type == "sqlite") require "extlib/dbabstraction/sqlite.php";
else
	die("Please select a sql_type");

make_sql_ensure_connection();

require "extlib/class.jabber.php";
require "extlib/Thread.php";
require "extlib/functions.php";
require "extlib/simplepie/simplepie.inc";

require "threads/fritzbox_thread.php";

if (!Thread :: available())
	die("Threads not supported\n");

ini_set("default_socket_timeout", 5);
ini_set("user_agent", "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-GB; rv:1.9.0.3) Gecko/2008092417 Firefox/3.0.3");

if (!$error_reporting)
	error_reporting(0);

$logday = "";

$modules_groupchat = array ();
$modules_chat = array ();
$modules_normal = array ();
$modules_cron = array ();

$topic = array ();
$trust_users = array ();

$handle = opendir("modules/");

while ($file = readdir($handle)) {
	if (preg_match('/^(.*)\.class\.php$/i', $file, $result)) {
		$modul_name = $result[1];
		require_once ("modules/" . $modul_name . ".class.php");
		$reflector = new ReflectionClass($modul_name);

		if ($reflector->hasMethod("groupchat"))
			array_push($modules_groupchat, $modul_name);

		if ($reflector->hasMethod("chat"))
			array_push($modules_chat, $modul_name);

		if ($reflector->hasMethod("normal"))
			array_push($modules_normal, $modul_name);

		if ($reflector->hasMethod("cron"))
			array_push($modules_cron, $modul_name);
	}
}

closedir($handle);

// cleanup status table
make_sql_query("UPDATE `status` SET `status`=0, `res`='';");

$JABBER = new Jabber;
$JABBER->server = $jabber_server;
$JABBER->port = $jabber_port;
$JABBER->username = $jabber_username;
$JABBER->password = $jabber_password;

$JABBER->iq_version_name = $jabber_version_name;
$JABBER->iq_version_version = $jabber_version_version;
$JABBER->iq_version_os = $jabber_version_os;

$JABBER->resource = $jabber_resource;

$JABBER->enable_logging = $jabber_enable_logging;
$JABBER->log_filename = $jabber_log_filename;
$JABBER->Connect() or die("Couldn't connect to jabber server!\n");
$JABBER->SendAuth() or die("Jabber authentication failed!\n");
$JABBER->SendPresence(NULL, NULL, $online_msg, NULL, $jabber_priority);

$channel_log = get_config("channel_log");
$rooms_log = explode("\n", $channel_log);

$channel = get_config("channel");
$rooms = explode("\n", $channel);

foreach ($rooms as $room)
	$JABBER->SendPresence(NULL, $room . "/" . $JABBER->username, NULL, NULL, NULL);

function Handler_presence_subscribed($message) {
	global $JABBER;

	$jid = $JABBER->GetInfoFromPresenceFrom($message);
	$JABBER->RosterUpdate();
}

function Handler_presence_available($message) {
	global $JABBER;
	global $trust_users;
	global $trusted_users;
	global $rooms;

	$jid2 = strtolower($JABBER->GetInfoFromPresenceFrom($message));
	$jid = $JABBER->StripJID($jid2);

	if (($jid != $JABBER->username . "@" . $JABBER->server) && (!in_array($jid, $rooms))) {
		$lines = make_sql_num_query("SELECT * FROM `status` WHERE `jid` = '" . make_sql_escape($jid) . "';");

		if ($lines < 1)
			$fp = make_sql_query("INSERT INTO `status` ( `id` , `jid` , `status` ) VALUES (NULL , '" . make_sql_escape($jid) . "', '1');");
		else
			if (make_sql_num_query("SELECT * FROM `status` WHERE `jid` = '" . make_sql_escape($jid) . "' AND INSTR(`res`, '" . make_sql_escape(md5($jid2)) . "')=0;") > 0)
				$fp = make_sql_query("UPDATE `status` SET `status` = `status`+1, `res`=CONCAT(`res`, '" . make_sql_escape(md5($jid2)) . "') WHERE `jid` ='" . make_sql_escape($jid) . "';");
	}

	if (in_array($jid, $trusted_users)) {
		if (!in_array($jid, $trust_users))
			$trust_users[] = $jid;
	}
}

function Handler_presence_unavailable($message) {
	global $JABBER;
	global $trust_users;
	global $trusted_users;

	$jid2 = strtolower($JABBER->GetInfoFromPresenceFrom($message));
	$jid = $JABBER->StripJID($jid2);

	$lines = make_sql_num_query("SELECT * FROM `status` WHERE `jid` = '" . make_sql_escape($jid) . "' AND `status` > 0 AND INSTR(`res`, '" . make_sql_escape(md5($jid2)) . "') > 0;");

	if ($lines > 0)
		$fp = make_sql_query("UPDATE `status` SET `status` = `status`-1, `res`=REPLACE(`res`, '" . make_sql_escape(md5($jid2)) . "', '') WHERE `jid` ='" . make_sql_escape($jid) . "';");

	if (in_array($jid, $trusted_users)) {
		if (in_array($jid, $trust_users)) {
			foreach ($trust_users as $trust_user) {
				if ($jid != $trust_user)
					$trust_users2[] = $trust_user;
			}

			$trust_users = $trust_users2;
		}
	}
}

function Handler_presence_subscribe($message) {
	global $JABBER;

	$jid = $JABBER->GetInfoFromPresenceFrom($message);
	$JABBER->SubscriptionAcceptRequest($jid);
	$JABBER->RosterUpdate;
	$JABBER->Subscribe($jid);
}

function Handler_message_groupchat($message) {
	global $modules_groupchat;

	foreach ($modules_groupchat as $modul_name)
		eval ($modul_name . '::groupchat($message);');
}

function Handler_message_normal($message) {
	global $modules_normal;
	global $JABBER;

	$from = $JABBER->GetInfoFromMessageFrom($message);
	$from = $JABBER->StripJID($from);

	if ($from == $JABBER->username . "@" . $JABBER->server)
		return;

	foreach ($modules_normal as $modul_name)
		eval ($modul_name . '::normal($message);');
}

function Handler_message_chat($message) {
	global $modules_chat;
	global $JABBER;

	$from = $JABBER->GetInfoFromMessageFrom($message);
	$from = $JABBER->StripJID($from);

	if ($from == $JABBER->username . "@" . $JABBER->server)
		return;

	foreach ($modules_chat as $modul_name)
		eval ($modul_name . '::chat($message);');
}

$fritzbox_thread = new Thread('fritzbox_cruiser');

$fritzbox_thread->start(10, 'fritzbox_thread');

$i = 0;
while ($JABBER->CruiseControl(1)) {
	$i++;

	foreach ($modules_cron as $modul_name)
		eval ($modul_name . '::cron($i);');
}

// cleanup status table
make_sql_query("UPDATE `status` SET `status`=0, `res`='';");

if ($fritzbox_thread->isAlive())
	$fritzbox_thread->stop();

$JABBER->Disconnect();
@ sql_close($sql_connection);
die("");
?>
