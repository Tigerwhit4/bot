<?php
function make_sql_ensure_connection() {
  global $sql_host;
  global $sql_user;
  global $sql_pass;
  global $sql_dtba;
  global $sql_connection;

  if(!$sql_connection) {
    @ sqlite_close($sql_connection);
    $sql_connection = sqlite_open($sql_host) || die();
  }
}

function make_sql_close($sql_connection) {
  global $sql_connection;

  sqlite_close($sql_connection);
}

function make_sql_query($query) {
  global $sql_connection;

  make_sql_ensure_connection();
  return sqlite_query(&$sql_connection, $query);
}

function make_sql_num_query($query) {
  global $sql_connection;

  make_sql_ensure_connection();
  $result = sqlite_query($sql_connection, $query);
  return sqlite_num_rows($result);
}

function make_sql_escape($query) {
  make_sql_ensure_connection();
  return sqlite_escape_string($query);
}

function make_sql_affected_rows() {
  return sqlite_affected_rows();
}

function make_sql_fetch_array($result, $result_type = NULL) {
  if(isset($result_type))
    str_replace("MYSQL", "SQLITE", $result_type);

  return sqlite_fetch_array($result, "'" . $result_type . "'");
}

function make_sql_fetch_row($result) {
  return sqlite_fetch_row($result);
}

function make_sql_fetch_assoc($result) {
  return sqlite_fetch_assoc($result);
}
?>
