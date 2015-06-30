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

//"Login as" feature for testing purposes. It is only allowed with the DEV_MODE enabled, wich mustn't be in production.
if($GLOBAL_CONFIG['DEV_MODE'] AND isset($_GET['instaCommend'])){
	$userProfile->commend();
}

//Initialise the level manager
$userLevel = new LevelManager($userProfile->commends * 100); //Each commend equals to 100 exp

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
    <?php require_once 'head.php' ?>
</head>
<body>
	<div class="container" id="wrap">
		<?php include 'menu.php';
		echo

		'<div class="jumbotron" style="margin-top: -15px">';
			if(isset($alert)) echo $alert;
			//Buttons
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
					<div>Adjectives are unlocked by leveling up. Be nice and friendly to your teammates, and they may commend you. You can chose to display an adjective before your position.</div>
					<select class="chosen-select" data-placeholder="Chose an adjective" style="width:350px;">
						<option value="null"></option>';
						for($i=0; isset($GLOBAL_CONFIG['adjectives'][$i]); $i++){ 
							if($GLOBAL_CONFIG['adjectives'][$i]['level'] <= $userLevel->getCurrentLevel())
								$modalContent .= '<option value="adj_'.$i.'">'.$GLOBAL_CONFIG['adjectives'][$i]['adjective'].'</option>';
						}
					$modalContent .= '</select>
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
			//Profile
			echo '<div class="row">';
				//Level system
				echo '<div class="progress"><div class="progress-bar progress-bar-success" aria-valuemin="'.$userLevel->getNeededExpForCurrentLevel().'" aria-valuemax="'.$userLevel->getNeededExpForNextLevel().'" data-transitiongoal="'.$userLevel->exp.'">	
				</div></div>
				<script type="text/javascript">
					$(".progress .progress-bar").progressbar({display_text: "center", use_percentage: false, amount_format: function(p, t) {return "Level '.$userLevel->getCurrentLevel().': "+ '.($userLevel->getRemainingExpForNextLevel()/100).' +" commend'.((($userLevel->getRemainingExpForNextLevel()/100) != 1) ? 's' : '').' to go.";}});
				</script>';
				echo '<div class="col-sm-3" style="text-align: right;">
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
        });

        //Chosen
        $(".chosen-select").chosen({});
	});
	</script>
</body>