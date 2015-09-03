<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <div class="navbar-brand"><?php echo $GLOBAL_CONFIG['site_name'] ?><span style="vertical-align: sub; font-size:60%;">ßeta</span> <?php if($GLOBAL_CONFIG['DEV_MODE']){ echo '<span class="label label-danger" data-show="tooltip" data-placement="bottom" title="The Dev Mode is currently on. It enables serious security issues that are useful for testing purposes but must be disabled for general usage. So if you\'re an end user and are seeing this, it means that the developer is a careless fuck. Tell him to disable this ASAP.">DEV MODE</span>';} ?></div>
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
                            window.location.replace('/notastacks/logout');
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

<?php

//Prepare the login modal
$loginModal['content'] = '
<div class="input-group">
    <span style="width: 95px;" class="input-group-addon" id="usernameLabel">Username</span>
    <input type="text" class="form-control" id="username" name="username" aria-describedby="usernameLabel" required="required">
</div>
<div class="input-group">
    <span style="width: 95px;" class="input-group-addon" id="passLabel">Password</span>
    <input type="password" class="form-control" id="pass" name="pass" aria-describedby="passLabel" required="required">
</div>'
.(($GLOBAL_CONFIG['ReCaptcha']['enabled']) ? '<br><div id="captcha1"></div>' : '');
$loginModal['buttons'] = '
<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
<button type="submit" name="login_submit" class="btn btn-primary">Log In</button>
';
$loginModal['callButton'] = array(
    'content' => 'Log in',
    'attributes' => 'type="button" class="btn btn-primary btn-lg"'
    );
$loginModal['formAttributes'] = '
method="post" action="http://'.$_SERVER['HTTP_HOST'].'/notastacks/"
';
$loginModal = new Modal('loginModal', 'Log in', $loginModal['content'], $loginModal['buttons'], $loginModal['callButton'], $loginModal['formAttributes'], 'small');
echo $loginModal->getModal();

//Prepare the register modal
$registerModal['content'] = '
<div class="alert alert-info alert-smallmargin" role="alert">You need to enter your Reddit username so that we can reset your password if you forget it (we can\'t use emails for that purpose, unafortunately).</div>
<div class="input-group">
    <span class="input-group-addon" id="usernameLabel">Reddit Username</span>
    <input type="text" class="form-control" id="username" name="username" aria-describedby="usernameLabel" required="required">
</div>
<br>
<div class="alert alert-info alert-smallmargin" role="alert">This doesn\'t need to be your Reddit password, but it can be. However, we do not recommend using the same password for multiple sites.</div>
<div class="input-group">
    <span class="input-group-addon" id="passLabel">Password</span>
    <input type="password" class="form-control" id="pass" name="pass" aria-describedby="passLabel" required="required">
</div>'
.(($GLOBAL_CONFIG['ReCaptcha']['enabled']) ? '<br><div id="captcha2"></div>' : '');
//.(($GLOBAL_CONFIG['ReCaptcha']['enabled']) ? '<br><div class="g-recaptcha" data-sitekey="'.$GLOBAL_CONFIG['ReCaptcha']['site_key'].'"></div>' : '');
$registerModal['buttons'] = '
<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
<button type="submit" name="register_submit" class="btn btn-primary">Sign Up</button>
';
$registerModal['callButton'] = array(
    'content' => 'Sign Up',
    'attributes' => 'type="button" class="btn btn-default btn-lg"'
    );
$registerModal['formAttributes'] = '
method="post" action="http://'.$_SERVER['HTTP_HOST'].'/notastacks/"
';
$registerModal = new Modal('registerModal', 'Sign Up!', $registerModal['content'], $registerModal['buttons'], $registerModal['callButton'], $registerModal['formAttributes'], 'normal');
echo $registerModal->getModal();

//Render the captchas
if($GLOBAL_CONFIG['ReCaptcha']['enabled']): ?>
    <script type="text/javascript">
        var onloadCallback = function() {
            grecaptcha.render('captcha1', {
                'sitekey' : '<?php echo $GLOBAL_CONFIG['ReCaptcha']['site_key']; ?>'
            });
            grecaptcha.render('captcha2', {
                'sitekey' : '<?php echo $GLOBAL_CONFIG['ReCaptcha']['site_key']; ?>'
            });
        };
    </script>
<?php endif; ?>