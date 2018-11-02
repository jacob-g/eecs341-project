<?php
define('SRV_ROOT', dirname(__FILE__));

include SRV_ROOT . '/includes/core.php';

include SRV_ROOT . '/config/database.php';
include SRV_ROOT . '/includes/database.php';

include SRV_ROOT . '/config/router.php';
include SRV_ROOT . '/includes/router.php';
include SRV_ROOT . '/includes/renderer.php';

echo route_page($_SERVER['REQUEST_URI']);
