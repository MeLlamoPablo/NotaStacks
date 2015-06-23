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
                <li class="active"><a href="#">Home</a></li>
                <?php if(isset($loggedUser)): ?>
                <li style="margin-top: -9px"><a href="#"><img src="<?php echo $loggedUser->avatar ?>" width="32" height="32" /></a></li>
                <li><a href="steamauth/logout.php"><span class="glyphicon glyphicon-off" aria-hidden="true"></span></a></li>
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
        <p class="navbar-text">Created by '.$GLOBAL_CONFIG['owner'].' for <a href="http://reddit.com/r/noobsoftheancient" target="_blank">/r/NoobsOfTheAncient</a>. Version '.$GLOBAL_CONFIG['version'].'.</p>
    </div>
</div>');
?>