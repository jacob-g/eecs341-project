<?php
$page_params['page_title'] = 'Register for &Eacute;amonBB';

if (isset($_POST['form_sent'])) { //process form input if any exists
	$errors = array();
	
	//check the form elements
	if ($_POST['username'] == '' || $_POST['password'] == '') { //check that the username and password are present
		$errors[] = 'Please fill out all required fields';
	} else if ($_POST['password'] != $_POST['password-confirm']) { //check that the passwords match
		$errors[] = 'Passwords do not match';
	}
	
	$mysqli->begin_transaction(MYSQLI_TRANS_START_READ_WRITE); //begin a MySQLi transaction for creating the user
	//see if the user already exists
	$statement = query('SELECT 1 FROM users WHERE name=?', 's', array($_POST['username']));
	if ($statement->fetch()) {
		$errors[] = 'Username already exists';
	}
	
	if (empty($errors)) { //if the user does not already exist, then create the user and redirect the user to the login page
		query('INSERT INTO users(name,email,password,group_ID) VALUES(?,?,?,?)', 'sssi', array($_POST['username'], $_POST['email'], ebb_hash($_POST['password']), DEFAULT_USER_GROUP))->close();
		$mysqli->commit();
		redirect('/login/registered');
	} else { //if the user does already exist (or there are any other errors), then discard the transaction and show an error message
		$alert_element = new PageElement('basicwarning.html');
		$alert_element->bind('text', 'The following errors were encountered: <ul><li>' . implode('</li><li>', $errors) . '</li></ul>');
		$page_params['error_message'] = $alert_element->render();
		
		$mysqli->rollback();
	}
} else {
	$page_params['error_message'] = '';
}