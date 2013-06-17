<?php
class topic {

	public static function groupchat($message) {
		global $JABBER;
		global $check_hosts;
		global $trusted_users;
		global $trust_users;
		global $logdir;
		global $room_topic;
		global $rooms_log;
		global $topic;

		$from = $JABBER->GetInfoFromMessageFrom($message);
		$from_temp = explode("/", $from);
		$from = $from_temp[0];
		$msg = $JABBER->GetInfoFromMessageBody($message);
		$user = $from_temp[1];

		$tmp = $JABBER->GetInfoFromMessageSubject($message);

		if($tmp != "")
			$topic[$from] = $tmp;

		if($JABBER->username == $user)
			return;

		$i = 0;

		while($timestamp == "" && $i < 5) {
			$timestamp = strtotime($message["message"]["#"]["x"][$i]["@"]["stamp"]);
			$i++;
		}

		if($timestamp)
			return;

		if(preg_match('/^!topic (.*)/i', $msg, $matches)) {
			$msg2 = trim($matches[1]);

			if($msg2 != "") {
				if($msg2 == "-clean") {
					$msg2 = "";
					$topic[$from] = "";
				} elseif($topic[$from] != "")
					$msg2 = $msg2 . " | " . $topic[$from];
			}

			$JABBER->SendMessage($from, "groupchat", NULL, array("subject" => $msg2));
		}
	}

	public static function help() {
		return "!topic <pattern> - adds <pattern> to topic. use -clean as second parameter to clean the topic";
	}
}
?>
