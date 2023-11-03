<?php

	ini_set("display_errors", "off");
    header("Access-Control-Allow-Origin: *");
	
	require_once 'functions.php';
	
	// Defining constants.. Perhaps better served for a separate cfg file?
	define("DEBUG_MODE", false);
	
  // Online:
	// define("SITE_ROOT", "https://ccsnube.com/ttt/");
	// define("HOST_ROOT", $_SERVER['DOCUMENT_ROOT'] . "/ttt/");
	// define("DATABASE_HOST", "127.0.0.1:3306");

	define("SITE_ROOT", "https://nefos.staging9.com/");
	define("HOST_ROOT", $_SERVER['DOCUMENT_ROOT']."/");
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
 		$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
 		$pdo->exec('SET NAMES "utf8"');
	}
	catch (PDOException $e)	{
		
  		$output = 'Unable to connect to the database server: ' . $e->getMessage();

 		echo $output;
 		exit();
 		
	}
	
	session_start();

	$domain = $_SESSION['domain'];
	$db_name = $_SESSION['db_name'];
	$db_user = $_SESSION['db_user'];
	$db_pwd = $_SESSION['db_pwd'];
	
	/*
	echo "domain: $domain<br />";
	echo "db_name: $db_name<br />";
	echo "db_user: $db_user<br />";
	echo "db_pwd: $db_pwd<br />";
	*/
	try	{
 		$pdo3 = new PDO('mysql:host='.DATABASE_HOST.';dbname='.$db_name, $db_user, $db_pwd);
 		$pdo3->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
 		$pdo3->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
 		$pdo3->exec('SET NAMES "utf8"');
	}
	catch (PDOException $e)	{
		
  		header("Location: /index.php");
  		
	}
	
	$db_name = "admin_nefos";
	$db_user = "admin_nefosu";
	$db_pwd = "5T8mHFvfQVIlCrg3";
	
	try	{
 		$pdo2 = new PDO('mysql:host='.DATABASE_HOST.';dbname='.$db_name, $db_user, $db_pwd);
 		$pdo2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
 		$pdo2->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
 		$pdo2->exec('SET NAMES "utf8"');
	}
	catch (PDOException $e)	{
  		$output = 'Unable to connect to the database server: ' . $e->getMessage();

 		echo $output;
 		exit();
	}
	
	if ($_SESSION['domain'] == 'bettyboopcpt') {

		$timezone = "Africa/Johannesburg";
		
		function tzo() {
			date_default_timezone_set("Africa/Johannesburg");
		}
		
	} else if ($_SESSION['domain'] == 'londonlounge') {

		$timezone = "Europe/London";
		
		function tzo() {
			date_default_timezone_set("Europe/London");
		}
		
	} else {

		$timezone = "Europe/Madrid";
		
		function tzo() {
			date_default_timezone_set("Europe/Madrid");
		}
		
	}
	// code update start by konstant for Task-15070266 on 31-03-2022
	//$date = time();
	function is_daylight($date){
		if (date('I', $date)) {
		    return true;
		} else {
		    return flase;
		}
	}
	// code update end by konstant for Task-15070266 on 31-03-2022
	
	$offsetSec = timezone_offset_get( timezone_open( "$timezone" ), new DateTime() );
	
