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
        $client = new MongoClient();
    }
    
    if(!$mdb && $newdb){
        $mdb = $client->$newdb;
    }
    
    if(!$mdb){
        $db = 'instabanner';
        $mdb = $client->$db;
    }
    return $mdb;
}


function mongo_connection_string(){

    $str = 'mongodb://';

    if((config('OPENSHIFT_MONGODB_DB_USERNAME'))){
        $str .= config('OPENSHIFT_MONGODB_DB_USERNAME') . ':';
    }
    if((config('OPENSHIFT_MONGODB_DB_PASSWORD'))){
        $str .= config('OPENSHIFT_MONGODB_DB_PASSWORD') . '@';
    }

    $str .= config('OPENSHIFT_MONGODB_DB_HOST') . ':' . config('OPENSHIFT_MONGODB_DB_PORT');
    return $str;
}