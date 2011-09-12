<?php
class wikipedia {

	public static function groupchat($message) {
		global $JABBER;
		global $trusted_users;
		global $trust_users;
		global $logdir;
		global $rooms_log;

		$i = 0;
		$timestamp = "";

		while ($timestamp == "" && $i < 5) {
			$timestamp = strtotime($message["message"]["#"]["x"][$i]["@"]["stamp"]);
			$i++;
		}

		if ($timestamp)
			return;

		$from = $JABBER->GetInfoFromMessageFrom($message);
		$from_temp = explode("/", $from);
		$from = $from_temp[0];
		$msg = $JABBER->GetInfoFromMessageBody($message);
		$user = $from_temp[1];

		if ($JABBER->username == $user)
			return;

		if (preg_match('/^!wikipedia (.*)/i', $msg, $matches)) {
			$msg2 = $matches[1];
			$http_response_header = array ();
			$url = "http://de.wikipedia.org/w/index.php?title=Spezial%3ASuche&search=" . urlencode($msg2);
			$content = file_get_contents($url, false, stream_context_create(array (
				'http' => array (
					'header' => "User-Agent: Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.5; de; rv:1.9.2.13) Gecko/20101203 Firefox/3.6.13\r\n"
				)
			)));
			$redir = explode(" ", $http_response_header[6]);
			$JABBER->SendMessage($from, "groupchat", NULL, array (
				"body" => $redir[1]
			));
		}
	}

	public static function help() {
		return "!wikipedia <pattern> - returns link of the wikipedia article";
	}

}
?>
