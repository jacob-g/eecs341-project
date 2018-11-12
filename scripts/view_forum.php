<?php
//get the forum title and make sure the user has permission to view this forum
$forum_id = $url_params['forum_id'];
$statement = query('SELECT f.name
	FROM forum AS f LEFT JOIN forum_group_permissions AS fgp ON fgp.forum_ID=f.ID AND fgp.group_ID=?
	WHERE f.ID=? AND (fgp.view_forum=1 OR (fgp.view_forum IS NULL AND (SELECT view_forums FROM groups WHERE ID=?)=1))', 'iii', array($user_info['group'], $forum_id, $user_info['group']));
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

//get the topics in this forum
$statement = query('SELECT t.ID AS topic_id,t.name,MAX(p.posted) AS last_post_time,COUNT(p.ID)-1 AS num_replies,t.num_views FROM topic AS t LEFT JOIN post AS p ON p.topic_ID=t.ID WHERE t.forum_ID=? GROUP BY t.ID ORDER BY MAX(p.posted) DESC', 'i', array($forum_id));
$statement->bind_result($topic_id, $subject, $last_post_time, $num_replies, $num_views);

$topic_rows = new MultiPageElement();
while ($statement->fetch()) {
	$topic_row = new PageElement('topic_row.html');
	$topic_row->bind('subject', $subject);
	$topic_row->bind('topic_id', $topic_id);
	$topic_row->bind('last_post_time', $last_post_time);
	$topic_row->bind('num_replies', $num_replies);
	$topic_row->bind('num_views', $num_views);
	$topic_rows->addElement($topic_row);
}
$page_params['topic_rows'] = $topic_rows->render();
$page_params['forum_id'] = $forum_id;

//render the header showing the topic name
$forum_header = new PageElement('forum_header.html');
$forum_header->bind('forum_name', $forum_name);

//see if we have permission to post new topics
$statement = query('SELECT 1
	FROM groups AS g LEFT JOIN forum_group_permissions AS fgp ON fgp.group_ID=g.ID AND fgp.forum_ID=?
	WHERE g.ID=? AND (fgp.post_topics=1 OR (fgp.post_topics IS NULL AND g.post_topics=1))', 'ii', array($forum_id, $user_info['group']));
if ($statement->fetch()) {
	$post_topic_link = new PageElement('post_topic_link.html');
	$post_topic_link->bind('forum_id', $forum_id);
	$forum_header->bind('post_topic_link', $post_topic_link->render());
} else {
	$forum_header->bind('post_topic_link', '');
}

$page_params['above_page_text'] = $forum_header->render();