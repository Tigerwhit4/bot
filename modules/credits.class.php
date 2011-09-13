<?php
class credits {
	public static function groupchat($message) {
		global $JABBER;

		$i = 0;

		while($timestamp == "" && $i < 5) {
			$timestamp = strtotime($message["message"]["#"]["x"][$i]["@"]["stamp"]);
			$i++;
		}

		if($timestamp)
			return;

		$from = $JABBER->GetInfoFromMessageFrom($message);
		$from_temp = explode("/", $from);
		$from = $from_temp[0];
		$msg = $JABBER->GetInfoFromMessageBody($message);
		$user = $from_temp[1];

		if($JABBER->username == $user)
			return;

		if($msg == "!credits") {
				$answer = "my founder was helios <helios@planetcyborg.de>\n";
				$answer .= "msquare added a lot of code and therefor he is my co-founder\n";
				$answer .= "---\n";
				$answer .= "helios: !mensa, !gw2, !hsmensa, !topic, !stream, !moo, !addquote, !quote, !gbo, !bash, !ddate, !pi, !date, !number, !checkaps, !fortune, !youporn\n";
				$answer .= "msquare: !ticket, !tabu, !wetter, !revue, !rechner\n";
				$answer .= "jplitza: RSS subscription\n";

				$JABBER->SendMessage($from, "groupchat", NULL, array("body" => trim($answer)));
			}
	}

	public static function help() {
		return "!credits - shows my credits";
	}

}
?>
