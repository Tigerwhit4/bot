<?php
class google {

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

		if(preg_match('/^!google (.*)/i', $msg, $matches)) {
			$msg2 = $matches[1];
			$msg2 = addcslashes($msg2, "'\\");

			ob_start();
			system("ruby << EOF
					require 'rubygems'
					require 'mechanize'

					agent = WWW::Mechanize.new
					page = agent.get(\"http://www.google.com/\")
					search_form = page.forms_with(:name => \"f\").first
					search_form.q = '" . $msg2 . "'
					agent.redirect_ok = false
					results = agent.submit(search_form, search_form.buttons_with(:name => \"btnI\").first)
					puts results.header[\"location\"]"
					);
					$google = rtrim(ob_get_contents());
					ob_end_clean();

					$JABBER->SendMessage($from, "groupchat", NULL, array("body" => utf8_encode($google)));
		}
	}

	public static function help() {
		return "!google <pattern> - returns link of the first google hit";
	}

}
?>
