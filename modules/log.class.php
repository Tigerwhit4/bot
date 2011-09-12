<?php
class log {

	public static function groupchat($message) {
		global $JABBER;
		global $trusted_users;
		global $trust_users;
		global $logdir;
		global $rooms_log;
		
		global $logday;

		$i = 0;
		$timestamp = "";

		while($timestamp == "" && $i < 5) {
			$timestamp = strtotime($message["message"]["#"]["x"][$i]["@"]["stamp"]);
			$i++;
		}

		if($timestamp)
			return;

		$from = $JABBER->GetInfoFromMessageFrom($message);
		$from = explode("/", $from);
		$from = $from[0];
		$msg = $JABBER->GetInfoFromMessageBody($message);

		$from2 = $JABBER->GetInfoFromMessageFrom($message);
		$from_temp = explode("/", $from2);
		$from = $from_temp[0];
		$user = $from_temp[1];

		$timestmp = date("Y-m-d H:i:s");
		$logday2 = date("Y-m-d");

		if(in_array($from, $rooms_log)) {
			if($logday2 != $logday) {
				$logday = $logday2;

				if(!is_dir($logdir . "/" . $from))
					system("mkdir -p " . $logdir . "/" . $from);
			}
			$handle = fopen($logdir . "/" . $from . "/" . $logday . ".log", "a");
			$msg2 = str_replace("\n", "\n>> ", $msg);
			fwrite($handle, $timestmp . " " . $from . ": <" . $user . "> " . $msg2 . "\n");
			@fclose($handle);
		}
	}
}
?>
