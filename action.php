<?php

require_once('DbModel.php');

$return = array();
$return['status'] = "0";

if (isset($_POST['action']) && $_POST['action'] == 'general_link') {
    $orginal_url = $_POST['url'];
    $type = $_POST['type'];
    $uid = uniqid();
    
    $dbModel = new DbModel();
    $result = $dbModel->insert_url($uid, $orginal_url, $type);
    
    if ($result) {
        $output_url = DOMAIN . "download.php?id=$uid";
        $result = "<label>Kết quả:</label><input class='form-control' value='$output_url'>";
        $return['status'] = "1";
        $return['html'] = $result;
    } else {
        $result = "<label>Có lỗi trong quá trình generate link. Vui lòng thử lại.</label>";
        $return['status'] = "1";
        $return['html'] = $result;
    }
}

echo json_encode($return);