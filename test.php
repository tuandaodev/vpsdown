<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$file = 'test.pdf';
$filename = 'test.pdf';

if (in_array('mod_xsendfile', apache_get_modules())) {
    header("X-Sendfile: $file");
    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    exit;
} else {
    echo "Warning! mod-xsendfile is NOT INSTALLED - sending file the old fashion way.....";
//    header('Content-Length: ' . filesize($file) );
//    print file_get_contents($file);
}