<?php
class cartesium {

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

		if($msg == "!cartesium") {
				$ch = curl_init("https://smartenergy.uni-bremen.de/yoda/");
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$json = curl_exec($ch);
				curl_close($ch);

				preg_match_all('/\<tr\>\<td\>\<div class="(.*)"\>\<h3\>(.*)\<\/h3\>\<\/td\>\<td align=right\>\<div class="(.*)"\>\<h3\>(.*)\<\/h3\>\<\/td\>\<\/tr\>/iU', $json, $info);
				preg_match_all('/\<tr\>\<td\>\<div class="temp"\>\<h3\>(.*)\<\/h3\>\<\/td\>\<\/tr\>/iU', $json, $info2);

				$answer = "";

				foreach($info[2] as $key=>$inf)
					$answer .= html_entity_decode(trim($inf), ENT_COMPAT, "UTF-8") . ": " . html_entity_decode(trim($info[4][$key]), ENT_COMPAT, "UTF-8") . "\n";

				if($info2[1][1] != "")
					$answer .= $info2[1][1] . "\n";

				if(empty($answer))
					$answer = "error fetching data";

				$JABBER->SendMessage($from, "groupchat", NULL, array("body" => trim($answer)));
		}
	}

	public static function help() {
		return "!cartesium - cartesium infos";
	}

}
?>
