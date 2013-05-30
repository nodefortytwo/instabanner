<?php
define('MONGO_TYPE_DOUBLE', 1);
define('MONGO_TYPE_STRING', 2);
define('MONGO_TYPE_OBJECT', 3);
define('MONGO_TYPE_ARRAY', 4);
define('MONGO_TYPE_BINARY', 5);
define('MONGO_TYPE_OBJECT_ID', 7);
define('MONGO_TYPE_BOOL', 8);
define('MONGO_TYPE_DATE', 9);
define('MONGO_TYPE_NULL', 10);

function mongo_init(){
    require 'mongo.class.php';
    mdb();
}

function mdb($newdb = null){
    static $client, $mdb;
    if(!$client){
        $con_string = "mongodb://" . config('OPENSHIFT_MONGODB_DB_USERNAME') . ":" . config('OPENSHIFT_MONGODB_DB_PASSWORD') . "@" . config('OPENSHIFT_MONGODB_DB_HOST') . ':' . config('OPENSHIFT_MONGODB_DB_PORT'); 
        $client = new MongoClient($con_string);
    }
    
    if(!$mdb && $newdb){
        $mdb = $client->$newdb;
    }
    
    if(!$mdb){
        $db = config()->db_name;
        $mdb = $client->$db;
    }
    return $mdb;
}
