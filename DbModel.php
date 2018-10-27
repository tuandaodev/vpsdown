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
    
    public function check_login($username, $password) {
        $query = "SELECT * FROM `users` WHERE username='$username' and password='$password'";
        
        $result = mysqli_query($this->link, $query);
        
        $count = mysqli_num_rows($result);
        
        if ($count > 0) {
            return true;
        } else {
            return false;
        }
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
    
    public function get_url_by_id($id) {
		
        $query = "SELECT * FROM url WHERE id = '$id'";
		
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
                        "' . time() . '")';
        
        $result = mysqli_query($this->link, $query);

        return $result;
        
    }
    
    public function get_all_url() {
		
        $query = "SELECT * FROM url order by id DESC";
		
        $result = mysqli_query($this->link, $query);
		
        if ($result) {
            $return = mysqli_fetch_all($result, MYSQLI_ASSOC);
            if ($return) {
                return $return;
            } else {
                return [];
            }
        } else {
            return [];
        }
        
    }
    
    public function delete_url($id) {
        $query = "DELETE FROM url WHERE id = $id";
        $result = mysqli_query($this->link, $query);
        return $result;
    }
    
    public function update_url($id, $url, $type) {
        $query = "  UPDATE url 
                    SET url = '$url', type = $type
                    WHERE id = $id";
        
        $result = mysqli_query($this->link, $query);

        return $result;
    }
    
    // Cache
    // Type 1: URL
    // Type 2: Package
    public function insert_cache($uid, $filename, $type = 1) {
        $query = '  INSERT INTO cache(uid, name, type, updated, status)
                        VALUES (
                        "' . $uid . '",
                        "' . $filename . '",
                        "' . $type . '",
                        "' . time() . '", 0)';
        
        $result = mysqli_query($this->link, $query);
        
        $last_id = 0;
        if ($result) {
            $last_id = mysqli_insert_id($this->link);
        }
        return $last_id;
    }
    
    // Status 1: cache completed
    // Status 0: caching
    public function get_cache($uid, $status = 1) {
        $query = "SELECT * FROM cache WHERE status = $status AND uid = '$uid'";
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
    
    // Status 1: cache completed
    // Status 0: caching
    public function get_cache_without_status($uid) {
        $query = "SELECT * FROM cache WHERE uid = '$uid'";
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
    
    public function update_cache_status($id) {
        $query = "  UPDATE cache 
                    SET status = 1
                    WHERE id = $id";
        $result = mysqli_query($this->link, $query);
        return $result;
    }
    
    public function update_cache_time($id) {
        $query = "  UPDATE cache 
                    SET updated = '".time()."'
                    WHERE id = $id";
        $result = mysqli_query($this->link, $query);
        return $result;
    }
    
    public function get_all_old_cache() {
		
        $query = "SELECT * FROM cache where updated <= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL " . CACHE_DAY . " HOUR))";
		
        $result = mysqli_query($this->link, $query);
		
        if ($result) {
            $return = mysqli_fetch_all($result, MYSQLI_ASSOC);
            if ($return) {
                return $return;
            } else {
                return [];
            }
        } else {
            return [];
        }
        
    }
    
    public function get_duplicate_cache() {
		
        $query = "  SELECT *
                    FROM cache
                    GROUP BY uid
                    HAVING count(uid) > 1";
		
        $result = mysqli_query($this->link, $query);
		
        if ($result) {
            $return = mysqli_fetch_all($result, MYSQLI_ASSOC);
            if ($return) {
                return $return;
            } else {
                return [];
            }
        } else {
            return [];
        }
        
    }
    
    public function delete_cache($id) {
        $query = "DELETE FROM cache WHERE id = $id";
        $result = mysqli_query($this->link, $query);
        return $result;
    }
    // End Cache
}

