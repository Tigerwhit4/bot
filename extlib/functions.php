<?php
	function clean_output($string)
	{
		$string = str_replace("<br>", " ", $string);
		$string = strip_tags(utf8_encode($string));
		$string = str_replace(">", "", $string);
		$string = str_replace("<", "", $string);
		$string = str_replace("  ", " ", $string);
		$string = trim($string);

		return $string;
	}

	function replace_day($tomorrow = false)
	{
		$tmp_day = date("w") - 1;
		$time = date("G");

		$weekday = array("Mo.", "Di.", "Mi.", "Do.", "Fr.", "Sa.", "So.");

		if($time >= 14 || $tomorrow)
			$tmp_day = $tmp_day + 1;

		return $weekday[$tmp_day];
	}

	function mysql_ensure_connection()
	{
		global $mysql_host;
		global $mysql_user;
		global $mysql_pass;
		global $mysql_dtba;
		global $mysql_connection;

		if(is_null($mysql_connection) || !mysql_ping($mysql_connection))
		{
			@mysql_close($mysql_connection);
			$mysql_connection = mysql_connect($mysql_host, $mysql_user, $mysql_pass);
			mysql_select_db($mysql_dtba);
		}
	}

	function make_mysql_query($query)
	{
		// echo $query . "\n";
		mysql_ensure_connection();
		return mysql_query($query);
	}

	function make_num_query($query)
	{
		mysql_ensure_connection();
		$result = mysql_query($query);
		return mysql_num_rows($result);
	}

	function make_mysql_escape($query)
	{
		mysql_ensure_connection();
		return mysql_real_escape_string($query);
	}

	function make_mysql_affected_rows()
	{
		return mysql_affected_rows();
	}

	function make_mysql_fetch_array($result, $result_type = NULL)
	{
		return mysql_fetch_array($result, $result_type);
	}

	function make_mysql_fetch_row($result)
	{
		return mysql_fetch_row($result);
	}

	function make_mysql_fetch_assoc($result)
	{
		return mysql_fetch_assoc($result);
	}

	$config = array();

	function get_config($name) 
	{
		global $config;
		if(isset($config[$name]))
		{
			return $config[$name];
		}

		$result = make_mysql_query("SELECT * FROM `config` WHERE `name` = '" . make_mysql_escape($name) . "' LIMIT 1;");
		$row = make_mysql_fetch_array($result, MYSQL_ASSOC);
		$config[$name] = $row["value"];
		return $row["value"];
	}

	function set_config($name, $value) 
	{
		global $config;
		if(get_config($name) == "") 
		{
			$result = make_mysql_query("INSERT INTO `config` SET `name` = '" . make_mysql_escape($name) . "', `value` = '" . make_mysql_escape($value) . "';");
		}
		else
		{
			$result = make_mysql_query("UPDATE `config` SET `value` = '" . make_mysql_escape($value) . "' WHERE `name` = '" . make_mysql_escape($name) . "' LIMIT 1;");
		}
		$config[$name] = $value;										
	}

	function del_config($name) {
		global $config;
		unset($config[$name]);
		$result = make_mysql_query("DELETE FROM `config` WHERE `name` = '" . $name . "' LIMIT 1;");
	}

	function shortText($str, $chars)
	{
		if (strlen($str) > $chars)
		{
			$str = mb_substr($str, 0, $chars, "UTF-8");
			$str = $str . "...";
			return $str;
		}
		else
		{
			return $str;
		}
	}

	function chkserver($host, $port)
	{
		$hostip = @gethostbyname($host);

		if ($hostip == $host)
		{
			return false;
		}
		else
		{
			if (!$x = @fsockopen($hostip, $port, $errno, $errstr, 5))
			{
				return false;
			}
			else
			{
				return true;
			}

			@fclose($x);
		}
	}

	function getwhois($domain)
	{
		$whois = new Whois();

		if(!$whois->ValidDomain($domain))
		{
			return "Sorry, the domain is not valid or not supported.";
		}

		if($whois->Lookup($domain))
		{
			return $whois->GetData(1);
		}
		else
		{
			return "Sorry, an error occurred.";
		}
	}

	function gethostbyname6($host, $try_a = false)
	{
		$dns = gethostbynamel6($host, $try_a);

		if($dns == false)
			return false;
		else
			return $dns[0];
	}

	function gethostbynamel6($host, $try_a = false)
	{
		if(function_exists("dns_get_record"))
		{
			$dns6 = dns_get_record($host, DNS_AAAA);

			if($try_a == true)
			{
				$dns4 = dns_get_record($host, DNS_A);
				$dns = array_merge($dns4, $dns6);
			}
			else
			{
				$dns = $dns6;
			}

			$ip6 = array();
			$ip4 = array();

			foreach($dns as $record)
			{
				if ($record["type"] == "A")
				{
					$ip4[] = $record["ip"];
				}

				if ($record["type"] == "AAAA")
				{
					$ip6[] = $record["ipv6"];
				}
			}

			if(count($ip6) < 1)
			{
				if ($try_a == true)
				{
					if (count($ip4) < 1)
					{
						return false;
					}
					else
					{
						return $ip4;
					}
				}
				else
				{
					return false;
				}
			}
			else
			{
				return $ip6;
			}
		}
		return false;
	}

	function zufallszahl($x = 0, $y = 1000)
	{
		list($u, $s) = explode(" ", microtime());
		mt_srand((float) $s + ((float) $u * 100000));
		$z = mt_rand($x, $y);
		return $z;
	}

	function extractstring($str, $start, $end)
	{
		$str_low = strtolower($str);
		$pos_start = strpos($str_low, $start);
		$pos_end = strpos($str_low, $end, ($pos_start + strlen($start)));

		if (($pos_start !== false) && ($pos_end !== false))
		{
			$pos1 = $pos_start + strlen($start);
			$pos2 = $pos_end - $pos1;
			return substr($str, $pos1, $pos2);
		}
	}

	function br2nl($string)
	{
		return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $string);
	}
?>
