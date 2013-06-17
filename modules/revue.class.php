<?php
class revue {

	public static $revues = array();

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

		if(!isset(revue::$revues[$from]))
			revue::$revues[$from] = array();

		if((rand(0, 10) == 1) && ($JABBER->username != $user) && !(preg_match("/^\!.*$/i", $msg)))
			revue::$revues[$from][rand(0, 9)] = array("time" => time(), "user" => $user, "msg" => $msg);

		if(preg_match("/^\!revue$/i", $msg)) {
			$temp = "";
			foreach(revue::$revues[$from] as $revue) {
				if($temp != "") $temp .= "\n";
				$temp .= $revue["user"] . ": " . $revue["msg"];
			}


			if($temp != "")
				$JABBER->SendMessage($from, "groupchat", NULL, array("body" => $temp));
		}
	}

	public static function help() {
		return "!revue - think creative";
	}

}
?>
