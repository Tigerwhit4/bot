<?php
class quotes {

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

		if($msg == "!gbo") {
			$url = "http://german-bash.org/action/random";

			$inputfile = file_get_contents($url);
			$temp = extractstring($inputfile, '<div class="zitat">', '</div>');
			$temp = strip_tags($temp);
			$temp = utf8_decode($temp);
			$temp = html_entity_decode($temp);
			$temp = str_replace("\n", "", $temp);
			$gbo = trim($temp);

			$JABBER->SendMessage($from, "groupchat", NULL, array("body" => utf8_encode($gbo)));
		} elseif($msg == "!bash") {
			$url = "http://bash.org/?random";

			$inputfile = file_get_contents($url);
			$temp = extractstring($inputfile, '<p class="qt">', '</p>');
			$temp = strip_tags($temp);
			$temp = html_entity_decode($temp);
			$bash = trim($temp);

			$JABBER->SendMessage($from, "groupchat", NULL, array("body" => utf8_encode($bash)));
		} elseif(preg_match('/^!addquote (.*)/is', $msg, $matches)) {
			$msg2 = trim($matches[1]);

			if($msg2 != "") {
				$fp = make_mysql_query("INSERT INTO `quotes` ( `id` , `content` , `channel` , `date` ) VALUES (NULL , '" . make_mysql_escape($msg2) . "', '" . make_mysql_escape($from) . "', NOW());");
				$JABBER->SendMessage($from, "groupchat", NULL, array("body" => "Successful added!"));
			}
		} elseif($msg == "!quote") {
			$result = make_mysql_query("SELECT * FROM `quotes` WHERE `channel` = '" . make_mysql_escape($from) . "';");

			while($row = make_mysql_fetch_array($result, MYSQL_ASSOC))
				$content[] = $row["content"];

			$quote = $content[zufallszahl(0, (count($content) - 1))];
			$JABBER->SendMessage($from, "groupchat", NULL, array("body" => $quote));
		}
	}

	public static function help() {
		return "!quote - shows random quote\n!addquote <pattern> - add <pattern> as quote\n!gbo - shows a quote of germanbash.org\n!bash - shows quote of bash.org";
	}

}
?>
