<?php
function message_fetcher() {
	global $JABBER;

	while (true) {
		if(file_exists("/var/lib/yoda/message_queue")) {
			$msgs = file("/var/lib/yoda/message_queue");

			foreach($msgs as $msg) {
				preg_match('/^"(.*?)" "(.*?)"(?: "(.*)")?$/i', $msg, $matches);
				if(preg_match('/^[0-9a-zA-Z_-]*@[0-9a-zA-Z_.-]*$/i', $matches[1])) {
					if(isset($matches[3]) && $matches[3] == "muc")
						$JABBER->SendMessage($matches[1], "groupchat", NULL, array("body" => rtrim(utf8_encode($matches[2]))));
					else
						$JABBER->SendMessage($matches[1], "chat", NULL, array("body" => rtrim(utf8_encode($matches[2]))));
				}
			}
		}
	}
	die();
}
?>
