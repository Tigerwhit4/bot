<?php
class log
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
		global $handle;

		$i = 0;

		while($timestamp == "" && $i < 5)
		{
			$timestamp = strtotime($message["message"]["#"]["x"][$i]["@"]["stamp"]);
			$i++;
		}

		if($timestamp) {return;}

		$from = $JABBER->GetInfoFromMessageFrom($message);
		$from = split("/", $from);
		$from = $from[0];
		$msg = $JABBER->GetInfoFromMessageBody($message);

		$from2 = $JABBER->GetInfoFromMessageFrom($message);
		$from_temp = split("/", $from2);
		$from = $from_temp[0];
		$user = $from_temp[1];

		$timestmp = date("Y-m-d H:i:s");
		$logday2 = date("Y-m-d");

		if(in_array($from, $rooms_log))
		{
			if($logday2 != $logday)
			{
				$logday = $logday2;

				if(!is_dir($logdir . "/" . $from))
				{
					system("mkdir -p " . $logdir . "/" . $from);
				}

				@fclose($handle);
				$handle = fopen($logdir . "/" . $from . "/" . $logday . ".log", "a");
			}

			$msg2 = ereg_replace("\n","\n>> ", $msg);
			fwrite($handle, $timestmp . " " . $from . ": <" . $user . "> " . $msg2 . "\n");
		}
	}

	public static function help()
	{
		return "btw: i am loggin your gelaber.";
	}
}
?>
