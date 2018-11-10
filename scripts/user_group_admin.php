<?php
$page_params['page_title'] = 'Manage User Groups';

//make sure that the user has permission to access this
if (!$user_info['permissions']['access_admin_panel']) {
	$page = new RoutedPage('base_template.html', 'error404.html', 'error404.php');
	echo $page->render();
	die;
}

$page_params['notice'] = '';

//we're adding a user to a group
if (isset($_POST['update_user'])) {
	$mysqli->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);
	$new_group_id = intval($_POST['group']);
	$username = $_POST['username'];
	
	//make sure the group really exists
	$statement = query('SELECT name FROM groups WHERE ID=?', 'i', array($new_group_id));
	$statement->bind_result($new_group_name);
	$valid_user_group = $statement->fetch() && $new_group_id != GUEST_USER_GROUP;
	$statement->close();
	
	//make sure the user exists too
	$statement = query('SELECT ID FROM users WHERE name=?', 's', array($username));
	$statement->bind_result($user_id);
	$valid_username = $statement->fetch();
	$statement->close();
	
	//show errors if there are any, and if there aren't, update the group
	if (!$valid_user_group) {
		$invalid_group_warning = new PageElement('basicwarning.html');
		$invalid_group_warning->bind('text', 'The group you specified is invalid.');
		$page_params['notice'] = $invalid_group_warning->render();
	} else if (!$valid_username) {
		$invalid_username_warning = new PageElement('basicwarning.html');
		$invalid_username_warning->bind('text', 'The username you specified is invalid.');
		$page_params['notice'] = $invalid_username_warning->render();
	} else {
		query('UPDATE users SET group_ID=? WHERE ID=?', 'ii', array($new_group_id, $user_id));
		$updated_user_notice = new PageElement('basicnotice.html');
		$updated_user_notice->bind('text', 'User group successfully updated: <b>' . htmlspecialchars($username) . '</b> is now a member of <b>' . htmlspecialchars($new_group_name) . '</b>.');
		$page_params['notice'] = $updated_user_notice->render();
	}
	$mysqli->commit();
}

//we're updating the permissions for a group
if (isset($_POST['form_sent'])) {
	$mysqli->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);
	$group_ids = array();
	$statement = query('SELECT ID FROM groups');
	$statement->bind_result($group_id);
	while ($statement->fetch()) {
		$group_ids[] = $group_id;
	}
	$statement->close();
	foreach ($group_ids as $group_id) {
		query('UPDATE groups SET
			name=?,
			user_title=?,
			view_forums=?,
			post_topics=?,
			post_replies=?,
			edit_own_posts=?,
			edit_other_posts=?,
			delete_own_posts=?,
			delete_other_posts=?,
			access_admin_panel=?
			WHERE ID=?', 'ssiiiiiiiii', array(
					$_POST['groups'][$group_id]['name'],
					$_POST['groups'][$group_id]['user_title'],
					isset($_POST['groups'][$group_id]['view_forums']),
					isset($_POST['groups'][$group_id]['post_topics']),
					isset($_POST['groups'][$group_id]['post_replies']),
					isset($_POST['groups'][$group_id]['edit_own_posts']),
					isset($_POST['groups'][$group_id]['edit_other_posts']),
					isset($_POST['groups'][$group_id]['delete_own_posts']),
					isset($_POST['groups'][$group_id]['delete_other_posts']),
					isset($_POST['groups'][$group_id]['access_admin_panel']),
					$group_id
				)
			)->close();
	}
	$mysqli->commit();
	
	$updated_group_notice = new PageElement('basicnotice.html');
	$updated_group_notice->bind('text', 'Group permissions have been updated.');
	$page_params['notice'] = $updated_group_notice->render();
}

if (isset($_POST['delete_group'])) { //delete a group
	$group_to_delete = intval($_POST['group_to_delete']);
	$group_to_reassign = intval($_POST['group_to_reassign']);
	$mysqli->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);
	
	$statement = query('SELECT 1 FROM groups WHERE id=?', 'i', array($group_to_delete)); //make sure the group we're deleting exists
	$group_to_delete_exists = $statement->fetch();
	$statement->close();
	
	$statement = query('SELECT 1 FROM groups WHERE id=?', 'i', array($group_to_reassign)); //make sure the group we're reassigning to exists
	$group_to_reassign_exists = $statement->fetch();
	$statement->close();
	
	//either show an error message or delete the group and reassign its members
	if (!$group_to_delete_exists) {
		$group_doesnt_exist_warning = new PageElement('basicwarning.html');
		$group_doesnt_exist_warning->bind('text', 'The group you asked to delete does not exist.');
		$page_params['notice'] = $group_doesnt_exist_warning->render();
	} else if (!$group_to_reassign_exists) {
		$bad_reassign_warning = new PageElement('basicwarning.html');
		$bad_reassign_warning->bind('text', 'The group you asked to reassign users to does not exist.');
		$page_params['notice'] = $bad_reassign_warning->render();
	} else if ($group_to_delete == $group_to_reassign) {
		$cannot_reassign_warning = new PageElement('basicwarning.html');
		$cannot_reassign_warning->bind('text', 'You cannot reassign users to the group you are deleting.');
		$page_params['notice'] = $cannot_reassign_warning->render();
	} else if ($group_to_delete == $user_info['group']) {
		$cannot_delete_yourself_warning = new PageElement('basicwarning.html');
		$cannot_delete_yourself_warning->bind('text', 'You cannot delete the group you are currently in.');
		$page_params['notice'] = $cannot_delete_yourself_warning->render();
	} else {
		query('UPDATE users SET group_ID=? WHERE group_ID=?', 'ii', array($group_to_reassign, $group_to_delete))->close(); //delete the group
		query('DELETE FROM groups WHERE ID=?', 'i', array($group_to_delete))->close(); //reassign its members
		$delete_successful_warning = new PageElement('basicnotice.html');
		$delete_successful_warning->bind('text', 'Group successfully deleted!');
		$page_params['notice'] = $delete_successful_warning->render();
	}
	$mysqli->commit();
}

