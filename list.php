<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="shortcut icon" href="favicon.ico">
        <title>URL Shortener and Hider</title>
    </head>
    <?php 
    
    require_once('config.php');
    require_once('DbModel.php');
    
    session_start();
    if (isset($_SESSION['username']) && !empty($_SESSION['username'])) {
        
    } else {
        header('Location: login.php');
        exit;
    }
    
    $dbModel = new DbModel();
    $list = $dbModel->get_all_url();
    
    ?>
    <body>
        <nav class="navbar navbar-default">
            <div class="container-fluid">
                <div class="navbar-header">
                    <a class="navbar-brand" href=".">URL Shortener and Hider</a>
                </div>
                <ul class="nav navbar-nav">
                    <li><a href=".">Generator</a></li>
                    <li class="active"><a href="list.php">List Links</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </div>
        </nav>

        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">Checking Links
                        </div>
                        <div class="panel-body">
                                <table id="list_links" class="table table-striped table-bordered" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Type</th>
                                            <th>URL Destination</th>
                                            <th>URL Source</th>
                                            <th>Time Created</th>
                                            <th>Options</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                            foreach ($list as $url) {
                                        ?>
                                            <tr id="urlItem_<?php echo $url['id'] ?>">
                                                <td><?php echo $url['id'] ?></td>
                                                <td><?php 
                                                    switch ($url['type']) {
                                                        case 1:
                                                            echo "Direct Link";
                                                            break;
                                                        case 2:
                                                            echo "Google Drive";
                                                            break;
                                                        default:
                                                            echo "Unknown";
                                                            break;
                                                    }
                                                ?></td>
                                                <td><a target='_blank' href='<?php echo DOMAIN . 'download.php?id=' . $url['uid'] ?>'><?php echo DOMAIN . 'download.php?id=' . $url['uid'] ?></a></td>
                                                <td><?php echo urldecode($url['url']) ?></td>
                                                <td><?php echo date('H:i:s d-m-Y', $url['created']) ?></td>
                                                <td class="text-center">
                                                    <a href="edit.php?id=<?php echo $url['id'] ?>" class="btn btn-xs btn-primary" title="Edit this URL">Edit</a><button type="button" class="btn btn-xs btn-danger" title="Delete this URL" onclick="deleteURL('<?php echo $url['id'] ?>')">Delete</button>
                                                </td>
                                            </tr>
                                            <?php } ?>
                                    </tbody>
                                </table>
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
        <link rel="stylesheet" href="//cdn.datatables.net/1.10.19/css/dataTables.bootstrap.min.css">
        <link rel="stylesheet" href="css/custom.css">
        <style>
            table.table-bordered.dataTable tbody td {
                word-break: break-word;
            }
        </style>
        <script src="//code.jquery.com/jquery-3.3.1.js"></script>
        <script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
        <script src="//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
        <script src="//cdn.datatables.net/1.10.19/js/dataTables.bootstrap.min.js"></script>
        <script>
            $(document).ready(function () {
                $('#list_links').DataTable();
            });
            
            function deleteURL(url_id) {
                    
                var r = confirm("Do you want to delete this item?");
                if (r == true) {
                    
                } else {
                    return;
                }
                var data = {};
                data.url_id = url_id;
                data.action = 'delete_url';

                $.ajax({
                    url: "action.php",
                    type: "post",
                    data: data,
                    dataType: 'json',
                    success: function (response) {
                        console.log(response);
                        if (response.status === "1") {
                            console.log("delete");
                            console.log("#urlItem_" . url_id);
                            $("#urlItem_" + url_id).remove();
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                       console.log(textStatus, errorThrown);
                    } 
                });
            };
        </script>
    </body>
</html>
