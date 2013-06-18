<?php
class rooms {

	public static function normal($message) {
		global $JABBER;
		global $trusted_users;

		$i = 0;
		$timestamp = "";

		while($timestamp == "" && $i < 5) {
			$timestamp = @strtotime($message["message"]["#"]["x"][$i]["@"]["stamp"]);
			$i++;
		}

		if($timestamp)
			return;

		list($from, , $msg) = split_message($message);

		if(array_key_exists("#", $message["message"])) {
			$invitearr = $message["message"]["#"];

			if(array_key_exists("x", $invitearr)) {
				$invitearr = $invitearr["x"];

				$i = 0;
				$tmpvar = "";
				$tmpvar2 = "";

				while(!is_array($tmpvar) && $i < 5)
					$tmpvar = $invitearr[$i]["#"]["invite"];

				$i = 0;

				while($tmpvar2 == "" && $i < 5)
					$tmpvar2 = $tmpvar[$i]["@"]["from"];

				$jid = $JABBER->StripJID($tmpvar2);

				if($from != "" && $jid != "") {
					$channel = get_config("channel");
					$channel2 = explode("\n", $channel);

					if(!in_array($from, $channel2) && in_array($jid, $trusted_users)) {
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
		global $trust_users;

		list($from, , $msg) = split_message($message);

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
	}

	public static function trusthelp() {
		return "channel add channel@server|del channel@server|list";
	}

}
?>
