<?php

function make_sql_ensure_connection() {
  global $sql_hostname, $sql_username, $sql_password, $sql_database, $sql_connection;

  if(!$sql_connection) {
    $sql_connection = new SQLite3($sql_hostname);
    if(!$sql_connection)
      die($sqlite_error);
  }
}

function make_sql_close($sql_connection) {
  global $sql_connection;

  $sql_connection->close();
}

function make_sql_query($query) {
  global $sql_connection;

  make_sql_ensure_connection();
  return $sql_connection->query($query);
}

function make_sql_num_query($query) {
  global $sql_connection;

  make_sql_ensure_connection();
  $result = $sql_connection->query($query);
  $rows = $result->fetchArray();
  return count($rows);
}

function make_sql_escape($query) {
  global $sql_connection;

  make_sql_ensure_connection();
  return $sql_connection->escapeString($query);
}

function make_sql_affected_rows() {
  return sqlite_affected_rows();
}

function make_sql_fetch_array($result, $result_type = NULL) {
  if(isset($result_type))
    str_replace('MYSQL', 'SQLITE', $result_type);

  return sqlite_fetch_array($result, "'" . $result_type . "'");
}

function make_sql_fetch_row($result) {
  return sqlite_fetch_row($result);
}

function make_sql_fetch_assoc($result) {
  return sqlite_fetch_assoc($result);
}

?>
