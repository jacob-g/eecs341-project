<?php
session_start();

if (isset($_SESSION['user_id'])) {
	$statement = query('SELECT name FROM users WHERE ID=?', 'i', array($_SESSION['user_id']));
	$statement->bind_result($username);
	if ($statement->fetch()) {
		$login_msg = new PageElement('loggedinmessage.html');
		$login_msg->bind('username', $username);
		$global_page_params['login_text'] = $login_msg->render();
	} else {
		$login_link = new PageElement('loginlink.html');
		$global_page_params['login_text'] = $login_link->render();
	}
} else {
	$login_link = new PageElement('loginlink.html');
	$global_page_params['login_text'] = $login_link->render();
}