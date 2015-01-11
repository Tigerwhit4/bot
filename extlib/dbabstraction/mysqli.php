<?php

function sql_error() {
  global $sql_connection;

  return $sql_connection->error;
}

function make_sql_ensure_connection() {
  global $sql_hostname, $sql_username, $sql_password, $sql_database, $sql_connection;

  if (is_null($sql_connection) || ! @ mysql_ping($sql_connection)) {
    @ mysql_close($sql_connection);
    $sql_connection = new mysqli($sql_hostname, $sql_username, $sql_password, $sql_database);
    if ($sql_connection->connect_errno)
      die("Unable to connect to MySQL: " . $sql_connection->connect_error . "\n");

    $result = $sql_connection->query('SET CHARACTER SET utf8;');
    if (! $result)
      die("Unable to set utf8 character set (" . $sql_connection->errno . ") " . $sql_connection->error . "\n");

    $result = $sql_connection->set_charset('utf8');
    if (! $result)
      die("Unable to set utf8 names (" . $sql_connection->errno . ") " . $sql_connection->error . "\n");
  }

  return $sql_connection;
}

function make_sql_close($sql_connection) {
  return $sql_connection->close();
}

function make_sql_query($query) {
  make_sql_ensure_connection();

  global $sql_connection;

  $result = $sql_connection->query($query);
  if ($result) {
    return $result;
  } else
    die("MySQL-query error: " . $query . " (" . $sql_connection->errno . ") " . $sql_connection->error . "\n");
}

function make_sql_num_query($query) {
  make_sql_ensure_connection();
  return make_sql_query($query)->num_rows;
}

function make_sql_escape($query) {
  make_sql_ensure_connection();
  global $sql_connection;
  return $sql_connection->real_escape_string($query);
}

function make_sql_affected_rows() {
  global $sql_connection;
  return $sql_connection->affected_rows;
}

function make_sql_fetch_array(mysqli_result $result, $result_type = NULL) {
  if ($result_type == MYSQL_ASSOC)
    return $result->fetch_array(MYSQLI_ASSOC);
  elseif ($result_type == MYSQL_NUM)
    return $result->fetch_array(MYSQLI_NUM);
  else
    return $result->fetch_array($result_type);
}

function make_sql_fetch_assoc(mysqli_result $result) {
  return $result->fetch_assoc();
}

?>
