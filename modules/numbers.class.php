<?php
class numbers {

	public static function groupchat($message) {
		global $JABBER;

		$i = 0;
		$timestamp = "";

		while($timestamp == "" && $i < 5) {
			$timestamp = strtotime($message["message"]["#"]["x"][$i]["@"]["stamp"]);
			$i++;
		}

		if($timestamp)
			return;

		list($from, $user, $msg) = split_message($message);

		if($JABBER->username == $user)
			return;

		if($msg == "!date") {
			$answer = date("d.m.Y W.") . " Woche " . date("H:i:s");
		} elseif($msg == "!pi") {
			$prec_for = ini_get("precision");
			ini_set("precision", "50");
			$answer = pi();
			ini_set("precision", $prec_for);
		} elseif($msg == "!number") {
			$answer = "";

			while(strlen($answer) != 10)
				$answer .= zufallszahl(0, 9);
		}

		if (!empty($answer))
			$JABBER->SendMessage($from, "groupchat", NULL, array("body" => $answer));
	}

	public static function help() {
		return "!pi - shows pi\n!date - shows actually date\n!number - shows a random number";
	}

}
?>
