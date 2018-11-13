<?php
$page_params['page_title'] = 'Post Edit History';

if (!$user_info['permissions']['access_admin_panel']) {
	$page = new RoutedPage('base_template.html', 'error404.html', 'error404.php');
	echo $page->render();
	die;
}

$post_id = intval($url_params['post_id']);

$statement = query('SELECT h.edit_time,u.name,h.description FROM post_edit_history AS h LEFT JOIN users AS u ON u.ID=h.editor_ID WHERE h.post_ID=? ORDER BY edit_time DESC', 'i', array($post_id));
$statement->bind_result($edit_time, $username, $message);

$statement->store_result();

if (!$statement->num_rows) {
	$statement->free_result();
	$statement->close();
	$page = new RoutedPage('base_template.html', 'error404.html', 'error404.php');
	echo $page->render();
	die;
}

$history_rows = new MultiPageElement();
while ($statement->fetch()) {
	$history_row = new PageElement('post_history_row.html');
	$history_row->bind('time', $edit_time);
	$history_row->bind('user', htmlspecialchars($username));
	$history_row->bind('message', htmlspecialchars($message));
	$history_rows->addElement($history_row);
}
$statement->free_result();
$statement->close();

$page_params['history_rows'] = $history_rows->render();