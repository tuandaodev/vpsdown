<?php

require_once('DbModel.php');
require_once('function.php');

$accept_source = array('localhost', 'apkhide.com', 'moddroid.com');

$check_source = false;

foreach ($accept_source as $source) {
    if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], $source) !== false && $_SERVER['HTTP_REFERER'] != DOMAIN) {
        $check_source = true;
        break;
    }
}

if (!session_id()) {
    session_start();
}

if (!$check_source) {
    header('Location: ' . DOMAIN . 'download.html');
    exit;
} else {
    if (isset($_GET['id'])) {

        $dbModel = new DbModel();
        
        $cache = check_cache($_GET['id']);
        if (!empty($cache)) {
            download_cache($cache);
            exit;
        }
        
        $result = $dbModel->get_url($_GET['id']);
        
        if (!empty($result)) {
            
            $_SESSION['cache_id'] = $result['uid'];
            $_SESSION['cache_type'] = 1;
            
            switch ($result['type']) {
                case 1:  // Direct Link
                    $file_url = $result['url'];
                    $file_url = urldecode($file_url);
                    download_direct_link($file_url);
                    break;
                case 2: // Google Drive
                    $file_url = $result['url'];
                    $file_url = urldecode($file_url);
                    download_google_drive_link($file_url);
                    break;
                case 3: // cloud.mail.ru
                    $file_url = $result['url'];
                    $file_url = urldecode($file_url);
                    download_cloud_mail_ru($file_url);
                    break;
                default:
                    $file_url = $result['url'];
                    header("Location: $file_url");
                    exit;
            }
        } else {
            unset($_SESSION['cache_id']);
            unset($_SESSION['cache_type']);
            
            echo "Can't find your file on the system.";
        }
    } else {
        echo "Can't find your file on the system.";
    }
}

?>