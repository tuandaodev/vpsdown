<?php

require_once('config.php');
require_once('sub_function.php');
require_once('DbModel.php');
require_once('secretxxx/api.class.php');

Class Downloader {
    
    var $dbModel;
    var $chunkSizeBytes = 5 * 1024 * 1024;
    
    function __construct($dbModel) {
        $this->dbModel = $dbModel;
    }
    
    public function __destruct() {
        $this->dbModel->close();
    }
    
    public function download_cloud_mail_ru($file_url) {
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
                $this->download_full_info($direct_link, $file_item['name'], $data_size);
            } else {
                $this->download_direct_link($direct_link);
            }
        } else {
            header("Location: $file_url");
            exit;
        }
    }

    public function download_full_info($file_url, $filename, $data_size = 0) {

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
        $this->downloadFile($file_url, $filename, $data_size);
    }

    public function check_url_is_404($response_headers) {
        $header_string = json_encode($response_headers);
        $disallow = array('404.html', '404 Not Found');

        foreach ($disallow as $source) {
            if (strpos($header_string, $source) !== false) {
                return false;
            }
        }
        return true;
    }

    public function check_url_is_403($response_headers) {
        $header_string = json_encode($response_headers);
        $disallow = array('403 Forbidden', 'HTTP/1.0 403');

        foreach ($disallow as $source) {
            if (strpos($header_string, $source) !== false) {
                return true;
            }
        }
        return false;
    }
    
    public function check_url_is_error($json_response) {
        if (isset($json_response['error']) && !empty($json_response['error'])) {
            return true;
        }
        return false;
    }

    public function download_direct_link($file_url, $replace_name = false) {

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
        $this->downloadFile($file_url, $filename, $data_size);
    }

    public function download_google_drive_link($google_url) {

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
        
        $try_old_method = false;
        try {
            if (!$this->downloadFileGoogleAPI($file_id)) {
                $try_old_method = true;
            }
        } catch (Exception $ex) {
            $try_old_method = true;
            write_logs("error_download_google_drive_link.txt", $google_url . " => Exception: " . $ex->getMessage(), 'www-logs');
        }
        
//        $try_old_method = true; //for testing only
        
        try {
            if ($try_old_method) {
            
                write_logs("old_method_download_google_drive_link.txt", $google_url, 'www-logs');

                $file_url = "https://drive.google.com/uc?export=download&id=$file_id";

                $response_headers = array_change_key_case(get_headers($file_url, TRUE));

                if (@$_REQUEST['test'] == 1) {
                    echo $file_url;
                    echo "<pre>";
                    print_r($response_headers);
                    echo "</pre>";
                }

                if (!$this->check_url_is_404($response_headers)) {
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
                    
                    if (empty($filename) || !$data_size) {
                        $url_info = "https://www.googleapis.com/drive/v3/files/$file_id?fields=name,size&key=" . DEVELOPER_KEY;
                        $file_info = json_decode(request_get($url_info), true);
                        if (!$file_info) {
                            $file_info = json_decode(request_get($url_info), true);
                        }
                        if ($file_info) {
                            if (isset($file_info['size']) && !empty($file_info['size'])) {
                                $data_size = $file_info['size'];
                            }
                            if (isset($file_info['name']) && !empty($file_info['name'])) {
                                $filename = $file_info['name'];
                            }
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
                        try {
                            $this->downloadFile($direct_link, $filename, $data_size);
                        } catch (Exception $ex) {
                            header_remove();
                            header("Location: $file_url");
                            write_logs("error_download_google_drive_downloadFile.txt", $google_url . " => Exception: " . $ex->getMessage(), 'www-logs');
                            exit;
                        }
                        
                    } else {
                        write_logs("error_show_gdrive_directlink.txt", "Missing File Name: " . $file_url . " => response_headers: " . json_encode($response_headers), 'www-logs');
                        header("Location: $file_url");
                        exit;
                    }
                } else {
                    write_logs("error_show_gdrive_directlink.txt", "Missing location: " . $file_url . " => response_headers: " . json_encode($response_headers), 'www-logs');
                    header("Location: $file_url");
                    exit;
                }
            }
        } catch (Exception $ex) {
            write_logs("try_old_method.txt", $google_url . " => Exception: " . $ex->getMessage(), 'www-logs');
        }
        
        exit();
    }

    public function downloadFileGoogleAPI($fileID) {
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

            $caching = $this->dbModel->get_cache($uid, 0);    // caching, don't cache anymore

            $url_info = "https://www.googleapis.com/drive/v3/files/$fileID?fields=name,size&key=" . DEVELOPER_KEY;
            
            $temp_data = request_get($url_info);
            $file_info = json_decode($temp_data, true);
            if (!$file_info) {  //try again
                $temp_data = request_get($url_info);
                $file_info = json_decode($temp_data, true);
            }
            
            if ($file_info) {
                if ($this->check_url_is_error($file_info)) {
                    write_logs("check_url_is_error.txt", "Pos 1: " . $url_info . " => Temp Data: " . @$temp_data, 'www-logs');
                    return false;
                }
            } else {
                write_logs("check_url_is_error.txt", "Pos Error Cannot json parse: " . $url_info . " => Temp Data: " . @$temp_data, 'www-logs');
                return false;
            }

            $file_name = "";
            $file_size = 0;
            if ($file_info) {
                $file_name = $file_info['name'];
                $file_size = $file_info['size'];
            }
            
            $file_name = clean_filename($file_name);
            $file_name = generate_filename($file_name);

            if (!empty($caching)) {
                //readfile($url);
                $this->subDownloadFileGoogleDriveWithOutSaveToDisk($fileID, $file_name, $file_size);
            } else {
                $cache_id = $this->dbModel->insert_cache($uid, $file_name, $type, $file_size);
                $this->subDownloadFileGoogleDrive($fileID, $file_name, $cache_id, $file_size);
            }
        } else {
            //readfile($url);
            $this->subDownloadFileGoogleDriveWithOutSaveToDisk($fileID, $file_name, $file_size);
        }
        
        return true;
    }

    public function subDownloadFileGoogleDriveWithOutSaveToDisk($fileID, $filename, $data_size = 0) {

        ignore_user_abort(true);
        set_time_limit(7200);

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

        if (!file_exists(DOWNLOAD_FOLDER)) {
            mkdir(DOWNLOAD_FOLDER, 0777, true);
        }

        $newfname = DOWNLOAD_FOLDER . '/' . $filename;

        $google_client = getGoogleDriveClient();
        $drive_service = new Google_Service_Drive($google_client);

        $response = $drive_service->files->get($fileID, array(
                    'alt' => 'media' ));
        
        while (!$response->getBody()->eof()) {
            echo $data_temp = $response->getBody()->read($this->chunkSizeBytes);
        }
        
        exit;
    }

    public function subDownloadFileGoogleDrive($fileID, $filename, $cache_id = 0, $data_size = 0) {

        ignore_user_abort(true);
        set_time_limit(7200);

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

        if (!file_exists(DOWNLOAD_FOLDER)) {
            mkdir(DOWNLOAD_FOLDER, 0777, true);
        }

        $newfname = DOWNLOAD_FOLDER . '/' . $filename;

        $google_client = getGoogleDriveClient();
        $http = $google_client->authorize();
        $drive_service = new Google_Service_Drive($google_client);

        $response = $drive_service->files->get($fileID, array(
                    'alt' => 'media' ));
        
        $newf = fopen($newfname, 'wb');
        if ($newf) {
            while (!$response->getBody()->eof()) {
                echo $data_temp = $response->getBody()->read($this->chunkSizeBytes);
                fwrite($newf, $data_temp);
            }
        }

        if ($newf) {
            // close the file pointer
            fclose($newf);

            $newfsize = filesize($newfname);

            if ($data_size) {
                if ($newfsize != $data_size || $newfsize == 0) {

                    $size_log = $data_size . " diff " . $newfsize;
                    if (file_exists($newfname)) {
                        unlink($newfname);
                    }
                    $this->dbModel->delete_cache($cache_id);

                    write_logs("error_cache_size.txt", $size_log, 'www-logs');
                    return false;
                }
            }

            // Let client know the caching is completed.
            if ($cache_id) {
                $this->dbModel->update_cache_status($cache_id, $newfsize);
            }
        }
        
        exit();
    }

