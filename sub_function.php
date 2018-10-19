<?php

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
    
    $element = $doc->getElementById('search-res');
    $links = [];
    if ($element) {
        $arr = $element->getElementsByTagName("a"); // DOMNodeList Object
        foreach($arr as $item) { // DOMElement Object
          $href =  $item->getAttribute("href");
          $links[] = $apkpure_url . $href;
        }
        if (count($links)>0) {
            return $links[0];
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
// End APKPure.com