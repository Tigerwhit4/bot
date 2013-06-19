<?php

/**
 * Yoda shutdown
 */
function shutdown() {
  global $modules_shutdown;
  global $JABBER;
  global $sql_connection;

  // cleanup status table
  make_sql_query("UPDATE `status` SET `status` = 0, `res` = '';");

  foreach ($modules_shutdown as $modul_name)
    call_user_func(array($modul_name, 'shutdown'));

  $JABBER->Disconnect();
  @sql_close($sql_connection);
  die();
}

function get_config($name) {
  global $config;

  if(isset($config[$name]))
    return $config[$name];

  $result = make_sql_query("SELECT * FROM `config` WHERE `name` = '" . make_sql_escape($name) . "' LIMIT 1;");
  $row = make_sql_fetch_array($result, MYSQL_ASSOC);
  $config[$name] = $row['value'];

  return trim($row['value']);
}

function set_config($name, $value) {
  global $config;

  if(get_config($name) == '')
    $result = make_sql_query("INSERT INTO `config` SET `name` = '" . make_sql_escape($name) . "', `value` = '" . make_sql_escape($value) . "';") || die(mysql_error());
  else
    $result = make_sql_query("UPDATE `config` SET `value` = '" . make_sql_escape($value) . "' WHERE `name` = '" . make_sql_escape($name) . "' LIMIT 1;") || die(mysql_error());

  $config[$name] = $value;
}

function del_config($name) {
  global $config;
  unset($config[$name]);
  $result = make_sql_query("DELETE FROM `config` WHERE `name` = '" . make_sql_escape($name) . "' LIMIT 1;");
}

function extractstring($str, $start, $end) {
  $str = stristr($str, $start);
  $str = substr($str, strlen($start));
  $str = stristr($str, $end, true);

  return $str;
}

/**
 * Returns exploded channel config set.
 */
function get_rooms() {
  return explode("\n", get_config("channel"));
}

function split_message($message) {
  global $JABBER;

  $from = $JABBER->GetInfoFromMessageFrom($message);
  $from_temp = explode('/', $from, 2);

  if(in_array($from_temp[0], get_rooms()) && $JABBER->GetInfoFromMessageType($message) != 'groupchat')
    $user = '';
  else
    @ list($from, $resource) = $from_temp;

  $msg = $JABBER->GetInfoFromMessageBody($message);

  return array($from, $resource, $msg);
}

function get_url($url, $disable_v6 = false) {
  if (function_exists("curl_init")) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, ini_get('user_agent'));

    if($disable_v6 == true)
      curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

    if (preg_match('/^(.*):\/\/(.*):(.*)@(.*)$/', $url, $matches)) {
      curl_setopt($ch, CURLOPT_USERPWD, $matches[2] . ':' . $matches[3]);
      curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
      $url = $matches[1] . "://" . $matches[4];
    }

    curl_setopt($ch, CURLOPT_URL, $url);
    return curl_exec($ch);
    curl_close($ch);
  }
  elseif (exec("which wget")) return shell_exec("wget --no-check-certificate -O - -- " . escapeshellarg($url));
  else
    return file_get_contents($url);
}
?>
