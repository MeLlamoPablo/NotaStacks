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

//"Login as" feature for testing purposes. It is only allowed with the DEV_MODE enabled, wich mustn't be in production.
if($GLOBAL_CONFIG['DEV_MODE'] AND isset($_GET['loginas'])){
    $loggedUser = new User('db', $_GET['loginas']);
    $_SESSION['steamid'] = $loggedUser->steamid;
    die('<meta http-equiv="refresh" content="0; url=index.php" />');
}

//If the user has created a stack, handle it.
if(isset($_POST['createStackButton'])){
    if(!isset($_POST['gamemode'])) die('The gamemode information was not sent correctly. You shouldn\'t be seeing this error, though. Blame /u/sfcpfc for his incompetence. Or maybe upgrade to a browser that supports HTML5, you lazy.');
    $gamemode = $mysqli->real_escape_string($_POST['gamemode']);

    if(!isset($_POST['timePicker'])) die('The time information was not sent correctly. You shouldn\'t be seeing this error, though. Blame /u/sfcpfc for his incompetence. Or maybe upgrade to a browser that supports HTML5, you lazy.');
    $time = $mysqli->real_escape_string($_POST['timePicker']);

    $servers['string'] = '';
    for($i=0; isset($GLOBAL_CONFIG['servers'][$i]); $i++){ 
        if(isset($_POST[$GLOBAL_CONFIG['servers'][$i]])){
            $servers[$GLOBAL_CONFIG['servers'][$i]] = TRUE;
            $servers['string'] .= '-'.$GLOBAL_CONFIG['servers'][$i];
        }else{
            $servers[$GLOBAL_CONFIG['servers'][$i]] = FALSE;
        }
    }
    $servers['string'] = substr($servers['string'], 1);

    //TODO there might be a better way of doing this
    function atLeastOneOfTheServersIsTrue($servers){
        global $GLOBAL_CONFIG;
        for($i=0; isset($GLOBAL_CONFIG['servers'][$i]); $i++){ 
           if($servers[$GLOBAL_CONFIG['servers'][$i]] === TRUE) return TRUE;
        }
        return FALSE;
    }

    //If the user hasn't selected any server, stop the handle and output him an error.
    //Else, continue with the handle
    if(!atLeastOneOfTheServersIsTrue($servers)){
        $error = 'noServerSelected';
    }else{
        $createdStack = new Stack('provided', array(
            'players' => array($loggedUser),
            'gamemode' => $gamemode,
            'time' => $time,
            'ownerid' => $loggedUser->id,
            'server' => $servers['string']));
    }

}

//If the player has joined a stack
if(isset($_GET['joinStack'])){
    $stack = new Stack($_GET['joinStack']);
    //If the stack is not full
    if(!isset($stack->players) OR (count($stack->players) === 5)) die('The stack is full<meta http-equiv="refresh" content="3; url=index.php" />');

    //Prevent the user for joining a stack where he already is
    if(in_array($loggedUser, $stack->players)) die('You\'ve already joined this stack.<meta http-equiv="refresh" content="3; url=index.php" />');

    //Add the player
    $stack->addPlayer($loggedUser);
    die('<meta http-equiv="refresh" content="0; url=index.php" />'); //We redirect the user so that we get rid of ?joinStack, thus, the user can refresh without being prompted an error.

}

//If the player has left a stack
if(isset($_GET['leaveStack'])){
    $stack = new Stack($_GET['leaveStack']);
    $stack->removePlayer($loggedUser);
    die('<meta http-equiv="refresh" content="0; url=index.php" />'); //We redirect the user so that we get rid of ?joinStack, thus, the user can refresh without being prompted an error.

}

if(!isset($error)) $error = 'none';

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
    <link rel="stylesheet" href="resources/custom.css" />
    <!-- JS functions -->
    <script type="text/javascript">
    //Hides the elements that have every server property set to hidden or false (that's the same as checking if data-%server%!=true)
    function hideNotTrue(){
        var selector = '<?php 
            for($i=0; isset($GLOBAL_CONFIG['servers'][$i]); $i++){
                echo '[data-'.$GLOBAL_CONFIG['servers'][$i].'!="TRUE"]';
            }
        ?>';
        $('.stack'+selector).css("display", "none");
    }
    </script>
