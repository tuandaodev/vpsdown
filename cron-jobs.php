<?php

if (!session_id()) {
        session_start();
}

require_once('config.php');
require_once('sub_function.php');
require_once('DbModel.php');
require_once('function.php');

$dbModel = new DbModel();
$old_cache = $dbModel->get_all_old_cache();

$current_path = dirname(__FILE__);

$log = '';
$count = 0;
foreach ($old_cache as $cache) {
    $count++;
    $path = $current_path . '/' . DOWNLOAD_FOLDER . '/' . $cache['name'];
    if (file_exists($path)) {
        unlink($path);
    }
    $dbModel->delete_cache($cache['id']);
    
    $log .= "[" . $cache['id'] . "] - " . $cache['uid'] . " - " . $cache['name'] . " - " . $cache['type'] . "\n";
}

$log .= " \n DELETE [$count] OLD CACHE COMPLETED \n";

$duplicate_cache = $dbModel->get_duplicate_cache();

$count = 0;
foreach ($duplicate_cache as $cache) {
    $count++;
    $path = $current_path . '/' . DOWNLOAD_FOLDER . '/' . $cache['name'];
    if (file_exists($path)) {
        unlink($path);
    }
    $dbModel->delete_cache($cache['id']);
    
    $log .= "[" . $cache['id'] . "] - " . $cache['uid'] . " - " . $cache['name'] . " - " . $cache['type'] . "\n";
}

$log .= " \n DELETE [$count] DUPLICATE CACHE COMPLETED \n";

echo $log;
write_logs('cron_delete_cache.txt', $log);
