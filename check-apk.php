<?php

require_once('DbModel.php');
require_once('function.php');


$return = array();
$return['status'] = "0";

if (isset($_POST['action']) && !empty($_POST['action'])) {
                
    if (isset($_POST['packname'])) {

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

        if ($app_url) {
            $return['status'] = "1";
            $return_url = str_replace('https://apkpure.com', "", $app_url);
            $return['download_url'] = $return_url;
            $result = "The download is ready.";
        } else {
            $return['status'] = "0";
            $result = "File not found, please check the package name or URL.";
        }
    }
    
    $return['html'] = $result;
}

echo json_encode($return);



?>