<?php
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

		if (is_array($rss_feeds) && (($i % 900) == 1)) {
			foreach ($rss_feeds as $key => $rss_feed) {
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
				$feed->enable_cache(true);
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
					foreach ($rss_receiver[$key] as $receiver) {
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

}
?>
