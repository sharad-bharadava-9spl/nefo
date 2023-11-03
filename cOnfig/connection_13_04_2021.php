<?php

	ini_set("display_errors", "off");
    header("Access-Control-Allow-Origin: *");
	
	require_once 'functions.php';
	
	// Defining constants.. Perhaps better served for a separate cfg file?
	define("DEBUG_MODE", false);

	
	if (isset($_SESSION['domain'])) {
		if ($_SESSION['domain'] == 'irena') {
			echo "HAU";
		}
	}
	
  // Online:
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
		// connection with admin_nefos

	$nefos_db_name = "admin_nefos_test";
	$nefos_db_user = "admin_nefos_testu";
	$nefos_db_pwd = "0g8uewQCkwh1TAd1";
	
	/*
	echo "domain: $domain<br />";
	echo "db_name: $db_name<br />";
	echo "db_user: $db_user<br />";
	echo "db_pwd: $db_pwd<br />";
	*/
	try	{
 		$pdo2 = new PDO('mysql:host='.DATABASE_HOST.';dbname='.$nefos_db_name, $nefos_db_user, $nefos_db_pwd);
 		$pdo2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
 		$pdo2->exec('SET NAMES "utf8"');
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
 		$pdo3->exec('SET NAMES "utf8"');
	}
	catch (PDOException $e)	{
  		$output = 'Unable to connect to the database server: ' . $e->getMessage();

 		echo $output;
 		exit();
	}


	$timezone = "Europe/Madrid";

/*
	// get timezone
 	try{
		$selectTimezone = "SELECT timeZone FROM systemsettings";

		$timeresult = $pdo3->prepare("$selectTimezone");
		$timeresult->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$resRow = $timeresult->fetch();
		$timezone_row = $resRow['timeZone'];

		if($timezone_row != '' && !empty($timezone_row)){
			$timezone = $timezone_row;
		}
*/
	//$offsetSec = timezone_offset_get( timezone_open( "$timezone" ), new DateTime() );
	$offsetSec = 0;
	
	function tzo() {

		//date_default_timezone_set($timezone);*/
	}