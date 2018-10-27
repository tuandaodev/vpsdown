<?php

require_once('config.php');
require_once('sub_function.php');
require_once('DbModel.php');

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

//    readfile($file_url); 
    downloadFile($file_url, $filename);
}

function check_url_is_404($response_headers) {
    $header_string = json_encode($response_headers);
    $disallow = array('404.html', '404 Not Found');
    
    foreach ($disallow as $source) {
        if (strpos($header_string, $source) !== false) {
            return false;
        }
    }
    return true;
}

function download_direct_link($file_url, $replace_name = false) {
    
    if (ob_get_level())
            ob_end_clean();
    
    $filename = basename($file_url);
    
    $filename_temp = parse_url($file_url, PHP_URL_PATH);
    if (isset($filename_temp) && !empty($filename_temp)) {
        $filename_temp = basename($filename_temp);
    }
    
    if ($filename_temp) {
        $filename = $filename_temp;
    }
    
    $response_headers = array_change_key_case(get_headers($file_url, TRUE));
    if (!check_url_is_404($response_headers)) {
        header("Location: $file_url");
        exit;
    }
    
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
    
    if ($replace_name) {
        $filename = str_replace("apkpure.com", "moddroid.com", $filename);
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

//    readfile($file_url);
    downloadFile($file_url, $filename);
}

function download_google_drive_link($google_url) {
    
    if (ob_get_level())
            ob_end_clean();
    
    $matches = array();
    preg_match("/.*file\/d\/([^ ]+)\/view/", $google_url, $matches);
    
    $file_id = '';
    if (isset($matches[1]) && !empty($matches[1])) {
        $file_id = $matches[1];
    }
    if (!$file_id) {
        $parts = parse_url($google_url);
        parse_str($parts['query'], $query);
        
        if (isset($query['id']) && !empty($query['id'])) {
            $file_id = $query['id'];
        }
    }
    $file_url = "https://drive.google.com/uc?export=download&id=$file_id";
    
    $response_headers = array_change_key_case(get_headers($file_url, TRUE));
    if (!check_url_is_404($response_headers)) {
        header("Location: $google_url");
        exit;
    }
    
    $filename = "";
    // Get data size
    $data_size = 0;
    // Get direct link
    $direct_link = $file_url;
    if (isset($response_headers['location']) && !empty($response_headers['location'])) {
        
        $direct_link = $response_headers['location'];
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
        
    } else {
         $direct_link = "https://www.googleapis.com/drive/v3/files/$file_id?key=" . DEVELOPER_KEY . "&alt=media";
         
         $url_info = "https://www.googleapis.com/drive/v3/files/$file_id?key=" . DEVELOPER_KEY;
         $file_info = json_decode(get_page_content($url_info, false), true);
         $filename = $file_info['name'];
         
         $response_headers = array_change_key_case(get_headers($direct_link, TRUE));
         if (isset($response_headers['content-length'])) {
            $data_size = $response_headers['content-length'];
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

//        readfile($direct_link); 
        downloadFile($direct_link, $filename);
        
    } else {
        header('Location: ' . $direct_link);
        exit;
    }
}

//  $type = 1: URL 
//  $type = 2: Package
function downloadFile($url, $filename) {
    // start the session
    if (!session_id()) {
        session_start();
    }
    // I can read/write to session
    $_SESSION['latestRequestTime'] = time();
    // close the session
    session_write_close();
    
    if (isset($_SESSION['cache_type']) && isset($_SESSION['cache_id'])) {
        
        $type = $_SESSION['cache_type'];
        $uid = $_SESSION['cache_id'];
        
        $dbModel = new DbModel();
        $caching = $dbModel->get_cache($uid, 0);    // caching, don't cache anymore
        
        if (!empty($caching)) {
            readfile($url);
        } else {
            $filename = clean_filename($filename);
            $filename = generate_filename($filename);
            $cache_id = $dbModel->insert_cache($uid, $filename, $type);
            subDownloadFile($url, $filename, $cache_id);
        }
    } else {
        readfile($url);
    }
}

// Begin caching, set status cache = 0, when it's done, update to 1 to let client know the cache is completed and they can download it
function subDownloadFile($url, $filename, $cache_id = 0) {
    
    ignore_user_abort(true);
    set_time_limit(0);
    
    if (!file_exists(DOWNLOAD_FOLDER)) {
        mkdir(DOWNLOAD_FOLDER, 0777, true);
    }
    
    $newfname = DOWNLOAD_FOLDER . '/' . $filename;
    
    $file = fopen ($url, 'rb');
    if ($file) {
        $newf = fopen ($newfname, 'wb');
        if ($newf) {
            while(!feof($file)) {
                $buf = '';
                echo $buf = fread($file, 1024 * 8);
                fwrite($newf, $buf, 1024 * 8);
            }
        }
    }
    
    if ($file) {
        fclose($file);
    }
    if ($newf) {
        fclose($newf);
        
        // Let client know the caching is completed.
        if ($cache_id) {
            $dbModel = new DbModel();
            $dbModel->update_cache_status($cache_id);
        }
    }
}

function clean_filename($filename) {
    $filename = preg_replace( '/[^a-z0-9\_\-\.]/i', '-', strtolower( $filename ) );
    return $filename;
}

function generate_filename($filename) {
    $parts = pathinfo($filename);
    $new_filename = $parts['filename'] . '_' . time() . "." . $parts['extension'];
    
    return $new_filename;
}

function check_cache($uid) {
    $dbModel = new DbModel();
    $cache = $dbModel->get_cache($uid);
    
    if (!empty($cache)) {
        $filepath = DOWNLOAD_FOLDER . '/' . $cache['name'];
        if (!file_exists($filepath)) {
            $dbModel->delete_cache($cache['id']);
            return [];
        }
        return $cache;
    } else {
        return [];
    }
}

function download_cache($cache) {
    $dbModel = new DbModel();
    $dbModel->update_cache_time($cache['id']);
    if (in_array('mod_xsendfile', apache_get_modules())) {
        download_cache_xsendfile($cache);
    } else {
        download_cache_normal($cache);
    }
}

function download_cache_xsendfile($cache) {
    $filename = $cache['name'];
    $filepath = DOWNLOAD_FOLDER . '/' . $filename;
    header("X-Sendfile: $filepath");
    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    exit;
}

function download_cache_normal($cache) {
    
    $filename = $cache['name'];
    
    @ini_set('error_reporting', E_ALL & ~ E_NOTICE);

    //- turn off compression on the server
    @apache_setenv('no-gzip', 1);
    @ini_set('zlib.output_compression', 'Off');

    // sanitize the file request, keep just the name and extension
    // also, replaces the file location with a preset one ('./myfiles/' in this example)
    
    $file_path  = DOWNLOAD_FOLDER . '/' . $filename;
    $path_parts = pathinfo($file_path);
    $file_name  = $path_parts['basename'];
    $file_ext   = $path_parts['extension'];

    // allow a file to be streamed instead of sent as an attachment
    $is_attachment = isset($_REQUEST['stream']) ? false : true;

    // make sure the file exists
    if (is_file($file_path))
    {
            $file_size  = filesize($file_path);
            $file = @fopen($file_path,"rb");
            if ($file)
            {
                    // set the headers, prevent caching
                    header("Pragma: public");
                    header("Expires: -1");
                    header("Cache-Control: public, must-revalidate, post-check=0, pre-check=0");
                    header("Content-Disposition: attachment; filename=\"$file_name\"");

            // set appropriate headers for attachment or streamed file
            if ($is_attachment) {
                    header("Content-Disposition: attachment; filename=\"$file_name\"");
            } else {
                    header('Content-Disposition: inline;');
                    header('Content-Transfer-Encoding: binary');
            }

            // set the mime type based on extension, add yours if needed.
            $ctype_default = "application/octet-stream";
            $content_types = array(
                    "exe" => "application/octet-stream",
                    "zip" => "application/zip",
                    "mp3" => "audio/mpeg",
                    "mpg" => "video/mpeg",
                    "avi" => "video/x-msvideo",
            );
            $ctype = isset($content_types[$file_ext]) ? $content_types[$file_ext] : $ctype_default;
            header("Content-Type: " . $ctype);

                    //check if http_range is sent by browser (or download manager)
                    if(isset($_SERVER['HTTP_RANGE']))
                    {
                            list($size_unit, $range_orig) = explode('=', $_SERVER['HTTP_RANGE'], 2);
                            if ($size_unit == 'bytes')
                            {
                                    //multiple ranges could be specified at the same time, but for simplicity only serve the first range
                                    //http://tools.ietf.org/id/draft-ietf-http-range-retrieval-00.txt
                                    list($range, $extra_ranges) = explode(',', $range_orig, 2);
                            }
                            else
                            {
                                    $range = '';
                                    header('HTTP/1.1 416 Requested Range Not Satisfiable');
                                    exit;
                            }
                    }
                    else
                    {
                            $range = '';
                    }

                    //figure out download piece from range (if set)
                    list($seek_start, $seek_end) = explode('-', $range, 2);

                    //set start and end based on range (if set), else set defaults
                    //also check for invalid ranges.
                    $seek_end   = (empty($seek_end)) ? ($file_size - 1) : min(abs(intval($seek_end)),($file_size - 1));
                    $seek_start = (empty($seek_start) || $seek_end < abs(intval($seek_start))) ? 0 : max(abs(intval($seek_start)),0);

                    //Only send partial content header if downloading a piece of the file (IE workaround)
                    if ($seek_start > 0 || $seek_end < ($file_size - 1))
                    {
                            header('HTTP/1.1 206 Partial Content');
                            header('Content-Range: bytes '.$seek_start.'-'.$seek_end.'/'.$file_size);
                            header('Content-Length: '.($seek_end - $seek_start + 1));
                    }
                    else
                      header("Content-Length: $file_size");

                    header('Accept-Ranges: bytes');

                    set_time_limit(0);
                    fseek($file, $seek_start);

                    while(!feof($file)) 
                    {
                            print(@fread($file, 1024*8));
                            ob_flush();
                            flush();
                            if (connection_status()!=0) 
                            {
                                    @fclose($file);
                                    exit;
                            }			
                    }

                    // file save was a success
                    @fclose($file);
                    exit;
            }
            else 
            {
                    // file couldn't be opened
                    header("HTTP/1.0 500 Internal Server Error");
                    exit;
            }
    }
    else
    {
            // file does not exist
            header("HTTP/1.0 404 Not Found");
            exit;
    }
}

function write_logs($file_name = '', $text = '') {
    
    if (empty($file_name)) {
        $t = date('Ymd');
        $file_name = "logs-{$t}.txt";
    }
    
    $folder_path = 'logs';
    $file_path = $folder_path . '/' . $file_name;
    
    if (!file_exists($folder_path)) {
        mkdir($folder_path, 0755, true);
    }
    
    $file = fopen($file_path, "a");
    
    date_default_timezone_set('Asia/Ho_Chi_Minh');
    $date = date('Y-m-d H:i:s', time());
    
    $body = "\n" . $date . ' ';
    $body .= $text;
    
    fwrite($file, $body);
    fclose($file);
    
}