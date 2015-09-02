<?php
//Config
require_once '../config.php';
//Steamauth requires
require_once '../steamauth/steamauth.php';
require_once '../steamauth/userInfo.php';
//DB requires
require_once '../connect.php';
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
	if(!isset($_GET['id'])) die('You need to log in to see your own profile.<meta http-equiv="refresh" content="3; url=/notastacks/" />');
	$loggedUser = NULL;
}

//Get the var for the user profile and check if he has set his profile
$output['userProfile'] = (isset($_GET['id'])) ? new User('db', $_GET['id']) : $loggedUser;
$r = $mysqli->query("SELECT profile_set FROM users WHERE id = ".$output['userProfile']->id);
$r = $r->fetch_assoc();

$hasSetProfile = ($r['profile_set'] === 'TRUE') ? TRUE : FALSE;
if($hasSetProfile){
	$r = $mysqli->query("SELECT position, adjective FROM users WHERE id = ".$output['userProfile']->id);
	$output['profile'] = $r->fetch_assoc();
}

//"Login as" feature for testing purposes. It is only allowed with the DEV_MODE enabled, wich mustn't be in production.
if($GLOBAL_CONFIG['DEV_MODE'] AND isset($_GET['instaCommend'])){
	$output['userProfile']->commend();
}

//Initialise the level manager
$output['userLevel'] = new LevelManager($output['userProfile']->commends * 100); //Each commend equals to 100 exp

//If the user has commended another user
if(isset($_POST['commendSubmit'])){
	$commendedUser = new User('db', $_POST['toUser']);
	if(!isset($_POST['commendMessage'])) die();
	$message = htmlentities($mysqli->real_escape_string($_POST['commendMessage']));
	$mysqli->query("INSERT INTO commends (`from`, `to`, `message`, `time`) VALUES ('".$loggedUser->id."', '".$commendedUser->id."', '".$message."', '".time()."')");
	$mysqli->query("UPDATE users SET commends = commends + 1 WHERE id = ".$commendedUser->id);
	//Re-initialise the $output['userLevel'] var
	$output['userLevel'] = new LevelManager(($output['userProfile']->commends + 1) * 100);
}

//If the user has created/edited his profile
if(isset($_POST['editProfileSubmit'])){
	if(isset($_POST['favRole'])){
		$favRole_number = str_replace("role_", "", $_POST['favRole']);
		$favRole = $GLOBAL_CONFIG['roles'][$favRole_number];
	}else{
		$favRole = NULL;
	}
	
	if(isset($_POST['adjective']) AND $_POST['adjective'] !== 'null'){
		$i = str_replace("adj_", "", $_POST['adjective']);
		//Check if the user has enough level to use the adjective
		if($GLOBAL_CONFIG['adjectives'][$i]['level'] <= $output['userLevel']->getCurrentLevel()){
			$adjective = $GLOBAL_CONFIG['adjectives'][$i]['adjective'];
		}else{
			die('An error has occurred while trying to proccess your adjective. If you didn\'t try to inject code, please contact the admins. If you did, fuck you. To workaround this, don\'t select any adjective for now. We apologize for the inconveniences caused.<meta http-equiv="refresh" content="3; url=/notastacks/profiles/me" />');
		}
	}else{
		$adjective = NULL;
	}

	$mysqli->query("UPDATE users SET `profile_set` = 'TRUE'".(!is_null($favRole) ? ", `position` = '".$favRole."'" : "").(!is_null($adjective) ? ", `adjective` = '".$adjective."'" : "")." WHERE `id` = ".$loggedUser->id);
	header('Location: /notastacks/profiles/me/');
	die();
}

//If the user has refreshed his data
if(isset($_GET['refresh']) AND $loggedUser !== NULL) $output['alert'] = ($loggedUser->refresh()) ? '<div class="alert alert-success" role="alert">Your information has been refreshed successfully.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>' : NULL;
?>