<?php
class google {

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

		$from = $JABBER->GetInfoFromMessageFrom($message);
		$from_temp = explode("/", $from);
		$from = $from_temp[0];
		$msg = $JABBER->GetInfoFromMessageBody($message);
		$user = $from_temp[1];

		if($JABBER->username == $user)
			return;

		if(preg_match('/^!google (.*)/i', $msg, $matches)) {
			$msg2 = $matches[1];

			$http_response_header = array ();

			$url = "http://www.google.de/search?source=ig&hl=de&rlz=&=&q=" . urlencode($msg2) . "&btnI=Auf+gut+Gl%C3%BCck%21&aq=f&aqi=&aql=&oq=";
			$content = file_get_contents($url, false, stream_context_create(array (
				'http' => array (
					'header' => "User-Agent: Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.5; de; rv:1.9.2.13) Gecko/20101203 Firefox/3.6.13\r\n"
				)
			)));

			unset ($redir);
			foreach ($http_response_header as $header_line)
				if (preg_match("/^Location: (.*)/", $header_line, $matches))
					$redir = $matches[1];

			if (!isset ($redir)) {
				preg_match_all("/<a href=\"(https?:\/\/[^\"]*)\"/iu", $content, $matches);
				foreach ($matches[1] as $match)
					if (!preg_match("/^https?:\/\/[^\/]*google/i", $match)) {
						$redir = $match;
						break;
					}
			}

			if (!isset ($redir))
				$redir = "nichts gefunden. nothing found. niada rhrefúndere.";	

			$JABBER->SendMessage($from, "groupchat", NULL, array (
				"body" => $redir
			));
		}
	}

	public static function help() {
		return "!google <pattern> - returns link of the first google hit";
	}

}
?>
