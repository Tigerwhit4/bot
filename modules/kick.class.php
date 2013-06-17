<?php
class kick {

	public static $kickqueue = array();

	public static function cron($i) {
		global $JABBER;

		if(count(kick::$kickqueue) > 0) {
			foreach(kick::$kickqueue as $nr => $kick) {
				if($kick["timeout"] > 0) {
					$JABBER->SendMessage($kick["room"], "groupchat", NULL, array("body" => $kick["timeout"]));
					kick::$kickqueue[$nr]["timeout"]--;
				} else {
					$JABBER->SendIq($kick["room"], "set", "kick" . time(), "http://jabber.org/protocol/muc#admin", "<item nick='{$kick["nick"]["nick"]}' role='none'><reason>Weils geht.</reason></item>", $kick["room"]);
					unset(kick::$kickqueue[$nr]);
				}
			}
		}
	}


	public static function chat($message) {
		global $JABBER;
		global $trust_users;
		global $rooms;

		list($from, , $msg) = split_message($message);

		if (!in_array($from, $trust_users))
			return;

		if(preg_match("/^kick ([^:]*):(.*)$/i", $msg, $matches)) {
			foreach($rooms as $room) {
				$room_temp = explode('@', $room);
				$room_temp = $room_temp[0];

				if(($matches[1] == $room) || ($matches[1] == $room_temp)) {
					$packet = "<iq from='$from' id='kick" . time() . "' to='" . $room . "' type='set'>";
					$packet .= "<query xmlns='http://jabber.org/protocol/muc#admin'>";
					$packet .= "<item nick='" . $matches[2] . "' role='none'><reason>Weils geht.</reason></item>";
					$packet .= "</query>";
					$packet .= "</iq>";
					$JABBER->SendPacket($packet);
				}
			}
		}

		if(preg_match("/^kickr (.*)$/i", $msg, $matches)) {
			foreach($rooms as $room) {
				$room_temp = explode('@', $room);
				$room_temp = $room_temp[0];

				if(($matches[1] == $room) || ($matches[1] == $room_temp)) {
					// zutreffender raum
					$nicklist = $JABBER->SendIq($room, "get", "voice" . time(), "http://jabber.org/protocol/muc#admin", "<item role='participant'/>", $from);
					
					if($nicklist["iq"]["@"]["type"] == "result") {
						$kickable = array();

						foreach($nicklist["iq"]["#"]["query"][0]["#"]["item"] as $participant) {
							$participant = $participant["@"];

							$from = explode("/", $participant["jid"]);
							$from = $from[0];

							if(!in_array($from, $trust_users))
								array_push($kickable, $participant);
						}
						array_push(kick::$kickqueue, array("timeout" => 10, "room" => $room, "nick" => $kickable[rand(0, count($kickable) - 1)]));
					}
				}
			}
		}
	}

	public static function trustHelp() {
		return "kick channel:nick - kicks nick from channel\nkickr channel - kicks some random persons from channel";
	}

}
?>
