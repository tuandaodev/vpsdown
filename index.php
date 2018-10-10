<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="shortcut icon" href="favicon.ico">
        <title>URL Shortener and Hider</title>
    </head>
    <body>
        <nav class="navbar navbar-default">
            <div class="container-fluid">
                <div class="navbar-header">
                    <a class="navbar-brand" href=".">URL Shortener and Hider</a>
                </div>
                <ul class="nav navbar-nav">
                    <li class="active"><a href=".">Home</a></li>
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
                            <div class="row show-grid">
                                <form method="POST" id="find_links_form">
                                    <div class="col-md-12" id="error" style="display: none;">
                                        <div class="alert alert-danger">
                                            URL không được trống.
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Direct URL</label>
                                            <input class="form-control" id="url" name="url">
                                        </div>
                                        <div class="form-group">
                                            <label>Type</label>
                                            <label class="radio-inline">
                                                <input type="radio" name="type" id="type_general" value="1" checked="">Direct Link
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" name="type" id="type_drive" value="2">Google Drive
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <input type="hidden" name="action" value="general_link"/>
                                        <button type="submit" class="btn btn-success" value="submit">Submit</button>
                                        <button type="reset" class="btn btn-default">Reset</button>
                                    </div>
                                </form>
                                <div class="col-md-12" style="margin-top: 20px;">
                                    <div class="form-group" id="result">
                                    </div>
                                </div>
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
                                $("#result").html(response.html);
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
