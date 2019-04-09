<?php

require_once('api.class.php');

$tokenPath = dirname(__FILE__) . '/token.json';
if (file_exists($tokenPath)) {
    $accessToken = json_decode(file_get_contents($tokenPath), true);
    echo "TOKEN EXISTS. REMOVE FIRST.";
    echo "<pre>";
    print_r($accessToken);
    echo "</pre>";
    exit;
}

$client = getGoogleDriveClient();
$service = new Google_Service_Drive($client);

if ($client->getAccessToken()) {
    
    file_put_contents($tokenPath, json_encode($client->getAccessToken()));
    
    echo "<pre>";
    print_r($client->getAccessToken());
    echo "</pre>";
    echo "Login Done.";
}