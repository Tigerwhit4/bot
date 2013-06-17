<?php
class topic {

  public static function groupchat($message, $from, $user, $msg) {
    global $JABBER;
    global $topic;

    $tmp = $JABBER->GetInfoFromMessageSubject($message);

    if(!empty($tmp))
      $topic[$from] = $tmp;

    if(preg_match('/^!topic (.*)/i', $msg, $matches)) {
      $newtopic = trim($matches[1]);

      if(!empty($newtopic)) {
        if($newtopic == "-clean") {
          $newtopic = "";
          $topic[$from] = "";
        } elseif(!empty($topic[$from]))
          $newtopic = $newtopic . " | " . $topic[$from];
      }

      $JABBER->SendMessage($from, "groupchat", NULL, array("subject" => $newtopic));
    }
  }

  public static function help() {
    return "!topic <pattern> - adds <pattern> to topic. use -clean as second parameter to clean the topic";
  }
}
?>
