<?php
class admin {

  public static function chat($message, $from, $resource, $msg) {
    global $JABBER;
    global $trusted_users;
    global $rooms;

    if (!in_array($from, $trusted_users))
      return;

    if(preg_match("/^say ([^:]*):(.*)$/i", $msg, $matches)) {
      foreach($rooms as $room) {
        if($matches[1] == $room) {
          $JABBER->sendMessage($room, "groupchat", NULL, array("body" => $matches[2]));
          return;
        }
      }
    } elseif($msg == "die") {
      $JABBER->Disconnect();
      die("Sent to death by " . $from . "\n");
    }
  }

  public static function trustHelp() {
    return "say channel:sentence let me say something\ndie send me to death";
  }

}
?>
