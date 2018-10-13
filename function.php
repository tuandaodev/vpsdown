<?php

require_once('config.php');
require_once('sub_function.php');

function download_cloud_mail_ru($file_url) {
    $is_ok = true;
    $page = get_page_content($file_url);

    $folder = GetMainFolder($page);
    $file_download_url = GetBaseUrl($page);
    $token = GetTokenDownload($page);

    if (isset($folder['list']) && count($folder['list']) > 0) {
        $file_item = reset($folder['list']);
    } else {
        $is_ok = false;
    }

    if (empty($file_item)) {
        $is_ok = false;
    }

    if ($is_ok) {
        $direct_link = pathcombine($file_download_url, $file_item['weblink']);
        
        if (!$token) {
            $direct_link .= '?key=' . $token;
        }

        if (isset($file_item['name']) && !empty($file_item['name'])) {
            $data_size = 0;
            if (isset($file_item['size']) && !empty($file_item['size'])) {
                $data_size = $file_item['size'];
            }
            download_full_info($direct_link, $file_item['name'], $data_size);
        } else {
            download_direct_link($direct_link);
        }
    } else {
        header("Location: $file_url");
        exit;
    }
}

function download_full_info($file_url, $filename, $data_size = 0) {
    
    if (ob_get_level())
            ob_end_clean();
    
    header('Content-Type: application/octet-stream');
    header("Content-Transfer-Encoding: Binary");
    header("Content-disposition: attachment; filename=\"" . $filename . "\"");
    header('Content-Transfer-Encoding: chunked'); //changed to chunked
    header('Expires: 0');
    if ($data_size) {
        header("Content-length: $data_size");
    }
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');

    readfile($file_url); 
}

function download_direct_link($file_url) {
    
    if (ob_get_level())
            ob_end_clean();
    
    $filename = basename($file_url);

    $response_headers = array_change_key_case(get_headers($file_url, TRUE));
    
    if (isset($response_headers['server']) && $response_headers['server'] == 'cloudflare') {
        header("Location: $file_url");
        exit;
    }
    
    // Get data size
    $data_size = 0;
    if (isset($response_headers['content-length'])) {
        $data_size = $response_headers['content-length'];
    }

    // Get File Name
    if (isset($response_headers["content-disposition"])) {
        // this catches filenames between Quotes
        if (preg_match('/.*filename=[\'\"]([^\'\"]+)/', $response_headers["content-disposition"], $matches)) {
            $filename = $matches[1];
        }
        // if filename is not quoted, we take all until the next space
        else if (preg_match("/.*filename=([^ ]+)/", $response_headers["content-disposition"], $matches)) {
            $filename = $matches[1];
        }
    }

    header('Content-Type: application/octet-stream');
    header("Content-Transfer-Encoding: Binary");
    header("Content-disposition: attachment; filename=\"" . $filename . "\"");
    header('Content-Transfer-Encoding: chunked'); //changed to chunked
    header('Expires: 0');
    if ($data_size) {
        header("Content-length: $data_size");
    }
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');

    readfile($file_url); 
}

function download_google_drive_link($google_url) {
    
    if (ob_get_level())
            ob_end_clean();
    
    $matches = array();
    preg_match("/.*file\/d\/([^ ]+)\/view/", $google_url, $matches);
    
    if (isset($matches[1]) && !empty($matches[1])) {
        $file_id = $matches[1];
    }
    
    $file_url = "https://drive.google.com/uc?export=download&id=$file_id";
    
    $response_headers = array_change_key_case(get_headers($file_url, TRUE));
    
//    echo "<pre>";
//    print_r($response_headers);
//    echo "<pre>";
//    exit;
    
    $filename = "";
    // Get data size
    $data_size = 0;
    if (isset($response_headers['content-length'])) {
        $data_size = $response_headers['content-length'];
    }
    
    // Get direct link
    $direct_link = $file_url;
    if (isset($response_headers['location']) && !empty($response_headers['location'])) {
        $direct_link = $response_headers['location'];
    }
    
    // Get File Name
    if (isset($response_headers["content-disposition"])) {
        // this catches filenames between Quotes
        if (preg_match('/.*filename=[\'\"]([^\'\"]+)/', $response_headers["content-disposition"], $matches)) {
            $filename = $matches[1];
    } 
        // if filename is not quoted, we take all until the next space
        else if (preg_match("/.*filename=([^ ]+)/", $response_headers["content-disposition"], $matches)) {
            $filename = $matches[1];
        }
    }
    
    if (!empty($filename)) {
        header('Content-Type: application/octet-stream');
        header("Content-Transfer-Encoding: Binary");
        header("Content-disposition: attachment; filename=\"" . $filename . "\"");
        header('Content-Transfer-Encoding: chunked'); //changed to chunked
        header('Expires: 0');
        if ($data_size) {
            header("Content-length: $data_size");
        }
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');

        readfile($direct_link); 
    } else {
        header('Location: ' . $direct_link);
        exit;
    }
}