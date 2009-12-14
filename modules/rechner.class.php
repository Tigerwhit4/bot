<?php
class rechner {
	
	public static function groupchat($message) {
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

		if($JABBER->username == $user)
			return;

		if(preg_match("/^\!rechner /i", $msg)) {
			if(preg_match("/^\!rechner ((w|wayne|inch|cm|c|h|pi|cos|sin|mod|tan|minuten|sekunden|meter|in|euro|dollar|[0-9+.,*/()\^ -]+)*$/i)", $msg, $matches)) {
				$match = preg_replace("/(w|wayne)/i", "(42/23)", $matches[1]);
				$url = "http://www.google.de/search?q=" . urlencode($match);
	
				$inputfile = file_get_contents($url);
				$temp = extractstring($inputfile, '<h2 class=r style="font-size:138%"><b>', '</b></h2>');
				
				$temp = str_replace('<sup>', '^', $temp);
				$temp = strip_tags($temp);
				$temp = utf8_encode($temp);
				$temp = html_entity_decode($temp, ENT_COMPAT, 'UTF-8');
				$temp = str_replace("\n", "", $temp);
				$temp = trim($temp);

				$JABBER->SendMessage($from, "groupchat", NULL, array("body" => $temp));
			} else {
				$JABBER->SendMessage($from, "groupchat", NULL, array("body" => '42'));
			}
		}
	}

	public static function help() {
		return "!rechner <term> - calculates term";
	}

}
?>
