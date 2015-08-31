<?php
define('ACTIVE_PAGE', 'index');

//If we got a joinStack variable, we pass it to the logic/index.php
$joinStack = (isset($_GET['joinStack'])) ? $_GET['joinStack'] : NULL;

//If we got a leaveStack variable, we pass it to the logic/index.php
$leaveStack = (isset($_GET['leaveStack'])) ? $_GET['leaveStack'] : NULL;

//We get the contents form the logic file.
require_once '../logic/index.php';
$data = $output;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo $data['site_name'] ?></title>
    <?php require_once 'head.php' ?>
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

    <?php include 'menu.php';

    //Output all modals
    foreach($data['modals'] as $key => $value){
        $modal = new Modal($key, $value['title'], $value['content'], isset($value['buttons']) ? $value['buttons'] : NULL);
        echo $modal->getModal($value['autocall']);
    }

    ?>

    <?php if($data['user_logged_in'] === FALSE):
    //If the user hasn't signed in, we show the welcome message. ?>
        <div class="jumbotron" style="margin-top: -15px">
            <h1>Find stacks. Get rampages.</h1>
            <p>NotA Stacks is a tool that can match you with friendly players. Unorganized games and unwanted teammates are a thing of the past. Have fun.</p>
            <p>
                <?php echo $data['steam_login_button'] ?>
            <p>
        </div>
    <?php else: //$data['user_logged_in'] === FALSE
    //Stack dashboard?>
        <?php $loggedUser->displayMessages() ?>
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
                            <form action="/notastacks/" method="POST">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    <h4 class="modal-title" id="addStack">Create a new Stack</h4>
                                </div>
                                <div class="modal-body">
                                    <p>Here you can create a Stack so that you can gather players and play with them!</p>
                                    <h3>What will you be playing?</h3>
                                    <div class="input-group">
                                        <span class="input-group-addon" id="gamemodeLabel">Kind of games</span>
                                        <input type="text" class="form-control" placeholder="e.g: &#34;Tryhard captains mode&#34; or &#34;Custom games&#34;" id="gamemode" name="gamemode" aria-describedby="gamemodeLabel" required="required">
                                    </div>
                                    <h3>What type of stack do you want?</h3>
                                    <div class="input-group">
                                        <div class="btn-toolbar">
                                            <div class="btn-group" data-toggle="buttons">
                                                <label class="server btn btn-default" data-value="5"><input type="radio" id="stackType_5'">Normal Stack (5 players)</label>
                                                <label class="server btn btn-default" data-value="10"><input type="radio" id="stackType_10'">Inhouse (10 players)</label>
                                            </div>
                                        </div>
                                        <input type="hidden" id="stackType" name="stackType" value="5">
                                        <script type="text/javascript">
                                            $(".server").click(function(){
                                                $("#stackType").val($(this).attr("data-value"));
                                            });
                                        </script>
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
                                    <?php if ($data['error'] === 'noServerSelected') echo '<div class="alert alert-danger" role="alert">You need to select at least one server.</div>'; ?>
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

                for($i=0; isset($data['servers'][$i]); $i++){
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
            <?php //Output stacks
            
            for($i=1; isset($data['stacks'][$i]); $i++){ //$i = 1; instead of $i = 0 because the row ID begins in 1
                //Panel
                echo '<div class="stack col-sm-4" id="stack'.$data['stacks'][$i]['id'].'" data-time="'.$data['stacks'][$i]['time'].'"';
                    //Add the server data into the div
                    //data-%server% can be:
                        //TRUE - the stack will play on that server
                        //FALSE - the stack won't play on that server
                        //HIDDEN - the stack will play on that server, but the user doesn't want to show stacks that only play on that server
                    $totalServers = count($data['stacks'][$i]['servers']);
                    for($i2=0; isset($data['servers'][$i2]); $i2++){ 
                        echo ' data-'.$data['servers'][$i2].'="';
                        if(in_array($data['servers'][$i2], $data['stacks'][$i]['servers'])){
                            echo 'TRUE';
                        }else{
                            echo 'FALSE';
                        }
                        echo '"';
                    }

                //Add the player data into the div
                echo ' data-players="'.$data['stacks'][$i]['playercount'].'"';
                echo ' data-userBelongsToStack="' . (($data['stacks'][$i]['userBelongsToStack']) ? 'TRUE' : 'FALSE') . '"';
                echo ' data-toggle="modal" data-target="joinStack'.$data['stacks'][$i]['id'].'">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">#'.$data['stacks'][$i]['id'].' - '.$data['stacks'][$i]['gamemode'].'';
                                //If the user belongs to the stack, show button to leave
                                if($data['stacks'][$i]['userBelongsToStack']){
                                    echo '<button id="leaveStack'.$data['stacks'][$i]['id'].'" type="button" class="btn btn-danger btn-xs" style="float:right;">'.(($data['stacks'][$i]['playercount'] === 1) ? 'Delete' : 'Leave').' stack</button>';
                                    echo '<script type="text/javascript">
                                            $("#leaveStack'.$data['stacks'][$i]['id'].'").click(function(){
                                                var confirmDialog = confirm("Are you sure you want to leave stack #'.$data['stacks'][$i]['id'].'?");
                                                if(confirmDialog){
                                                    window.location.replace("/notastacks/leave/'.$data['stacks'][$i]['id'].'");
                                                }
                                            });
                                        </script>';
                                }
                            echo '</h3></div>
                            <div class="panel-body">';
                                //List players
                                    echo '<div class="playerContainer">';
                                    for ($i2=0; $i2 < $data['stacks'][$i]['playercount']; $i2++) { 
                                        echo '<a href="profile.php?id='.$data['stacks'][$i]['players'][$i2]['id'].'" target="_blank"><img src="'.$data['stacks'][$i]['players'][$i2]['avatar'].'" alt="'.$data['stacks'][$i]['players'][$i2]['name'].'\'s avatar" width="64" height="64" /></a>';
                                    }

                                    //If the stack is not full, show join buttons
                                    if($i2 !== $data['stacks'][$i]['maxplayers']){
                                        if($data['stacks'][$i]['userBelongsToStack']){
                                            //If the player is already in the stack, show "looking for players" buttons
                                            $modalId = 'modalInvitetoStack'.$data['stacks'][$i]['id'];
                                            $modalTitle = 'Invite firends to Stack #'.$data['stacks'][$i]['id'];
                                            $modalContent =

                                            '<p>If you want your friends to join, you can give them the following link to invite them:</p>
                                            <input if="inviteLink" class="form-control" value="http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'invitation/'.$stacks[$i]->id.'" type="text" readonly style="background-color: white;"></input>
                                            <p>The following players have already joined the stack:</p>'
                                            .$data['stacks'][$i]['playerlist'];

                                            $modalButtons = NULL;

                                            for($i3=0; $i3 < ($data['stacks'][$i]['maxplayers'] - $i2); $i3++){ 
                                                echo '<button class="btn btn-default invitetoStack'.$data['stacks'][$i]['id'].'" style="width: 64px; height: 64px;" data-show="tooltip" title="Looking for players. Click to invite your friends!" data-toggle="modal" data-target="#modalInvitetoStack'.$data['stacks'][$i]['id'].'">
                                                <span class="glyphicon glyphicon-search" aria-hidden="true"></span>
                                                </button>';
                                            }
                                            
                                        }else{
                                            //If the player isn't in the stack yet, show join buttons
                                            $modalId = 'modalJoinStack'.$data['stacks'][$i]['id'];
                                            $modalTitle = 'Join stack #'.$data['stacks'][$i]['id'].'?';
                                            $modalContent =

                                            '<p>You will be matched with the following players:</p>'
                                            .$data['stacks'][$i]['playerlist'].
                                            '<p>Please, be friendly and respectful towards them, and try not to be late. Have fun!</p>';

                                            $modalButtons = '<button type="button" onclick="window.location.href=\'index.php?joinStack='.$data['stacks'][$i]['id'].'\';" class="btn btn-primary">Join the stack</button>';

                                            for($i3=0; $i3 < ($data['stacks'][$i]['maxplayers'] - $i2); $i3++){ 
                                                echo '<button class="btn btn-info joinStack'.$data['stacks'][$i]['id'].'" style="width: 64px; height: 64px;" data-show="tooltip" title="Join the stack" data-toggle="modal" data-target="#modalJoinStack'.$data['stacks'][$i]['id'].'">
                                                <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                                            </button>';
                                            }
                                        }
                                        $modal = new Modal($modalId, $modalTitle, $modalContent, $modalButtons);
                                        echo $modal->getModal();
                                    echo '</div>';
                                    }

                                //Output time
                                echo '<p>In <span id="timeForStack'.$data['stacks'][$i]['id'].'"></span> (that\'s <span id="timeRemainingForStack'.$data['stacks'][$i]['id'].'"></span>!)</p>';
                                echo "<script type=\"text/javascript\">
                                    var stacktime = moment('".$data['stacks'][$i]['time']."', 'X');
                                    $('#timeForStack".$data['stacks'][$i]['id']."').html(stacktime.format('LLLL'));
                                    $('#timeRemainingForStack".$data['stacks'][$i]['id']."').html(stacktime.fromNow());
                                </script>";

                                //Output servers
                                echo '<p>The stack will play in ';
                                for($i2=0; $i2 < count($data['stacks'][$i]['servers']); $i2++){ 
                                    echo $data['stacks'][$i]['servers'][$i2];
                                    //If there's more than one server, we'll need to add commas (",") and "or"
                                    if(count($data['stacks'][$i]['servers']) > 1){
                                        //Add a comma on every server but the last one
                                        if(($i2+1) !== count($data['stacks'][$i]['servers'])) echo ', ';
                                        //Add "or" before the last one
                                        if(($i2+2) === count($data['stacks'][$i]['servers'])) echo 'or ';

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

        <div id="footer" class="navbar navbar-default navbar-fixed-bottom">
            <div class="container">
                <p class="navbar-text">Created by <?php echo $GLOBAL_CONFIG['owner'] ?> for <a href="http://reddit.com/r/noobsoftheancient" target="_blank">/r/NoobsOfTheAncient</a>. Version <?php echo $GLOBAL_CONFIG['version'] ?>. <a href="https://github.com/MeLlamoPablo/NotaStacks" target="_blank">Source code</a>.</p>
            </div>
        </div>
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
        if($data['error'] === 'noServerSelected'){
            echo "$('#addStack').modal();\n";
            echo "$('#gamemode').val('".$gamemode."');\n";
        }
        ?>

    });
    </script>
</body>
</html>