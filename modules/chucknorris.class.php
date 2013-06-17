<?php
class chucknorris {

	public static function chat($message) {
		global $JABBER;
		global $check_hosts;
		global $trusted_users;
		global $trust_users;
		global $logdir;
		global $room_topic;
		global $rooms_log;
		global $rooms;
		
		global $sql_connection;

		$from = $JABBER->GetInfoFromMessageFrom($message);
		$from = explode("/", $from);
		$from = $from[0];
		$msg = $JABBER->GetInfoFromMessageBody($message);

		if((preg_match("/^say ([^:]*):(.*)$/i", $msg, $matches)) && (in_array($from, $trust_users))) {
			foreach($rooms as $room) {
				$room_temp = explode('@', $room);
				$room_temp = $room_temp[0];

				if($matches[1] == $room_temp) {
					$JABBER->sendMessage($room, "groupchat", NULL, array("body" => $matches[2]));
					return;
				}
			}
		} elseif(($msg == "die") && (in_array($from, $trust_users))) {
			$JABBER->Disconnect();
			die("Sent to death by " . $from . "\n");
		}
	}

	public static function trustHelp() {
		return "say channel:sentence let me say something\ndie send me to death";
	}
	
}
?>
