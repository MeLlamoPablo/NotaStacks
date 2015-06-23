<?php

//Before connecting the database to the app, make sure you follow the steps on db_setup.sql

//Database config
$db_host		= '';
$db_user		= '';
$db_pass		= '';
$db_database	= ''; 

$mysqli = new mysqli($db_host,$db_user,$db_pass,$db_database);
if ($mysqli->connect_errno) {
    die("Couldn't connect to the database. Please, let /u/sfcpfc know the following error:<br>Error: " . $mysqli->connect_error);
}
$mysqli->query("SET NAMES utf8");

?>
