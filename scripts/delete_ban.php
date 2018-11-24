<?php
$page_params['page_title'] = 'Delete Ban';

//make sure that the user has permission to access this
if (!$user_info['permissions']['access_admin_panel']) {
	$page = new RoutedPage('base_template.html', 'error404.html', 'error404.php');
	echo $page->render();
	die;
}

$ban_id = intval($url_params['ban_id']);
$statement = query('SELECT b.ip,b.message,b.expires,u.name FROM bans AS b LEFT JOIN users AS u ON u.id=b.user_ID WHERE b.id=?', 'i', array($ban_id));
$statement->bind_result($ip, $message, $expires, $username);
$ban_exists = $statement->fetch();
$statement->close();
if (!$ban_exists) {
	$page = new RoutedPage('base_template.html', 'error404.html', 'error404.php');
	echo $page->render();
	die;
}

//delete if we received a request to
if (isset($_POST['delete'])) {
	query('DELETE FROM bans WHERE id=?', 'i', array($ban_id))->close();
	redirect('/forums/admin/bans/');
} else if (isset($_POST['cancel'])) {
	redirect('/forums/admin/bans/');
}

$page_params['ip'] = $ip;
$page_params['message'] = htmlspecialchars($message);
$page_params['username'] = htmlspecialchars($username);
$page_params['expires'] = $expires;
$page_params['ban_id'] = $ban_id;