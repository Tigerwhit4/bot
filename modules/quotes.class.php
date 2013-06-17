<?php
class quotes {

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

		if($msg == "!gbo") {
			$answer = self::get_quote_from_site("http://german-bash.org/action/random", "<div class=\"zitat\">", "</div>", true);
			$answer = str_replace("\n", "", $answer);
		} elseif($msg == "!bash")
			$answer = self::get_quote_from_site("http://bash.org/?random", "<p class=\"qt\">", "</p>");
		elseif($msg == "!ibash")
			$answer = self::get_quote_from_site("http://mobil.ibash.de/zitate.php?order=random", "<div width='100%' class='quotetable'>", "</div>");
		elseif(preg_match('/^!addquote (.*)/is', $msg, $matches)) {
			$new_quote = trim($matches[1]);

			if(!empty($new_quote)) {
				$fp = make_sql_query("INSERT INTO `quotes` ( `id` , `content` , `channel` , `date` ) VALUES (NULL , '" . make_sql_escape($new_quote) . "', '" . make_sql_escape($from) . "', NOW());");
				if (make_sql_affected_rows() == 1)
					$answer = "Successfully added!";
			}
		} elseif($msg == "!quote") {
			// get a random row from SQL - it's tricky!
			$result = make_sql_query("SELECT FLOOR(RAND() * COUNT(*)) FROM `quotes` WHERE `channel` = '" . make_sql_escape($from) . "';");
			list($offset) = make_sql_fetch_array($result, MYSQL_NUM);
			$result = make_sql_query("SELECT `content` FROM `quotes` WHERE `channel` = '" . make_sql_escape($from) . "' LIMIT " . $offset . ", 1;");

			list($answer) = make_sql_fetch_array($result, MYSQL_NUM);
		}

		if (!empty($answer))
			$JABBER->SendMessage($from, "groupchat", NULL, array("body" => $answer));
	}

	private static function get_quote_from_site($url, $starttoken, $endtoken, $source_is_utf8 = false) {
		$inputfile = file_get_contents($url);
		$temp = extractstring($inputfile, $starttoken, $endtoken);
		$temp = strip_tags($temp);
		$temp = html_entity_decode($temp, ENT_COMPAT, $source_is_utf8 ? 'UTF-8' : 'ISO-8859-1');
		$temp = str_replace("\n", "", $temp);
		$temp = trim($temp);

		if ($source_is_utf8)
			return $temp;
		else
			return utf8_encode($temp);
	}

	public static function help() {
		return "!quote - shows random quote\n!addquote <pattern> - add <pattern> as quote\n!gbo - shows a quote of germanbash.org\n!bash - shows quote of bash.org\n!ibash - shows quote of ibash.de";
	}

}
?>
