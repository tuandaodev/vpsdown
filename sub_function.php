<?php

function get_page_content($url) {
    $proxy = null;

    $http["method"] = "GET";
    if ($proxy) {
        $http["proxy"] = "tcp://" . $proxy;
        $http["request_fulluri"] = true;
    }
    $options['http'] = $http;
    $context = stream_context_create($options);
    $body = @file_get_contents($url, NULL, $context);

    if (preg_match('~<body[^>]*>(.*?)</body>~si', $body, $matches)) {
        $body = $matches[1];
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
