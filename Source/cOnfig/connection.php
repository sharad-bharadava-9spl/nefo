<?php

	ini_set("display_errors", "off");
	
	require_once 'functions.php';
	
	// Defining constants.. Perhaps better served for a separate cfg file?
	define("DEBUG_MODE", false);

	$timezone = "Europe/Madrid";
	
	function tzo() {
		date_default_timezone_set("Europe/Madrid");
	}
	
	$offsetSec = timezone_offset_get( timezone_open( "$timezone" ), new DateTime() );
	
	if (isset($_SESSION['domain'])) {
		if ($_SESSION['domain'] == 'irena') {
			echo "HAU";
		}
	}
	
/*  Online:
	define("SITE_ROOT", "https://ccsnubev2.com/test/");
	define("HOST_ROOT", $_SERVER['DOCUMENT_ROOT'] . "/test/");
	define("DATABASE_HOST", "172.30.205.116:3306");
*/

//  Offline Local:
	define("SITE_ROOT", "https://localhost/");
	define("HOST_ROOT", $_SERVER['DOCUMENT_ROOT'] . "/");
	define("DATABASE_HOST", "localhost");
	
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