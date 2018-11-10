<?php
$page_params['page_title'] = 'Manage User Groups';

//make sure that the user has permission to access this
if (!$user_info['permissions']['access_admin_panel']) {
	$statement->close();
	$page = new RoutedPage('base_template.html', 'error404.html', 'error404.php');
	echo $page->render();
	die;
}

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
			view_forums=?,
			post_topics=?,
			post_replies=?,
			edit_own_posts=?,
			edit_other_posts=?,
			delete_own_posts=?,
			delete_other_posts=?,
			access_admin_panel=?
			WHERE ID=?', 'iiiiiiiii', array(
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
}

$group_rows = new MultiPageElement();

$statement = query('SELECT ID,name,view_forums,post_topics,post_replies,edit_own_posts,edit_other_posts,delete_own_posts,delete_other_posts,access_admin_panel FROM groups ORDER BY ID ASC');
$statement->bind_result($group_id, $group_name, $view_forums, $post_topics, $post_replies, $edit_own_posts, $edit_other_posts, $delete_own_posts, $delete_other_posts, $access_admin_panel);
while ($statement->fetch()) {
	$group_row = new PageElement('user_group_admin_row.html');
	$group_row->bind('group_name', htmlspecialchars($group_name));
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
}
$statement->close();

$page_params['group_rows'] = $group_rows->render();