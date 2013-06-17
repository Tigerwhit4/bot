<?php
class cartesium {

  public static function groupchat($message, $from, $user, $msg) {
    global $JABBER;

    if($from != 'blackhole@muc.planetcyborg.de')
      return;

    if($msg == "!cartesium") {
      $json_in = get_url("http://act.informatik.uni-bremen.de/weather");

      $answer = html_entity_decode(trim($json_in), ENT_COMPAT, "UTF-8");

      if(empty($answer))
        $answer = "error fetching data";

      $JABBER->SendMessage($from, "groupchat", NULL, array("body" => trim($answer)));
    }
  }

  public static function help() {
    return "!cartesium - cartesium infos";
  }

}
?>
