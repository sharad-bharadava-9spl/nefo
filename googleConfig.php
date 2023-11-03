<?php

// load GCS library
require_once 'vendor/autoload.php';


$google_bucket = 'ccsnubev2';

// define base urls for google bucket
define("GLOCAL_SERVER", "https://storage.googleapis.com/".$google_bucket."/local_server/");
define("GDEMO_SERVER", "https://storage.googleapis.com/".$google_bucket."/demo_server/");
define("GLIVE_SERVER", "https://storage.googleapis.com/".$google_bucket."/live_server/");


if($_SERVER['HTTP_HOST'] == '192.168.0.41' || $_SERVER['HTTP_HOST'] == 'localhost'){
        $google_root = GLOCAL_SERVER;
        $google_root_folder = "local_server/";
    }else if($_SERVER['HTTP_HOST'] == 'ccsnube.com'){
        $google_root = GDEMO_SERVER;
        $google_root_folder = "demo_server/";
    }else{
        $google_root = GLIVE_SERVER;
        $google_root_folder = "live_server/";
    }

use Google\Cloud\Storage\StorageClient;

// Please use your own private key (JSON file content) which was downloaded in step 3 and copy it here
// your private key JSON structure should be similar like dummy value below.
// WARNING: this is only for QUICK TESTING to verify whether private key is valid (working) or not.  
// NOTE: to create private key JSON file: https://console.cloud.google.com/apis/credentials  
$privateKeyFileContent = '{
    "type": "service_account",
    "project_id": "single-scholar-289707",
    "private_key_id": "ed6b6b7f4556be608839b469a95ce986a8b785db",
    "private_key": "-----BEGIN PRIVATE KEY-----\nMIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQC9aBoqZJuQWqj8\nTsAUcLk01RUrQZGq71zGtH4+sRvjsUQvukXoBs+tQvo2Dgqi0BxplxtTh3U9njXp\n+qVVuqNgfUavA+xJUpS1JtDLdqNV451YAeYS9v5WkEDwykt6BTSGpqv4ZMWiOTqR\n+Go1fjyhEnR5sBcHOX6vq/vDqf99KNDzQWsnfYoseBBClHpCFsF9BaDK6vldah0h\nKxKEl2ohGdUrCWsd90SoRISrWjpN1EXcSAirTvia/k/xc2k8FK87cpbMtACW4/KG\nADovnkV7hjbpfus3cfBSYRCfKeLGscnD2I4CMbB2XU8Ep3eMmRYuwN7gcMcJrRz0\nLAkqVKGVAgMBAAECggEAFPB1lhK1zQVs0anuG8RjgUb+VmAjF8PrHI+XNlyTof42\ntNURxec83eF98mxmeh3qSjUZoQgkarm7KaEbVqkxxyjKX+5d2LRPuuAt7JRy1bu6\naSCanCWgtBayCzy+D+ET77/s2qLfY4ISqtENAtlwH/l/lXVlclfFnOu1Q2ofk+k2\nwTHPaCCM/WaIAhol+FUPcx4EUWb3Vb62Mr3Z0uumhZzmm/MopxD5wwhhHr9/OauK\nSCMxHhkVC10H3DIcm8xf6kTHxHoZCkR4+bRyKQBVXLQF0WjOEGJqLb3ti0ZxfC8o\nd3jok5NZGrAJQgCsY6jEPTiBFbSNTvI/TpW3PK/BIQKBgQDoDsbLsJR5EVOZby0d\n6ZPQgCs6yJ28ymwel3YdT3Q8//vaiDf38yY1jsD+niGKp+YoXLU9w/KjFF5tbSs+\nC1zdjtbJlIbUeiR9TUP6pBmzucxamO7HNi1IxuUC/GTbPBO3FwxOxSoILKNVg9zT\nvmR6CYFzk9r/CGtHpZaMpeHFIQKBgQDQ8s3vio6t+sj4p2z56EYIu//qhXMYzynY\nXJoX3nTXxaHxGer7yPuEq+wEXUhyIGwHID6cQNgzD1qLNV/XGYo0bSJHloEEocKn\n5HV64XC3fdq82AJ40PoUtgN1coEpi6MYA7tCO8T3xzwi5RjRrJ2SWYhfO/SyBMnd\n+DgfuerZ9QKBgEc9Ggw35iyQhCCLP8d6CFWusxa1ta7aWXy+tJTk6mM7ln6M6Nmm\nfGlerKK9usBNMNvdMgqHF/q2axKeu/KtHFIr4oY5oXajFIae7KqKLBW/iRua7Vqc\nqCQ6Yt2ay/99nvPLEI0x++hMg8vh0i3yufe46VOo5Ub7t6tkCyacWgXhAoGBAIrI\n23RB6ecxbg+K1V1gpDPk+2TAYuSVpUuqpkc4YQ94m8nS1zuKEVXcA5Yah6YWVuqc\nxjlu8KtphVB1kUUEF8Iewugd1XBIKUzu+AE4gG/ATmukZm/tDk53XvDBr3zy3M90\nmwqxPHD/irf6x3NtXfP5OBVZc3xSpeqjllOsX4uJAoGBALjIn1UHfGKRubwBktah\nLneZJ2BTHz9lKsaJ/lxiymrKHjJXIFPZ0QS8c79mP47u/zKlKMvFJoQQoBYQuTR3\ndpUmHbS2oU9wOdzzGAxLbUWkExtHHYEWAUwrSbZ2BGLYW5RVkhgfo37HldA8NYtP\n6dMNScry2O/1qOo3dhVAQUbP\n-----END PRIVATE KEY-----\n",
    "client_email": "ccstest@single-scholar-289707.iam.gserviceaccount.com",
    "client_id": "108987938506576374032",
    "auth_uri": "https://accounts.google.com/o/oauth2/auth",
    "token_uri": "https://accounts.google.com/o/oauth2/token",
    "auth_provider_x509_cert_url": "https://www.googleapis.com/oauth2/v1/certs",
    "client_x509_cert_url": "https://www.googleapis.com/robot/v1/metadata/x509/ccstest%40single-scholar-289707.iam.gserviceaccount.com"
    }';

