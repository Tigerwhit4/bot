<?php
require_once "extlib/simplepie/simplepie.inc";

class rss {

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
			$result = make_sql_query("SELECT DISTINCT `rss_url` FROM `rss_subscriptions`;");
			while ($row = make_sql_fetch_array($result)) {
				$rss_feed = $row[0];
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
	}

		$from = $JABBER->GetInfoFromMessageFrom($message);
		$from_temp = explode("/", $from);
		$from = $from_temp[0];
		$msg = $JABBER->GetInfoFromMessageBody($message);
		$user = $from_temp[1];

		if (preg_match("#^subscribe https?://#", $msg)) {
			list(, $url) = explode($msg, ' ');

			$result = make_sql_num_query("SELECT * FROM `rss_subscriptions` WHERE `rss_url` = '" . make_sql_escape($from) . "' AND `jid` = '" . make_sql_escape($from) . "';");
			if ($result > 0)
				$msg = "You are already subscribed to " . $url;
			else {
				$feed = new SimplePie();
				$feed->set_feed_url($url);
				$feed->init();
				$feed->handle_content_type();
				if($feed->get_type() & SIMPLEPIE_TYPE_ALL) {
					# feed is valid and supported
					make_sql_query("INSERT INTO `rss_subscriptions` (`rss_url`, `jid`) VALUES ('" . make_sql_escape($url) . "', '" . make_sql_escape($from) . "');");
					if(make_sql_affected_rows() == 1)
						$msg = "You have been successfully subscribed to " . $url . ". To unsubscribe, send me\nunsubscribe " . $url;
					else
						$msg = "Sorry, subscribing you to " . $url . " failed.";
				}
				else
					$msg = $url . " is not a valid feed!";
			}

			$JABBER->SendMessage($receiver, "chat", NULL, array (
				"body" => $msg
			));
		}
		if (preg_match("#^unsubscribe https?://#", $msg)) {
			list(, $url) = explode($msg, ' ');
			$subscribed = make_sql_num_query("SELECT * FROM `rss_subscriptions` WHERE `rss_url` = '" . make_sql_escape($url) . "' AND `jid` = '" . make_sql_escape($from) . "');");
			if ($subscribed) {
				make_sql_query("DELETE FROM `rss_subscriptions` WHERE `rss_url` = '" . make_sql_escape($url) . "' AND `jid` = '" . make_sql_escape($from) . "');");
				if(make_sql_affected_rows() == 1)
					$msg = "You have been successfully unsubscribed from " . $url;
				else
					$msg = "Sorry, unsubscribing you from " . $url . " failed.";

				# if this was the last subscriber, delete the cached entries
				if(make_sql_num_query("SELECT * FROM `rss_subscriptions` WHERE `rss_url` = '" . make_sql_escape($url) . "';") === 0)
					make_sql_query("DELETE FROM `rss_feeds` WHERE `rss_url` = '" . make_sql_escape($url) . "';");
			} else
				$msg = "You are not subscribed to " . $url . ", thus I can't unsubscribe you.";

			$JABBER->SendMessage($receiver, "chat", NULL, array (
				"body" => $msg
			));
		}
}
?>
