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
	'/forums/delete/$post_id' => array(
		'template'	=> 'base_template.html',
		'content'	=> 'delete_post.html',
		'behavior'	=> 'delete_post.php'
	),
	'/forums/edit/$post_id' => array(
		'template'	=> 'base_template.html',
		'content'	=> 'edit-post.html',
		'behavior'	=> 'edit_post.php'
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
	'/forums/admin/groups/' => array(
		'template'	=> 'base_template.html',
		'content'	=> 'user_group_admin.html',
		'behavior'	=> 'user_group_admin.php'
	),
	'/forums/admin/manage_forums/' => array(
		'template'	=> 'base_template.html',
		'content'	=> 'modify-all-forums.html',
		'behavior'	=> 'manage_forums.php'
	),
	'/forums/admin/permissions/$forum_id' => array(
		'template'	=> 'base_template.html',
		'content'	=> 'forum_permissions.html',
		'behavior'	=> 'forum_permissions.php'
	),
	'/forums/admin/delete_forum/$forum_id' => array(
		'template'	=> 'base_template.html',
		'content'	=> 'delete_forum.html',
		'behavior'	=> 'delete_forum.php'
	),
	'/forums/admin/delete_category/$category_id' => array(
		'template'	=> 'base_template.html',
		'content'	=> 'delete_category.html',
		'behavior'	=> 'delete_category.php'
	),
	'/about/' => array(
		'template'	=> 'base_template.html',
		'content'	=> 'about.html',
		'behavior'	=> 'about.php'
	),
	'/profile/' => array(
		'template'	=> 'base_template.html',
		'content'	=> 'profile.html',
		'behavior'	=> 'profile.php'
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