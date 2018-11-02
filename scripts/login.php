<?php
$page_params['page_title'] = 'Log in to &Eacute;amonBB';

$page_params['login_error'] = '';
if (isset($_POST['form_sent'])) {
	$statement = query('SELECT id FROM users WHERE name=? AND password=?', 'ss', array($_POST['username'], ebb_hash($_POST['password'])));
	$statement->bind_result($user_id);
	if ($statement->fetch()) {
		redirect('/');
	} else {
		$alert_element = new PageElement('basicwarning.html');
		$alert_element->bind('text', 'Invalid username or password');
		$page_params['login_error'] = $alert_element->render();
	}
}

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