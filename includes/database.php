<?php
$mysqli = @new MySQLi($db_host, $db_username, $db_password, $db_name);
if ($mysqli->connect_error) {
	display_error('Failed to connect to database');
}

//a wrapper to run a MySQLi prepared query, given a query and paremeters, return the statement object with the results
function query($query, $param_types = '', $params = array()) {
	global $mysqli;
	
	//prepare the statement
	$statement = $mysqli->prepare($query);
	if (!$statement) {
		display_error('Failed to prepare query: <b>' . $mysqli->error . '</b>');
	}
	
	//bind the parameters if any are passed
	if ($param_types != '' && !empty($params)) {
		$bind_params = array($param_types);
		foreach ($params as &$param) {
			$bind_params[] = &$param;
		}
		if (!call_user_func_array(array($statement, 'bind_param'), $bind_params)) {
			display_error('Failed to bind parameters to query: <b>' . $mysqli->error . '</b>');
		}
	}
	
	//execute the query
	if (!$statement->execute()) {
		display_error('Failed to run query: <b>' . $mysqli->error . '</b>');
	}
	
	return $statement;
}

//populate an associative array with the results of a SELECT * (or any other SELECT) query
function bind_result_array($statement, &$result) {
	$meta = $statement->result_metadata();
	$result = array();
	$params = array();
	while ($field = $meta->fetch_field()) {
		$params[] = &$result[$field->name];
	}
	
	call_user_func_array(array($statement, 'bind_result'), $params); 
}
