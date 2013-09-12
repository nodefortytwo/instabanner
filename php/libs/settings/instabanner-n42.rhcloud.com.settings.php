<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

//Database
define('OPENSHIFT_MONGODB_DB_HOST', 'localhost');
define('OPENSHIFT_MONGODB_DB_PORT', '27017');
define('OPENSHIFT_APP_NAME', 'instabanner');
define('OPENSHIFT_MONGODB_DB_USERNAME', '');
define('OPENSHIFT_MONGODB_DB_PASSWORD', '');

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
define('INSTAGRAM_ID', 'db6b0369a5d140f3b49b0524d78ec32a');
define('INSTAGRAM_SECRET', 'e3a13306584d4cdfa878479008e0d4b7');
?>
