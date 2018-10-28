<?php
include SRV_ROOT . '/includes/router.php';
include SRV_ROOT . '/includes/renderer.php';

$test_element = new PageElement('template_test.html');
$test_element->bind('page_title', 'Page Title!');
echo $test_element->render();