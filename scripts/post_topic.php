<?php
$page_params['page_title'] = 'Post New Topic';

$forum_id = intval($url_params['forum_id']);
$page_params['forum_id'] = $forum_id;

//see if the user is allowed to post a topic
$statement = query('SELECT f.name
	FROM forum AS f LEFT JOIN forum_group_permissions AS fgp ON fgp.forum_ID=f.ID AND fgp.group_ID=?
	WHERE f.ID=? AND (fgp.post_topics=1 OR (fgp.post_topics IS NULL AND (SELECT post_topics FROM groups WHERE ID=?)=1))', 'iii', array($user_info['group'], $forum_id, $user_info['group']));
$statement->bind_result($forum_name);
if ($statement->fetch()) { //if we have the ID, then set the forum title in all appropriate pages
	$page_params['forum_name'] = htmlspecialchars($forum_name);
} else {
	$page = new RoutedPage('base_template.html', 'error404.html', 'error404.php');
	echo $page->render();
	die;
}
$statement->close();

$page_params['error_message'] = '';

if (isset($_POST['form_sent'])) {
	
	$errors = array();
	if ($_POST['subject'] == '') {
		$errors[] = 'You need to specify a subject';
	}
	
	if (empty($errors)) {
		$mysqli->begin_transaction();
		query('INSERT INTO topic(forum_ID,name) VALUES(?,?)', 'is', array($forum_id, $_POST['subject']))->close();
		$statement = query('SELECT LAST_INSERT_ID()');
		$statement->bind_result($topic_id);
		$statement->fetch();
		$statement->close();
		query('INSERT INTO post(topic_ID,name,description,poster_ID) VALUES(LAST_INSERT_ID(),\'\',?,?)', 'si', array($_POST['message'], $user_info['id']))->close();
		$mysqli->commit();
		
		redirect('/forums/forum/' . $forum_id . '/topic/' . $topic_id);
	} else {
		$alert_element = new PageElement('basicwarning.html');
		$alert_element->bind('text', 'The following errors were encountered: <ul><li>' . implode('</li><li>', $errors) . '</li></ul>');
		$page_params['error_message'] = $alert_element->render();
	}
}