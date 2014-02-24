<?php
class messages {
  private static $thread;
  private static $port = 59152;

  public static function init() {
    self::$thread = new Thread(array(__CLASS__, 'worker'));
    self::$thread->start();
  }

  public static function worker() {
    global $JABBER;

    while (true) {
      if(!($socket = socket_create(AF_INET6, SOCK_STREAM, SOL_TCP))) {
        echo 'socket_create() failed: ' . socket_strerror(socket_last_error()) . "\n";
        sleep(60);
        continue;
      }
      if(!socket_bind($socket, '::', self::$port)) {
        echo 'socket_bind() failed: ' . socket_strerror(socket_last_error($socket)) . "\n";
        @socket_shutdown($socket, 2);
        @socket_close($socket);
        sleep(60);
        continue;
      }
      if(!socket_listen($socket)) {
        echo 'socket_listen() failed: ' . socket_strerror(socket_last_error($socket)) . "\n";
        @socket_shutdown($socket, 2);
        @socket_close($socket);
        sleep(60);
        continue;
      }

      while ($client = socket_accept($socket)) {
        $clientaddress = "";
        socket_getpeername($client, $clientaddress);
        $token = trim(socket_read($client, 128, PHP_NORMAL_READ));
        $tokens = json_decode(get_config('message_tokens'), true);

        if ($token == $tokens[$clientaddress]) {
          while(($msg = @socket_read($client, 1024, PHP_NORMAL_READ)) && !empty($msg)) {
            preg_match('/^"(.*?)" "(.*?)"(?: "(.*)")?\s*$/i', $msg, $matches);
            if(preg_match('/^[0-9a-zA-Z_-]*@[0-9a-zA-Z_.-]*$/i', $matches[1])) {
              if(isset($matches[3]) && $matches[3] == "muc")
                $JABBER->SendMessage($matches[1], "groupchat", NULL, array("body" => rtrim($matches[2])));
              else
                $JABBER->SendMessage($matches[1], "chat", NULL, array("body" => rtrim($matches[2])));
            }
          }
        }

        socket_shutdown($client, 2);
        socket_close($client);
      }

      socket_shutdown($socket, 2);
      socket_close($socket);
    }
    die();
  }

  public static function shutdown() {
    if (self::$thread->isAlive())
      self::$thread->stop(SIGKILL, true);
  }
}
?>
