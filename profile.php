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

//Get the var for the user profile and check if he has set his profile
$userProfile = (isset($_GET['id'])) ? new User('db', $_GET['id']) : $loggedUser;
$r = $mysqli->query("SELECT profile_set FROM users WHERE id = ".$userProfile->id);
$r = $r->fetch_assoc();
$hasSetProfile = ($r['profile_set'] === 'TRUE') ? TRUE : FALSE;
if($hasSetProfile){
	$r = $mysqli->query("SELECT position, adjective, servers, pref_server FROM users WHERE id = ".$userProfile->id);
	$profile = $r->fetch_assoc();
}

//"Login as" feature for testing purposes. It is only allowed with the DEV_MODE enabled, wich mustn't be in production.
if($GLOBAL_CONFIG['DEV_MODE'] AND isset($_GET['instaCommend'])){
	$userProfile->commend();
}

//Initialise the level manager
$userLevel = new LevelManager($userProfile->commends * 100); //Each commend equals to 100 exp

//If the user has created/edited his profile
if(isset($_POST['editProfileSubmit'])){
	if(isset($_POST['favPosition'])){
		switch ($_POST['favPosition']) {
			case 'position_carry':
				$favPosition = 'Safelane Carry';
				break;
			case 'position_mid':
				$favPosition = 'Midlaner';
				break;
			case 'position_off':
				$favPosition = 'Offlaner';
				break;
			case 'position_supp':
				$favPosition = 'Support';
				break;
			case 'position_hsupp':
				$favPosition = 'Hard Support';
				break;
			default:
				$favPosition = NULL;
				break;
		}
	}else{
		$favPosition = NULL;
	}
	
	if(isset($_POST['adjective']) AND $_POST['adjective'] !== 'null'){
		$i = str_replace("adj_", "", $_POST['adjective']);
		//Check if the user has enough level to use the adjective
		if($GLOBAL_CONFIG['adjectives'][$i]['level'] <= $userLevel->getCurrentLevel()){
			$adjective = $GLOBAL_CONFIG['adjectives'][$i]['adjective'];
		}else{
			die('An error has occurred while trying to proccess your adjective. If you didn\'t try to inject code, please contact the admins. If you did, fuck you. To workaround this, don\'t select any adjective for now. We apologize for the inconveniences caused.<meta http-equiv="refresh" content="3; url=profile.php" />');
		}
	}else{
		$adjective = NULL;
	}

	for($i=0; isset($GLOBAL_CONFIG['servers'][$i]); $i++){
		if(isset($_POST['server_'.$GLOBAL_CONFIG['servers'][$i]]))
			$servers[$i] = str_replace('server_', '', $_POST['server_'.$GLOBAL_CONFIG['servers'][$i]]);
			//echo $servers[$i];
		if(isset($servers[$i]) AND !in_array($servers[$i], $GLOBAL_CONFIG['servers']))
			die('An error has occured while trying to proccess your servers. If you didn\'t try to inject code, please contact the admins. If you did, fuck you. To workaround this, don\'t select any servers for now. We apologize for the inconveniences caused.<meta http-equiv="refresh" content="3; url=profile.php" />');
	}
	$servers = isset($servers) ? implode('-', $servers) : NULL;

	if(isset($_POST['favServer'])){
		if(in_array($_POST['favServer'], $GLOBAL_CONFIG['servers'])){
			$favServer = $_POST['favServer'];
		}else{
			$favServer = NULL;
		}
	}else{
		$favServer = NULL;
	}

	/*$mysqli->query*/die("UPDATE users SET `profile_set` = 'TRUE'".(!is_null($favPosition) ? ", `position` = '".$favPosition."'" : "").(!is_null($adjective) ? ", `adjective` = '".$adjective."'" : "").(!is_null($servers) ? ", `servers` = '".$servers."'" : "").(!is_null($favServer) ? ", `pref_server` = '".$favServer."'" : "")." WHERE `id` = ".$loggedUser->id);
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
						<label class="position btn btn-default" data-value="position_carry"><input type="radio" id="position_carry">Safelane Carry</label>
						<label class="position btn btn-default" data-value="position_mid"><input type="radio" id="position_mid">Midlaner</label>
						<label class="position btn btn-default" data-value="position_off"><input type="radio" id="position_off">Offlaner</label>
						<label class="position btn btn-default" data-value="position_supp"><input type="radio" id="position_supp">Support</label>
						<label class="position btn btn-default" data-value="position_hsupp"><input type="radio" id="position_hsupp">Hard Support</label>
						<input type="hidden" id="favPosition" name="favPosition">
					</div></div>
					<script type="text/javascript">
					$(".position").click(function(){
						$("#favPosition").val($(this).attr("data-value"));
					});
					</script>

					<h4>Adjective</h4>
					<div>Adjectives are unlocked by leveling up. Be nice and friendly to your teammates, and they may commend you. You can chose to display an adjective before your position.</div>
					<select name="adjective" class="chosen-select" data-placeholder="Chose an adjective" style="width:350px;">
						<option value="null"></option>';
						for($i=0; isset($GLOBAL_CONFIG['adjectives'][$i]); $i++){ 
							if($GLOBAL_CONFIG['adjectives'][$i]['level'] <= $userLevel->getCurrentLevel())
								$modalContent .= '<option value="adj_'.$i.'">'.$GLOBAL_CONFIG['adjectives'][$i]['adjective'].'</option>';
						}
					$modalContent .= '</select>

					<h4>Servers</h4>
					<div>Select the servers on wich you are available to play</div>';
					for($i=0; isset($GLOBAL_CONFIG['servers'][$i]); $i++){ 
                        $modalContent .= '<label class="checkbox-inline">';
                            $modalContent .= '<input type="checkbox" id="'.$GLOBAL_CONFIG['servers'][$i].'check" name="server_'.$GLOBAL_CONFIG['servers'][$i].'" value="server_'.$GLOBAL_CONFIG['servers'][$i].'"> '.$GLOBAL_CONFIG['servers'][$i];
                        $modalContent .= '</label>';
                    }
                    $modalContent .= '<div>Select your preferred server</div>
                    <div class="btn-toolbar"><div class="btn-group" data-toggle="buttons">';
                    for($i=0; isset($GLOBAL_CONFIG['servers'][$i]); $i++){ 
                         $modalContent .= '<label class="server btn btn-default" data-value="'.$GLOBAL_CONFIG['servers'][$i].'"><input type="radio" id="prefserver_'.$GLOBAL_CONFIG['servers'][$i].'">'.$GLOBAL_CONFIG['servers'][$i].'</label>';
                    }
                    $modalContent .= '</div></div>
                    <input type="hidden" id="favServer" name="favServer">
                    <script type="text/javascript">
					$(".server").click(function(){
						$("#favServer").val($(this).attr("data-value"));
					});
					</script>
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
						echo '<h3>'.(isset($profile['adjective']) ? $profile['adjective'] : 'Level '.$userLevel->getCurrentLevel()).(isset($profile['position']) ? ' '.$profile['position'] : '').'</h3>';
						echo '<ul>';
							if(isset($profile['servers'])){
								echo '<li>This user can play on ';
								$servers = explode('-', $profile['servers']);
								$totalServers = count($servers);
								foreach($servers as $key => $value){
									echo $value;
									//If there's more than one server, we'll need to add commas (",") and "or"
                                    if($totalServers > 1){
                                        //Add a comma on every server but the last one
                                        if(($key+1) !== $totalServers) echo ', ';
                                        //Add "or" before the last one
                                        if(($key+2) === $totalServers) echo 'and ';

                                    }
								}
								echo '.</li>';
							}
							if(isset($profile['pref_server'])){
								echo '<li>He prefers to play on '.$profile['pref_server'].'.</li>';
							}
								echo '<li>Here\'s a <a href="'.$userProfile->getUrl().'" target="_blank">link to his Steam profile</a>.</li>';
						echo '</ul>';
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