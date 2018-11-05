<?php
//get the forum title
$forum_id = $url_params['forum_id'];
$statement = query('SELECT name FROM forum WHERE ID=?', 'i', array($forum_id));
$statement->bind_result($forum_name);
if ($statement->fetch()) { //if we have the ID, then set the forum title in all appropriate pages
	$page_params['page_title'] = $forum_name . ' - &Eacute;amonBB Forums';
	$page_params['forum_name'] = htmlspecialchars($forum_name);
} else { //if we don't have the ID, then show a 404
	$page = new RoutedPage('base_template.html', 'error404.html', 'error404.php');
	echo $page->render();
	die;
}
$statement->close();

$statement = query('SELECT t.ID,t.name,MAX(p.posted),COUNT(p.ID) FROM topic AS t LEFT JOIN post AS p ON p.topic_ID=t.ID WHERE t.forum_ID=? GROUP BY t.ID ORDER BY MAX(p.posted) DESC', 'i', array($forum_id));
$statement->bind_result($topic_id, $subject, $last_post_time, $num_replies);

$topic_rows = new MultiPageElement();
while ($statement->fetch()) {
	$topic_row = new PageElement('topic_row.html');
	$topic_row->bind('subject', $subject);
	$topic_row->bind('topic_id', $topic_id);
	$topic_row->bind('last_post_time', $last_post_time);
	$topic_row->bind('num_replies', $num_replies);
	$topic_rows->addElement($topic_row);
}
$page_params['topic_rows'] = $topic_rows->render();
$page_params['forum_id'] = $forum_id;