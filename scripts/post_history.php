<?php
$page_params['page_title'] = 'Post Edit History';

if (!$user_info['permissions']['access_admin_panel']) {
	$page = new RoutedPage('base_template.html', 'error404.html', 'error404.php');
	echo $page->render();
	die;
}

$post_id = intval($url_params['post_id']);

$statement = query('SELECT t.ID AS topic_id,f.ID AS forum_ID,f.name AS forum_name,t.name AS subject FROM post AS p LEFT JOIN topic AS t ON t.ID=p.topic_ID LEFT JOIN forum AS f ON f.ID=t.forum_ID WHERE p.ID=?', 'i', array($post_id));
$statement->bind_result($topic_id, $forum_id, $forum_name, $topic_name);
$post_exists = $statement->fetch();
$statement->close();
if (!$post_exists) {
	$statement->close();
	$page = new RoutedPage('base_template.html', 'error404.html', 'error404.php');
	echo $page->render();
	die;
}

$statement = query('SELECT h.edit_time,u.name,h.description FROM post_edit_history AS h LEFT JOIN users AS u ON u.ID=h.editor_ID WHERE h.post_ID=? ORDER BY edit_time DESC', 'i', array($post_id));
$statement->bind_result($edit_time, $username, $message);

$history_rows = new MultiPageElement();
while ($statement->fetch()) {
	$history_row = new PageElement('post_history_row.html');
	$history_row->bind('time', $edit_time);
	$history_row->bind('user', htmlspecialchars($username));
	$history_row->bind('message', htmlspecialchars($message));
	$history_rows->addElement($history_row);
}
$statement->close();

$page_params['history_rows'] = $history_rows->render();

//generate breadcrumbs
$breadcrumbs = array(
	'/forums/' => 'Forums',
	'/forums/forum/' . $forum_id => htmlspecialchars($forum_name),
	'/forums/forum/' . $forum_id . '/topic/' . $topic_id => htmlspecialchars($topic_name),
	'' => 'Edit History'
);