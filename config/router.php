<?php
//NOTE: these are ordered by precedence
$page_routes = array(
	'/' => array(
		'template'	=> 'base_template.html',
		'content'	=> 'index.html',
		'behavior'	=> 'index.php'
	),
	'/login/' => array(
		'template'	=> 'base_template.html',
		'content'	=> 'login_page.html',
		'behavior'	=> 'login.php'
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