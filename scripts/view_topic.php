<?php
//the parameters from the URL
$forum_id = intval($url_params['forum_id']);
$topic_id = intval($url_params['topic_id']);

//make sure that the topic and forum exist and that the user has permission to view them
$statement = query('SELECT f.name,t.name 
	FROM topic AS t INNER JOIN forum AS f ON f.ID=t.forum_ID LEFT JOIN forum_group_permissions AS fgp ON fgp.forum_ID=f.ID AND fgp.group_ID=?
	WHERE f.ID=? AND t.ID=? AND (fgp.view_forum=1 OR (fgp.view_forum IS NULL AND (SELECT view_forums FROM groups WHERE ID=?)=1))', 'iiii', array($user_info['group'], $forum_id, $topic_id, $user_info['group']));
$statement->bind_result($forum_name, $topic_name);
if ($statement->fetch()) { //the topic exists, and we have a topic and forum name
	$page_params['page_title'] = $topic_name . ' - ' . $forum_name . ' - &Eacute;amonBB Forums';
} else { //the topic does not exist (or we don't have permission to view it), so show a 404
	$page = new RoutedPage('base_template.html', 'error404.html', 'error404.php');
	echo $page->render();
	die;
}
$statement->close();

//increment the view counter (we don't bother with a transaction for this one since view count does not affect anything else and it is not critical to maintain its integrity)
query('UPDATE topic SET num_views=num_views+1 WHERE ID=?', 'i', array($topic_id));

//see if the user is allowed to submit a reply
$statement = query('SELECT g.ID FROM groups AS g LEFT JOIN forum_group_permissions AS fgp ON fgp.forum_ID=? AND fgp.group_ID=g.ID WHERE g.ID=? AND (fgp.post_replies=1 OR (fgp.post_replies IS NULL AND g.post_replies=1))', 'ii', array($forum_id, $user_info['group']));
$can_post_reply = $statement->fetch();
$statement->close();

//if we are trying to submit a reply and have permission to, then do so
if ($can_post_reply && isset($_POST['post_reply'])) {
	$mysqli->begin_transaction(MYSQLI_TRANS_START_READ_WRITE); //begin a MySQLi transaction for creating the reply
	query('SET @forum_user_id=?', 'i', array($user_info['id'])); //set the user ID of the user making the post for use in database triggers
	query('INSERT INTO post(topic_ID,name,description,poster_ID) VALUES(?,\'\',?,?)', 'isi', array($topic_id, $_POST['message'], $user_info['id']))->close();
	$mysqli->commit();
}

//get all of the posts associated with this topic and show them
$statement = query('SELECT p.ID,p.description,u.name,u.ID,p.posted,u.registered,g.user_title FROM post AS p LEFT JOIN users AS u ON u.ID=p.poster_ID LEFT JOIN groups AS g ON g.ID=u.group_ID WHERE p.topic_ID=? ORDER BY posted ASC', 'i', array($topic_id));
$statement->bind_result($post_id, $message, $author_username, $author_id, $post_time, $author_registered, $author_title);
$post_rows = new MultiPageElement();
while ($statement->fetch()) {
	//create a row for each post
	$post_row = new PageElement('post_row.html');
	$post_row->bind('post_message', htmlspecialchars($message));
	$post_row->bind('author_username', htmlspecialchars($author_username));
	$post_row->bind('author_registered', htmlspecialchars($author_registered));
	$post_row->bind('author_title', htmlspecialchars($author_title));
	$post_row->bind('post_time', $post_time);
	
	//generate the actions that can be taken on this post (edit/delete)
	$post_actions = array();
	//see if the user can edit the post (either they have permission to edit ALL posts or they have permission to edit their own posts and this is their own post)
	if (($author_id == $user_info['id'] && $user_info['permissions']['edit_own_posts']) || $user_info['permissions']['edit_other_posts']) {
		$edit_link = new PageElement('edit_post_link.html');
		$edit_link->bind('post_id', $post_id);
		$post_actions[] = $edit_link->render();
	}
	//see if the user can view the post history (currently considered an admin feature)
	if ($user_info['permissions']['access_admin_panel']) {
		$history_link = new PageElement('post_history_link.html');
		$history_link->bind('post_id', $post_id);
		$post_actions[] = $history_link->render();
	}
	//see if the user has permission to delete the post (same way as checking editing posts)
	if (($author_id == $user_info['id'] && $user_info['permissions']['delete_own_posts']) || $user_info['permissions']['delete_other_posts']) {
		$delete_link = new PageElement('delete_post_link.html');
		$delete_link->bind('post_id', $post_id);
		$post_actions[] = $delete_link->render();
	}
	if (empty($post_actions)) {
		$post_row->bind('post_actions', ''); //no actions, so leave the list blank
	} else {
		$post_row->bind('post_actions', '<ul class="post_actions"><li>' . implode('</li><li>', $post_actions) . '</li></ul>'); //add the list	
	}
	
	$post_rows->addElement($post_row);
}
$statement->close();
$page_params['post_rows'] = $post_rows->render();

$topic_header = new PageElement('topic_header.html');
$topic_header->bind('topic_subject', htmlspecialchars($topic_name));
$page_params['above_page_text'] = $topic_header->render();

//if the user has permission to post a reply, show the reply box
if ($can_post_reply) {
	$reply_box = new PageElement('post_reply.html');
	$reply_box->bind('forum_id', $forum_id);
	$reply_box->bind('topic_id', $topic_id);
	$page_params['reply_box'] = $reply_box->render();
} else {
	$page_params['reply_box'] = '';
}