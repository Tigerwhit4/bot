<?php
class cartesium {

  public static function groupchat($message, $from, $user, $msg) {
    if($from != 'blackhole@muc.planetcyborg.de')
      return;

    if($msg == "!cartesium") {
      $json_in = get_url("http://act.informatik.uni-bremen.de/weather");
      $answer = html_entity_decode(trim($json_in), ENT_COMPAT, "UTF-8");

      return $answer;
    }
  }

  public static function help() {
    return "!cartesium - cartesium infos";
  }

}
?>
