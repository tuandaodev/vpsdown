<?php

function request_get($url) {
    $curl = curl_init();
    // Set some options - we are passing in a useragent too here
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $url,
    ]);
    // Send the request & save response to $resp
    $resp = curl_exec($curl);
    // Close request to clear up some resources
    curl_close($curl);
    
    return $resp;
}

function get_page_content($url, $body_only = true) {
    $proxy = null;

    $http["method"] = "GET";
    if ($proxy) {
        $http["proxy"] = "tcp://" . $proxy;
        $http["request_fulluri"] = true;
    }
    $options['http'] = $http;
    $context = stream_context_create($options);
    $body = @file_get_contents($url, NULL, $context);
    
    if ($body_only) {
        if (preg_match('~<body[^>]*>(.*?)</body>~si', $body, $matches)) {
            $body = $matches[1];
        }
    }
    return $body;
}

function pathcombine() {
    $result = "";
    foreach (func_get_args() as $arg) {
        if ($arg !== '') {
            if ($result && substr($result, -1) != "/")
                $result .= "/";
            $result .= $arg;
        }
    }
    return $result;
}

// Google Drive - Not Used
function GetConfirmCode($page_content) {
    $doc = new DomDocument;
    // We need to validate our document before refering to the id
    $doc->validateOnParse = true;
    $internalErrors = libxml_use_internal_errors(true);
    $doc->loadHtml($page_content);
    libxml_use_internal_errors($internalErrors);

    $element = $doc->getElementById('uc-download-link');
    if ($element) {
        $link = $element->getAttribute('href');

        $parts = parse_url($link);
        parse_str($parts['query'], $query);
        if (isset($query['confirm']) && !empty($query['confirm'])) {
            return $query['confirm'];
        }
    }
    return false;
}

// End Google Drive
// Begin for cloud.mail.ru
function GetMainFolder($page) {
    if (preg_match('~"folder":\s+(\{.*?"id":\s+"[^"]+"\s+\})\s+}~s', $page, $match))
        return json_decode($match[1], true);
    else
        return false;
}

function GetBaseUrl($page) {
    if (preg_match('~"weblink_get":.*?"url":\s*"(https:[^"]+)~s', $page, $match))
        return $match[1];
    else
        return false;
}

function GetTokenDownload($page) {
    if (preg_match('~"tokens":.*?"download":\s*"(.*?)"~s', $page, $match))
        return $match[1];
    else
        return false;
}

// End for cloud.mail.ru
// Begin APKPure.com
function GetApkPureFullUrlByPackname($page_content) {

    $apkpure_url = "https://apkpure.com";

    $doc = new DomDocument;
    // We need to validate our document before refering to the id
    $doc->validateOnParse = true;
    $internalErrors = libxml_use_internal_errors(true);
    $doc->loadHtml($page_content);
    libxml_use_internal_errors($internalErrors);

    $xpath = new \DOMXpath($doc);
    $articles = $xpath->query('//div[@class="ny-down"]');

    if (count($articles) == 0)
        return false;

    $links = [];

    foreach ($articles as $container) {
        $arr = $container->getElementsByTagName("a");
        foreach ($arr as $item) {
            $href = $item->getAttribute("href");
            $links[] = $apkpure_url . $href;
        }
    }

    if (count($links) > 0) {
        foreach ($links as $url) {
            if (strpos($url, 'download?from=details') !== false) {
                return $url;
            }
        }
    }

    return false;
}

function GetApkPureDownloadURL($page_content) {
    $doc = new DomDocument;
    // We need to validate our document before refering to the id
    $doc->validateOnParse = true;
    $internalErrors = libxml_use_internal_errors(true);
    $doc->loadHtml($page_content);

    libxml_use_internal_errors($internalErrors);

    $element = $doc->getElementById('iframe_download');
    if ($element) {
        $link = $element->getAttribute('src');
        return $link;
    }
    return false;
}

function write_logs($file_name = '', $text = '', $folder_path = 'logs') {

    if (empty($file_name)) {
        $t = date('Ymd');
        $file_name = "logs-{$t}.txt";
    }

//    $folder_path = 'logs';
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

function clean_filename($filename) {
    $filename = preg_replace('/[^a-z0-9\_\-\.]/i', '-', strtolower($filename));
    return $filename;
}

function generate_filename($filename) {
    $parts = pathinfo($filename);
    $new_filename = $parts['filename'] . '_' . time() . "." . $parts['extension'];

    return $new_filename;
}
// End APKPure.com
