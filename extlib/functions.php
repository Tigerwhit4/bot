<?php
	function get_config($name) {
		global $config;

		if(isset($config[$name]))
			return $config[$name];

		$result = make_sql_query("SELECT * FROM `config` WHERE `name` = '" . make_sql_escape($name) . "' LIMIT 1;");
		$row = make_sql_fetch_array($result, MYSQL_ASSOC);
		$config[$name] = $row["value"];
		return trim($row["value"]);
	}

	function set_config($name, $value) {
		global $config;

		if(get_config($name) == "")
			$result = make_sql_query("INSERT INTO `config` SET `name` = '" . make_sql_escape($name) . "', `value` = '" . make_sql_escape($value) . "';") || die(mysql_error());
		else
			$result = make_sql_query("UPDATE `config` SET `value` = '" . make_sql_escape($value) . "' WHERE `name` = '" . make_sql_escape($name) . "' LIMIT 1;") || die(mysql_error());

		$config[$name] = $value;										
	}

	function del_config($name) {
		global $config;
		unset($config[$name]);
		$result = make_sql_query("DELETE FROM `config` WHERE `name` = '" . make_sql_escape($name) . "' LIMIT 1;");
	}

	function shortText($str, $chars) {
		if(strlen($str) > $chars) {
			$str = mb_substr($str, 0, $chars, "UTF-8");
			$str = $str . "...";
			return $str;
		} else
			return $str;
	}

	function chkserver($host, $port) {
		$hostip = @gethostbyname($host);

		if ($hostip == $host)
			return false;
		else {
			if (!$x = @fsockopen($hostip, $port, $errno, $errstr, 5))
				return false;
			else
				return true;

			@fclose($x);
		}
	}

	function getwhois($domain) {
		$whois = new Whois();

		if(!$whois->ValidDomain($domain))
			return "Sorry, the domain is not valid or not supported.";

		if($whois->Lookup($domain))
			return $whois->GetData(1);
		else
			return "Sorry, an error occurred.";
	}

	function gethostbyname6($host, $try_a = false) {
		$dns = gethostbynamel6($host, $try_a);

		if($dns == false)
			return false;
		else
			return $dns[0];
	}

	function gethostbynamel6($host, $try_a = false) {
		if(function_exists("dns_get_record")) {
			$dns6 = dns_get_record($host, DNS_AAAA);

			if($try_a == true) {
				$dns4 = dns_get_record($host, DNS_A);
				$dns = array_merge($dns4, $dns6);
			} else
				$dns = $dns6;

			$ip6 = array();
			$ip4 = array();

			foreach($dns as $record) {
				if ($record["type"] == "A")
					$ip4[] = $record["ip"];

				if ($record["type"] == "AAAA")
					$ip6[] = $record["ipv6"];
			}

			if(count($ip6) < 1) {
				if ($try_a == true) {
					if (count($ip4) < 1)
						return false;
					else
						return $ip4;
				} else
					return false;
			} else
				return $ip6;
		}
		return false;
	}

	function zufallszahl($x = 0, $y = 1000) {
		list($u, $s) = explode(" ", microtime());
		mt_srand((float) $s + ((float) $u * 100000));
		$z = mt_rand($x, $y);
		return $z;
	}

	function extractstring($str, $start, $end) {
		$str_low = strtolower($str);
		$pos_start = strpos($str_low, $start);
		$pos_end = strpos($str_low, $end, ($pos_start + strlen($start)));

		if (($pos_start !== false) && ($pos_end !== false)) {
			$pos1 = $pos_start + strlen($start);
			$pos2 = $pos_end - $pos1;
			return substr($str, $pos1, $pos2);
		}
	}

	function br2nl($string) {
		return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $string);
	}
?>
