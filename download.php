<?php 

if (ob_get_level()) ob_end_clean();

$file_url = 'https://archive.org/download/apkmodeio/14182-MORTAL-KOMBAT-X-v1-19-0-cache-Tegra.zip';

$head = array_change_key_case(get_headers($file_url, TRUE));
$data_size = $head['content-length'];

header('Content-Type: application/octet-stream');
header("Content-Transfer-Encoding: Binary"); 
header("Content-disposition: attachment; filename=\"" . basename($file_url) . "\""); 
header('Content-Transfer-Encoding: chunked'); //changed to chunked
header('Expires: 0');
header("Content-length: $data_size");
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');

readfile($file_url); // do the double-download-dance (dirty but worky)

?>