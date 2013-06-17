<?php
class chucknorris {

	public static function chat($message) {
		global $JABBER;
		global $trust_users;
		global $rooms;
		
		list($from, , $msg) = split_message($message);

		if (!in_array($from, $trust_users))
			return;

		if(preg_match("/^say ([^:]*):(.*)$/i", $msg, $matches)) {
			foreach($rooms as $room) {
				$room_temp = explode('@', $room);

				if($matches[1] == $room_temp[0]) {
					$JABBER->sendMessage($room, "groupchat", NULL, array("body" => $matches[2]));
					return;
				}
			}
		} elseif($msg == "die") {
			$JABBER->Disconnect();
			die("Sent to death by " . $from . "\n");
		}
	}

	public static function trustHelp() {
		return "say channel:sentence let me say something\ndie send me to death";
	}
	
}
?>
