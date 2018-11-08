<?php
if (!$logged_in) {
	$page = new RoutedPage('base_template.html', 'error404.html', 'error404.php');
	echo $page->render();
	die;
}

$page_params['page_title'] = 'Manage Profile';
$page_params['username'] = htmlspecialchars($user_info['username']);
$page_params['email_address'] = htmlspecialchars($user_info['email']);

//the user requested an email change
if (isset($_POST['change_email'])) {
	if (preg_match('%^([a-zA-Z0-9_\-\.]+)@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z]{2,5})$%', $_POST['email'])) { //make sure the email address is valid format
		//update the database entry for the user with the new email
		$mysqli->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);
		query('UPDATE users SET email=? WHERE id=?', 'si', array($_POST['email'], $user_info['id']))->close();
		$page_params['email_address'] = htmlspecialchars($_POST['email']);
		$mysqli->commit();
		
		//show a notice that the email was updated
		$email_updated_notice = new PageElement('basicnotice.html');
		$email_updated_notice->bind('text', 'Your email address has been successfully updated.');
		$page_params['email_notice'] = $email_updated_notice->render();
	} else {
		//show a notice that the provided email is invalid
		$email_warning = new PageElement('basicwarning.html');
		$email_warning->bind('text', 'Invalid email address format.');
		$page_params['email_notice'] = $email_warning->render();
	}
} else {
	$page_params['email_notice'] = '';
}

//a password change was requested
if (isset($_POST['change_password'])) {
	if ($_POST['password'] == $_POST['password-confirm']) { //make sure the passwords match
		//update the password
		$mysqli->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);
		$statement = query('UPDATE users SET password=? WHERE id=? AND password=?', 'sis', array(ebb_hash($_POST['password']), $user_info['id'], ebb_hash($_POST['current-password'])));
		if ($statement->affected_rows > 0) {
			//show a notice that the password was changed
			$password_notice = new PageElement('basicnotice.html');
			$password_notice->bind('text', 'Password updated successfully.');
			$page_params['password_notice'] = $password_notice->render();
		} else {
			$password_warning = new PageElement('basicwarning.html');
			$password_warning->bind('text', 'Invalid current password.');
			$page_params['password_notice'] = $password_warning->render();
		}
		$statement->close();
		$mysqli->commit();

		
	} else { //...if the passwords don't match, show a warning
		$password_warning = new PageElement('basicwarning.html');
		$password_warning->bind('text', 'Passwords do not match.');
		$page_params['password_notice'] = $password_warning->render();
	}
} else {
	$page_params['password_notice'] = '';
}