if (isset($_POST['add_group'])) { //add a new group
	$mysqli->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);
	
	//make sure the group doesn't already exist (i.e. the same title)
	$statement = query('SELECT 1 FROM groups WHERE name=?', 's', array($_POST['group_name'])); //make sure the group doesn't already exist
	$group_exists = $statement->fetch();
	$statement->close();
	
	//show a warning message or create the group
	if ($group_exists) {
		$group_exists_warning = new PageElement('basicwarning.html');
		$group_exists_warning->bind('text', 'Group name already in use: <b>' . htmlspecialchars($_POST['group_name']) . '</b>');
		$page_params['notice'] = $group_exists_warning->render();
	} else {
		query('INSERT INTO groups(name,user_title) VALUES(?,?)', 'ss', array($_POST['group_name'], $_POST['user_title']))->close();
		$group_exists_warning = new PageElement('basicnotice.html');
		$group_exists_warning->bind('text', 'Group <b>' . htmlspecialchars($_POST['group_name']) . '</b> created successfully! Set the permissions below.');
		$page_params['notice'] = $group_exists_warning->render();
	}
	$mysqli->commit();
}

$group_rows = new MultiPageElement();
$group_dropdown_options = new MultiPageElement();

//get the full list of all the groups and its permissions
$statement = query('SELECT ID,name,user_title,view_forums,post_topics,post_replies,edit_own_posts,edit_other_posts,delete_own_posts,delete_other_posts,access_admin_panel FROM groups ORDER BY ID ASC');
$statement->bind_result($group_id, $group_name, $user_title, $view_forums, $post_topics, $post_replies, $edit_own_posts, $edit_other_posts, $delete_own_posts, $delete_other_posts, $access_admin_panel);
while ($statement->fetch()) {
	//add the row to the table containing the group and its permission checkboxes
	$group_row = new PageElement('user_group_admin_row.html');
	$group_row->bind('group_name', htmlspecialchars($group_name));
	$group_row->bind('group_user_title', htmlspecialchars($user_title));
	$group_row->bind('group_id', $group_id);
	$group_row->bind('guest_disable', $group_id == GUEST_USER_GROUP ? 'disabled="disabled"' : '');
	$group_row->bind('view_forums_checked', $view_forums && $group_id != GUEST_USER_GROUP ? 'checked="checked"' : '');
	$group_row->bind('post_topics_checked', $post_topics && $group_id != GUEST_USER_GROUP ? 'checked="checked"' : '');
	$group_row->bind('post_replies_checked', $post_replies && $group_id != GUEST_USER_GROUP ? 'checked="checked"' : '');
	$group_row->bind('edit_own_posts_checked', $edit_own_posts && $group_id != GUEST_USER_GROUP ? 'checked="checked"' : '');
	$group_row->bind('edit_other_posts_checked', $edit_other_posts && $group_id != GUEST_USER_GROUP ? 'checked="checked"' : '');
	$group_row->bind('delete_own_posts_checked', $delete_own_posts && $group_id != GUEST_USER_GROUP ? 'checked="checked"' : '');
	$group_row->bind('delete_other_posts_checked', $delete_other_posts && $group_id != GUEST_USER_GROUP ? 'checked="checked"' : '');
	$group_row->bind('access_admin_panel_checked', $access_admin_panel && $group_id != GUEST_USER_GROUP ? 'checked="checked"' : '');
	$group_rows->addElement($group_row);
	
	//add a row to the dropdown to assign a user to a group
	if ($group_id != GUEST_USER_GROUP) {
		$group_dropdown_row = new PageElement('group_dropdown_item.html');
		$group_dropdown_row->bind('group_id', $group_id);
		$group_dropdown_row->bind('group_name', $group_name);
		$group_dropdown_options->addElement($group_dropdown_row);
	}
}
$statement->close();

$page_params['group_rows'] = $group_rows->render();
$page_params['group_dropdown_items'] = $group_dropdown_options->render();