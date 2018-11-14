<?php
$page_params['page_title'] = 'Edit Post';

$post_id = intval($url_params['post_id']);
$page_params['post_id'] = $post_id;

//get information about the post and make sure the user has permission to edit it
$statement = query('SELECT p.description,t.name,p.ID=t.first_post_ID AS is_first_post,t.forum_ID AS forum_ID,t.ID AS topic_ID,f.name AS forum_name,t.name AS subject FROM post AS p LEFT JOIN topic AS t ON t.ID=p.topic_ID LEFT JOIN forum AS f ON f.ID=t.forum_ID LEFT JOIN users AS u ON u.id=? LEFT JOIN groups AS g ON g.ID=u.group_ID WHERE p.ID=? AND (g.edit_other_posts=1 OR (g.edit_own_posts=1 AND p.poster_ID=u.ID))', 'ii', array($user_info['id'], $post_id));
$statement->bind_result($message, $subject, $is_first_post, $forum_id, $topic_id, $forum_name, $topic_name);
if ($statement->fetch()) {
} else {
	$statement->close();
	$page = new RoutedPage('base_template.html', 'error404.html', 'error404.php');
	echo $page->render();
	die;
}
$statement->close();

if (isset($_POST['cancel_edit'])) { //we decided to cancel, so redirect the user back to the topic
	redirect('/forums/forum/' . $forum_id . '/topic/' . $topic_id);
	die;
}
if (isset($_POST['submit_edit'])) { //we submitted the edit
	$mysqli->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);
	query('SET @forum_user_id=?', 'i', array($user_info['id'])); //set the user ID of the user making the edit in database triggers
	query('UPDATE post SET description=? WHERE id=?', 'si', array($_POST['message'], $post_id))->close(); //update the post content
	if ($is_first_post) { //if this is the first post on a topic, also update the topic subject
		query('UPDATE topic SET name=? WHERE id=?', 'si', array($_POST['subject'], $topic_id))->close();
	}
	//figure out the page the post is on
	$statement = query('SELECT CEIL((COUNT(ID)+1)/?) FROM post WHERE topic_ID=? AND posted<(SELECT posted FROM post WHERE ID=?)', 'iii', array(POSTS_PER_PAGE, $topic_id, $post_id));
	$statement->bind_result($page_number);
	$statement->fetch();
	$statement->close();
	$mysqli->commit();
	
	redirect('/forums/forum/' . $forum_id . '/topic/' . $topic_id . '?page=' . $page_number);
	die;
}

if ($is_first_post) {
	$subject_line = new PageElement('edit_post_edit_subject.html');
	$subject_line->bind('topic_name', htmlspecialchars($subject));
	$page_params['subject_line'] = $subject_line->render();
} else {
	$subject_line = new PageElement('edit_post_static_subject.html');
	$subject_line->bind('topic_name', htmlspecialchars($subject));
	$page_params['subject_line'] = $subject_line->render();
}

$page_params['post_content'] = htmlspecialchars($message);

//generate breadcrumbs
$breadcrumbs = array(
	'/forums/' => 'Forums',
	'/forums/forum/' . $forum_id => htmlspecialchars($forum_name),
	'/forums/forum/' . $forum_id . '/topic/' . $topic_id => htmlspecialchars($topic_name),
	'' => 'Edit Post'
);