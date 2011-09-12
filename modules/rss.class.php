<?php
require_once "extlib/simplepie/simplepie.inc";

class rss {

	const ERROR_GENERIC = 1;
	const ERROR_ALREADY_SUBSCRIBED = 2;
	const ERROR_NOT_SUBSCRIBED = 3;
	const ERROR_NOT_A_FEED = 4;

	public static function cron($i) {
		global $JABBER;
		global $check_hosts;
		global $trusted_users;
		global $trust_users;
		global $logdir;
		global $room_topic;
		global $rooms_log;
		global $rss_feeds;
		global $rooms;
		global $rss_receiver;

		if (($i % 900) == 1) {
			$feeds = make_sql_query("SELECT DISTINCT `rss_url` FROM `rss_subscriptions`;");
			while (list($rss_feed) = make_sql_fetch_array($feeds, MYSQL_NUM)) {
				$msg = "";
				$item_old_title = array ();
				$item_old_date = array ();

				$result = make_sql_query("SELECT * FROM `rss_feeds` WHERE `rss_url` = '" . make_sql_escape($rss_feed) . "';");

				while ($row = make_sql_fetch_array($result, MYSQL_ASSOC)) {
					array_push($item_old_title, md5($row["title"]));
					array_push($item_old_date, strtotime($row["date"]));
				}

				static $feed = NULL;

				if ($feed !== NULL) {
					$feed->__destruct();
					$feed = NULL;
				}

				$feed = new SimplePie();
				$feed->set_feed_url($rss_feed);
				$feed->enable_cache(false);
				$feed->init();

				$items = $feed->get_items();

				foreach ($items as $item) {
					$content = $item->get_content();
					$timestamp = strtotime($item->get_date());
					$title = $item->get_title();
					$author = $item->get_author();
					$link = $item->get_link();

					if (!in_array(md5($title), $item_old_title) && (!in_array($timestamp, $item_old_date))) {
						if (count($item_old_title) != 0)
							$msg .= "new post: " . $title . " on " . $link . "\n";

						$fp = make_sql_query("INSERT INTO `rss_feeds` ( `id`, `rss_url`, `title`, `date` ) VALUES ( NULL, '" . make_sql_escape($rss_feed) . "', '" . make_sql_escape($title) . "', '" . make_sql_escape(date("Y-m-d H:i:s", $timestamp)) . "')");
					}
				}

				if ($msg != "") {
					$result = make_sql_query("SELECT `jid` FROM `rss_subscriptions` WHERE `rss_url` = '" . make_sql_escape($rss_feed) . "';");
					while ($row = make_sql_fetch_assoc($result)) {
						$receiver = $row["jid"];
						if (in_array($receiver, $rooms))
							$JABBER->SendMessage($receiver, "groupchat", NULL, array (
								"body" => rtrim($msg)
							));
						else
							$JABBER->SendMessage($receiver, "chat", NULL, array (
								"body" => rtrim($msg)
							));
					}
				}
			}
		}
	}

	public static function chat($message) {
		global $JABBER;
		global $check_hosts;
		global $trusted_users;
		global $trust_users;
		global $logdir;
		global $room_topic;
		global $rooms_log;
		global $config;

		$i = 0;
		$timestamp = "";
		
		while($timestamp == "" && $i < 5) {
			$timestamp = strtotime($message["message"]["#"]["x"][$i]["@"]["stamp"]);
			$i++;
		}

		if($timestamp)
			return;

		$from = $JABBER->GetInfoFromMessageFrom($message);
		$from_temp = explode("/", $from);
		$from = $from_temp[0];
		$msg = $JABBER->GetInfoFromMessageBody($message);
		$user = $from_temp[1];

		if (preg_match("#^subscribe(?: ([^@\s]+@[^@\s]+))? (https?://.*)$#", $msg, $match)) {
			list($jid, $url) = $match;
			if (!empty($jid) && !in_array($from, $trust_users))
				return;

			if (empty($jid))
				$jid = $from;

			switch(self::subscribe($jid, $url)) {
				case 0:
					$msg = "Subscription successful. To unsubscribe, send me \"unsubscribe " . (($jid != $from)? $jid . " " : "") . $url . "\"";
					break;
				case self::ERROR_ALREADY_SUBSCRIBED:
					$msg = "Subscription failed: " . $jid . " are already subscribed to " . $url;
					break;
				case self::ERROR_NOT_A_FEED:
					$msg = "Subscription failed: " . $url . " is not a valid feed.";
					break;
				default:
					$msg = "Subscription failed.";
					break;
			}

			$JABBER->SendMessage($from, "chat", NULL, array (
				"body" => $msg
			));
		}
		elseif (preg_match("#^unsubscribe(?: ([^@\s]+@[^@\s]+))? (https?://.*)$#", $msg, $match)) {
			list($jid, $url) = $match;
			if (!empty($jid) && !in_array($from, $trust_users))
				return;

			if (empty($jid))
				$jid = $from;

			switch(self::unsubscribe($from, $url)) {
				case 0:
					$msg = "Unsubscription successful.";
					break;
				case self::ERROR_NOT_SUBSCRIBED:
					$msg = "Unsubscription failed: You are not subscribed to " . $url;
					break;
				default:
					$msg = "Unsubscription failed.";
					break;
			}

			$JABBER->SendMessage($from, "chat", NULL, array (
				"body" => $msg
			));
		}
	}

	private static function subscribe($jid, $url) {
		$result = make_sql_num_query("SELECT * FROM `rss_subscriptions` WHERE `rss_url` = '" . make_sql_escape($url) . "' AND `jid` = '" . make_sql_escape($jid) . "';");
		if ($result > 0)
			return self::ERROR_ALREADY_SUBSCRIBED;

		$feed = new SimplePie();
		$feed->set_feed_url($url);
		$feed->enable_cache(false);
		if ($feed->init()) {
			$feed->handle_content_type();
			if($feed->get_type() & SIMPLEPIE_TYPE_ALL) {
				# feed is valid and supported
				make_sql_query("INSERT INTO `rss_subscriptions` (`rss_url`, `jid`) VALUES ('" . make_sql_escape($url) . "', '" . make_sql_escape($jid) . "');");
				if(make_sql_affected_rows() != 1)
					return self::ERROR_GENERIC;
			}
			else
				return self::ERROR_NOT_A_FEED;
		}
		else
			return self::ERROR_NOT_A_FEED;
		return 0;
	}

	private static function unsubscribe($jid, $url) {
		$subscribed = make_sql_num_query("SELECT * FROM `rss_subscriptions` WHERE `rss_url` = '" . make_sql_escape($url) . "' AND `jid` = '" . make_sql_escape($jid) . "';");
		if (!$subscribed)
			return self::ERROR_NOT_SUBSCRIBED;

		make_sql_query("DELETE FROM `rss_subscriptions` WHERE `rss_url` = '" . make_sql_escape($url) . "' AND `jid` = '" . make_sql_escape($jid) . "';");
		if(make_sql_affected_rows() != 1)
			return self::ERROR_GENERIC;

		# if this was the last subscriber, delete the cached entries
		if(make_sql_num_query("SELECT * FROM `rss_subscriptions` WHERE `rss_url` = '" . make_sql_escape($url) . "';") === 0)
			make_sql_query("DELETE FROM `rss_feeds` WHERE `rss_url` = '" . make_sql_escape($url) . "';");
		
		return 0;
	}

	public static function help() {
		return "subscribe <url> - subscribe you to the feed <url>";
	}

	public static function trustHelp() {
		return "subscribe <jid> <url> - subscribe <jid> to the feed <url>";
	}
}
?>
