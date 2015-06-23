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
	switch($version){
		case '0':
			return array(
					'title' => 'Hello! Welcome to NotA Stacks!',
					'content' => '<p>Please, take a moment to read our rules.</p>
					<div class="alert alert-warning" role="alert">By continuing to use NotA Stacks, you are agreeing to the following rules.</div>
					<p>NotA (Noobs of the Ancient) is a nice and friendly community, and we want to keep it that way, so our first and most important rule is:</p>
					<h3>1.- You are not allowed to flame anyone</h3>
					<p>Even if your teammate is rushing Radiance on Witch Doctor, please, do not flame him. If you feel like your teammates aren\'t doing well, instead of flamming, try to politely explain what you think they are doing bad so that they can improve their play. If you can\'t stand noobs and can\'t help raging, then we\'re afraid that you shouldn\'t join this community. However, if that\'s the case, we encourage you to change your attitude, there\'s nothing like playing Dota with friendly people!
					<h4>2.- Try not to miss the play time</h4>
					<p>In order for this project to work, we need commitment. It\'d be a dissapointment if someone gets a 5 stack and ends up playing with only one person. Try to be there when the play time arrives, and if you can\'t, leave the stack. If you don\'t know for sure if you will be up for playing at the stack\'s time, please do not join it.</p>
					<h4>3.- We reserve the right to terminate your account at any time</h4>
					<p>That sounds scary, but it just means that we can ban you if you don\'t follow the rules. Though, that\'s the last thing we wanna do.
					<p>We hope you enjoy your time in NotA. Thanks for reading, and GLHF!</p>'
					);
		
		default:
			return FALSE;
	}
}
?>