<?php
//Config
require_once 'config.php';
//Steamauth requires
require_once 'steamauth/steamauth.php';
require_once 'steamauth/userInfo.php';
//DB requires
require_once 'connect.php';
//Classes
require_once 'classes.php';

//Check if the user has logged in
if(isset($_SESSION['steamid'])){
    //Check if the user is not in the database
    $r = $mysqli->query("SELECT steamid FROM users WHERE steamid = ".$_SESSION['steamid']);
    $r = $r->fetch_assoc();
    if(!isset($r)){
        $steamprofile = getInfo(); //TODO improve getInfo(); make it OOP.
        $mysqli->query("INSERT INTO users (`steamid`, `name`, `avatar`) VALUES ('".$mysqli->real_escape_string($steamprofile['steamid'])."', '".$mysqli->real_escape_string($steamprofile['personaname'])."', '".$mysqli->real_escape_string($steamprofile['avatarfull'])."');");
    }

    $r = $mysqli->query("SELECT * FROM users WHERE steamid = ". 1);
    $r = $r->fetch_assoc();

    //Create a logged user object
    $loggedUser = new User();

}

//TODO add submit to create stack and handle it

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>NotA Stacks</title>

    <!-- Meta tags -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->

    <script type="text/javascript" src="bower_components/jquery/dist/jquery.min.js"></script>
    <script type="text/javascript" src="bower_components/moment/min/moment.min.js"></script>
    <script type="text/javascript" src="bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="bower_components/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js"></script>
    <link rel="stylesheet" href="bower_components/bootstrap/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="bower_components/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css" />
</head>
<body>
    <div class="container">

    <?php include 'menu.php'; ?>

    <?php if(!isset($loggedUser)):
    //If the user hasn't signed in, we show the welcome message. ?>
        <div class="jumbotron" style="margin-top: -15px">
            <h1>Find stacks. Get rampages.</h1>
            <p>NotA Stacks is a tool that can match you with friendly players. Unorganized games and unwanted teammates are a thing of the past. Have fun.</p>
            <p>
                <?php steamlogin(); ?>
            <p>
        </div>
    <?php else: //!isset($loggedUser)) 
    //Stack dashboard?>
        <div class="well well-sm col-sm-4 btn-group container center-block">
            <!-- Add stack modal trigger -->
            <button type="button" class="btn btn-default" aria-label="Create Stack" data-show="tooltip" data-placement="bottom" title="Create a new stack" data-toggle="modal" data-target="#addStack">
                <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
            </button>

            <!-- Modal -->
            <div class="modal fade" id="addStack" tabindex="-1" role="dialog" aria-labelledby="addStack">
                <div class="modal-dialog" role="document">
                    <div class="modal-content"><form action="index.php" method="POST">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="myModalLabel">Create a new Stack</h4>
                        </div>
                        <div class="modal-body">
                            <p>Here you can create a Stack so that you can gather players and play with them!</p>
                            
                                <h3>What will you be playing?</h3>
                                <div class="input-group">
                                    <span class="input-group-addon" id="gamemodeLabel">Kind of games</span>
                                    <input type="text" class="form-control" placeholder="e.g: &#34;Tryhard captains mode&#34; or &#34;Custom games&#34;" name="gamemode" aria-describedby="gamemodeLabel">
                                </div>
                                <h3>When will you be playing?</h3>
                                <div class="input-group">
                                    <div class="container">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <div class="row">
                                                    <div class="col-md-8">
                                                        <input type="hidden" id="timePicker">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <script type="text/javascript">
                                            $(function () {
                                                $('#timePicker').datetimepicker({
                                                    inline: true,
                                                    sideBySide: true,
                                                    format: 'D-M-YYYY-H-m'
                                                });
                                                //Convert the user input to an unix timestamp, then set it as the input value.

                                                //We do this for avoiding problems with timezones.
                                                //Time should be stored in the server as an unix timestamp, then converted client-side with moment.js

                                                //The operation is performed once after the timePicker is created, and every time it's modified
                                                $('#timePicker').val(moment($('#timePicker').val(), 'D-M-YYYY-H-m').unix());
                                                $('#timePicker').on('dp.change', function(){
                                                    $(this).val(moment($(this).val(), 'D-M-YYYY-H-m').unix());
                                                });
                                            });
                                        </script>
                                    </div>
                                </div>
                                <h3>In what servers will you be playing?</h3>
                                <div class="input-group">
                                    <?php
                                    for($i=0; isset($GLOBAL_CONFIG['servers'][$i]); $i++){ 
                                        echo '<label class="checkbox-inline">';
                                            echo '<input type="checkbox" id="'.$GLOBAL_CONFIG['servers'][$i].'check" name="'.$GLOBAL_CONFIG['servers'][$i].'" value="'.$GLOBAL_CONFIG['servers'][$i].'"> '.$GLOBAL_CONFIG['servers'][$i];
                                        echo '</label>';
                                    }
                                    ?>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary">Create the stack</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="well well-sm col-sm-8 btn-group container center-block">
            <p>Filter stacks:</p>
        </div>
    <?php endif; //!isset($loggedUser)) ?>

    <?php echo FOOTER //This is defined in menu.php ?>
    </div>

    <script type="text/javascript">
    $( document ).ready(function() {
        //Tooltip opt-in
        $(function () {
            $('[data-show="tooltip"]').tooltip()
            //We use "data-show" instead of "data-toggle" so a button can have a tooltip and trigger a modal at once
        })
    });
    </script>
</body>
</html>