<?php
class hello {

	public static function groupchat($message) {
		global $JABBER;

		$i = 0;
		$timestamp = "";

		while($timestamp == "" && $i < 5) {
			$timestamp = @strtotime($message["message"]["#"]["x"][$i]["@"]["stamp"]);
			$i++;
		}

		if($timestamp)
			return;

		list($from, $user, $msg) = split_message($message);

		if($JABBER->username == $user)
			return;

		$greetings = get_config("hello_greetings");

		if($greetings == "")
			return;

		if(preg_match("/^(" . $greetings . ").*" . $JABBER->username . ".*$/", $msg)) {
			$greetings = explode("|", $greetings);
			$greet = $greetings[array_rand($greetings)];
			$JABBER->SendMessage($from, "groupchat", NULL, array("body" => $greet));
		}
	}

	public static function chat($message) {
		global $JABBER;

		list($from, , $msg) = split_message($message);

		$greetings = get_config("hello_greetings");

		if($greetings == "")
			return;

		$greetings = explode("|", $greetings);
		
		if(preg_match("/^(" . join("|", $greetings) . ")$/i", $msg)) {
			$greet = $greetings[array_rand($greetings)];
			$JABBER->SendMessage($from, "chat", NULL, array("body" => $greet));
		}
	}

	public static function help() {
		return "you can say " . str_replace("|", " or ", get_config("hello_greetings")) . " to me.";
	}

}
?>
