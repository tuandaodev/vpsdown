<?php

require_once('DbModel.php');

$return = array();
$return['status'] = "0";

$dbModel = new DbModel();

if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'general_link':
            $orginal_url = $_POST['url'];
            $type = $_POST['type'];
            $uid = uniqid();

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
            break;
        case 'delete_url':
            $url_id = $_POST['url_id'];
            $result = $dbModel->delete_url($url_id);
            if ($result) {
                $return['status'] = "1";
            }
            break;
        case 'update_link':
            $url_id = $_POST['url_id'];
            $url = $_POST['url'];
            $url = urlencode($url);
            $type = $_POST['type'];
            $result = $dbModel->update_url($url_id, $url, $type);
            if ($result) {
                $return['status'] = "1";
            }
            break;
    }
}

echo json_encode($return);