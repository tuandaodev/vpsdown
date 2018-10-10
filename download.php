<?php

require_once('DbModel.php');
require_once('function.php');

if (isset($_GET['id'])) {

    if (ob_get_level())
        ob_end_clean();

    $dbModel = new DbModel();
    $result = $dbModel->get_url($_GET['id']);

//    $file_url = 'https://archive.org/download/apkmodeio/14182-MORTAL-KOMBAT-X-v1-19-0-cache-Tegra.zip';
    
    if (!empty($result)) {
        
        if ($result['type'] == 1) {     // Direct Link
            $file_url = $result['url'];
            $file_url = urldecode($file_url);
            download_direct_link($file_url);
        } else {
            $file_url = $result['url'];
            $file_url = urldecode($file_url);
            download_google_drive_link($file_url);
        }
        
    } else {
        echo "Can't find your file on the system.";
    }
} else {
    echo "Can't find your file on the system.";
}

?>