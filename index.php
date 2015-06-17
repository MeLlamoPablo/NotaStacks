<?php
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
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>NotA Stacks</title>

    <!-- Meta tags -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->

    <!-- Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <!--<link href="css/custom.css" rel="stylesheet">-->
</head>
<body>
    <div class="container">

    <?php include 'menu.php'; ?>

    <?php
    //If the user hasn't signed in, we show the welcome message.
    if(!isset($loggedUser)){
        echo '<div class="jumbotron" style="margin-top: -15px">';
            echo '<h1>Find stacks. Get rampages.</h1>';
            echo '<p>NotA Stacks is a tool that can match you with friendly players. Unorganized games and unwanted teammates are a thing of the past.</p>';
            echo '<p>';
                steamlogin();
            echo '<p>';
        echo '</div>';
    }else{
        echo 'You\'re '.$loggedUser->name;
        echo '<br><img src="'.$loggedUser->avatar.'" />';
        echo '<br><a href="steamauth/logout.php">Logout</a>';
    }
    ?>

    <?php echo FOOTER //This is defined in menu.php ?>
    </div>

    <!-- jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="js/bootstrap.min.js"></script>
</body>
</html>