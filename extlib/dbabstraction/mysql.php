<?php
	function make_sql_ensure_connection() {
		global $sql_host;
		global $sql_user;
		global $sql_pass;
		global $sql_dtba;
		global $sql_connection;

		if(is_null($sql_connection) || !@mysql_ping($sql_connection)) {
			@mysql_close($sql_connection);
			$sql_connection = mysql_connect($sql_host, $sql_user, $sql_pass) || die("Error establishing database connection.");
			mysql_set_charset('utf8', $sql_connection);
			mysql_select_db($sql_dtba);
		}
	}

	function make_sql_close($sql_connection) {
		mysql_close($sql_connection);
	}

	function make_sql_query($query) {
		mysql_ensure_connection();
		return mysql_query($query);
	}

	function make_sql_num_query($query) {
		mysql_ensure_connection();
		$result = mysql_query($query);
		return mysql_num_rows($result);
	}

	function make_sql_escape($query) {
		mysql_ensure_connection();
		return mysql_real_escape_string($query);
	}

	function make_sql_affected_rows() {
		return mysql_affected_rows();
	}

	function make_sql_fetch_array($result, $result_type = NULL) {
		return mysql_fetch_array($result, $result_type);
	}

	function make_sql_fetch_row($result) {
		return mysql_fetch_row($result);
	}

	function make_sql_fetch_assoc($result) {
		return mysql_fetch_assoc($result);
	}
?>
