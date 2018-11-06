<?php
//make sure that the topic and forum exist
//TODO: make sure the user has permission to view
$forum_id = intval($url_params['forum_id']);
$topic_id = intval($url_params['topic_id']);
$statement = query('SELECT f.name,t.name 
	FROM topic AS t INNER JOIN forum AS f ON f.ID=t.forum_ID LEFT JOIN forum_group_permissions AS fgp ON fgp.forum_ID=f.ID AND fgp.group_ID=?
	WHERE f.ID=? AND t.ID=? AND (fgp.view_forum=1 OR (fgp.view_forum IS NULL AND (SELECT view_forums FROM groups WHERE ID=?)=1))', 'iiii', array($user_info['group'], $forum_id, $topic_id, $user_info['group']));
$statement->bind_result($forum_name, $topic_name);
if ($statement->fetch()) {
	$page_params['page_title'] = $topic_name . ' - ' . $forum_name . ' - &Eacute;amonBB Forums'; //TODO: get the topic and forum name
} else {
	$page = new RoutedPage('base_template.html', 'error404.html', 'error404.php');
	echo $page->render();
	die;
}
$statement->close();

$statement = query('SELECT p.description,u.name,p.posted,u.registered FROM post AS p LEFT JOIN users AS u ON u.ID=p.poster_ID WHERE p.topic_ID=? ORDER BY posted ASC', 'i', array($topic_id));
$statement->bind_result($message, $author_username, $post_time, $author_registered);
$post_rows = new MultiPageElement();
while ($statement->fetch()) {
	$post_row = new PageElement('post_row.html');
	$post_row->bind('post_message', htmlspecialchars($message));
	$post_row->bind('author_username', htmlspecialchars($author_username));
	$post_row->bind('author_registered', htmlspecialchars($author_registered));
	$post_rows->addElement($post_row);
}
$page_params['post_rows'] = $post_rows->render();