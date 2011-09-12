<?php
class credits {
	public static function groupchat($message) {
		global $JABBER;
		global $trusted_users;
		global $trust_users;
		global $logdir;
		global $rooms_log;
		global $rooms;

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
				$msg = "my founder was helios <helios@planetcyborg.de>\n";
				$msg .= "msquare added a lot of code and therefor he is my co-founder\n";
				$msg .= "---\n";
				$msg .= "helios: !mensa, !gw2, !hsmensa, !topic, !stream, !moo, !addquote, !quote, !gbo, !bash, !ddate, !pi, !date, !number, !checkaps, !fortune, !youporn\n";
				$msg .= "jix: !google, !wikipedia\n";
				$msg .= "msquare: !ticket, !tabu, !wetter, !revue, !rechner\n";

				$JABBER->SendMessage($from, "groupchat", NULL, array("body" => trim($msg)));
			}
	}

	public static function help() {
		return "!credits - shows my credits";
	}

}
?>
