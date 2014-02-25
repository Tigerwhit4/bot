#! /usr/bin/env php
<?php
$config = array();

require_once "config.default.php";
if(file_exists("config.php"))
  require_once "config.php";

if (file_exists("extlib/dbabstraction/" . $sql_type . ".php"))
  require "extlib/dbabstraction/" . $sql_type . ".php";
else {
  error_log("Please select a sql_type!\n");
  exit(1);
}

make_sql_ensure_connection();

require "extlib/class.jabber.php";
require "extlib/Thread.php";
require "extlib/functions.php";

if (!Thread :: available()) {
  error_log("Threads not supported!\n");
  exit(1);
}

if (!function_exists("curl_init")) {
  error_log("curl is necessary!\n");
  exit(1);
}

ini_set("default_socket_timeout", 5);
ini_set("user_agent", "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-GB; rv:1.9.0.3) Gecko/2008092417 Firefox/3.0.3");

if (!$error_reporting)
  error_reporting(0);
else
  error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);

$modules_groupchat = array();
$modules_chat = array();
$modules_normal = array();
$modules_cron = array();
$modules_init = array();
$modules_shutdown = array();

$topic = array();

$handle = opendir("modules/");

while ($file = readdir($handle)) {
  if(is_dir("modules/" . $file) && file_exists("modules/" . $file . "/" . $file . ".class.php")) {
    $modul_name = $file;

    require_once ("modules/" . $modul_name . "/" . $modul_name . ".class.php");
    $reflector = new ReflectionClass($modul_name);

    try {
      $responsibilities = $reflector->getProperty('responsibilities')->getValue();
    } catch (ReflectionException $e) {
    }

    if (is_array($responsibilities)) {
      if (array_key_exists('groupchat', $responsibilities)) {
        foreach($responsibilities as $responsibility) {
          if(is_array($responsibility)) {
            foreach($responsibility as $sub_responsibility)
              $modules_groupchat[$sub_responsibility] = $modul_name;
          } else
            $modules_groupchat[$responsibility] = $modul_name;
        }
      }

      if (array_key_exists('chat', $responsibilities)) {
        foreach($responsibilities as $responsibility) {
          if(is_array($responsibility)) {
            foreach($responsibility as $sub_responsibility)
              $modules_chat[$sub_responsibility] = $modul_name;
          } else
            $modules_chat[$responsibility] = $modul_name;
        }
      }

      if (array_key_exists('normal', $responsibilities)) {
        foreach($responsibilities as $responsibility) {
          if(is_array($responsibility)) {
            foreach($responsibility as $sub_responsibility)
              $modules_normal[$sub_responsibility] = $modul_name;
          } else
            $modules_normal[$responsibility] = $modul_name;
        }
      }
    }

    if ($reflector->hasMethod("cron"))
      array_push($modules_cron, $modul_name);

    if ($reflector->hasMethod("init"))
      array_push($modules_init, $modul_name);

    if ($reflector->hasMethod("shutdown"))
      array_push($modules_shutdown, $modul_name);
  }
}

closedir($handle);

// cleanup status table
make_sql_query("UPDATE `status` SET `status` = 0, `res` = '';");

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

foreach (explode("\n", get_config("channel")) as $room)
  $JABBER->SendPresence(NULL, $room . "/" . $JABBER->username, NULL, NULL, NULL);

function Handler_presence_subscribed($message) {
  global $JABBER;

  $JABBER->RosterUpdate();
}

function Handler_presence_available($message) {
  global $JABBER;
  global $trusted_users;

  $jid_with_resource = strtolower($JABBER->GetInfoFromPresenceFrom($message));
  $jid = $JABBER->StripJID($jid_with_resource);

  if (($jid != $JABBER->username . '@' . $JABBER->server) && (!in_array($jid, get_rooms()))) {
    if (make_sql_num_query("SELECT * FROM `status` WHERE `jid` = '" . make_sql_escape($jid) . "';") == 0)
      $fp = make_sql_query("INSERT INTO `status` ( `id` , `jid` , `status` ) VALUES (NULL , '" . make_sql_escape($jid) . "', '1');");
    elseif (make_sql_num_query("SELECT * FROM `status` WHERE `jid` = '" . make_sql_escape($jid) . "' AND INSTR(`res`, '" . make_sql_escape(md5($jid_with_resource)) . "')=0;") > 0)
      $fp = make_sql_query("UPDATE `status` SET `status` = `status`+1, `res`=CONCAT(`res`, '" . make_sql_escape(md5($jid_with_resource)) . "') WHERE `jid` ='" . make_sql_escape($jid) . "';");
  }
}

