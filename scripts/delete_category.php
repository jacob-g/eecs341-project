<?php
$page_params['page_title'] = 'Delete Category';

//make sure that the user has permission to access this
if (!$user_info['permissions']['access_admin_panel']) {
	$page = new RoutedPage('base_template.html', 'error404.html', 'error404.php');
	echo $page->render();
	die;
}

$category_id = intval($url_params['category_id']);
$statement = query('SELECT name FROM category WHERE ID=?', 'i', array($category_id));
$statement->bind_result($category_name);
$category_exists = $statement->fetch();
$statement->close();

if (!$category_exists) {
	$page = new RoutedPage('base_template.html', 'error404.html', 'error404.php');
	echo $page->render();
	die;
}

if (isset($_POST['form_sent'])) {
	$mysqli->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);
	//begin the process of deleting everything in the forum
	query('UPDATE topic SET first_post_ID=NULL WHERE forum_ID IN (SELECT ID FROM (SELECT ID,category_ID FROM forum) AS f WHERE category_ID=?)', 'i', array($category_id))->close(); //mark all the first post IDs as null to prevent foreign key problems
	query('DELETE FROM post WHERE topic_ID IN (SELECT ID FROM topic WHERE forum_ID IN (SELECT ID FROM (SELECT ID,category_ID FROM forum) AS f WHERE category_ID=?))', 'i', array($category_id))->close();
	query('DELETE FROM topic WHERE forum_ID IN (SELECT ID FROM (SELECT ID,category_ID FROM forum) AS f WHERE category_ID=?)', 'i', array($category_id))->close();
	query('DELETE FROM forum WHERE ID IN (SELECT ID FROM (SELECT ID,category_ID FROM forum) AS f WHERE category_ID=?)', 'i', array($category_id))->close();
	query('DELETE FROM category WHERE ID=?', 'i', array($category_id))->close();
	$mysqli->commit();
	redirect('/forums/admin/manage_forums/');
	die;
}

$page_params['category_name'] = htmlspecialchars($category_name);
$page_params['category_id'] = $category_id;