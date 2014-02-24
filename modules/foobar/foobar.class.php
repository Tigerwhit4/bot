<?php
class foobar {

  public static function groupchat($message) {
    global $JABBER;

    $i = 0;
    $timestamp = "";

    while($timestamp == "" && $i < 5) {
      $timestamp = @strtotime($message["message"]["#"]["x"][$i]["@"]["stamp"]);
      $i++;
    }

    if($timestamp)
      return;

    list($from, $user, $msg) = split_message($message);

    if($JABBER->username == $user)
      return;

    if(preg_match('/^!moo (.*)/i', $msg, $matches)) {
      if($matches[1] == $JABBER->username)
        $answer = "fuck you!";
      elseif($matches[1] == $user)
        $answer = "moo at yourself...";
      else
        $answer = "i moo at you, " . $matches[1];
    } elseif(preg_match('/^!rev (.*)/i', $msg, $matches))
      $answer = utf8_encode(strrev(utf8_decode($matches[1])));
    elseif($msg == "cow")
      $answer = "moo!";
    elseif($msg == "badger badger badger badger badger badger badger badger badger badger badger badger")
      $answer = "mushroom mushroom!";
    elseif($msg == "snake")
      $answer = "Ah snake a snake! Snake, a snake! Ooooh, it's a snake!";
    elseif($msg == "moo?")
      $answer = "To moo, or not to moo, that is the question. Whether 'tis nobler in the mind to suffer the slings and arrows of outrageous fish...";
    elseif($msg == "martian")
      $answer = "Don't run! We are your friends!";

    if (!empty($answer))
      $JABBER->SendMessage($from, "groupchat", NULL, array("body" => $answer));
  }

  public static function help() {
    return "!moo <name> - moo at <name>\n!rev <string> - reverses string";
  }

}
?>
