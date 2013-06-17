<?php
class wetter {

	public static function groupchat($message) {
		global $JABBER;
		global $check_hosts;
		global $trusted_users;
		global $trust_users;
		global $logdir;
		global $room_topic;
		global $rooms_log;

		$i = 0;

		while ($timestamp == "" && $i < 5) {
			$timestamp = strtotime($message["message"]["#"]["x"][$i]["@"]["stamp"]);
			$i++;
		}

		if ($timestamp)
			return;

		$from = $JABBER->GetInfoFromMessageFrom($message);
		$from_temp = explode("/", $from);
		$from = $from_temp[0];
		$msg = $JABBER->GetInfoFromMessageBody($message);
		$user = $from_temp[1];

		if ($JABBER->username == $user)
			return;

		if (preg_match("/^\!wetter/i", $msg)) {
			if (preg_match("/^\!wetter ([a-zäüöß0-9 -]*)$/i", $msg, $matches)) {
				if (intval($matches[1]) == 0)
					$query = urlencode($matches[1]);
				else
					$query = urlencode($matches[1]) . "+deutschland";
			} else {
				$query = "bremen";
			}

			$result = wetter :: google_weather($query);
			if ($result)
				$temp = "Hier ist das Wetter für " . $result["stadt"] . ":\nAktuell: " . $result["jetzt_temp"] . " °C, " . $result["jetzt_wetter"] . "\n" . $result["jetzt_wind"] . "\n" . $result["jetzt_feuchtigkeit"];
			else
				$temp = "Nicht verfügbar.";
			
			$temp = strip_tags($temp);
			$temp = html_entity_decode($temp, ENT_COMPAT, 'UTF-8');
			$temp = trim($temp);

			$JABBER->SendMessage($from, "groupchat", NULL, array (
				"body" => $temp
			));
		}
	}

	public static function help() {
		return "!wetter von Bremen, !wetter <Ort>";
	}

	function google_weather($city) {

		// replace special characters in city name
		// Ö->Oe, Ä->Ae, Ü->Ue
		// ö->oe, ä->ae, ü->ue
		$search = array (
			"/Ö/",
			"/Ä/",
			"/Ü/",
			"/ö/",
			"/ä/",
			"/ü/",
			"/ /"
		);
		$replace = array (
			"Oe",
			"Ae",
			"Ue",
			"oe",
			"ae",
			"ue",
			"+"
		);
		$city = trim(preg_replace($search, $replace, $city));

		//
		// Requesting the weather information from the google weather service.
		// The google weather service is available under the following url.
		// example http://www.google.com/ig/api?weather=Wien
		$url = "http://www.google.com/ig/api?hl=de&weather=" . $city;
		$file = file_get_contents($url);
		$wetter = simplexml_load_string($file);

		// Requested google weather url
		$Data["URL"] = $url;

		if (isset ($wetter->weather->problem_cause))
			return false;

		// general information elements
		$Data["stadt"] = $wetter->weather->forecast_information->city->attributes()->data;
		$Data["postalcode"] = $wetter->weather->forecast_information->postal_code->attributes()->data;
		$Data["datum"] = $wetter->weather->forecast_information->forecast_date->attributes()->data;
		$Data["current_time"] = $wetter->weather->forecast_information->current_date_time->attributes()->data;

		// Current weather conditions (condition and icon seems to be always empty)
		$Data["jetzt_wetter"] = $wetter->weather->current_conditions->condition->attributes()->data;
		$Data["jetzt_temp"] = $wetter->weather->current_conditions->temp_c->attributes()->data;
		$Data["jetzt_feuchtigkeit"] = $wetter->weather->current_conditions->humidity->attributes()->data;
		$Data["jetzt_wind"] = $wetter->weather->current_conditions->wind_condition->attributes()->data;
		$Data["jetzt_icon"] = $wetter->weather->current_conditions->icon->attributes()->data;

		// Today weather conditions
		$Data["heute"] = $wetter->weather->forecast_conditions[0]->day_of_week->attributes()->data;
		$Data["heute_min"] = $wetter->weather->forecast_conditions[0]->low->attributes()->data;
		$Data["heute_max"] = $wetter->weather->forecast_conditions[0]->high->attributes()->data;
		$Data["heute_wetter"] = $wetter->weather->forecast_conditions[0]->condition->attributes()->data;
		$Data["heute_icon"] = $wetter->weather->forecast_conditions[0]->icon->attributes()->data;

		// Day 2 weather conditions
		$Data["zwei"] = $wetter->weather->forecast_conditions[1]->day_of_week->attributes()->data;
		$Data["zwei_min"] = $wetter->weather->forecast_conditions[1]->low->attributes()->data;
		$Data["zwei_max"] = $wetter->weather->forecast_conditions[1]->high->attributes()->data;
		$Data["zwei_wetter"] = $wetter->weather->forecast_conditions[1]->condition->attributes()->data;
		$Data["zwei_icon"] = $wetter->weather->forecast_conditions[1]->icon->attributes()->data;

		// Day 3 weather conditions
		$Data["drei"] = $wetter->weather->forecast_conditions[2]->day_of_week->attributes()->data;
		$Data["drei_min"] = $wetter->weather->forecast_conditions[2]->low->attributes()->data;
		$Data["drei_max"] = $wetter->weather->forecast_conditions[2]->high->attributes()->data;
		$Data["drei_wetter"] = $wetter->weather->forecast_conditions[2]->condition->attributes()->data;
		$Data["drei_icon"] = $wetter->weather->forecast_conditions[2]->icon->attributes()->data;

		// Day 4 weather conditions
		$Data["vier"] = $wetter->weather->forecast_conditions[3]->day_of_week->attributes()->data;
		$Data["vier_min"] = $wetter->weather->forecast_conditions[3]->low->attributes()->data;
		$Data["vier_max"] = $wetter->weather->forecast_conditions[3]->high->attributes()->data;
		$Data["vier_wetter"] = $wetter->weather->forecast_conditions[3]->condition->attributes()->data;
		$Data["vier_icon"] = $wetter->weather->forecast_conditions[3]->icon->attributes()->data;

		return $Data;
	}

}
?>
