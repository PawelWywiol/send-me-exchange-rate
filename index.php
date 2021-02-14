<?php

defined('PTW') or define('PTW', '476f7420796f7521205468657265206973206e6f7468696e67206865726521');

defined('SERVER_PATH') or define('SERVER_PATH', './');

if (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] == 'localhost') {
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);
}

require_once SERVER_PATH . 'vendor/autoload.php';
require_once SERVER_PATH . 'src/defines.php';
require_once SERVER_PATH . 'src/routes.php';
