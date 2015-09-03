<?php

//The site name
$GLOBAL_CONFIG['site_name'] = 'ToS Parties';

//Dev mode. MUST set to FALSE before opening the product to the public.
$GLOBAL_CONFIG['DEV_MODE'] = FALSE;

//ReCaptcha settings
$GLOBAL_CONFIG['ReCaptcha']['enabled']     = FALSE;
$GLOBAL_CONFIG['ReCaptcha']['site_key']    = '';
$GLOBAL_CONFIG['ReCaptcha']['secret_key']  = '';

//Current version of the script
$GLOBAL_CONFIG['version'] = '1.2';

//Owner of the script. If you forked it, please, either do not modify it or change the footer to credit the original author
$GLOBAL_CONFIG['owner'] = 'Pablo Rodr&iacute;guez (<a href="http://steamcommunity.com/id/MeLlamoPablo" target="_blank">MeLlamoPablo</a> a.k.a. <a href="http://www.reddit.com/user/sfcpfc" target="_blank">/u/sfcpfc</a>)';

//Supported servers. CANNOT contain "-"
//$GLOBAL_CONFIG['servers'] = array('USE', 'EUW', 'EUE');

//Time that the user has to wait before he can refresh his data from Steam again, in seconds.
$GLOBAL_CONFIG['refreshWaitTime'] = 60 /*seconds*/ * 60 /*minutes*/ * 24 /*hours*/;

//Rules. Can use HTML.
$GLOBAL_CONFIG['rules'] = '

<p><a href="https://www.reddit.com/r/townofsalemgame" target="_blank">/r/TownOfSalemGame</a> is a nice and friendly community, and we want to keep it that way, so our first and most important rule is:</p>
<h3>1.- You are not allowed to flame anyone</h3>
<p>No one is the perfect player. So, if some of your teammates make a mistake, please do not flame. Instad, point out their mistakes and encourage them to improve! <i>(Though, if you\'re the Jester, flaming is a legitimate strategy and you are free to use it)</i></p>
<h4>2.- Do not throw games</h4>
<p>Throwing games means making mistakes consciously with the sole purpose of making your team lose the game (e.g: executing the revealed mayor, pointing out who your fellow mafia are). Do not do this. It ruins the fun for your teammates.</p>
<p>That said, not everything is game throwing. Do not be afraid of making honest mistakes, and do not call people who make honest mistakes game throwers.</p>
<h4>3.- Do not meta game</h4>
<p>Currently, the two most popular forms of meta gaming are:</p>
<ul>
	<li><b>"Skyping"</b>, or using an external chat to give away information. It\'s fine if you want to talk about the game on an external chat, but do not give away information that your partner wouldn\'t have had access to otherwise (e.g: telling the jailor the identity of the mafia members)</li>
	<li><b>Asking for in-game system messages</b>. For example, if you\'re the Jailor, you can\'t ask your prisoner to write their attributes in order to prove their identity. This is unfair for evil roles because it forces them to have the wiki open while playing, and in case they haven\'t, it results in a death. Please, do not do that.</li>
</ul>
<h4>4.- Try not to miss the play time</h4>
<p>In order for this project to work, we need commitment. It\'d be a dissapointment if someone gets a party of 15 people and ends up playing with only one person. Try to be there when the play time arrives, and if you can\'t, leave the party. If you don\'t know for sure if you will be up for playing at the party\'s time, please do not join it.</p>
<p>We hope you enjoy your time here. Thanks for reading, and GLHF!</p>

';

//Adjectives file
//Adjectives are unlocked by players depending on the number of commends and are displayed right before the player's position. For example: "Awesome Midlaner". The more commends the player has, the better the unlocked adjectives are.
$GLOBAL_CONFIG['adjectives'] = json_decode(file_get_contents('../resources/adjectives.json'), TRUE);

//Roles file
//A file with all the Town of Salem roles for users to choose
$GLOBAL_CONFIG['roles'] = json_decode(file_get_contents('../resources/roles.json'), TRUE);

?>