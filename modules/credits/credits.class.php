<?php
class credits {
  public static function groupchat($message, $from, $user, $msg) {
    if($msg == "!credits") {
        $answer = "Jabberbotframework: https://gitlab.planetcyborg.de/planetcyborg/yoda\n";

        return $answer;
      }
  }

  public static function help() {
    return "!credits - shows my credits";
  }

}
?>