//  $type = 1: URL 
//  $type = 2: Package
    public function downloadFile($url, $filename, $data_size = 0) {
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

            $caching = $this->dbModel->get_cache($uid, 0);    // caching, don't cache anymore

            if (!empty($caching)) {
                readfile($url);
            } else {
                $filename = clean_filename($filename);
                $filename = generate_filename($filename);
                $cache_id = $this->dbModel->insert_cache($uid, $filename, $type, $data_size);
                $this->subDownloadFile($url, $filename, $cache_id, $data_size);
            }
        } else {
            readfile($url);
        }
    }

// Begin caching, set status cache = 0, when it's done, update to 1 to let client know the cache is completed and they can download it
    public function subDownloadFile($url, $filename, $cache_id = 0, $data_size = 0) {

        ignore_user_abort(true);
        set_time_limit(0);

        if (!file_exists(DOWNLOAD_FOLDER)) {
            mkdir(DOWNLOAD_FOLDER, 0777, true);
        }

        $newfname = DOWNLOAD_FOLDER . '/' . $filename;

        $file = fopen($url, 'rb');
        if ($file) {
            $newf = fopen($newfname, 'wb');
            if ($newf) {
                while (!feof($file)) {
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

            // Check connections
//        if (connection_aborted()) {
//            if (file_exists($newfname)) {
//                unlink($newfname);
//            }
//            $this->dbModel->delete_cache($cache_id);
//            $size_log = "Connection aborted";
//            write_logs("error_cache_size.txt", $size_log, 'www-logs');
//            return false;
//        }

            $newfsize = filesize($newfname);

            if ($data_size) {
                if ($newfsize != $data_size || $newfsize == 0) {

                    $size_log = $data_size . " diff " . $newfsize;
                    if (file_exists($newfname)) {
                        unlink($newfname);
                    }
                    $this->dbModel->delete_cache($cache_id);

                    write_logs("error_cache_size.txt", $size_log, 'www-logs');
                    return false;
                }
            }

            // Let client know the caching is completed.
            if ($cache_id) {
                $this->dbModel->update_cache_status($cache_id, $newfsize);
            }
        }
    }

    public function check_cache($uid) {

        $cache = $this->dbModel->get_cache($uid);

        if (!empty($cache)) {
            $filepath = DOWNLOAD_FOLDER . '/' . $cache['name'];
            if (!file_exists($filepath)) {
                $this->dbModel->delete_cache($cache['id']);
                return [];
            }
            return $cache;
        } else {
            return [];
        }
    }

    public function download_cache($cache) {
        //$this->dbModel->update_cache_time($cache['id']);
        if (in_array('mod_xsendfile', apache_get_modules())) {
            $this->download_cache_xsendfile($cache);
        } else {
            $this->download_cache_normal($cache);
        }
    }

    public function download_cache_xsendfile($cache) {
        $filename = $cache['name'];
        $filepath = DOWNLOAD_FOLDER . '/' . $filename;
        header("X-Sendfile: $filepath");
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        exit;
    }

    public function download_cache_normal($cache) {

        $filename = $cache['name'];

        @ini_set('error_reporting', E_ALL & ~ E_NOTICE);

        //- turn off compression on the server
        @apache_setenv('no-gzip', 1);
        @ini_set('zlib.output_compression', 'Off');

        // sanitize the file request, keep just the name and extension
        // also, replaces the file location with a preset one ('./myfiles/' in this example)

        $file_path = DOWNLOAD_FOLDER . '/' . $filename;
        $path_parts = pathinfo($file_path);
        $file_name = $path_parts['basename'];
        $file_ext = $path_parts['extension'];

        // allow a file to be streamed instead of sent as an attachment
        $is_attachment = isset($_REQUEST['stream']) ? false : true;

        // make sure the file exists
        if (is_file($file_path)) {
            $file_size = filesize($file_path);
            $file = @fopen($file_path, "rb");
            if ($file) {
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
                if (isset($_SERVER['HTTP_RANGE'])) {
                    list($size_unit, $range_orig) = explode('=', $_SERVER['HTTP_RANGE'], 2);
                    if ($size_unit == 'bytes') {
                        //multiple ranges could be specified at the same time, but for simplicity only serve the first range
                        //http://tools.ietf.org/id/draft-ietf-http-range-retrieval-00.txt
                        list($range, $extra_ranges) = explode(',', $range_orig, 2);
                    } else {
                        $range = '';
                        header('HTTP/1.1 416 Requested Range Not Satisfiable');
                        exit;
                    }
                } else {
                    $range = '';
                }

                //figure out download piece from range (if set)
                list($seek_start, $seek_end) = explode('-', $range, 2);

                //set start and end based on range (if set), else set defaults
                //also check for invalid ranges.
                $seek_end = (empty($seek_end)) ? ($file_size - 1) : min(abs(intval($seek_end)), ($file_size - 1));
                $seek_start = (empty($seek_start) || $seek_end < abs(intval($seek_start))) ? 0 : max(abs(intval($seek_start)), 0);

                //Only send partial content header if downloading a piece of the file (IE workaround)
                if ($seek_start > 0 || $seek_end < ($file_size - 1)) {
                    header('HTTP/1.1 206 Partial Content');
                    header('Content-Range: bytes ' . $seek_start . '-' . $seek_end . '/' . $file_size);
                    header('Content-Length: ' . ($seek_end - $seek_start + 1));
                } else
                    header("Content-Length: $file_size");

                header('Accept-Ranges: bytes');

                set_time_limit(0);
                fseek($file, $seek_start);

                while (!feof($file)) {
                    print(@fread($file, 1024 * 8));
                    ob_flush();
                    flush();
                    if (connection_status() != 0) {
                        @fclose($file);
                        exit;
                    }
                }

                // file save was a success
                @fclose($file);
                exit;
            } else {
                // file couldn't be opened
                header("HTTP/1.0 500 Internal Server Error");
                exit;
            }
        } else {
            // file does not exist
            header("HTTP/1.0 404 Not Found");
            exit;
        }
    }

}
