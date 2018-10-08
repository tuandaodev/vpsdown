<?php 
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

readfile_chunked_remote($file_url);

function readfile_chunked_remote($filename, $seek = 0, $retbytes = true, $timeout = 3) { 
    set_time_limit(0); 
    $defaultchunksize = 1024*1024; 
    $chunksize = $defaultchunksize; 
    $buffer = ''; 
    $cnt = 0; 
    $remotereadfile = false; 

    if (preg_match('/[a-zA-Z]+:\/\//', $filename)) 
        $remotereadfile = true; 

    $handle = @fopen($filename, 'rb'); 

    if ($handle === false) { 
        return false; 
    } 

    stream_set_timeout($handle, $timeout); 
    
    if ($seek != 0 && !$remotereadfile) 
        fseek($handle, $seek); 

    while (!feof($handle)) { 

        if ($remotereadfile && $seek != 0 && $cnt+$chunksize > $seek) 
            $chunksize = $seek-$cnt; 
        else 
            $chunksize = $defaultchunksize; 

        $buffer = @fread($handle, $chunksize); 

        if ($retbytes || ($remotereadfile && $seek != 0)) { 
            $cnt += strlen($buffer); 
        } 

        if (!$remotereadfile || ($remotereadfile && $cnt > $seek)) 
            echo $buffer; 

        ob_flush(); 
        flush(); 
    } 

    $info = stream_get_meta_data($handle); 

    $status = fclose($handle); 

    if ($info['timed_out']) 
        return false; 

    if ($retbytes && $status) { 
        return $cnt; 
    } 

    return $status; 
} 