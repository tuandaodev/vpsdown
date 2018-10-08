<?php 


$file_url = 'https://archive.org/download/apkmodeio/14182-MORTAL-KOMBAT-X-v1-19-0-cache-Tegra.zip';
$chunksize = 5 * (1024 * 1024); //5 MB (= 5 242 880 bytes) per one chunk of file.

$filename = $file_url;

if(file_exists($filename))
{
    set_time_limit(300);

    $size = intval(sprintf("%u", filesize($filename)));

    header('Content-Type: application/octet-stream');
    header('Content-Transfer-Encoding: binary');
    header('Content-Length: '.$size);
    header('Content-Disposition: attachment;filename="'.basename($filename).'"');

    if($size > $chunksize)
    { 
        $handle = fopen($filename, 'rb'); 

        while (!feof($handle))
        { 
          print(@fread($handle, $chunksize));

          ob_flush();
          flush();
        } 

        fclose($handle); 
    }
    else readfile($path);

    exit;
}
else echo 'File "'.$filename.'" does not exist!';
