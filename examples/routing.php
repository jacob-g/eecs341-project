<?php
include SRV_ROOT . '/includes/router.php';
include SRV_ROOT . '/includes/renderer.php';

$test_page = new RoutedPage('base_template.html', 'test_page.html', 'test_page.php');
echo $test_page->render();