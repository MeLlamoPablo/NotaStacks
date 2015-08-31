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

//If the URL is "ugly" (happens after someone logs in), refresh the page
if($_SERVER['REQUEST_URI'] === '/notastacks/layout/index.php'){
    header('Location: /notastacks/');
    die();
}

//Site info
$output['site_name'] = $GLOBAL_CONFIG['site_name'];
$output['servers'] = $GLOBAL_CONFIG['servers'];

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

}

//"Login as" feature for testing purposes. It is only allowed with the DEV_MODE enabled, wich mustn't be in production.
if($GLOBAL_CONFIG['DEV_MODE'] AND isset($_GET['loginas'])){
    $loggedUser = new User('db', $_GET['loginas']);
    $_SESSION['steamid'] = $loggedUser->steamid;
    die('<meta http-equiv="refresh" content="0; url=/notastacks/" />');
}

//If the user has created a stack, handle it.
if(isset($_POST['createStackButton'])){
    if(!isset($_POST['gamemode'])) die('The gamemode information was not sent correctly. You shouldn\'t be seeing this error, though. Blame /u/sfcpfc for his incompetence. Or maybe upgrade to a browser that supports HTML5, you lazy.');
    $gamemode = $mysqli->real_escape_string($_POST['gamemode']);

    if(!isset($_POST['timePicker'])) die('The time information was not sent correctly. You shouldn\'t be seeing this error, though. Blame /u/sfcpfc for his incompetence. Or maybe upgrade to a browser that supports HTML5, you lazy.');
    $time = $mysqli->real_escape_string($_POST['timePicker']);

    if(!isset($_POST['stackType'])) die('The stack type information was not sent correctly. You shouldn\'t be seeing this error, though. Blame /u/sfcpfc for his incompetence. Or maybe upgrade to a browser that supports HTML5, you lazy.');

    if($_POST['stackType'] == 5 OR $_POST['stackType'] == 10){
        $stackType = $mysqli->real_escape_string($_POST['stackType']);
    }else{
        $stackType = 5;
    }

    if($time < time()) die('You can\'t set a stack for the past');

    $servers['string'] = '';
    for($i=0; isset($GLOBAL_CONFIG['servers'][$i]); $i++){ 
        if(isset($_POST[$GLOBAL_CONFIG['servers'][$i]])){
            $servers[$GLOBAL_CONFIG['servers'][$i]] = TRUE;
            $servers['string'] .= '-'.$GLOBAL_CONFIG['servers'][$i];
        }else{
            $servers[$GLOBAL_CONFIG['servers'][$i]] = FALSE;
        }
    }
    $servers['string'] = substr($servers['string'], 1);

    //TODO there might be a better way of doing this
    function atLeastOneOfTheServersIsTrue($servers){
        global $GLOBAL_CONFIG;
        for($i=0; isset($GLOBAL_CONFIG['servers'][$i]); $i++){ 
           if($servers[$GLOBAL_CONFIG['servers'][$i]] === TRUE) return TRUE;
        }
        return FALSE;
    }

    //If the user hasn't selected any server, stop the handle and output him an error.
    //Else, continue with the handle
    if(!atLeastOneOfTheServersIsTrue($servers)){
        $output['error'] = 'noServerSelected';
    }else{
        $createdStack = new Stack('provided', array(
            'players' => array($loggedUser),
            'maxplayers' => $stackType,
            'gamemode' => $gamemode,
            'time' => $time,
            'ownerid' => $loggedUser->id,
            'server' => $servers['string'])
        );
    }

}

//If the player has joined a stack
if(isset($loggedUser) AND (isset($joinStack) OR isset($_GET['joinStack']))){
    if(isset($_GET['joinStack'])) $joinStack = $_GET['joinStack'];
    $stack = new Stack($joinStack);
    //If the stack is not full
    if(!isset($stack->players) OR (count($stack->players) === $stack->maxplayers)) die('The stack is full<meta http-equiv="refresh" content="3; url=/notastacks/" />');

    //Prevent the user for joining a stack where he already is
    if(in_array($loggedUser, $stack->players)) die('You\'ve already joined this stack.<meta http-equiv="refresh" content="3; url=/notastacks/" />');

    //Add the player
    $stack->addPlayer($loggedUser);
    die('<meta http-equiv="refresh" content="0; url=/notastacks/" />'); //We redirect the user so that we get rid of ?joinStack, thus, the user can refresh without being prompted an error.

}elseif(!isset($loggedUser) AND (isset($joinStack) OR isset($_GET['joinStack']))){
    //If the user attempted to join a stack, but he wasn't logged in, that means he followed an invitation link
    //Force him to log in.
    $_GET['login'] = TRUE;
    steamlogin();
    die();
}

//If the player has left a stack
if(isset($loggedUser) AND (isset($leaveStack) OR isset($_GET['leaveStack']))){
    if(isset($_GET['leaveStack'])) $leaveStack = $_GET['leaveStack'];
    $stack = new Stack($_GET['leaveStack']);
    $stack->removePlayer($loggedUser);
    die('<meta http-equiv="refresh" content="0; url=/notastacks/" />'); //We redirect the user so that we get rid of ?leaveStack, thus, the user can refresh without being prompted an error.

}

if(!isset($output['error'])) $output['error'] = 'none';

