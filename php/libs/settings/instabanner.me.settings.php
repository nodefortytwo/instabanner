<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

//DB Details come from the ENV

//Theme Stuff
define('HOST', 'instabanner.me');
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
define('INSTAGRAM_ID', '39fa4b8fad714975b820b9e4b0033255');
define('INSTAGRAM_SECRET', 'c9c8595c2c7b47dc82b1f850a0588ecd');
?>