/*
 * NOTE: if the server is a shared hosting by third party company then private key should not be stored as a file,
 * may be better to encrypt the private key value then store the 'encrypted private key' value as string in database,
 * so every time before use the private key we can get a user-input (from UI) to get password to decrypt it.
 */

function uploadFile($bucketName, $fileContent, $cloudPath) {
    $privateKeyFileContent = $GLOBALS['privateKeyFileContent'];
    // connect to Google Cloud Storage using private key as authentication
    try {
        $storage = new StorageClient([
            'keyFile' => json_decode($privateKeyFileContent, true)
        ]);
    } catch (Exception $e) {
        // maybe invalid private key ?
        print $e;
        return false;
    }

    // set which bucket to work in
    $bucket = $storage->bucket($bucketName);

    // upload/replace file 
    $storageObject = $bucket->upload(
            $fileContent,
            ['name' => $cloudPath]
            // if $cloudPath is existed then will be overwrite without confirmation
            // NOTE: 
            // a. do not put prefix '/', '/' is a separate folder name  !!
            // b. private key MUST have 'storage.objects.delete' permission if want to replace file !
    );

    // is it succeed ?
    return $storageObject != null;
}

function getFileInfo($bucketName, $cloudPath) {
    $privateKeyFileContent = $GLOBALS['privateKeyFileContent'];
    // connect to Google Cloud Storage using private key as authentication
    try {
        $storage = new StorageClient([
            'keyFile' => json_decode($privateKeyFileContent, true)
        ]);
    } catch (Exception $e) {
        // maybe invalid private key ?
        print $e;
        return false;
    }

    // set which bucket to work in
    $bucket = $storage->bucket($bucketName);
    $object = $bucket->object($cloudPath);
    return $object->info();
}
//this (listFiles) method not used in this example but you may use according to your need 
function listFiles($bucketName, $directory = null) {

    $privateKeyFileContent = $GLOBALS['privateKeyFileContent'];
    // connect to Google Cloud Storage using private key as authentication
    try {
        $storage = new StorageClient([
            'keyFile' => json_decode($privateKeyFileContent, true)
        ]);
    } catch (Exception $e) {
        // maybe invalid private key ?
        print $e;
        return false;
    }

    $bucket = $storage->bucket($bucketName);
    if ($directory == null) {
        // list all files
        $objects = $bucket->objects();
    } else {
        // list all files within a directory (sub-directory)
        $options = array('prefix' => $directory);
        $objects = $bucket->objects($options);
    }

    foreach ($objects as $object) {
        print $object->name() . PHP_EOL;
        // NOTE: if $object->name() ends with '/' then it is a 'folder'
    }
}

