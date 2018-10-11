<html lang="en" class="gr__blackrockdigital_github_io"><head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Login - URL Shortener and Hider</title>

    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/custom.css">
    
</head>
<?php 

require_once('DbModel.php');
session_start();

if (isset($_SESSION['username']) && !empty($_SESSION['username'])) {
    header('Location: index.php');
    exit;
}

$message = '';
$cookie_name = 'siteAuth';
$cookie_time = (3600 * 24 * 30); // 30 days

if (isset($_POST['username']) and isset($_POST['password'])){
    
    if(empty($_POST['username']))
    {
        $message = "Username không được rỗng.";
    }
     
    if(empty($_POST['password']))
    {
        $message = "Password không được rỗng.";
    }
    
    if (empty($message) ) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        $dbModel = new DbModel();
        $password = md5($password);
        $check = $dbModel->check_login($username, $password);

        if ($check){
            $_SESSION['username'] = $username;
            if (isset($_POST['remember']) && $_POST['remember'] == 1) {
                setcookie($cookie_name, 'username='.$username.'&hash='.$password, time() + $cookie_time);
            }
            header('Location: index.php');
            exit;
        } else {
            $message = "Username hoặc Mật khẩu bị sai. Vui lòng thử lại.";
        }
    }
} else {
    if (isset($_COOKIE[$cookie_name]) && !empty($_COOKIE[$cookie_name])) {
        parse_str($_COOKIE[$cookie_name]);
        $dbModel = new DbModel();
        $check = $dbModel->check_login($username, $hash);
        if ($check) {
            $_SESSION['username'] = $username;
            header('Location: index.php');
            exit;
        }
    }
}

?>
<body data-gr-c-s-loaded="true">
    <div class="container">
        <div class="row">
            <div class="col-md-4 col-md-offset-4">
                <div class="login-panel panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Please Sign In</h3>
                    </div>
                    <div class="panel-body">
                        <?php
                        if (!empty($message)) {
                        ?>
                        <div class="alert alert-danger"><?php echo $message ?></div>
                        <?php } ?>
                        <form role="form" method="POST" style="margin-bottom: 0px;">
                            <fieldset>
                                <div class="form-group">
                                    <input class="form-control" placeholder="Username" name="username" type="text" autofocus="">
                                </div>
                                <div class="form-group">
                                    <input class="form-control" placeholder="Password" name="password" type="password" value="">
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input name="remember" type="checkbox" value="1">Remember Me
                                    </label>
                                </div>
                                <button type="submit" class="btn btn-lg btn-success btn-block">Login</button>
                            </fieldset>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>