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
define('HOST', 'local.instabanner.me');
define('SITE_ROOT', '');
define('PATH_TO_MODULES', 'libs/modules');
define('SITE_NAME', 'Platform');

define('UPLOAD_PATH', 'public');






//Dev / Live Settings
//any call to elog with a level >= what is defined below will be written to the database
define('DEBUG_LEVEL', 0);
define('TRACE', true);

//APIS

?>
