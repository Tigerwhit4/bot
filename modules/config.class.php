<?php
class config {

	public static function chat($message) {
		global $JABBER;
		global $trust_users;
		global $config;

		$i = 0;
		$timestamp = "";

		while ($timestamp == "" && $i < 5) {
			$timestamp = strtotime($message["message"]["#"]["x"][$i]["@"]["stamp"]);
			$i++;
		}

		if ($timestamp)
			return;

		list($from, , $msg) = split_message($message);

		if (!in_array($from, $trust_users))
			return;

		if (preg_match("/^config (set|get|del|list) (.*)$/mi", $msg, $matches)) {
			$return = "ERR.";

			if ($matches[1] == "set" && preg_match("/^([^:]{1,}):(.*)$/mi", $matches[2], $submatches)) {
				set_config($submatches[1], $submatches[2]);

				if (get_config($submatches[1]) == $submatches[2])
					$return = "ok.";
				else
					$return = "ERR.";
			} elseif ($matches[1] == "get") {
				$return = get_config($matches[2]);

				if ($return == "")
					$return = "-ENOENTRY";
			} elseif ($matches[1] == "del") {
				del_config($matches[2]);
				if (get_config($matches[2]) == "")
					$return = "deleted";
			}
		} elseif (preg_match("/^config rehash$/i", $msg)) {
			$config = array();
			$return = "ok.";
		} elseif (preg_match("/^config list$/i", $msg)) {
			$return = "actual config:\n";
			$result = make_sql_query("SELECT * FROM `config` ORDER BY `name`;");
			while ($row = make_sql_fetch_array($result, MYSQL_ASSOC)) {
				$return .= $row['name'] . ": " . $row['value'] . "\n";
			}
		}

                if (!empty($return))
                        $JABBER->SendMessage($from, "chat", NULL, array (
                            "body" => $return
                        ));
	}

	public static function trustHelp() {
		return "config set foo:bar|get foo|del foo|rehash to clear cache|list";

	}

}
?>
