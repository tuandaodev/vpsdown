<?php

ini_set('memory_limit', '2048M');
ini_set('user_agent', 'Mozilla/5.0');
set_time_limit(3600);
date_default_timezone_set("Asia/Bangkok");

define('DEVELOPER_KEY', 'AIzaSyCmmf1ifYYQkUd8P4QoDpiaxkQmDJtGt5M');

define('DB_NAME', 'vpsdown');
define('DB_USER', 'thien');
define('DB_PASSWORD', 'Thiendien1');
define('DB_HOST', 'localhost');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');

define('DOMAIN', 'http://localhost:1111/vpsdown/');
define('DOWNLOAD_FOLDER', 'cache_files');
define('CACHE_DAY', 24); // by hour

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);