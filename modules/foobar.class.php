<?php
class foobar {

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

		// keine eigenen Aussagen interpretieren
		if($JABBER->username == $user)
			return;

		if(preg_match('/^!moo (.*)/i', $msg, $matches)) {
			$msg2 = $matches[1];

			if($msg2 == $JABBER->username)
				$numsg = "fuck you!";
			elseif($msg2 == $user)
				$numsg = "moo at yourself...";
			else
				$numsg = "i moo at you, " . $msg2;

			$JABBER->SendMessage($from, "groupchat", NULL, array("body" => $numsg));
		} elseif(preg_match('/^!rev (.*)/i', $msg, $matches))
			$JABBER->SendMessage($from, "groupchat", NULL, array("body" => utf8_encode(strrev(utf8_decode($matches[1])))));
		elseif($msg == "cow")
			$JABBER->SendMessage($from, "groupchat", NULL, array("body" => "moo!"));
		elseif($msg == "badger badger badger badger badger badger badger badger badger badger badger badger")
			$JABBER->SendMessage($from, "groupchat", NULL, array("body" => "mushroom mushroom!"));
		elseif($msg == "snake")
			$JABBER->SendMessage($from, "groupchat", NULL, array("body" => "Ah snake a snake! Snake, a snake! Ooooh, it's a snake!"));
		elseif($msg == "moo?")
			$JABBER->SendMessage($from, "groupchat", NULL, array("body" => "To moo, or not to moo, that is the question. Whether 'tis nobler in the mind to suffer the slings and arrows of outrageous fish..."));
		elseif($msg == "martian")
			$JABBER->SendMessage($from, "groupchat", NULL, array("body" => "Don't run! We are your friends!"));
	}

	public static function help() {
		return "!moo <name> - moo at <name>\n!rev <string> - reverses string";
	}

}
?>
