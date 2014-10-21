<?php

class helper {
  public static $responsibilities = array('chat' => 'help', 'groupchat' => 'help');

  public static function groupchat($message, $from, $user, $msg) {
    global $JABBER;
    global $trusted_users;
    global $modules_groupchat;

    if ($msg == '!help') {
      $answer = $JABBER->username . " knows these commands:\n";

      foreach (array_unique($modules_groupchat) as $modul_name) {
        $reflector = new ReflectionClass($modul_name);

        if ($reflector->hasMethod('help') && $reflector->hasMethod('groupchat')) {
          $method = new ReflectionMethod($modul_name, 'help');
          $answer .= $method->invoke(NULL) . "\n";
        }
        if ($reflector->hasMethod('trustHelp') && $reflector->hasMethod('groupchat') && in_array($from, $trusted_users)) {
          $method = new ReflectionMethod($modul_name, 'trustHelp');
          $answer .= $method->invoke(NULL) . "\n";
        }
      }

      return $answer;
    }
  }

  public static function chat($message, $from, $resource, $msg) {
    global $JABBER;
    global $trusted_users;
    global $modules_chat;

    if($msg == 'help') {
      $answer = $JABBER->username . " knows these commands:\n";

      foreach(array_unique($modules_chat) as $modul_name) {
        $reflector = new ReflectionClass($modul_name);

        if ($reflector->hasMethod('help') && $reflector->hasMethod('chat')) {
          $method = new ReflectionMethod($modul_name, 'help');
          $answer .= $method->invoke(NULL) . "\n";
        }

        if ($reflector->hasMethod('trustHelp') && $reflector->hasMethod('chat') && in_array($from, $trusted_users)) {
          $method = new ReflectionMethod($modul_name, 'trustHelp');
          $answer .= $method->invoke(NULL) . "\n";
        }
      }

      return str_replace('!', '', $answer);
    }
  }

  public static function help() {
    return "!help - returns this helptext";
  }

}

?>
