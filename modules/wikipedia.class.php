<?php
class wikipedia
{

	public static function groupchat($message)
	{
		global $JABBER;
		global $check_hosts;
		global $trusted_users;
		global $trust_users;
		global $logdir;
		global $room_topic;
		global $rooms_log;

		$i = 0;

		while($timestamp == "" && $i < 5)
		{
			$timestamp = strtotime($message["message"]["#"]["x"][$i]["@"]["stamp"]);
			$i++;
		}

		if($timestamp) {return;}

		$from = $JABBER->GetInfoFromMessageFrom($message);
		$from_temp = split("/", $from);
		$from = $from_temp[0];
		$msg = $JABBER->GetInfoFromMessageBody($message);
		$user = $from_temp[1];

		if($JABBER->username == $user) {return;}

		if(preg_match('/^!wikipedia (.*)/i', $msg, $matches))
		{
			$msg2 = $matches[1];
			$msg2 = addcslashes($msg2, "'\\");

			ob_start();
			system("ruby << EOF
					require 'rubygems'
					require 'mechanize'

					agent = WWW::Mechanize.new
					agent.user_agent_alias = 'Mac Safari' # wikipedia doesn't like bots
					page = agent.get(\"http://de.wikipedia.org/wiki/Hauptseite\")
					search_form = page.forms.first
					search_form.search = '" . $msg2 . "'
					agent.redirect_ok = false
					results = agent.submit(search_form, search_form.buttons_with(:name => \"go\").first)
					puts results.header[\"location\"]"
					);
					$wikipedia = rtrim(ob_get_contents());
					ob_end_clean();

					$JABBER->SendMessage($from, "groupchat", NULL, array("body" => utf8_encode($wikipedia)));
		}
	}

	public static function help()
	{
		return "!wikipedia <pattern> - returns link of the wikipedia article";
	}

}
?>
