<?php
//Ban hammer handle
if(isset($loggedUser) AND $loggedUser->ban !== '0'){
    if($loggedUser->ban === 'permanent'){
        $modal = new Modal('banHammer', 'You have been banned forever', 'Please contact '.$GLOBAL_CONFIG['owner'].' if you think this is a mistake.');
        echo $modal->getModal(TRUE);
        unset($loggedUser);
    }elseif($loggedUser->ban > time()){
        $content = '<p>You will be able to use this site again <span id="banExpire"></span>.<br>Please contact '.$GLOBAL_CONFIG['owner'].' if you think this is a mistake.</p>';
        $content .= "<script type=\"text/javascript\">
        var bantime = moment('".$loggedUser->ban."', 'X');
        $('#banExpire').html(bantime.fromNow());
        </script>";
        $modal = new Modal('banHammer', 'You have been temporarily banned', $content);
        $time = gmdate('Y-m-d\TH:i:s\Z', (time() - $loggedUser->ban));
        echo $modal->getModal(TRUE);
        unset($loggedUser);
    }
}

//Invitation handle
if(isset($_GET['i'])){
    $stack = new Stack($_GET['i']);
    if(isset($stack->id)){ //Dont contiune if the id provided is not valid
        $title = 'You\'ve been invited to play in a stack!';
        $content = '<p>The stack will play '.$stack->gamemode.'. The following players have already joined the stack:</p>'
                    .$stack->listPlayers().'<p>The stack will play on <span id="timeForStack'.$stack->id.'"></span> (that\'s <span id="timeRemainingForStack'.$stack->id.'"></span>!). Please, if you can\'t play (or you\'re not sure if you\'ll be able) at that time, refrain from joining the stack.</p>
                    <script type="text/javascript">
                        var stacktime = moment(\''.$stack->time.'\', \'X\');
                        $(\'#timeForStack'.$stack->id.'\').html(stacktime.format(\'LLLL\'));
                        $(\'#timeRemainingForStack'.$stack->id.'\').html(stacktime.fromNow());
                    </script>';

        if(!isset($loggedUser)) $steamlogin = steamlogin(array('joinInvitation' => $_GET['i']));
        $modalButtons = isset($steamlogin) ? $steamlogin : '//TODO';
        $modal = new Modal('invitationToStack', $title, $content, $modalButtons);
        echo $modal->getModal(true);
    }
}

//Prepare the rules modal
$rulesModal = new Modal('rulesModal', 'NotA Stacks\' rules', $GLOBAL_CONFIG['rules']);
echo $rulesModal->getModal();
?>
<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <div class="navbar-brand">NotA Stacks<span style="vertical-align: sub; font-size:60%;">ßeta</span> <?php if($GLOBAL_CONFIG['DEV_MODE']){ echo '<span class="label label-danger" data-show="tooltip" data-placement="bottom" title="The Dev Mode is currently on. It enables serious security issues that are useful for testing purposes but must be disabled for general usage. So if you\'re an end user and are seeing this, it means that the developer is a careless fuck. Tell him to disable this ASAP.">DEV MODE</span>';} ?></div>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
            <ul class="nav navbar-nav navbar-right">
                <li <?php if(ACTIVE_PAGE === 'index') echo 'class="active"' ?>><a href="index.php">Home</a></li>
                <li><a id="showRules" style="cursor: pointer; cursor: hand;">Rules</a></li>
                <?php if(isset($loggedUser)): ?>
                <li <?php if(ACTIVE_PAGE === 'profile') echo 'class="active"' ?> style="margin-top: -9px"><a href="profile.php"><img src="<?php echo $loggedUser->avatar ?>" width="32" height="32" /></a></li>
                <li><a id="logout" style="cursor: pointer; cursor: hand;"><span class="glyphicon glyphicon-off" aria-hidden="true"></span></a></li>
                <script type="text/javascript">
                $(document).ready(function(){
                    $('#logout').click(function(){
                        var confirmDialog = confirm('Are you sure you want to log out?');
                        if(confirmDialog){
                            window.location.replace('steamauth/logout.php');
                        }
                    });

                    $('#showRules').click(function(){
                        $('#rulesModal').modal();
                    });
                });
                </script>
                <?php endif; ?>
            </ul>
        </div>
        <!--/.nav-collapse -->
    </div>
</nav>

<?php
//Footer
define('FOOTER',
'<div id="footer" class="navbar navbar-default navbar-fixed-bottom">
    <div class="container">
        <p class="navbar-text">Created by '.$GLOBAL_CONFIG['owner'].' for <a href="http://reddit.com/r/noobsoftheancient" target="_blank">/r/NoobsOfTheAncient</a>. Version '.$GLOBAL_CONFIG['version'].'. <a href="https://github.com/MeLlamoPablo/NotaStacks" target="_blank">Source code</a>.</p>
    </div>
</div>');
?>