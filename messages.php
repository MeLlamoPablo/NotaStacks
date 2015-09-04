<?php

/*
	This script contains all the messages to be displayed to the user, depending on the porperty "lastmessage" on the DB.
	It handles the welcome/rules message and notifications for important updates.
	Once a message is displayed, the "lastmessage" field is updated with the current version, so that the message isn't displayed again.
	The file is used by the User class on classes.php

	The structure for adding a message is:
	case 'version':
		return array(
				'title' => 'title', //The message title, used for the modal title. Can use HTML but shouldn't.
				'content' => '<p>message</p>' //The message content. Can (and should) use HTML.
				'closeButton' => 'Close' //String to be displayed on the close buttion. Optional; default is "Close".
				);
*/

function getMessage($version){
	global $GLOBAL_CONFIG;
	switch($version){
		/*case '0':
			return array(
					'title' => 'Hello! Welcome to '.$GLOBAL_CONFIG['site_name'].'!',
					'content' => '<p>Please, take a moment to read our rules.</p>
					<div class="alert alert-warning" role="alert">By continuing to use '.$GLOBAL_CONFIG['site_name'].', you are agreeing to the following rules.</div>
					'.$GLOBAL_CONFIG['rules']);*/
		
		default:
			return FALSE;
	}
}
?>