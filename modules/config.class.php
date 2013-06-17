<?php
class config {

  public static function chat($message, $from, $resource, $msg) {
    global $JABBER;
    global $trust_users;
    global $config;

    if (!in_array($from, $trust_users))
      return;

    if (preg_match("/^config (set|get|del|list) (.*)$/mi", $msg, $matches)) {
      $answer = "ERR.";

      if ($matches[1] == "set" && preg_match("/^([^:]{1,}):(.*)$/mi", $matches[2], $submatches)) {
        set_config($submatches[1], $submatches[2]);

        if (get_config($submatches[1]) == $submatches[2])
          $answer = "ok.";
        else
          $answer = "ERR.";
      } elseif ($matches[1] == "get") {
        $answer = get_config($matches[2]);

        if ($answer == "")
          $answer = "-ENOENTRY";
      } elseif ($matches[1] == "del") {
        del_config($matches[2]);
        if (get_config($matches[2]) == "")
          $answer = "deleted";
      }
    } elseif (preg_match("/^config rehash$/i", $msg)) {
      $config = array();
      $answer = "ok.";
    } elseif (preg_match("/^config list$/i", $msg)) {
      $answer = "actual config:\n";
      $result = make_sql_query("SELECT * FROM `config` ORDER BY `name`;");
      while ($row = make_sql_fetch_array($result, MYSQL_ASSOC)) {
        $answer .= $row['name'] . ": " . $row['value'] . "\n";
      }
    }

    if (!empty($answer))
      $JABBER->SendMessage($from . '/' . $resource, "chat", NULL, array ("body" => $answer));
  }

  public static function trustHelp() {
    return "config set foo:bar|get foo|del foo|rehash to clear cache|list";
  }

}
?>
