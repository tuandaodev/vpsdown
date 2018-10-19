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

$app_url = '';
if (!$check_source) {
    header('Location: ' . DOMAIN . 'download.html');
    exit;
} else {
    if (isset($_GET['packname']) && !empty($_GET['packname'])) {
        
        $packname = $_GET['packname'];
        
        if (strpos($packname, 'http') !== false) {
            $parts = parse_url($packname);
            parse_str($parts['query'], $query);

            if (isset($query['id']) && !empty($query['id'])) {
                $packname = $query['id'];
            }
        }
        $package_url = "https://apkpure.com/store/apps/details?id=" . $packname;
        $app_url = GetApkPureFullUrlByPackname(get_page_content($package_url, false));
        
    } elseif (isset($_GET['url']) && !empty($_GET['url'])) {
        
        $app_url = "https://apkpure.com" . urldecode($_GET['url']);
        
    } else {
        echo "Can't find your file on the system.";
        exit;
    }
    
    if ($app_url) {
            $direct_url = GetApkPureDownloadURL(get_page_content($app_url, false));
            
            if ($direct_url) {
                $response_headers = array_change_key_case(get_headers($direct_url, TRUE));
                if (isset($response_headers['location']) && !empty($response_headers['location'])) {
                    $direct_link = $response_headers['location'];
                    download_direct_link($direct_link, true);
                } else {
                    download_direct_link($direct_url, true);
                }
            }
        } else {
            echo "Can't find your file on the system.";
            exit;
        }
}

?>