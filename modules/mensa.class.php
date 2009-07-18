<?php
class mensa {

	public static function groupchat($message) {
		global $JABBER;
		global $check_hosts;
		global $trusted_users;
		global $trust_users;
		global $logdir;
		global $room_topic;
		global $rooms_log;

		$i = 0;

		while($timestamp == "" && $i < 5) {
			$timestamp = strtotime($message["message"]["#"]["x"][$i]["@"]["stamp"]);
			$i++;
		}

		if($timestamp)
			return;

		$from = $JABBER->GetInfoFromMessageFrom($message);
		$from_temp = split("/", $from);
		$from = $from_temp[0];
		$msg = $JABBER->GetInfoFromMessageBody($message);
		$user = $from_temp[1];

		if($JABBER->username == $user)
			return;

		if(preg_match('/^!mensa(\s+(.*))?$/i', $msg, $matches)) {
			$time = date("G");
			$matches[1] = trim($matches[1]);

			if($time >= 14 || $matches[1] == "tomorrow")
				$mensa = file_get_contents("http://mortzu.de/mensa/?when=tomorrow");
			else
				$mensa = file_get_contents("http://mortzu.de/mensa/");

			if(eregi("<div id=\"mensadata\">(.*)</div>", $mensa, $match))
				$content = $match[1];
			else
				$content = "";

			if(preg_match('/\<h3\>/iU', $content)) {
				preg_match_all('/\<h3\>(.*)\<\/h3\>(.*)\<br \/\>/iU', $content, $matches);
				preg_match_all('/\<h3\>(.*)\<\/h3\>\n\<ul\>\n\<li\>(.*)\<\/li\>\n\<li\>(.*)\<\/li\>\n\<li\>(.*)\<\/li\>\n\<li\>(.*)\<\/li\>\n\<\/ul\>/iU', $content, $auflauf);

				$msg = "Essen 1: " . $matches[2][0] . "\n";
				$msg .= "Essen 2: " . $matches[2][1] . "\n";
				$msg .= "Wok u. Pfanne: " . $matches[2][2] . "\n";
				$msg .= "Vegetarisch: " . $matches[2][3] . "\n";
				$msg .= "Auflaeufe: " . $auflauf[2][0] . "; " . $auflauf[3][0] . "; " . $auflauf[4][0] . "; " . $auflauf[5][0];
			} else
				$msg = strip_tags($content);

			$JABBER->SendMessage($from, "groupchat", NULL, array("body" => $msg));
		}
	}

	public static function help() {
		return "!mensa <tomorrow> - !mensa outputs meal. after 2pm outputs meal for tomorrow; also with parameter tomorrow. parameter week outputs meals for the week";
	}

}
?>
