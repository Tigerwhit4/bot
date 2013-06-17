<?php
class cartesium {

	public static function groupchat($message) {
		global $JABBER;
		global $check_hosts;
		global $trusted_users;
		global $trust_users;
		global $logdir;
		global $room_topic;
		global $rooms_log;

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

		if($msg == "!cartesium") {
				$json = shell_exec("wget --no-check-certificate -q -O - -- https://smartenergy.uni-bremen.de/yoda/");
				preg_match_all('/\<tr\>\<td\>\<div class="(.*)"\>\<h3\>(.*)\<\/h3\>\<\/td\>\<td align=right\>\<div class="(.*)"\>\<h3\>(.*)\<\/h3\>\<\/td\>\<\/tr\>/iU', $json, $info);
				preg_match_all('/\<tr\>\<td\>\<div class="temp"\>\<h3\>(.*)\<\/h3\>\<\/td\>\<\/tr\>/iU', $json, $info2);
				$msg = "";

				foreach($info[2] as $key=>$inf)
					$msg .= html_entity_decode(trim($inf), ENT_COMPAT, "UTF-8") . ": " . html_entity_decode(trim($info[4][$key]), ENT_COMPAT, "UTF-8") . "\n";

				if($info2[1][1] != "")
					$msg .= $info2[1][1] . "\n";

				if($msg == "")
					$msg = "error fetching data";

				$JABBER->SendMessage($from, "groupchat", NULL, array("body" => trim($msg)));
		}
	}

	public static function help() {
		return "!cartesium - cartesium infos";
	}

}
?>
