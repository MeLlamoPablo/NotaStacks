<?php

//Dev mode. MUST set to FALSE before opening the product to the public.
$GLOBAL_CONFIG['DEV_MODE'] = FALSE;

//Current version of the script
$GLOBAL_CONFIG['version'] = '0.1';

//Steam API Key
$GLOBAL_CONFIG['steamAPIKey'] = '';

//Owner of the script. If you forked it, please, either do not modify it or change the footer to credit the original author
$GLOBAL_CONFIG['owner'] = 'Pablo Rodríguez (<a href="http://steamcommunity.com/id/MeLlamoPablo" target="_blank">MeLlamoPablo</a> a.k.a. <a href="http://www.reddit.com/user/sfcpfc" target="_blank">/u/sfcpfc</a>)';

//Supported servers. CANNOT contain "-"
$GLOBAL_CONFIG['servers'] = array('USE', 'EUW', 'EUE');

?>