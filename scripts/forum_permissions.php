<?php
//make sure we have permission to access this page
if (!$user_info['permissions']['access_admin_panel']) {
	$page = new RoutedPage('base_template.html', 'error404.html', 'error404.php');
	echo $page->render();
	die;
}

$forum_id = intval($url_params['forum_id']);
$page_params['forum_id'] = $forum_id;

//get the forum metadata (and make sure it exists)
$statement = query('SELECT name FROM forum WHERE id=?', 'i', array($forum_id));
$statement->bind_result($page_params['forum_name']);
if (!$statement->fetch()) {
	$page = new RoutedPage('base_template.html', 'error404.html', 'error404.php');
	echo $page->render();
	die;
}
$statement->close();

if (isset($_POST['form_sent'])) {
	$mysqli->begin_transaction(MYSQLI_TRANS_START_READ_WRITE); //begin a MySQLi transaction for updating the permissions
	query('DELETE FROM forum_group_permissions WHERE forum_ID=?', 'i', array($forum_id))->close(); //delete all existing permission entries
	if (isset($_POST['permissions'])) {
		foreach ($_POST['permissions'] as $group_id => $group_permissions) {
			if (!isset($group_permissions['inherit'])) {
				query('INSERT INTO forum_group_permissions(group_ID,forum_ID,view_forum,post_topics,post_replies) VALUES(?,?,?,?,?)', 'iiiii', 
					array(
						$group_id,
						$forum_id,
						isset($group_permissions['view_forum']) ? 1 : 0,
						isset($group_permissions['post_topics']) ? 1 : 0,
						isset($group_permissions['post_replies']) ? 1 : 0
					)
				)->close();
			}
		}
	}
	$mysqli->commit();
	redirect('/forums/admin/manage_forums/?permupdate');
	die;
}

//display the permission rows for each group
$permission_rows = new MultiPageElement();

$page_params['page_title'] = 'Edit Forum Permissions';
$statement = query('SELECT g.ID,g.name,fgp.ID IS NULL AS inherited,fgp.view_forum,fgp.post_topics,fgp.post_replies FROM groups AS g LEFT JOIN forum_group_permissions AS fgp ON fgp.forum_ID=? AND fgp.group_ID=g.ID ORDER BY g.ID ASC', 'i', array($forum_id));
$statement->bind_result($group_id, $group_name, $inherited, $view_forum, $post_topics, $post_replies);
while ($statement->fetch()) {
	$permission_row = new PageElement('permission_group_row.html');
	$permission_row->bind('group_id', $group_id);
	$permission_row->bind('group_name', $group_name);
	$permission_row->bind('inherit_checked', $inherited ? 'checked="checked"' : '');
	$permission_row->bind('view_forum_checked', $view_forum ? 'checked="checked"' : '');
	$permission_row->bind('post_topics_checked', $post_topics && $group_id != GUEST_USER_GROUP ? 'checked="checked"' : '');
	$permission_row->bind('post_replies_checked', $post_replies && $group_id != GUEST_USER_GROUP ? 'checked="checked"' : '');
	$permission_row->bind('guest_disable', $group_id == GUEST_USER_GROUP ? 'disabled="disabled"' : '');
	$permission_rows->addElement($permission_row);
}
$statement->close();

$page_params['permission_rows'] = $permission_rows->render();