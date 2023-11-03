<?php

	ini_set("display_errors", "off");
    header("Access-Control-Allow-Origin: *");
	
	// Defining constants.. Perhaps better served for a separate cfg file?
	define("DEBUG_MODE", false);

	$timezone = "Europe/Madrid";
	
	function tzo() {
		date_default_timezone_set("Europe/Madrid");
	}
	
	$offsetSec = timezone_offset_get( timezone_open( "$timezone" ), new DateTime() );
	
/*  Online:
	define("SITE_ROOT", "https://ccsnubev2.com/test/");
	define("HOST_ROOT", $_SERVER['DOCUMENT_ROOT'] . "/test/");
	define("DATABASE_HOST", "172.30.205.116:3306");
*/

//  Offline Local:
	define("SITE_ROOT", "https://ccsnube.com/ttt/");
	define("HOST_ROOT", $_SERVER['DOCUMENT_ROOT'] . "/ttt/");
	define("DATABASE_HOST", "127.0.0.1:3306");
	
	$siteroot = SITE_ROOT; // Used for href, src, header(Location:)
	$hostroot = HOST_ROOT; // Used for includes --- and for uploads? CHECK!

	// Define constants for success/error messages
	define("MESSAGESUCCESS", "success");
	define("MESSAGEERROR", "error");

	define("USERNAME", "ccs_masterdbu");
	define("PASSWORD", "GMjq8iG8mEkPMJRf");
	define("DATABASE_NAME", "ccs_masterdb");
	
	try	{
 		$pdo = new PDO('mysql:host='.DATABASE_HOST.';dbname='.DATABASE_NAME, USERNAME, PASSWORD);
 		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
 		$pdo->exec('SET NAMES "utf8"');
	}
	catch (PDOException $e)	{
  		$output = 'Unable to connect to the database server: ' . $e->getMessage();

 		echo $output;
 		exit();
	}
/*	$db_name = "admin_nefos_test";
	$db_user = "admin_nefos_testu";
	$db_pwd = "0g8uewQCkwh1TAd1";*/

	$db_name = "admin_nefos";
	$db_user = "admin_nefosu";
	$db_pwd = "5T8mHFvfQVIlCrg3";
	
	/*
	echo "domain: $domain<br />";
	echo "db_name: $db_name<br />";
	echo "db_user: $db_user<br />";
	echo "db_pwd: $db_pwd<br />";
	*/
	try	{
 		$pdo2 = new PDO('mysql:host='.DATABASE_HOST.';dbname='.$db_name, $db_user, $db_pwd);
 		$pdo2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
 		$pdo2->exec('SET NAMES "utf8"');
	}
	catch (PDOException $e)	{
  		$output = 'Unable to connect to the database server: ' . $e->getMessage();

 		echo $output;
 		exit();
	}