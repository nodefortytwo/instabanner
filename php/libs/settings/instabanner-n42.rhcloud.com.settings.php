<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

//Database
define('DB_NAME', 'cycle');

//Theme Stuff
define('HOST', 'instabanner-n42.rhcloud.com');
define('SITE_ROOT', '');
define('PATH_TO_MODULES', 'libs/modules');
define('SITE_NAME', 'Instabanner.me');
define('DEFAULT_STYLE', 'cosmo');

define('UPLOAD_PATH', 'public');


//Dev / Live Settings
//any call to elog with a level >= what is defined below will be written to the database
define('DEBUG_LEVEL', 0);
define('TRACE', true);

//APIS
define('INSTAGRAM_ID', '09916b2de1c940bc9b2d1df04f94d67c');
define('INSTAGRAM_SECRET', '30b8dd872c5148adb3f1230706c2eef1');
?>
