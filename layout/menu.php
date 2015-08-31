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
                <li <?php if(ACTIVE_PAGE === 'index') echo 'class="active"' ?>><a href="/notastacks/">Home</a></li>
                <li><a id="showRules" style="cursor: pointer; cursor: hand;">Rules</a></li>
                <?php if(isset($loggedUser)): ?>
                <li <?php if(ACTIVE_PAGE === 'profile') echo 'class="active"' ?> style="margin-top: -9px"><a href="/notastacks/profiles/"><img src="<?php echo $loggedUser->avatar ?>" width="32" height="32" /></a></li>
                <li><a id="logout" style="cursor: pointer; cursor: hand;"><span class="glyphicon glyphicon-off" aria-hidden="true"></span></a></li>
                <script type="text/javascript">
                $(document).ready(function(){
                    $('#logout').click(function(){
                        var confirmDialog = confirm('Are you sure you want to log out?');
                        if(confirmDialog){
                            window.location.replace('/notastacks/steamauth/logout.php');
                        }
                    });
                });
                </script>
                <?php endif; ?>
                <script type="text/javascript">
                $(document).ready(function(){
                    $('#showRules').click(function(){
                        $('#rulesModal').modal();
                    });
                });
                </script>
            </ul>
        </div>
        <!--/.nav-collapse -->
    </div>
</nav>