<?php
class rechner {
	
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

		if(preg_match("/^\!rechner /i", $msg)) {
			if(preg_match("/^\!rechner ((w|wayne|exp|e|inch|cm|c|h|pi|cos|sin|mod|tan|minuten|sekunden|meter|in|euro|dollar|sqrt|[0-9!+.,*\/()^ -]+)*)$/i", $msg, $matches)) {
				$match = preg_replace("/(wayne|w)/i", "(42/23)", $matches[1]);
				$url = "http://www.google.de/search?q=" . urlencode($match);
	
				$inputfile = file_get_contents($url);
				$answer = extractstring($inputfile, '<h2 class=r style="font-size:138%"><b>', '</b></h2>');
				
				$answer = str_replace('<sup>', '^', $answer);
				$answer = strip_tags($answer);
				$answer = html_entity_decode($answer, ENT_COMPAT, 'UTF-8');
				$answer = str_replace("\n", "", $answer);
				$answer = str_replace("Â", "", $answer);
				$answer = trim($answer);

				if($answer == "")
					$answer = "42";

				$JABBER->SendMessage($from, "groupchat", NULL, array("body" => $answer));
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
