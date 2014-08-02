#! /usr/bin/env php
<?php
$config = array();
$command_prefix = '';

require_once "config.default.php";
if (file_exists("config.php"))
  require_once "config.php";

if (file_exists("extlib/dbabstraction/" . $sql_type . ".php"))
  require "extlib/dbabstraction/" . $sql_type . ".php";
else {
  error_log("Please select a sql_type!\n");
  exit(1);
}

make_sql_ensure_connection();

require_once "extlib/class.jabber.php";
require_once "extlib/Thread.php";
require_once "extlib/functions.php";

if (!Thread :: available()) {
  error_log("Threads not supported!\n");
  exit(1);
}

if (!function_exists("curl_init")) {
  error_log("curl is necessary!\n");
  exit(1);
}

ini_set("default_socket_timeout", 5);
ini_set("user_agent", $user_agent);

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
      $responsibilities = array();
    }

    if (array_key_exists('groupchat', $responsibilities)) {
      if (is_array($responsibilities['groupchat']))
        foreach($responsibilities['groupchat'] as $command)
          $modules_groupchat[$command] = $modul_name;
      else
        $modules_groupchat[$responsibilities['groupchat']] = $modul_name;
    }

    if (array_key_exists('chat', $responsibilities)) {
      if (is_array($responsibilities['chat']))
        foreach($responsibilities['chat'] as $command)
          $modules_chat[$command] = $modul_name;
      else
        $modules_chat[$responsibilities['chat']] = $modul_name;
    }

    if (array_key_exists('normal', $responsibilities)) {
      if(is_array($responsibilities['normal']))
        foreach($responsibilities['normal'] as $command)
          $modules_normal[$command] = $modul_name;
      else
        $modules_normal[$responsibilities['normal']] = $modul_name;
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

$JABBER = new Jabber;
$JABBER->server = $jabber_server;
$JABBER->port = $jabber_port;
$JABBER->ssl = $jabber_ssl;
$JABBER->custom_host = $jabber_custom_host;
$JABBER->username = $jabber_username;
$JABBER->password = $jabber_password;

$JABBER->iq_version_name = $jabber_version_name;
$JABBER->iq_version_version = $jabber_version_version;
$JABBER->iq_version_os = $jabber_version_os;

$JABBER->resource = $jabber_resource;

$JABBER->enable_logging = $jabber_enable_logging;
$JABBER->log_filename = $jabber_log_filename;

if(!$JABBER->Connect()) {
  error_log("Couldn't connect to jabber server!\n");
  exit(1);
}

if(!$JABBER->SendAuth()) {
  error_log("Jabber authentication failed!\n");
  exit(1);
}

$JABBER->SendPresence(NULL, NULL, $online_msg, NULL, $jabber_priority);

foreach (explode("\n", get_config("channel")) as $room)
  $JABBER->SendPresence(NULL, $room . "/" . $JABBER->username, NULL, NULL, NULL);

function Handler_presence_subscribed($message) {
  global $JABBER;

  $JABBER->RosterUpdate();
}

function Handler_presence_available($message) {
  return true;
}

function Handler_presence_unavailable($message) {
  return true;
}

function Handler_presence_subscribe($message) {
  global $JABBER;

  $jid = $JABBER->GetInfoFromPresenceFrom($message);
  $JABBER->SubscriptionAcceptRequest($jid);
  $JABBER->RosterUpdate();
  $JABBER->Subscribe($jid);
}

function Handler_message_groupchat($message) {
  global $command_prefix;
  global $modules_groupchat;
  global $JABBER;

  $i = 0;
  $timestamp = '';

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

  foreach ($modules_normal as $trigger => $modul_name) {
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

  foreach ($modules_chat as $trigger => $modul_name) {
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
