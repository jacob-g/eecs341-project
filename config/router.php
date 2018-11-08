<?php
//NOTE: these are ordered by precedence
$page_routes = array(
	'/' => array(
		'template'	=> 'base_template.html',
		'content'	=> 'index.html',
		'behavior'	=> 'index.php'
	),
	'/login/$type' => array(
		'template'	=> 'base_template.html',
		'content'	=> 'login_page.html',
		'behavior'	=> 'login.php'
	),
	'/register/' => array(
		'template'	=> 'base_template.html',
		'content'	=> 'register.html',
		'behavior'	=> 'register.php'
	),
	'/forums/' => array(
		'template'	=> 'base_template.html',
		'content'	=> 'forum_index.html',
		'behavior'	=> 'forum_index.php'
	),
	'/forums/forum/$forum_id' => array(
		'template'	=> 'base_template.html',
		'content'	=> 'view_forum.html',
		'behavior'	=> 'view_forum.php'
	),
	'/forums/post_topic/$forum_id' => array(
		'template'	=> 'base_template.html',
		'content'	=> 'post_topic.html',
		'behavior'	=> 'post_topic.php'
	),
	'/forums/forum/$forum_id/topic/$topic_id' => array(
		'template'	=> 'base_template.html',
		'content'	=> 'view_topic.html',
		'behavior'	=> 'view_topic.php'
	),
	'/forums/admin/permissions/$forum_id' => array(
		'template'	=> 'base_template.html',
		'content'	=> 'forum_permissions.html',
		'behavior'	=> 'forum_permissions.php'
	),
	'/about/' => array(
		'template'	=> 'base_template.html',
		'content'	=> 'about.html',
		'behavior'	=> 'about.php'
	),
	'/contact/' => array(
		'template'	=> 'base_template.html',
		'content'	=> 'contact.html',
		'behavior'	=> 'about.php'
	),
	'/development/' => array(
		'template'	=> 'base_template.html',
		'content'	=> 'development.html',
		'behavior'	=> 'about.php'
	),
	'/get-started/' => array(
		'template'	=> 'base_template.html',
		'content'	=> 'get-started.html',
		'behavior'	=> 'about.php'
	),
	'/test/$param' => array(
		'template'	=> 'base_template.html',
		'content'	=> 'test_page.html',
		'behavior'	=> 'test_page.php'
	)
);