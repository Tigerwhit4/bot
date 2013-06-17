<?php
class config {

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

		if(eregi("^config (set|get|del) (.*)$", $msg, $matches)) {
			$return = "FAIL!";

			if($matches[1] == "set" && eregi("^([^:]{1,}):(.*)$", $matches[2], $submatches)) {
				set_config($submatches[1], $submatches[2]);

				if(get_config($submatches[1]) == $submatches[2])
					$return = "ok.";
				else
					$return = "nicht ok.\n\ndas heisst FAIL!";
			} elseif($matches[1] == "get") {
				$return = utf8_decode(get_config($matches[2]));
				$return = utf8_encode($return);

				if($return == "")
					$return = "nix is da gewesen.";
			} elseif($matches[1] == "del") {
				del_config($matches[2]);
				if(get_config($matches[2]) == "")
					$return = "deleted";
			}

			$JABBER->SendMessage($from, "chat", NULL, array("body" => $return));
		} elseif(eregi("^config rehash$", $msg)) {
			global $config;
			$config = array();
			$JABBER->SendMessage($from, "chat", NULL, array("body" => "ok."));
		}
	}

	public static function trustHelp() {
		return "config set foo:bar|get foo|del foo|rehash to clear cache";

	}

}
?>
