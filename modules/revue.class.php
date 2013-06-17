<?php
class revue {

  public static $revues = array();

  public static function groupchat($message, $from, $user, $msg) {
    global $JABBER;

    if(!isset(revue::$revues[$from]))
      revue::$revues[$from] = array();

    if((rand(0, 10) == 1) && !(preg_match("/^\!.*$/i", $msg)))
      revue::$revues[$from][rand(0, 9)] = array("time" => time(), "user" => $user, "msg" => $msg);

    if(preg_match("/^\!revue$/i", $msg)) {
      $answer = "";
      foreach(revue::$revues[$from] as $revue) {
        if(!empty($answer)) $answer .= "\n";
        $answer .= $revue['user'] . ": " . $revue['msg'];
      }

      if(!empty($answer))
        $JABBER->SendMessage($from, "groupchat", NULL, array("body" => $answer));
    }
  }

  public static function help() {
    return "!revue - think creative";
  }

}
?>
