<?php
class tabu {

	public static $on = array();

	public static $words = array();

	public static function groupchat($message) {
		global $JABBER;
		global $check_hosts;
		global $trusted_users;
		global $trust_users;
		global $logdir;
		global $room_topic;
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
		$from_temp = split("/", $from);
		$from = $from_temp[0];
		$msg = $JABBER->GetInfoFromMessageBody($message);
		$user = $from_temp[1];

		if($from != 'blackhole@conference.cyb0rg.org')
			return;

		if($JABBER->username == $user)
			return;

		if(tabu::$on[$from] && is_array(tabu::$words[$from])) {
			foreach(tabu::$words[$from] as $word) {
				if(eregi("^[^\!]{1}.*$", $msg) && (eregi("[^a-z0-9öäüß]{1}" . $word . "[^a-z0-9äöüß]{1}", $msg) || eregi("^" . $word . "[^a-z0-9äöüß]{1}", $msg) || eregi("[^a-z0-9äöüß]{1}" . $word . "$", $msg) || eregi("^" . $word . "$", $msg))) {
					$JABBER->SendMessage($from, "groupchat", NULL, array("body" => $user . " hat leider $word benutzt, was verboten ist."));

					$kick = $JABBER->SendIq($from, "set", "kick" . time(), "http://jabber.org/protocol/muc#admin", "<item nick='{$user}' role='none'><reason>Tabu! " . $word . " ist doch verboten.</reason></item>", $from);

					if($kick["iq"]["@"]["type"] == "result") {
						$JABBER->SendMessage($from, "groupchat", NULL, array("body" => "Abflug!"));
					} else {
						$JABBER->SendMessage($from, "groupchat", NULL, array("body" => "Aber ich kann " . $user . " leider nicht rausschmeissen."));
					}

					break;
				}
			}
		}

		if((eregi("^\!tabu start$", $msg, $matches))) {
			tabu::$on[$from] = true;
			$JABBER->SendMessage($from, "groupchat", NULL, array("body" => "Tabu gestartet."));
		}

		if((eregi("^!tabu stop$", $msg, $matches))) {
			tabu::$on[$from] = false;
			$JABBER->SendMessage($from, "groupchat", NULL, array("body" => "Tabu kann man nicht stoppen..."));
		}

		if((eregi("^\!tabu add ([a-z0-9üöäß]*)$", $msg, $matches))) {
			$match = strtolower($matches[1]);

			if(!is_array(tabu::$words[$from])) tabu::$words[$from] = array();

			if(!in_array($match, tabu::$words[$from])) {
				array_push(tabu::$words[$from], $match);
				$JABBER->SendMessage($from, "groupchat", NULL, array("body" => "Suckzessfuhl äddät."));
			} else
				$JABBER->SendMessage($from, "groupchat", NULL, array("body" => $match . " ist schon auf der Liste."));
		}

		if((eregi("^\!tabu del ([a-z0-9üöäß]*)$", $msg, $matches))) {
			$match = strtolower($matches[1]);

			if(in_array($match, tabu::$words[$from])) {
				unset(tabu::$words[$from][array_search($match, tabu::$words[$from])]);
				$JABBER->SendMessage($from, "groupchat", NULL, array("body" => $match . " ist weg von der Liste."));
			} else
				$JABBER->SendMessage($from, "groupchat", NULL, array("body" => "Du mogelst. " . $match . " ist garnicht auf der Liste."));
		}

		if((eregi("^\!tabu$", $msg, $matches))) {
			if(is_array(tabu::$words[$from])) {
				if(tabu::$on[$from])
					$JABBER->SendMessage($from, "groupchat", NULL, array("body" => "Tabu laeuft mit folgender Liste:\n" . join("\n", tabu::$words[$from])));
				else
					$JABBER->SendMessage($from, "groupchat", NULL, array("body" => "Tabu laeuft nicht. -> !tabu start\n" . join("\n", tabu::$words[$from])));
			} else {
				if(tabu::$on[$from])
					$JABBER->SendMessage($from, "groupchat", NULL, array("body" => "Tabu laeuft, aber die List ist leer.\nWelch Unfug."));
				else
					$JABBER->SendMessage($from, "groupchat", NULL, array("body" => "Tabu laeuft nicht."));
			}
		}
	}

	public static function help() {
		return "!tabu - shows tabu status and wordlist\n!tabu add <word> - adds word to the list\n!tabu del <word> - removes word from the list\n!tabu start/stop - starts/stops the game";
	}

}
?>
