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
		$timestamp = "";

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

		if(preg_match('/^!(mensa|gw2|hsmensa)(\s+(.*))?$/i', $msg, $matches)) {
			$time = date("G");
			$matches[2] = trim($matches[2]);

			switch(trim($matches[1])) {
				case "mensa":
					$url = "https://mortzu.de/mensa/";
					break;
				case "gw2":
					$url = "https://mortzu.de/gw2/";
					break;
				case "hsmensa":
					$url = "https://mortzu.de/hsmensa/";
					break;
			}

			if($time >= 14 || $matches[2] == "tomorrow")
				$mensa = file_get_contents($url . "?when=tomorrow");
			else
				$mensa = file_get_contents($url);

			if(preg_match("/\<div id=\"mensadata\"\>(.*)\<\/div\>/isU", $mensa, $match))
				$content = html_entity_decode($match[1], ENT_COMPAT, "UTF-8");
			else
				$content = "";

			if(preg_match('/\<h3\>/iU', $content)) {
				preg_match_all('/\<h3\>(.*)\<\/h3\>\n\<p\>(.*)\<\/p\>/iU', $content, $matches);
				preg_match_all('/\<h3\>(.*)\<\/h3\>\n\<ul\>\n\<li\>(.*)\<\/li\>\n\<li\>(.*)\<\/li\>\n\<li\>(.*)\<\/li\>\n\<li\>(.*)\<\/li\>\n\<\/ul\>/iU', $content, $auflauf);
				preg_match_all('/\<h3\>Beilagen\<\/h3\>\n\<ul\>\n(.*)\n\<\/ul\>/iUs', $content, $beilagen);

				$msg = "";

				foreach($matches[1] as $key=>$essen)
					$msg .= $essen . ": " . $matches[2][$key] . "\n";

				if(is_array($auflauf) && isset($auflauf[1][0]) && $auflauf[1][0] == "Aufläufe")
					$msg .= $auflauf[1][0] . ": " . $auflauf[2][0] . "; " . $auflauf[3][0] . "; " . $auflauf[4][0] . "; " . $auflauf[5][0] . "\n";

				if(is_array($beilagen) && isset($beilagen[1][0]))
                                        $msg2 .= "Beilagen: ";

				foreach(explode("\n", $beilagen[1][0]) as $bei)
					$msg2 .= strip_tags($bei) . "; ";

				$msg .= trim($msg2, "; ");
			} else
				$msg = strip_tags($content);

			$JABBER->SendMessage($from, "groupchat", NULL, array("body" => trim($msg)));
		}
	}

	public static function help() {
		return "!mensa o. !gw2 o. !hsmensa <tomorrow> - outputs meal. after 2pm outputs meal for tomorrow; also with parameter tomorrow.";
	}

}
?>
