<?php
//a script to check whether or not the user is logged in
session_start();

$cur_user_info = array(); //a variable that stores information about the currently-logged-in user

$logged_in = false;
if (isset($_SESSION['user_id'])) { //if a user ID is set, the user may be logged in
	$statement = query('SELECT name,group_ID,email FROM users WHERE ID=?', 'i', array($_SESSION['user_id'])); //pull the user's username from the database
	$statement->bind_result($username, $group_id, $email);
	if ($statement->fetch()) { //see if there are any records associated with this user ID
		//if there are, then...
		$logged_in = true; //...mark that the user is logged in
		
		//...show a message that the user is logged in
		$login_msg = new PageElement('loggedinmessage.html');
		$login_msg->bind('username', $username);
		$global_page_params['login_text'] = $login_msg->render();
		
		//...set a bunch of data about the user
		$user_info = array(
			'id'		=> $_SESSION['user_id'],
			'username'	=> $username,
			'group'		=> $group_id,
			'email'		=> $email
		);
	}
	$statement->close();
}

//if the user is not logged in, show a message inviting them to log in
if (!$logged_in) {
	$login_link = new PageElement('loginlink.html');
	$global_page_params['login_text'] = $login_link->render();
	
	//set the user information to everything for the default user
	$user_info = array(
		'id'		=> '-1',
		'username'	=> '',
		'group'		=> GUEST_USER_GROUP
	);
}

//get all user group permissions (we use SELECT * in case more are added in the future)
$statement = query('SELECT * FROM groups WHERE ID=?', 'i', array($user_info['group']));
bind_result_array($statement, $user_info['permissions']);
$statement->fetch();
$statement->close();

//add the links to administration pages
if ($user_info['permissions']['access_admin_panel']) {
	$admin_links = new PageElement('admin_links.html');
	$global_page_params['admin_links'] = $admin_links->render();
} else {
	$global_page_params['admin_links'] = '';
}

//check for bans
$statement = query('SELECT message FROM bans WHERE (user_ID=? OR ip=?) AND (expires IS NULL OR EXPIRES>NOW())', 'is', array($user_info['id'], $_SERVER['REMOTE_ADDR']));
$statement->bind_result($ban_message);
$is_banned = $statement->fetch();
$statement->close();
if ($is_banned) {
	$ban_page = new RoutedPage('base_template.html', 'banned.html', 'banned.php');
	$global_page_params['ban_reason'] = htmlspecialchars($ban_message);
	echo $ban_page->render();
	die;
}