/**
 * Delete an object.
 *
 * @param string $bucketName the name of your Cloud Storage bucket.
 * @param string $objectName the name of your Cloud Storage object.
 * @param array $options
 *
 * @return void
 */
function delete_object($bucketName, $objectName, $options = [])
{
    $privateKeyFileContent = $GLOBALS['privateKeyFileContent'];
    // connect to Google Cloud Storage using private key as authentication
    try {
        $storage = new StorageClient([
            'keyFile' => json_decode($privateKeyFileContent, true)
        ]);
    } catch (Exception $e) {
        // maybe invalid private key ?
        print $e;
        return false;
    }
    $bucket = $storage->bucket($bucketName);
    $object = $bucket->object($objectName);
    $object->delete();
    //printf('Deleted gs://%s/%s' . PHP_EOL, $bucketName, $objectName);
}

/**
 * Download an object from Cloud Storage and save it as a local file.
 *
 * @param string $bucketName the name of your Google Cloud bucket.
 * @param string $objectName the name of your Google Cloud object.
 * @param string $destination the local destination to save the encrypted object.
 *
 * @return void
 */
function download_object($bucketName, $objectName, $destination)
{
    $privateKeyFileContent = $GLOBALS['privateKeyFileContent'];
    // connect to Google Cloud Storage using private key as authentication
    try {
        $storage = new StorageClient([
            'keyFile' => json_decode($privateKeyFileContent, true)
        ]);
    } catch (Exception $e) {
        // maybe invalid private key ?
        print $e;
        return false;
    }
    
    $bucket = $storage->bucket($bucketName);
    $object = $bucket->object($objectName);
    $object->downloadToFile($destination);
    printf('Downloaded gs://%s/%s to %s' . PHP_EOL,
        $bucketName, $objectName, basename($destination));
}


function object_exist($bucketName, $objectName){

        $privateKeyFileContent = $GLOBALS['privateKeyFileContent'];
    // connect to Google Cloud Storage using private key as authentication
    try {
        $storage = new StorageClient([
            'keyFile' => json_decode($privateKeyFileContent, true)
        ]);
    } catch (Exception $e) {
        // maybe invalid private key ?
        print $e;
        return false;
    }
    $bucket = $storage->bucket($bucketName);
    $object = $bucket->object($objectName);
    return $object->exists();

}

/**
 * Move an object to a new name and/or bucket.
 *
 * @param string $bucketName the name of your Cloud Storage bucket.
 * @param string $objectName the name of your Cloud Storage object.
 * @param string $newBucketName the destination bucket name.
 * @param string $newObjectName the destination object name.
 *
 * @return void
 */
function rename_object($bucketName, $objectName, $newBucketName, $newObjectName)
{
    $privateKeyFileContent = $GLOBALS['privateKeyFileContent'];
    // connect to Google Cloud Storage using private key as authentication
    try {
        $storage = new StorageClient([
            'keyFile' => json_decode($privateKeyFileContent, true)
        ]);
    } catch (Exception $e) {
        // maybe invalid private key ?
        print $e;
        return false;
    }
    $bucket = $storage->bucket($bucketName);
    $object = $bucket->object($objectName);
    $object->copy($newBucketName, ['name' => $newObjectName]);
    $object->delete();
    printf('Moved gs://%s/%s to gs://%s/%s' . PHP_EOL,
        $bucketName,
        $objectName,
        $newBucketName,
        $newObjectName);
}
