<?php
$page_params['page_title'] = 'Delete Forum';

//make sure that the user has permission to access this
if (!$user_info['permissions']['access_admin_panel']) {
	$page = new RoutedPage('base_template.html', 'error404.html', 'error404.php');
	echo $page->render();
	die;
}

$forum_id = intval($url_params['forum_id']);
$statement = query('SELECT name FROM forum WHERE ID=?', 'i', array($forum_id));
$statement->bind_result($forum_name);
$forum_exists = $statement->fetch();
$statement->close();

if (!$forum_exists) {
	$page = new RoutedPage('base_template.html', 'error404.html', 'error404.php');
	echo $page->render();
	die;
}

if (isset($_POST['form_sent'])) {
	$mysqli->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);
	//begin the process of deleting everything in the forum
	query('UPDATE topic SET first_post_ID=NULL WHERE forum_ID=?', 'i', array($forum_id))->close(); //mark all the first post IDs as null to prevent foreign key problems
	query('DELETE FROM post WHERE topic_ID IN (SELECT ID FROM topic WHERE forum_ID=?)', 'i', array($forum_id))->close();
	query('DELETE FROM topic WHERE forum_ID=?', 'i', array($forum_id))->close();
	query('DELETE FROM forum WHERE ID=?', 'i', array($forum_id))->close();
	$mysqli->commit();
	redirect('/forums/admin/manage_forums/?deletedforum');
	die;
}

if (isset($_POST['cancel'])) {
	redirect('/forums/admin/manage_forums/');
	die;
}

$page_params['forum_name'] = htmlspecialchars($forum_name);
$page_params['forum_id'] = $forum_id;

//generate breadcrumbs
$breadcrumbs = array(
	'/forums/admin/manage_forums/' => 'Forum Management',
	'' => 'Delete Forum'
);