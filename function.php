<?php

include_once 'config.php';

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
    
    $filename = basename($file_url);

    $response_headers = array_change_key_case(get_headers($file_url, TRUE));

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