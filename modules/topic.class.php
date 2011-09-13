<?php
class topic {

	public static function groupchat($message) {
		global $JABBER;
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
		$timestamp = "";

		while($timestamp == "" && $i < 5) {
			$timestamp = strtotime($message["message"]["#"]["x"][$i]["@"]["stamp"]);
			$i++;
		}

		if($timestamp)
			return;

		if(preg_match('/^!topic (.*)/i', $msg, $matches)) {
			$newtopic = trim($matches[1]);

			if($newtopic != "") {
				if($newtopic == "-clean") {
					$newtopic = "";
					$topic[$from] = "";
				} elseif($topic[$from] != "")
					$newtopic = $newtopic . " | " . $topic[$from];
			}

			$JABBER->SendMessage($from, "groupchat", NULL, array("subject" => $newtopic));
		}
	}

	public static function help() {
		return "!topic <pattern> - adds <pattern> to topic. use -clean as second parameter to clean the topic";
	}
}
?>
