<?php
class google {

  public static function groupchat($message, $from, $user, $msg) {
    if(preg_match('/^!google (.*)/i', $msg, $matches)) {
      $http_response_header = array();

      $content = get_url("http://www.google.de/search?source=ig&hl=de&rlz=&=&q=" . urlencode($matches[1]) . "&btnI=Auf+gut+Gl%C3%BCck%21&aq=f&aqi=&aql=&oq=");

      foreach($http_response_header as $header_line)
        if(preg_match("/^Location: (.*)/", $header_line, $matches))
          $answer = $matches[1];

      if(!isset ($answer)) {
        preg_match_all("/<a href=\"(https?:\/\/[^\"]*)\"/iu", $content, $matches);
        foreach($matches[1] as $match)
          if(!preg_match("/^https?:\/\/[^\/]*google/i", $match)) {
            $answer = $match;
            break;
          }
      }

      return $answer;
    }
  }

  public static function help() {
    return "!google <pattern> - returns link of the first google hit";
  }

}
?>