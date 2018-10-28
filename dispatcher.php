<?php
define('SRV_ROOT', dirname(__FILE__));
//TODO: initialize the database
include SRV_ROOT . '/config/database.php';
include SRV_ROOT . '/includes/database.php';

include SRV_ROOT . '/config/router.php';
include SRV_ROOT . '/includes/router.php';
include SRV_ROOT . '/includes/renderer.php';

echo route_page($_SERVER['REQUEST_URI']);
//$test_page = new RoutedPage('base_template.html', 'test_page.html', 'test_page.php');
//echo $test_page->render();