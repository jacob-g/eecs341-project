<?php
$page_params['page_title'] = 'Log in to &Eacute;amonBB';

//log the user out if they specify to do so in the URL
if (isset($url_params['type']) && $url_params['type'] == 'out') {
	session_destroy();
	redirect('/');
}

$page_params['login_error'] = '';
//if the user sent a form request, try to log them in
if (isset($_POST['form_sent'])) {
	//see if there are any users with the given name and password
	$statement = query('SELECT id FROM users WHERE name=? AND password=?', 'ss', array($_POST['username'], ebb_hash($_POST['password'])));
	$statement->bind_result($user_id);
	//if there are, then mark the user as logged in
	if ($statement->fetch()) {
		$_SESSION['user_id'] = $user_id;
		redirect('/');
	} else { //...and if there aren't, show a warning to the user saying there aren't
		$alert_element = new PageElement('basicwarning.html');
		$alert_element->bind('text', 'Invalid username or password');
		$page_params['login_error'] = $alert_element->render();
	}
}

//create the login form
$form_fields = create_form(array(
	'username' => array(
		'type'		=> 'text',
		'caption'	=> 'Username'
	),
	'password' => array(
		'type'		=> 'password',
		'caption'	=> 'Password'
	)
));
$page_params['form_elements'] = $form_fields->render();