<div class="inner">
    <h3 class="masthead-brand"></h3>
    <nav>
        <ul class="nav masthead-nav">
            <li class="active"><a href="#">Home</a></li>
            
        </ul>
    </nav>
</div>
<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <div class="navbar-brand">NotA Stacks<span style="vertical-align: sub; font-size:60%;">ßeta</span></div>
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
        <p class="navbar-text">Created by Pablo Rodríguez (<a href="http://steamcommunity.com/id/MeLlamoPablo" target="_blank">MeLlamoPablo</a> a.k.a. <a href="http://www.reddit.com/user/sfcpfc" target="_blank">/u/sfcpfc</a>) for <a href="http://reddit.com/r/noobsoftheancient" target="_blank">/r/NoobsOfTheAncient</a>.</p>
    </div>
</div>');
?>