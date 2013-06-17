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

		$from = $JABBER->GetInfoFromMessageFrom($message);
		$from_temp = explode("/", $from);
		$from = $from_temp[0];
		$msg = $JABBER->GetInfoFromMessageBody($message);
		$user = $from_temp[1];

		if($JABBER->username == $user)
			return;

		if($msg == "!date") {
			$date = date("d.m.Y W.") . " Woche " . date("H:i:s");
			$JABBER->SendMessage($from, "groupchat", NULL, array("body" => $date));
		} elseif($msg == "!pi") {
			$prec_for = ini_get("precision");
			ini_set("precision", "50");
			$JABBER->SendMessage($from, "groupchat", NULL, array("body" => pi()));
			ini_set("precision", $prec_for);
		} elseif($msg == "!number") {
			$number = "";

			while(strlen($number) != 10)
				$number .= zufallszahl(0, 9);

			$JABBER->SendMessage($from, "groupchat", NULL, array("body" => $number));
		}
	}

	public static function help() {
		return "!pi - shows pi\n!date - shows actually date\n!number - shows a random number";
	}

}
?>
