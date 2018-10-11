<?php

require_once('DbModel.php');
require_once('function.php');

$accept_source = array('apkhide.com', 'moddroid.com');

$check_source = false;

foreach ($accept_source as $source) {
    if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], $source) != false && $_SERVER['HTTP_REFERER'] != DOMAIN) {
        $check_source = true;
        break;
    }
}

if (!$check_source) {
    header('Location: https://moddroid.com');
    exit;
} else {
    if (isset($_GET['id'])) {

        $dbModel = new DbModel();
        $result = $dbModel->get_url($_GET['id']);
        
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
}

?>