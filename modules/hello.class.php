<?php
class hello {

  public static function groupchat($message, $from, $user, $msg) {
    global $JABBER;

    $greetings = get_config("hello_greetings");

    if(empty($greetings))
      return;

    if(preg_match("/^(" . $greetings . ").*" . $JABBER->username . ".*$/", $msg)) {
      $greetings = explode("|", $greetings);
      $greet = $greetings[array_rand($greetings)];
      $JABBER->SendMessage($from, "groupchat", NULL, array("body" => $greet));
    }
  }

  public static function help() {
    return "you can say " . str_replace("|", " or ", get_config("hello_greetings")) . " to me.";
  }

}
?>
