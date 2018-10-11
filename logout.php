<?php
    session_start();
    session_destroy();
    
    $cookie_name = 'siteAuth';
    if (isset($_COOKIE[$cookie_name])) {
        unset($_COOKIE[$cookie_name]);
        setcookie($cookie_name, '', time() - 3600);
    }
    
    header('Location: login.php');
?>