<?php
class rooms {

	public static function normal($message) {
		global $JABBER;
		global $trusted_users;
		global $trust_users;

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

		if(array_key_exists("#", $message["message"])) {
			$invitearr = $message["message"]["#"];

			if(array_key_exists("x", $invitearr)) {
				$invitearr = $invitearr["x"];

				$i = 0;

				while(!is_array($tmpvar) && $i < 5)
					$tmpvar = $invitearr[$i]["#"]["invite"];

				$i = 0;

				while($tmpvar2 == "" && $i < 5)
					$tmpvar2 = $tmpvar[$i]["@"]["from"];

				$jid = $JABBER->StripJID($tmpvar2);

				if($from != "" && $jid != "") {
					$channel = get_config("channel");
					$channel2 = explode("\n", $channel);

					if(!in_array($from, $channel2) && in_array($jid, $trust_users)) {
						$channel = trim($channel . "\n" . $from);
						del_config("channel");
						set_config("channel", $channel);
						$channel = get_config("channel");
                                    		$rooms = explode("\n", $channel);
					}

					$JABBER->SendPresence(NULL, $from . "/" . $JABBER->username, NULL, NULL, NULL);
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

		if(!in_array($from, $trust_users))
			return;

		if(preg_match("/^channel (add|del) (.*)$/", $msg, $matches)) {
			if($matches[1] == "add") {
				$channel = get_config("channel");
				$channel = trim($channel . "\n" . $matches[2]);
				del_config("channel");
				set_config("channel", $channel);
				$JABBER->SendPresence(NULL, $matches[2] . "/" . $JABBER->username, NULL, NULL, NULL);

				$channel = get_config("channel");
				$rooms = explode("\n", $channel);
			} elseif($matches[1] == "del") {
				$channel = get_config("channel");
				$channel = explode("\n", $channel);

				foreach($channel as $chan) {
					if($chan != $matches[2])
						$newchannel = trim($newchannel . "\n" . $chan);
					else
						$JABBER->SendPresence("unavailable", $matches[2] . "/" . $JABBER->username, NULL, NULL, NULL);
				}

				del_config("channel");
				set_config("channel", $newchannel);

				$channel = get_config("channel");
				$rooms = explode("\n", $channel);
			}
		} elseif($msg == "channel list") {
			$channel = get_config("channel");
			$JABBER->SendMessage($from, "chat", NULL, array("body" => $channel));
		}

		if(preg_match("/^channel_log (add|del) (.*)$/i", $msg, $matches)) {
			if($matches[1] == "add") {
				$channel_log = get_config("channel_log");
				$channel_log = trim($channel_log . "\n" . $matches[2]);
				del_config("channel_log");
				set_config("channel_log", $channel_log);

				$channel_log = get_config("channel_log");
				$rooms_log = explode("\n", $channel_log);
			} elseif($matches[1] == "del") {
				$channel_log = get_config("channel_log");
				$channel_log = explode("\n", $channel_log);

				foreach($channel_log as $chan_log) {
					if($chan_log != $matches[2])
						$newchannel_log = trim($newchannel_log . "\n" . $chan_log);
				}

				del_config("channel_log");
				set_config("channel_log", $newchannel_log);

				$channel_log = get_config("channel_log");
				$rooms_log = explode("\n", $channel_log);
			}
		} elseif($msg == "channel_log list") {
			$channel_log = get_config("channel_log");
			$JABBER->SendMessage($from, "chat", NULL, array("body" => $channel_log));
		}
	}

	public static function trusthelp() {
		return "channel add channel@server|del channel@server|list\nchannel_log add channel@server|del channel@server|list";
	}

}
?>
