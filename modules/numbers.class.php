<?php
class numbers
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

		if($msg == "!date")
		{
			$date = date("d.m.Y W.") . " Woche " . date("H:i:s");
			$JABBER->SendMessage($from, "groupchat", NULL, array("body" => utf8_encode($date)));
		}
		elseif($msg == "!pi")
		{
			$JABBER->SendMessage($from, "groupchat", NULL, array("body" => utf8_encode(pi())));
		}
		elseif($msg == "!number")
		{
			$number = "";

			while(strlen($number) != 10)
			{
				$number .= zufallszahl(0, 9);
			}

			$JABBER->SendMessage($from, "groupchat", NULL, array("body" => utf8_encode($number)));
		}
	}

	public static function help()
	{
		return "!pi - shows pi\n!date - shows actually date\n!number - shows a random number";
	}

}
?>
