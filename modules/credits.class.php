<?php
class credits {
  public static function groupchat($message, $from, $user, $msg) {
    if($msg == "!credits") {
        $answer = "my founder was mortzu <me@mortzu.de>\n";
        $answer .= "msquare added a lot of code and therefor he is my co-founder\n";
        $answer .= "---\n";
        $answer .= "mortzu: !mensa, !gw2, !hsmensa, !topic, !stream, !moo, !addquote, !quote, !gbo, !bash, !ddate, !pi, !date, !number, !checkaps, !fortune, !youporn\n";
        $answer .= "msquare: !ticket, !tabu, !wetter, !revue, !rechner\n";
        $answer .= "jplitza: RSS subscription\n";

        return $answer;
      }
  }

  public static function help() {
    return "!credits - shows my credits";
  }

}
?>
