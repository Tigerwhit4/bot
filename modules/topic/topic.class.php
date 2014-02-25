<?php
class topic {
  public static $responsibilities = array('groupchat' => 'topic');

  public static function groupchat($message, $from, $user, $msg) {
    global $JABBER;
    global $topic;

    $topic_old = $JABBER->GetInfoFromMessageSubject($message);

    if(!empty($topic_old))
      $topic[$from] = $topic_old;

    if(preg_match('/^!topic (.*)/i', $msg, $matches)) {
      $topic_new = trim($matches[1]);

      if(!empty($topic_new)) {
        if($topic_new == "-clean") {
          $topic_new = "";
          $topic[$from] = "";
        } elseif(!empty($topic[$from]))
          $topic_new = $topic_new . " | " . $topic[$from];
      }

      $JABBER->SendMessage($from, "groupchat", NULL, array("subject" => $topic_new));
    }
  }

  public static function help() {
    return "!topic <pattern> - adds <pattern> to topic. use -clean as second parameter to clean the topic";
  }
}
?>