function Handler_presence_unavailable($message) {
  global $JABBER;
  global $trusted_users;

  $jid_with_resource = strtolower($JABBER->GetInfoFromPresenceFrom($message));
  $jid = $JABBER->StripJID($jid_with_resource);

  if (make_sql_num_query("SELECT * FROM `status` WHERE `jid` = '" . make_sql_escape($jid) . "' AND `status` > 0 AND INSTR(`res`, '" . make_sql_escape(md5($jid_with_resource)) . "') > 0;") > 0)
    $fp = make_sql_query("UPDATE `status` SET `status` = `status`-1, `res`=REPLACE(`res`, '" . make_sql_escape(md5($jid_with_resource)) . "', '') WHERE `jid` ='" . make_sql_escape($jid) . "';");
}

function Handler_presence_subscribe($message) {
  global $JABBER;

  $jid = $JABBER->GetInfoFromPresenceFrom($message);
  $JABBER->SubscriptionAcceptRequest($jid);
  $JABBER->RosterUpdate();
  $JABBER->Subscribe($jid);
}

function Handler_message_groupchat($message) {
  global $modules_groupchat;
  global $JABBER;

  $i = 0;
  $timestamp = "";

  while(empty($timestamp) && $i < 5) {
    $timestamp = @strtotime($message['message']['#']['x'][$i]['@']['stamp']);
    $i++;
  }

  if($timestamp)
    return;

  list($from, $user, $msg) = split_message($message);
  list($command) = explode(' ', $msg);

  if($JABBER->username == $user)
    return;

  foreach ($modules_groupchat as $trigger => $modul_name) {
    if($trigger != ltrim($command, $command_prefix))
      continue;

    $answer = call_user_func_array(array($modul_name, 'groupchat'), array($message, $from, $user, $msg));

    if(!empty($answer))
      $JABBER->SendMessage($from, "groupchat", NULL, array ("body" => $answer));

    unset($answer);
  }
}

function Handler_message_normal($message) {
  global $modules_normal;
  global $JABBER;

  list($from, $resource, $msg) = split_message($message);
  list($command) = explode(' ', $msg);

  if ($from == $JABBER->username . '@' . $JABBER->server)
    return;

  foreach ($modules_normal as $modul_name) {
    if($trigger != $command)
      continue;

    $answer = call_user_func_array(array($modul_name, 'normal'), array($message, $from, $resource, $msg));

    if(!empty($answer))
      $JABBER->SendMessage($from . '/' . $resource, "normal", NULL, array ("body" => $answer));

    unset($answer);
  }
}

function Handler_message_chat($message) {
  global $modules_chat;
  global $JABBER;

  list($from, $resource, $msg) = split_message($message);
  list($command) = explode(' ', $msg);

  if ($from == $JABBER->username . '@' . $JABBER->server)
    return;

  foreach ($modules_chat as $modul_name) {
    if($trigger != $command)
      continue;

    $answer = call_user_func_array(array($modul_name, 'chat'), array($message, $from, $resource, $msg));

    if(!empty($answer))
      $JABBER->SendMessage($from . '/' . $resource, "chat", NULL, array ("body" => $answer));

    unset($answer);
  }
}

foreach ($modules_init as $modul_name)
  call_user_func(array($modul_name, 'init'));

$i = 0;
while ($JABBER->CruiseControl(1)) {
  $i++;

  foreach ($modules_cron as $modul_name)
    call_user_func_array(array($modul_name, 'cron'), array($i));
}

shutdown();

?>
