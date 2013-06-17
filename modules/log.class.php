<?php
class log {

	public static function groupchat($message) {
		global $JABBER;
		global $logdir;
		global $rooms_log;
		
		global $logday;

		$i = 0;
		$timestamp = "";

		while($timestamp == "" && $i < 5) {
			$timestamp = @strtotime($message["message"]["#"]["x"][$i]["@"]["stamp"]);
			$i++;
		}

		if($timestamp)
			return;

		$from = $JABBER->GetInfoFromMessageFrom($message);
		@list($from, ) = explode("/", $from);
		$msg = $JABBER->GetInfoFromMessageBody($message);

		$from2 = $JABBER->GetInfoFromMessageFrom($message);
		@list($from, $user) = explode("/", $from2);

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
