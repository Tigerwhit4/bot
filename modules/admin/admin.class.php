<?php

class admin {
  public static $responsibilities = array('chat' => array('say', 'die'));

  public static function chat($message, $from, $resource, $msg) {
    global $JABBER;
    global $trusted_users;

    if (!in_array($from, $trusted_users))
      return;

    if (preg_match("/^say ([^:]*):(.*)$/i", $msg, $matches)) {
      foreach(get_rooms() as $room) {
        if ($matches[1] == $room) {
          $JABBER->sendMessage($room, 'groupchat', NULL, array('body' => $matches[2]));
          return;
        }
      }
    } elseif ($msg == 'die') {
      echo "Sent to death by " . $from . "\n";
      shutdown();
    }
  }

  public static function trustHelp() {
    return "say channel:sentence let me say something\ndie send me to death";
  }

}

?>
