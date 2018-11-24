<?php
$page_params['page_title'] = 'Bans';

//make sure that the user has permission to access this
if (!$user_info['permissions']['access_admin_panel']) {
	$page = new RoutedPage('base_template.html', 'error404.html', 'error404.php');
	echo $page->render();
	die;
}

$error = '';
if (isset($_POST['form_sent'])) {
	$mysqli->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);
	$user_id = null;
	$valid_username = true;
	if ($_POST['username'] != '') {
		$statement = query('SELECT ID FROM users WHERE name=?', 's', array($_POST['username']));
		$statement->bind_result($user_id);
		if (!$statement->fetch()) {
			$valid_username = false;
		}
		$statement->close();
	}
	if ($valid_username) {
		query('INSERT INTO bans(user_ID,ip,message,expires) VALUES(?,?,?,?)', 'isss', array($user_id, $_POST['ip'], $_POST['message'], date("Y-m-d H:i:s", time() + intval($_POST['expires']) * 3600)));
		$mysqli->commit();
	} else {
		$error = 'Invalid username';
		$mysqli->rollback();
	}
}

if ($error == '') {
	$page_params['error'] = '';
} else {
	$error_template = new PageElement('basicwarning.html');
	$error_template->bind('text', $error);
	$page_params['error'] = $error_template->render();
}

query('DELETE FROM bans WHERE expires<NOW()')->close(); //delete all expired bans (does not need to be run concurrently necesesarily, so not part of a transaction)

$ban_rows = new MultiPageElement();
$statement = query('SELECT b.ID,b.ip,b.message,b.expires,u.name FROM bans AS b LEFT JOIN users AS u ON u.ID=b.user_ID ORDER BY expires ASC');
$statement->bind_result($id, $ip, $message, $expires, $username);
while ($statement->fetch()) {
	$ban_row = new PageElement('admin_bans_ban_row.html');
	$ban_row->bind('username', htmlspecialchars($username));
	$ban_row->bind('ip', $ip);
	$ban_row->bind('message', htmlspecialchars($message));
	$ban_row->bind('expires', $expires);
	$ban_row->bind('ban_id', $id);
	$ban_rows->addElement($ban_row);
}
$statement->close();

$page_params['ban_rows'] = $ban_rows->render();