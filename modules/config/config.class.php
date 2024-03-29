<?php

class config {
  public static $responsibilities = array('chat' => 'config');

  public static function chat($message, $from, $resource, $msg) {
    global $trusted_users;
    global $config;

    if (!in_array($from, $trusted_users))
      return;

    if (preg_match("/^config (set|get|del) (.*)$/mi", $msg, $matches)) {
      $answer = 'ERR.';

      if ($matches[1] == 'set' && preg_match("/^([^:]{1,}):(.*)$/mi", $matches[2], $submatches)) {
        set_config($submatches[1], $submatches[2]);

        if (get_config($submatches[1]) == $submatches[2])
          $answer = 'OK.';
        else
          $answer = 'ERR.';
      } elseif ($matches[1] == 'get') {
        $answer = get_config($matches[2]);

        if (empty($answer))
          $answer = '-ENOENTRY';
      } elseif ($matches[1] == 'del') {
        del_config($matches[2]);
        if (get_config($matches[2]) == '')
          $answer = 'deleted';
      }
    } elseif (preg_match("/^config rehash$/i", $msg)) {
      $config = array();
      $answer = 'OK.';
    } elseif (preg_match("/^config list$/i", $msg)) {
      $answer = "actual config:\n";
      $result = make_sql_query("SELECT * FROM `config` ORDER BY `name`;");
      while ($row = make_sql_fetch_array($result, MYSQL_ASSOC))
        $answer .= $row['name'] . ': ' . $row['value'] . "\n";
    }

    return $answer;
  }

  public static function trustHelp() {
    return "config set foo:bar|get foo|del foo|rehash to clear cache|list";
  }

}

?>