//Ban hammer handle
if(isset($loggedUser) AND $loggedUser->ban !== '0'){
    if($loggedUser->ban === 'permanent'){
        $output['modals']['banHammer'] = array(
            'title' => 'You have been banned forever',
            'content' => 'Please contact '.$GLOBAL_CONFIG['owner'].' if you think this is a mistake.',
            'autocall' => TRUE
        );
        unset($loggedUser);
    }elseif($loggedUser->ban > time()){
        $content = '<p>You will be able to use this site again <span id="banExpire"></span>.<br>Please contact '.$GLOBAL_CONFIG['owner'].' if you think this is a mistake.</p>';
        $content .= "<script type=\"text/javascript\">
        var bantime = moment('".$loggedUser->ban."', 'X');
        $('#banExpire').html(bantime.fromNow());
        </script>";
        $output['modals']['banHammer'] = array(
            'title' => 'You have been temporarily banned',
            'content' => $content,
            'autocall' => TRUE
        );
        unset($loggedUser);
    }
}

//Invitation handle
if(isset($_GET['i'])){
    $stack = new Stack($_GET['i']);
    if(isset($stack->id)){ //Dont contiune if the id provided is not valid
        $content = "<p>The stack will play ".$stack->gamemode.". The following players have already joined the stack:</p>"
                    .$stack->listPlayers()."<p>The stack will play on <span id='timeForStack".$stack->id."'></span> (that's <span id='timeRemainingForStack".$stack->id."'></span>!). Please, if you can't play (or you're not sure if you'll be able) at that time, refrain from joining the stack.</p>";
        $content .= "<script type='text/javascript'>";
            $content .= "var stacktime = moment('".$stack->time."', 'X');";
            $content .= "$('#timeForStack".$stack->id."').html(stacktime.format('LLLL'));";
            $content .= "$('#timeRemainingForStack".$stack->id."').html(stacktime.fromNow());";
        $content .= "</script>";

        if(!isset($loggedUser)) $steamlogin = '<a href="/notastacks/layout/index.php?joinStack='.$_GET['i'].'"><img src="http://localhost/notastacks/steamauth/signinthroughsteam.png"></a>';
        $modalButtons = isset($steamlogin) ? $steamlogin : '//TODO';
        $output['modals']['invitationToStack'] = array(
            'title' => 'You\'ve been invited to play in a stack!',
            'content' => $content,
            'autocall' => TRUE,
            'buttons' => $modalButtons
        );
    }
}

//Prepare the rules modal
$output['modals']['rulesModal'] = array(
    'title' => $GLOBAL_CONFIG['site_name'].'\' rules',
    'content' => $GLOBAL_CONFIG['rules'],
    'autocall' => FALSE
);

if(!isset($loggedUser)){
    $output['user_logged_in'] = FALSE;
    //If the user hasn't signed in, we need the steam login button
    $output['steam_login_button'] = steamlogin();
    //$output['main_page'] = str_replace('%STEAM_LOGIN_BUTTON%', steamlogin(), $GLOBAL_CONFIG['welcome_message']);
}else{
    $output['user_logged_in'] = TRUE;
    //Get a list of all upcoming stacks
    $r = $mysqli->query("SELECT id FROM stacks WHERE time > ".(time() - (3600 * 24))); //We display upcoming stacks and also stacks that were already played up to one day ago. We'll only display these if the user belongs to the stack.

    $displayedStackCount = 0; //This is the number of stacks that will be displayed to the user, not the number of stacks proccessed ($i).

    for($i=1; $r2 = $r->fetch_assoc(); $i++){ //$i = 1; instead of $i = 0 because the row ID begins in 1
        $stacks[$i] = new Stack($r2['id']);
        //Does the user belongs to the stack?
        $userBelongsToStack = (in_array($loggedUser, $stacks[$i]->players) ? TRUE : FALSE);
        //If the user doens't belong to the stack and the stack time has passed, skip this iteration. However, if the user belongs to it, the stack won't disappear until one hour has passed.
        if(!$userBelongsToStack AND ($stacks[$i]->time < time())) continue;

        //Output all the stack information
        $displayedStackCount++;
        $output['stacks'][$displayedStackCount] = array(
            'id' => $stacks[$i]->id,
            'gamemode' => $stacks[$i]->gamemode,
            'time' => $stacks[$i]->time,
            'ownerid' => $stacks[$i]->ownerid,
            'servers' => explode('-', $stacks[$i]->server),
            'userBelongsToStack' => $userBelongsToStack,
            'playercount' => count($stacks[$i]->players),
            'maxplayers' => $stacks[$i]->maxplayers,
            'playerlist' => $stacks[$i]->listPlayers()
        );

        //Output players
        for($i2=0; $i2 < $output['stacks'][$displayedStackCount]['playercount']; $i2++){
            $output['stacks'][$displayedStackCount]['players'][$i2] = array(
                'id' => $stacks[$i]->players[$i2]->id,
                'name' => $stacks[$i]->players[$i2]->name,
                'avatar' => $stacks[$i]->players[$i2]->avatar
            );
        }
    }
}

//Serve the data as JSON. This is useful for cross-platform apps. This currently has no implementation.
if(FALSE){
    header('Content-Type: application/json');
    echo json_encode($output, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); 
}

?>