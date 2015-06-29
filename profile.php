<?php
define('ACTIVE_PAGE', 'profile');
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

}else{
	if(!isset($_GET['id'])) die('You need to log in to see your own profile.<meta http-equiv="refresh" content="3; url=index.php" />');
	$loggedUser = NULL;
}

//Get the var for the user profile
$userProfile = (isset($_GET['id'])) ? new User('db', $_GET['id']) : $loggedUser;
$hasSetProfile = FALSE;

//If the user has created/edited his profile
if(isset($_POST['editProfileSubmit'])){
	die(print_r($_POST));
}

//If the user has refreshed his data
if(isset($_GET['refresh']) AND $loggedUser !== NULL) $alert = ($loggedUser->refresh()) ? '<div class="alert alert-success" role="alert">Your information has been refreshed successfully.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>' : NULL;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title><?php echo $userProfile->name.((substr($userProfile->name, -1) === 's') ? '\'' : '\'s') ?> profile - NotA Stacks</title>

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
</head>
<body>
	<div class="container" id="wrap">
		<?php include 'menu.php';
		echo

		'<div class="jumbotron" style="margin-top: -15px">';
			if(isset($alert)) echo $alert;
			if($loggedUser !== NULL){
				echo '<div class="row well well-sm container btn-group">';
				if($loggedUser->id === $userProfile->id){
					echo '<button data-toggle="modal" data-target="#editProfileModal" id="editProfileButton" class="btn '.($hasSetProfile ? 'btn-default">Edit profile</button>' : 'btn-success">Create profile</button>').
					'<button '.(($loggedUser->timeSinceLastRefresh() > $GLOBAL_CONFIG['refreshWaitTime']) ? 'onclick="window.location.replace(\'profile.php?refresh\')" ' : '').'id="refreshButton" class="btn btn-primary'.(($loggedUser->timeSinceLastRefresh() < $GLOBAL_CONFIG['refreshWaitTime']) ? ' disabled' : '' ).'" data-show="tooltip" data-placement="bottom" title="Doing this will download your new avatar and name from Steam to the server. For performance reasons, you can only do this every 24 hours.">Refresh data from Steam</button>';
					$modalContent = '

					'.(!$hasSetProfile ? '<div>Creating a NotA Stacks profile allows you to give information about your playstile, your likes, your availability, etc. You do not have to fill every field, but it\'s encouraged to give as much information as possible to find the best match.</div>' : '').'

					<h4>Favourite position</h4>
					<div class="btn-toolbar"><div class="btn-group" data-toggle="buttons">
						<label class="btn btn-default"><input type="radio" name="position_supp" id="position_supp">Safelane Carry</label>
						<label class="btn btn-default"><input type="radio" name="position_mid" id="position_mid">Midlaner</label>
						<label class="btn btn-default"><input type="radio" name="position_off" id="position_off">Offlaner</label>
						<label class="btn btn-default"><input type="radio" name="position_supp" id="position_supp">Support</label>
					</div></div>

					<h4>Adjective</h4>
					<div>Adjectives are unlocked by earning commends. Be nice and friendly to your teammates, and they may commend you. You can chose to display an adjective before your position.</div>
					<div> //TODO select
					</div>

					';
					$modalButtons = '<button type="button" class="btn btn-danger" data-dismiss="modal">Discard changes</button>
					<button type="submit" name="editProfileSubmit" class="btn btn-'.($hasSetProfile ? 'primary' : 'success').'">Save changes</button>';
					$editProfileModal = new Modal('editProfileModal', $hasSetProfile ? 'Edit my profile' : 'Create a NotA Stacks profile', $modalContent, $modalButtons, NULL, 'method="post" action"profile.php"');
					echo $editProfileModal->getModal();
				}else{
					echo '<button id="commendButton" class="btn btn-success" data-show="tooltip" data-placement="bottom" title="Commend this player"><span class="glyphicon glyphicon-thumbs-up" aria-hidden="true"></span></button>';
					echo '<button id="reportButton" class="btn btn-danger" data-show="tooltip" data-placement="bottom" title="Report this player"><span class="glyphicon glyphicon-flag" aria-hidden="true"></span></button>';
				}
				echo '</div>';
			}
			echo '<div class="row">
				<div class="col-sm-3" style="text-align: right;">
					<img src="'.$userProfile->avatar.'" class="img-responsive img-thumbnail" />
				</div>
				<div clas="col-sm-9">
					<h1>'.$userProfile->name.((substr($userProfile->name, -1) === 's') ? '\'' : '\'s').' profile</h1>';
					if($hasSetProfile){
						//TODO
					}else{
						if(($loggedUser !== NULL) AND ($loggedUser->id === $userProfile->id)){
							echo '<p>You haven\'t configured your NotA Stacks profile yet.<br>
							Click the button above to do it.';
						}else{
							echo '<p>This user hasn\'t configured his NotA Stacks profile yet.<br>
							Click <a href="'.$userProfile->getUrl().'" target="_blank">here</a> to go to his Steam profile.';
						}
					}
				echo '</div>
			</div>
		</div>'

		;
		echo FOOTER //This is defined in menu.php ?>
	</div>
	<script type="text/javascript">
	$(document).ready(function(){
		//Tooltip opt-in
        $(function () {
            $('[data-show="tooltip"]').tooltip()
            //We use "data-show" instead of "data-toggle" so a button can have a tooltip and trigger a modal at once
        })
	});
	</script>
</body>