<?php
class kick {

	public static $kickqueue = array();

	public static function cron($i) {
		global $JABBER;
		global $rooms;
		global $trust_users;

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
		global $check_hosts;
		global $trusted_users;
		global $trust_users;
		global $logdir;
		global $room_topic;
		global $rooms_log;
		global $rooms;

		$from = $JABBER->GetInfoFromMessageFrom($message);
		$from = explode("/", $from);
		$from = $from[0];
		$msg = $JABBER->GetInfoFromMessageBody($message);

		if((preg_match("/^kick ([^:]*):(.*)$/i", $msg, $matches)) && (in_array($from, $trust_users))) {
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
					// return;
				}
			}
		}

		if((preg_match("/^kickr (.*)$/i", $msg, $matches)) && (in_array($from, $trust_users))) {
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
