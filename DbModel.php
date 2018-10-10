<?php

require_once('config.php');
/**
 *
 * @author MT
 */

class DbModel {

    private $link;

    public function __construct() {
        $this->link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        mysqli_set_charset($this->link, "utf8");
    }
    
    public function query($query) {
        $result = mysqli_query($this->link, $query);
        
        if ($result) {
            $return = mysqli_fetch_all($result, MYSQLI_ASSOC);
        } else {
            $return = array();
        }
        
        return $return;
    }
    
    public function get_url($uid) {
		
        $query = "SELECT * FROM url WHERE uid = '$uid'";
		
        $result = mysqli_query($this->link, $query);
		
        if ($result) {
            $return = mysqli_fetch_all($result, MYSQLI_ASSOC);
            if ($return) {
                return $return[0];
            } else {
                return [];
            }
        } else {
            return [];
        }
        
    }
    
    public function insert_url($uid, $url, $type = 1) {
        
        $query = '  INSERT INTO url(uid, url, type, created)
                        VALUES (
                        "' . $uid . '",
                        "' . urlencode($url) . '",
                        "' . $type . '",
                        Now())';
        
        $result = mysqli_query($this->link, $query);

        return $result;
        
    }
    
}

