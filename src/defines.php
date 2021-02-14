<?php

defined('PTW') or define('PTW', '476f7420796f7521205468657265206973206e6f7468696e67206865726521');

defined('REALM') or define('REALM', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}/");

if (($_SERVER['HTTP_HOST'] ?? "") === 'localhost') {
  Dotenv\Dotenv::createImmutable(dirname(__DIR__, 1), '.env.development')->load();
} else {
  Dotenv\Dotenv::createImmutable(dirname(__DIR__, 1))->load();
}
