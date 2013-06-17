<?php
class wetter {

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
		$from_temp = split("/", $from);
		$from = $from_temp[0];
		$msg = $JABBER->GetInfoFromMessageBody($message);
		$user = $from_temp[1];

		if($JABBER->username == $user)
			return;

		if(eregi("^\!wetter", $msg)) {
			if(eregi("^\!wetter ([a-zäüöß0-9 -]*$)", $msg, $matches))
				$url = "http://www.google.com/search?ie=UTF-8&oe=UTF-8&hl=de&q=wetter+" . urlencode($matches[1]);
			else
				$url = "http://www.google.com/search?ie=UTF-8&oe=UTF-8&hl=de&q=wetter+bremen";

			$inputfile = file_get_contents($url);
			$head = extractstring($inputfile, '<div style="padding:5px;float:left">', '</div>');
			
			if($head == "") {
				$JABBER->SendMessage($from, "groupchat", NULL, array("body" => 'Du meinstest wohl !wetter china?'));
				return;
			}

			$temperatur = extractstring($inputfile, '<div style="font-size:140%"><b>', '</b></div>');

			preg_match("/<div>Aktuell: <b>([^<]*)<\/b><br>([^<]*)<br>([^<]*)<\/div>/i", $inputfile, $matches);
			$info = "Aktuell: " . $temperatur . ", " . $matches[1] . "\n" . $matches[2] . "\n" . $matches[3];

			preg_match_all("/<div align=center style=\"padding:5px;float:left\">([^<]*)<br><img style=\"border:1px solid #bbc;margin-bottom:2px\" src=\"\/images\/weather\/[^\.]*\.gif\" alt=\"[^\"]*\" title=\"([^\"]*)\" width=40 height=40 border=0><br><nobr>([^<]*)<\/nobr><\/div>/i", $inputfile, $matches);

			foreach($matches[0] as $nr => $match)
				$forecast .= $matches[1][$nr] . ": " . $matches[2][$nr] . ", " . $matches[3][$nr] . "\n";
			
			$temp = $info . "\n";
			$temp.= $forecast;

			$temp = strip_tags($temp);
			$temp = html_entity_decode($temp, ENT_COMPAT, 'UTF-8');
			$temp = trim($temp);

			$JABBER->SendMessage($from, "groupchat", NULL, array("body" => $temp));
		}
	}

	public static function help() {
		return "!wetter von Bremen, !wetter <Ort>";
	}

}
?>
