<?php
class helper {

	public static function groupchat($message) {
		global $JABBER;
		global $trust_users;
		global $modules_groupchat;

		$i = 0;
		$timestamp = "";
		
		while($timestamp == "" && $i < 5) {
			$timestamp = @strtotime($message["message"]["#"]["x"][$i]["@"]["stamp"]);
			$i++;
		}

		if($timestamp)
			return;

		list($from, $user, $msg) = split_message($message);

		if($JABBER->username == $user)
			return;

		if($msg == "!help") {
			$help = $JABBER->username . " knows these commands:\n";

			foreach($modules_groupchat as $modul_name) {
				$reflector = new ReflectionClass($modul_name);

				if($reflector->hasMethod("help") && $reflector->hasMethod("groupchat")) {
					$method = new ReflectionMethod($modul_name, "help");
					$help .= $method->invoke(NULL) . "\n";
				}
				if(($reflector->hasMethod("trustHelp")) && ($reflector->hasMethod("groupchat")) && (in_array($from, $trust_users))) {
					$method = new ReflectionMethod($modul_name, "trustHelp");
					$help .= $method->invoke(NULL) . "\n";
				}
			}

			$JABBER->SendMessage($from, "groupchat", NULL, array("body" => $help));
		}
	}

	public static function chat($message) {
		global $JABBER;
		global $trust_users;
		global $modules_chat;

		list($from, , $msg) = split_message($message);

		if($msg == "help") {
			$help = $JABBER->username . " knows these commands:\n";

			foreach($modules_chat as $modul_name) {
				$reflector = new ReflectionClass($modul_name);

				if($reflector->hasMethod("help") && $reflector->hasMethod("chat")) {
					$method = new ReflectionMethod($modul_name, "help");
					$help .= $method->invoke(NULL) . "\n";
				}
				if(($reflector->hasMethod("trustHelp")) && ($reflector->hasMethod("chat")) && (in_array($from, $trust_users))) {
					$method = new ReflectionMethod($modul_name, "trustHelp");
					$help .= $method->invoke(NULL) . "\n";
				}
			}

			$JABBER->SendMessage($from, "chat", NULL, array("body" => str_replace("!", "", $help)));
		}
	}

	public static function help() {
		return "!help - returns this helptext";
	}

}
?>
