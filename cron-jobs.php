<?php

if (!session_id()) {
        session_start();
}

require_once('config.php');
require_once('sub_function.php');
require_once('DbModel.php');

$dbModel = new DbModel();
$old_cache = $dbModel->get_all_old_cache();

foreach ($old_cache as $cache) {
    $path = DOWNLOAD_FOLDER . '/' . $cache['name'];
    if (file_exists($path)) {
        unlink($path);
    }
    $dbModel->delete_cache($cache['id']);
}

echo "DELETE OLD CACHE COMPLETED";
