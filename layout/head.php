<!-- Meta tags -->
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->

<!-- JS files -->
<?php if($GLOBAL_CONFIG['ReCaptcha']['enabled']) echo '<script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit"></script>' ?>
<script type="text/javascript" src="http://<?php echo $_SERVER['HTTP_HOST'] ?>/notastacks/bower_components/jquery/dist/jquery.min.js"></script>
<script type="text/javascript" src="http://<?php echo $_SERVER['HTTP_HOST'] ?>/notastacks/bower_components/moment/min/moment.min.js"></script>
<script type="text/javascript" src="http://<?php echo $_SERVER['HTTP_HOST'] ?>/notastacks/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
<script type="text/javascript" src="http://<?php echo $_SERVER['HTTP_HOST'] ?>/notastacks/bower_components/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="http://<?php echo $_SERVER['HTTP_HOST'] ?>/notastacks/bower_components/bootstrap-progressbar/bootstrap-progressbar.min.js"></script>
<script type="text/javascript" src="http://<?php echo $_SERVER['HTTP_HOST'] ?>/notastacks/bower_components/chosen/chosen.jquery.min.js"></script>
<!-- CSS files -->
<link rel="stylesheet" href="http://<?php echo $_SERVER['HTTP_HOST'] ?>/notastacks/bower_components/bootstrap/dist/css/bootstrap.min.css" />
<link rel="stylesheet" href="http://<?php echo $_SERVER['HTTP_HOST'] ?>/notastacks/bower_components/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" type="text/css" href="http://<?php echo $_SERVER['HTTP_HOST'] ?>/notastacks/bower_components/bootstrap-progressbar/css/bootstrap-progressbar-3.3.4.min.css">
<link rel="stylesheet" type="text/css" href="http://<?php echo $_SERVER['HTTP_HOST'] ?>/notastacks/bower_components/chosen-bootstrap/chosen.bootstrap.min.css">
<link rel="stylesheet" href="http://<?php echo $_SERVER['HTTP_HOST'] ?>/notastacks/resources/custom.css" />