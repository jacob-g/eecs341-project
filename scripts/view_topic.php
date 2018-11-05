<?php
$page_params['page_title'] = '(topic name) - (forum name) - &Eacute;amonBB Forums'; //TODO: get the topic and forum name

$posts = array(
	'eamon' => 'Welcome to EECS 341!',
	'arbaz' => 'I feel very welcomed'
);

$post_rows = new MultiPageElement();
foreach ($posts as $author => $message) {
	$post_row = new PageElement('post_row.html');
	$post_row->bind('author_username', $author);
	$post_row->bind('post_message', $message);
	$post_rows->addElement($post_row);
}
$page_params['post_rows'] = $post_rows->render();