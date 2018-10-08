<?php

if (ob_get_level())
    ob_end_clean();

//$file_url = 'https://archive.org/download/apkmodeio/14182-MORTAL-KOMBAT-X-v1-19-0-cache-Tegra.zip';
$file_url = 'https://apkmemory.com/wl/?id=v7VB7KqqWPOwCxtUApJV0V2NNHCoPWTc';
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

readfile($file_url); // do the double-download-dance (dirty but worky)
?>