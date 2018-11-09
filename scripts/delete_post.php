<?php
$page_params['page_title'] = 'Delete Post';

$post_id = intval($url_params['post_id']);
$page_params['post_id'] = $post_id;

//in one wonderful query, find the post, see if it's the first post in the topic, and make sure the user has permission to delete it
$statement = query('SELECT p.description,t.first_post_ID=p.ID AS is_first_post,t.forum_ID AS forum_ID,t.ID AS topic_ID FROM post AS p LEFT JOIN topic AS t ON t.ID=p.topic_ID LEFT JOIN users AS u ON u.id=? LEFT JOIN groups AS g ON g.ID=u.group_ID WHERE p.ID=? AND (g.delete_other_posts=1 OR (g.delete_own_posts=1 AND p.poster_ID=u.ID))', 'ii', array($user_info['id'], $post_id));
$statement->bind_result($message, $first_post, $forum_id, $topic_id);
if ($statement->fetch()) {
} else {
	$statement->close();
	$page = new RoutedPage('base_template.html', 'error404.html', 'error404.php');
	echo $page->render();
	die;
}
$statement->close();

if (isset($_POST['delete_confirm'])) { //we received confirmation that we should delete the post
	$mysqli->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);
	if ($first_post) { //if this is the first post, delete the entire topic
		query('UPDATE topic SET first_post_ID=NULL WHERE ID=?', 'i', array($topic_id))->close();
		query('DELETE FROM post WHERE topic_ID=?', 'i', array($topic_id))->close();
		query('DELETE FROM topic WHERE ID=?', 'i', array($topic_id))->close();
		$redirect_url = '/forums/forum/' . $forum_id;
	} else { //...otherwise, just delete the post
		query('DELETE FROM post WHERE ID=?', 'i', array($post_id))->close();
		$redirect_url = '/forums/forum/' . $forum_id . '/topic/' . $topic_id;
	}
	$mysqli->commit();
	redirect($redirect_url);
} else if (isset($_POST['delete_cancel'])) { //we received "cancel", so redirect the user back to the topic page
	redirect('/forums/forum/' . $forum_id . '/topic/' . $topic_id);
}

if ($first_post) {
	$delete_topic_warning = new PageElement('delete_topic_warning.html');
	$page_params['delete_topic_warning'] = $delete_topic_warning->render();
} else {
	$page_params['delete_topic_warning'] = '';
}

$page_params['post_message'] = htmlspecialchars($message);