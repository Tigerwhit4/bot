<?php
class topic {

	public static function groupchat($message) {
		global $JABBER;
		global $topic;

		list($from, $user, $msg) = split_message($message);

		$tmp = $JABBER->GetInfoFromMessageSubject($message);

		if(!empty($tmp))
			$topic[$from] = $tmp;

		if($JABBER->username == $user)
			return;

		$i = 0;
		$timestamp = "";

		while($timestamp == "" && $i < 5) {
			$timestamp = @strtotime($message["message"]["#"]["x"][$i]["@"]["stamp"]);
			$i++;
		}

		if($timestamp)
			return;

		if(preg_match('/^!topic (.*)/i', $msg, $matches)) {
			$newtopic = trim($matches[1]);

			if(!empty($newtopic)) {
				if($newtopic == "-clean") {
					$newtopic = "";
					$topic[$from] = "";
				} elseif(!empty($topic[$from]))
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
