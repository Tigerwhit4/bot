<?php
class hello {

	public static function groupchat($message) {
		global $JABBER;
		global $check_hosts;
		global $trusted_users;
		global $trust_users;
		global $logdir;
		global $room_topic;
		global $rooms_log;
		global $greetings;

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

		$greetings = get_config("hello_greetings");

		if($greetings == "")
			return;

		if(preg_match("/^(" . $greetings . ").*" . $JABBER->username . ".*$/", $msg)) {
			$greetings = explode("|", $greetings);
			$greet = $greetings[zufallszahl(0, (count($greetings) - 1))];
			$JABBER->SendMessage($from, "groupchat", NULL, array("body" => $greet));
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
		global $greetings;

		$from = $JABBER->GetInfoFromMessageFrom($message);
		$from_temp = explode("/", $from);
		$from = $from_temp[0];
		$msg = $JABBER->GetInfoFromMessageBody($message);
		$user = $from_temp[1];

		if($JABBER->username == $user)
			return;

		$greetings = get_config("hello_greetings");

		if($greetings == "")
			return;

		$greetings = explode("|", $greetings);
		
		if(preg_match("/^(" . join("|", $greetings) . ")$/i", $msg)) {
			$greet = $greetings[zufallszahl(0, (count($greetings) - 1))];
			$JABBER->SendMessage($from, "chat", NULL, array("body" => $greet));
		}
	}

	public static function help() {
		return utf8_decode("you can say " . str_replace("|", " or ", get_config("hello_greetings")) . " to me.");
	}

}
?>
