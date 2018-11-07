<?php
$page_params['page_title'] = 'Register for &Eacute;amonBB';

if (isset($_POST['form_sent'])) {
	$errors = array();
	
	//check the form elements
	if ($_POST['username'] == '' || $_POST['password'] == '') {
		$errors[] = 'Please fill out all required fields';
	} else if ($_POST['password'] != $_POST['password-confirm']) {
		$errors[] = 'Passwords do not match';
	}
	
	$mysqli->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);
	$statement = query('SELECT 1 FROM users WHERE name=?', 's', array($_POST['username']));
	if ($statement->fetch()) {
		$errors[] = 'Username already exists';
	}
	
	if (empty($errors)) {
		$statement = query('INSERT INTO users(name,email,password,group_ID) VALUES(?,?,?,?)', 'sssi', array($_POST['username'], $_POST['email'], ebb_hash($_POST['password']), DEFAULT_USER_GROUP));
		$mysqli->commit();
		redirect('/login/');
	} else {
		$alert_element = new PageElement('basicwarning.html');
		$alert_element->bind('text', '<ul><li>' . implode('</li><li>', $errors) . '</li></ul>');
		$page_params['error_message'] = $alert_element->render();
		
		$mysqli->rollback();
	}
} else {
	$page_params['error_message'] = '';
}