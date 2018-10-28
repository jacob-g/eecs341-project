<?php
//NOTE: these are ordered by precedence
$page_routes = array(
	'/' => array(
		'template'	=> 'base_template.html',
		'content'	=> 'index.html',
		'behavior'	=> 'index.php'
	),
	'/test/$param' => array(
		'template'	=> 'base_template.html',
		'content'	=> 'test_page.html',
		'behavior'	=> 'test_page.php'
	)
);