</head>
<body>
    <div class="container" id="wrap">

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
        <?php $loggedUser->displayMessage() ?>
        <div id="joinedStacksRow" class="row">
            <h3>Joined Stacks:</h3>
            <div id="joinedStacks" class=""></div>
        </div>
        <div class="row">
            <h3>Available Stacks:</h3>
            <div class="well well-sm col-sm-4 btn-group container center-block">
                <!-- Add stack modal trigger -->
                <button type="button" class="btn btn-default" aria-label="Create Stack" data-show="tooltip" data-placement="bottom" title="Create a new stack" data-toggle="modal" data-target="#addStack">
                    <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                </button>

                <!-- Modal -->
                <div class="modal fade" id="addStack" tabindex="-1" role="dialog" aria-labelledby="addStack">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <form action="index.php" method="POST">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    <h4 class="modal-title" id="myModalLabel">Create a new Stack</h4>
                                </div>
                                <div class="modal-body">
                                    <p>Here you can create a Stack so that you can gather players and play with them!</p>
                                    <h3>What will you be playing?</h3>
                                    <div class="input-group">
                                        <span class="input-group-addon" id="gamemodeLabel">Kind of games</span>
                                        <input type="text" class="form-control" placeholder="e.g: &#34;Tryhard captains mode&#34; or &#34;Custom games&#34;" id="gamemode" name="gamemode" aria-describedby="gamemodeLabel" required="required">
                                    </div>
                                    <h3>When will you be playing?</h3>
                                    <div class="input-group">
                                        <div class="container">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <div class="row">
                                                        <div class="col-md-8">
                                                            <input type="hidden" id="timePicker" name="timePicker">
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
                                    <?php if ($error === 'noServerSelected') echo '<div class="alert alert-danger" role="alert">You need to select at least one server.</div>'; ?>
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
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                    <button type="submit" name="createStackButton" class="btn btn-primary">Create the stack</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="well well-sm col-sm-8 container center-block">
                <div class="btn-group">
                <?php

                for($i=0; isset($GLOBAL_CONFIG['servers'][$i]); $i++){
                    //Code
                    echo '<button id="toggle'.$GLOBAL_CONFIG['servers'][$i].'" type="button" class="btn btn-default active" aria-label="Show/hide '.$GLOBAL_CONFIG['servers'][$i].'" data-show="tooltip" data-placement="bottom" title="Show/hide stacks that only play on '.$GLOBAL_CONFIG['servers'][$i].'">
                        '.$GLOBAL_CONFIG['servers'][$i].'
                    </button>';
                    //Script
                    echo '<script type="text/javascript">';
                        echo '$("#toggle'.$GLOBAL_CONFIG['servers'][$i].'").click(function(){
                                if($(this).hasClass("active")){ //If the server is being shown
                                    //Change the attribute to "HIDDEN". "HIDDEN" works the same way as "FALSE", but it can be recovered
                                    $(".stack[data-'.$GLOBAL_CONFIG['servers'][$i].'=\'TRUE\']").attr("data-'.$GLOBAL_CONFIG['servers'][$i].'", "HIDDEN");
                                    //"Un-press" the button
                                    $(this).removeClass("active");
                                    //Make the button lose focus
                                    $(this).blur();
                                    //Hide the stacks whose every data-%server% is either FALSE or HIDDEN
                                    hideNotTrue();
                                }else{
                                    //Display the items whose data-'.$GLOBAL_CONFIG['servers'][$i].' is HIDDEN and change its attribute to "TRUE"
                                    $(".stack[data-'.$GLOBAL_CONFIG['servers'][$i].'=\'HIDDEN\']").css("display", "inline");
                                    $(".stack[data-'.$GLOBAL_CONFIG['servers'][$i].'=\'HIDDEN\']").attr("data-'.$GLOBAL_CONFIG['servers'][$i].'", "TRUE");
                                    //"Press" the button
                                    $(this).addClass("active");
                                    //Make the button lose focus
                                    $(this).blur();
                                }
                            });';
                    echo '</script>';
                }

                ?>
            </div><!-- /.btn-group -->
            </div>
        </div><!--/class="row"-->
        <div class="row stackContainter">
            <?php //Get the stacks and output them
            
            $r = $mysqli->query("SELECT id FROM stacks WHERE time > ".time());
            for($i=1; $r2 = $r->fetch_assoc(); $i++){ //$i = 1; instead of $i = 0 because the row ID begins in 1
                $stacks[$i] = new Stack($r2['id']);
                //Does the user belongs to the stack?
                $userBelongsToStack = (in_array($loggedUser, $stacks[$i]->players) ? TRUE : FALSE);   

                //Panel
                echo '<div class="stack col-sm-4" id="stack'.$stacks[$i]->id.'" data-time="'.$stacks[$i]->time.'"';
                    //Add the server data into the div
                    //data-%server% can be:
                        //TRUE - the stack will play on that server
                        //FALSE - the stack won't play on that server
                        //HIDDEN - the stack will play on that server, but the user doesn't want to show stacks that only play on that server
                    $stackServers = explode('-', $stacks[$i]->server);
                    $totalServers = count($stackServers);
                    for($i2=0; isset($GLOBAL_CONFIG['servers'][$i2]); $i2++){ 
                        echo ' data-'.$GLOBAL_CONFIG['servers'][$i2].'="';
                        if(in_array($GLOBAL_CONFIG['servers'][$i2], $stackServers)){
                            echo 'TRUE';
                        }else{
                            echo 'FALSE';
                        }
                        echo '"';
                    }

                    //Add the player data into the div
                    echo ' data-players="'.count($stacks[$i]->players).'"';
                    echo ' data-userBelongsToStack="';if($userBelongsToStack){echo 'TRUE';}else{echo 'FALSE';}echo '"';
                echo ' data-toggle="modal" data-target="joinStack'.$stacks[$i]->id.'">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">#'.$stacks[$i]->id.' - '.$stacks[$i]->gamemode.'';
                                //If the user belongs to the stack, show button to leave
                                if($userBelongsToStack){ echo '<button id="leaveStack'.$stacks[$i]->id.'" type="button" class="btn btn-danger btn-xs" style="float:right;">Leave stack</button>';
                                    echo '<script type="text/javascript">
                                            $("#leaveStack'.$stacks[$i]->id.'").click(function(){
                                                var confirmDialog = confirm("Are you sure you want to leave stack #'.$stacks[$i]->id.'?");
                                                if(confirmDialog){
                                                    window.location.replace("index.php?leaveStack='.$stacks[$i]->id.'");
                                                }
                                            });
                                        </script>';
                                }
                            echo '</h3></div>
                            <div class="panel-body">';
                                //List players
                                    echo '<div class="playerContainer">';
                                    for ($i2=0; $i2 < count($stacks[$i]->players); $i2++) { 
                                        echo '<img src="'.$stacks[$i]->players[$i2]->avatar.'" alt="'.$stacks[$i]->players[$i2]->avatar.'\'s avatar width="64" height="64" />';
                                    }

                                    //If there are less than 5 players, show join buttons
                                    if($i2 !== 5){
                                        if($userBelongsToStack){
                                            //If the player is already in the stack, show "looking for players" buttons
                                            for($i3=0; $i3 < (5 - $i2); $i3++){ 
                                                echo '<button class="btn btn-default invitetoStack'.$stacks[$i]->id.'" style="width: 64px; height: 64px;" data-show="tooltip" title="Looking for players. Click to invite your friends!" data-toggle="modal" data-target="#modalInvitetoStack'.$stacks[$i]->id.'">
                                                <span class="glyphicon glyphicon-search" aria-hidden="true"></span>
                                            </button>';
                                            }
                                            echo '<div class="modal fade" id="modalInvitetoStack'.$stacks[$i]->id.'" tabindex="-1" role="dialog" aria-labelledby="modalInvitetoStack'.$stacks[$i]->id.'Label">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                            <h4 class="modal-title" id="modalInvitetoStack'.$stacks[$i]->id.'Label">Invite firends to Stack #'.$stacks[$i]->id.'</h4>
                                                        </div>
                                                        <div class="modal-body">
                                                            ';
                                                                echo '<p>Hey there, this feature isn\'t implemented yet, but soon&#8482; you\'ll be able to give a link to your firends and invite them.</p>
                                                                <p>The following players have already joined the stack:</p>
                                                                <table class="table table-bordered table-striped">
                                                                    <tbody>';
                                                                        for ($i2=0; $i2 < count($stacks[$i]->players); $i2++) { 
                                                                            echo '<tr>';
                                                                                echo '<td style="width:11%;"><img src="'.$stacks[$i]->players[$i2]->avatar.'" alt="'.$stacks[$i]->players[$i2]->avatar.'\'s avatar width="48" height="48" /></td>';
                                                                                echo '<td><a href="'.$stacks[$i]->players[$i2]->getURL().'" target="_blank">'.$stacks[$i]->players[$i2]->name.'</a></td>';
                                                                            echo '</tr>';
                                                                        }
                                                                    echo '</tbody>
                                                                </table>';
                                                            echo '</div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                        </div>
                                                    </div><!-- /.modal-content -->
                                                </div><!-- /.modal-dialog -->
                                            </div><!-- /.modal -->';
                                        }else{
                                            //If the player isn't in the stack yet, show join buttons
                                            for($i3=0; $i3 < (5 - $i2); $i3++){ 
                                                echo '<button class="btn btn-info joinStack'.$stacks[$i]->id.'" style="width: 64px; height: 64px;" data-show="tooltip" title="Join the stack" data-toggle="modal" data-target="#modalJoinStack'.$stacks[$i]->id.'">
                                                <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                                            </button>';
                                            }
                                            echo '<div class="modal fade" id="modalJoinStack'.$stacks[$i]->id.'" tabindex="-1" role="dialog" aria-labelledby="modalJoinStack'.$stacks[$i]->id.'Label">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                            <h4 class="modal-title" id="modalJoinStack'.$stacks[$i]->id.'Label">Join stack #'.$stacks[$i]->id.'?</h4>
                                                        </div>
                                                        <div class="modal-body">
                                                            ';
                                                                echo '<p>You will be matched with the following players:</p>
                                                                <table class="table table-bordered table-striped">
                                                                    <tbody>';
                                                                        for ($i2=0; $i2 < count($stacks[$i]->players); $i2++) { 
                                                                            echo '<tr>';
                                                                                echo '<td style="width:11%;"><img src="'.$stacks[$i]->players[$i2]->avatar.'" alt="'.$stacks[$i]->players[$i2]->avatar.'\'s avatar width="48" height="48" /></td>';
                                                                                echo '<td><a href="'.$stacks[$i]->players[$i2]->getURL().'" target="_blank">'.$stacks[$i]->players[$i2]->name.'</a></td>';
                                                                            echo '</tr>';
                                                                        }
                                                                    echo '</tbody>
                                                                </table>';
                                                            echo '<p>Please, be friendly and respectful towards them, and try not to be late. Have fun!</p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                            <button type="button" onclick="window.location.href=\'index.php?joinStack='.$stacks[$i]->id.'\';" class="btn btn-primary">Join the stack</button>
                                                        </div>
                                                    </div><!-- /.modal-content -->
                                                </div><!-- /.modal-dialog -->
                                            </div><!-- /.modal -->';
                                        }
                                    echo '</div>';
                                    }

                                //Output time
                                echo '<p>In <span id="timeForStack'.$stacks[$i]->id.'"></span> (that\'s <span id="timeRemainingForStack'.$stacks[$i]->id.'"></span>!)</p>';
                                echo "<script type=\"text/javascript\">
                                    var stacktime = moment('".$stacks[$i]->time."', 'X');
                                    $('#timeForStack".$stacks[$i]->id."').html(stacktime.format('LLLL'));
                                    $('#timeRemainingForStack".$stacks[$i]->id."').html(stacktime.fromNow());
                                </script>";

                                //Output servers
                                echo '<p>The stack will play in ';
                                for($i2=0; $i2 < $totalServers; $i2++){ 
                                    echo $stackServers[$i2];
                                    //If there's more than one server, we'll need to add commas (",") and "or"
                                    if($totalServers > 1){
                                        //Add a comma on every server but the last one
                                        if(($i2+1) !== $totalServers) echo ', ';
                                        //Add "or" before the last one
                                        if(($i2+2) === $totalServers) echo 'or ';

                                    }
                                }
                                echo '</p>';
                            echo '</div>
                        </div>
                    </div>';
            }
            ?>
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

        //Sort stacks by time
        $('.stackContainter').html(sortStacks('.stack', 'data-time', true));
        //Thanks to http://blog.troygrosfield.com/2014/04/25/jquery-sorting/
        function sortStacks(selector, attrName, lowToHi) {
            return $($(selector).toArray().sort(function(a, b){
                var aVal = parseInt(a.getAttribute(attrName)),
                    bVal = parseInt(b.getAttribute(attrName));
                if(lowToHi){
                    return aVal - bVal;
                }else{
                    return bVal - aVal;
                }
            }));
        }

        //Move stacks where the player belongs into #joinedStacks
        $('.stack[data-userBelongsToStack="TRUE"]').appendTo('#joinedStacks');
        //If there aren't joined stacks, remove the div
        if(!$('#joinedStacks > .stack').length){
            $('#joinedStacksRow').remove();
        }
        
        <?php
        //If there was an error upon creating a stack, automatically call the modal and fill the blanks
        if($error === 'noServerSelected'){
            echo "$('#addStack').modal();\n";
            echo "$('#gamemode').val('".$gamemode."');\n";
        }
        ?>

    });
    </script>
</body>
</html>