<?php
class helper {

  public static function groupchat($message, $from, $user, $msg) {
    global $JABBER;
    global $trust_users;
    global $modules_groupchat;

    if($msg == "!help") {
      $help = $JABBER->username . " knows these commands:\n";

      foreach($modules_groupchat as $modul_name) {
        $reflector = new ReflectionClass($modul_name);

        if($reflector->hasMethod("help") && $reflector->hasMethod("groupchat")) {
          $method = new ReflectionMethod($modul_name, "help");
          $help .= $method->invoke(NULL) . "\n";
        }
        if(($reflector->hasMethod("trustHelp")) && ($reflector->hasMethod("groupchat")) && (in_array($from, $trust_users))) {
          $method = new ReflectionMethod($modul_name, "trustHelp");
          $help .= $method->invoke(NULL) . "\n";
        }
      }

      return $help;
    }
  }

  public static function chat($message, $from, $resource, $msg) {
    global $JABBER;
    global $trust_users;
    global $modules_chat;

    if($msg == "help") {
      $help = $JABBER->username . " knows these commands:\n";

      foreach($modules_chat as $modul_name) {
        $reflector = new ReflectionClass($modul_name);

        if($reflector->hasMethod("help") && $reflector->hasMethod("chat")) {
          $method = new ReflectionMethod($modul_name, "help");
          $help .= $method->invoke(NULL) . "\n";
        }
        if(($reflector->hasMethod("trustHelp")) && ($reflector->hasMethod("chat")) && (in_array($from, $trust_users))) {
          $method = new ReflectionMethod($modul_name, "trustHelp");
          $help .= $method->invoke(NULL) . "\n";
        }
      }

      return str_replace('!', '', $help);
    }
  }

  public static function help() {
    return "!help - returns this helptext";
  }

}
?>
