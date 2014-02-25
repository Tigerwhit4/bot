<?php
class credits {
  public static $responsibilities = array('groupchat' => 'credits');

  public static function groupchat($message, $from, $user, $msg) {
    if($msg == "!credits")
      return "Jabberbotframework: https://gitlab.planetcyborg.de/planetcyborg/yoda\n";
  }

  public static function help() {
    return "!credits - shows my credits";
  }

}
?>
