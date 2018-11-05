<?php
$page_params['page_title'] = '&Eacute;amonBB Forum Home';

$forums = array(
	'EamonBB' => array(
		'EamonBB Discussion',
		'Announcements'
	),
	'Test' => array(
		'Forum 1',
		'Forum 2'
	)
);

$category_rows = new MultiPageElement();
foreach ($forums as $category => $category_forums) {
	$category_row = new PageElement('index_category.html');
	$category_row->bind('category_name', $category);
	$forum_rows = new MultiPageElement();
	foreach ($category_forums as $forum_name) {
		$forum_row = new PageElement('index_forum_row.html');
		$forum_row->bind('forum_name', $forum_name);
		$forum_rows->addElement($forum_row);
	}
	$category_row->bind('forum_rows', $forum_rows->render());
	$category_rows->addElement($category_row);
}
$page_params['category_tables'] = $category_rows->render();