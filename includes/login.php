<?php
//a script to check whether or not the user is logged in
session_start();

$cur_user_info = array(); //a variable that stores information about the currently-logged-in user

$logged_in = false;
if (isset($_SESSION['user_id'])) { //if a user ID is set, the user may be logged in
	$statement = query('SELECT name,group_ID FROM users WHERE ID=?', 'i', array($_SESSION['user_id'])); //pull the user's username from the database
	$statement->bind_result($username, $group_id);
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
			'group'		=> $group_id
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