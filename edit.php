<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="shortcut icon" href="favicon.ico">
        <title>Edit Link - URL Shortener and Hider</title>
    </head>
    <?php 
    
    session_start();
    if (isset($_SESSION['username']) && !empty($_SESSION['username'])) {
        
    } else {
        header('Location: login.php');
        exit;
    }
    
    require_once('DbModel.php');
    require_once('config.php');
    
    if (isset($_GET['id']) && !empty($_GET['id'])) {
        
    } else {
        header('Location: index.php');
        exit;
    }
    
    $url_id = $_GET['id'];
    $dbModel = new DbModel();
    $url = $dbModel->get_url_by_id($url_id);
    
    ?>
    <body>
        <nav class="navbar navbar-default">
            <div class="container-fluid">
                <div class="navbar-header">
                    <a class="navbar-brand" href=".">URL Shortener and Hider</a>
                </div>
                <ul class="nav navbar-nav">
                    <li><a href=".">Generator</a></li>
                    <li><a href="list.php">List Links</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </div>
        </nav>

        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">Edit Link
                        </div>
                        <div class="panel-body">
                            <div class="row show-grid">
                                <form method="POST" id="find_links_form">
                                    <div class="col-md-12" id="error" style="display: none;">
                                        <div class="alert alert-danger">
                                            URL không được để trống.
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>URL</label>
                                            <input class="form-control" disabled value="<?php echo DOMAIN . 'download.php?id=' . $url['uid'] ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Source URL</label>
                                            <input class="form-control" id="url" name="url" value="<?php echo urldecode($url['url']) ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Type</label>
                                            <label class="radio-inline">
                                                <input type="radio" name="type" id="type_general" value="1" <?php echo $url['type'] == 1 ? 'checked=""' : ''; ?>>Direct Link
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" name="type" id="type_drive" value="2" <?php echo $url['type'] == 2 ? 'checked=""' : ''; ?>>Google Drive
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" name="type" id="type_cloud_mail_ru" value="3" <?php echo $url['type'] == 3 ? 'checked=""' : ''; ?>>Cloud.mail.ru
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <input type="hidden" name="url_id" value="<?php echo $url['id'] ?>"/>
                                        <input type="hidden" name="action" value="update_link"/>
                                        <button type="submit" class="btn btn-success" value="submit">Update</button>
                                        <button type="reset" class="btn btn-default">Reset</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <footer class="page-footer font-small teal pt-4">
                <div class="footer-copyright py-3" style='text-align: right'>© 2018 Developer by
                    <a href='skype:live:tuandao.dev?chat'> Tuan Dao</a>
                </div>
            </footer>
        </div>

        <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
        <link rel="stylesheet" href="css/custom.css">
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
        <script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
        <script>
            $(document).ready(function () {
                $("#find_links_form").submit(function(event){
                    event.preventDefault();
                    $("#error").hide();
                    if( $("#url").val().length === 0 ) {
                        $("#error").show();
                        return;
                    }
                    var values = $(this).serialize();
                    $.ajax({
                        url: "action.php",
                        type: "post",
                        data: values,
                        dataType: 'json',
                        success: function (response) {
                            if (response.status === "1") {
                                var r = confirm("Update successful. Back to list.");
                                if (r == true) {
                                    window.location.href = 'list.php';
                                } else {
                                    return;
                                }
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                           console.log(textStatus, errorThrown);
                        } 
                    });
                });
            });
        </script>
    </body>
</html>
