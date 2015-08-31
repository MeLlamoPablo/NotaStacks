<?php

//The site name
$GLOBAL_CONFIG['site_name'] = 'NotA Stacks';

//Dev mode. MUST set to FALSE before opening the product to the public.
$GLOBAL_CONFIG['DEV_MODE'] = TRUE;

//Current version of the script
$GLOBAL_CONFIG['version'] = '1.1';

//Owner of the script. If you forked it, please, either do not modify it or change the footer to credit the original author
$GLOBAL_CONFIG['owner'] = 'Pablo Rodr&iacute;guez (<a href="http://steamcommunity.com/id/MeLlamoPablo" target="_blank">MeLlamoPablo</a> a.k.a. <a href="http://www.reddit.com/user/sfcpfc" target="_blank">/u/sfcpfc</a>)';

//Supported servers. CANNOT contain "-"
$GLOBAL_CONFIG['servers'] = array('USE', 'EUW', 'EUE');

//Time that the user has to wait before he can refresh his data from Steam again, in seconds.
$GLOBAL_CONFIG['refreshWaitTime'] = 60 /*seconds*/ * 60 /*minutes*/ * 24 /*hours*/;

//Rules. Can use HTML.
$GLOBAL_CONFIG['rules'] = '

<p>NotA (Noobs of the Ancient) is a nice and friendly community, and we want to keep it that way, so our first and most important rule is:</p>
<h3>1.- You are not allowed to flame anyone</h3>
<p>Even if your teammate is rushing Radiance on Witch Doctor, please, do not flame him. If you feel like your teammates aren\'t doing well, instead of flamming, try to politely explain what you think they are doing bad so that they can improve their play. If you can\'t stand noobs and can\'t help raging, then we\'re afraid that you shouldn\'t join this community. However, if that\'s the case, we encourage you to change your attitude, there\'s nothing like playing Dota with friendly people!
<h4>2.- Try not to miss the play time</h4>
<p>In order for this project to work, we need commitment. It\'d be a dissapointment if someone gets a 5 stack and ends up playing with only one person. Try to be there when the play time arrives, and if you can\'t, leave the stack. If you don\'t know for sure if you will be up for playing at the stack\'s time, please do not join it.</p>
<h4>3.- We reserve the right to terminate your account at any time</h4>
<p>That sounds scary, but it just means that we can ban you if you don\'t follow the rules. Though, that\'s the last thing we wanna do.
<p>We hope you enjoy your time in NotA. Thanks for reading, and GLHF!</p>

';

//Welcome message. The message that is displayed to guests (non-logged users). Can use HTML.
//%STEAM_LOGIN_BUTTON% must be present.
$GLOBAL_CONFIG['welcome_message'] = '

<div class="jumbotron" style="margin-top: -15px">
    <h1>Find stacks. Get rampages.</h1>
    <p>NotA Stacks is a tool that can match you with friendly players. Unorganized games and unwanted teammates are a thing of the past. Have fun.</p>
    <p>
        %STEAM_LOGIN_BUTTON%
    <p>
</div>

';

//Adjectives file
//Adjectives are unlocked by players depending on the number of commends and are displayed right before the player's position. For example: "Awesome Midlaner". The more commends the player has, the better the unlocked adjectives are.
$GLOBAL_CONFIG['adjectives'] = json_decode(file_get_contents('../resources/adjectives.json'), TRUE);

?>