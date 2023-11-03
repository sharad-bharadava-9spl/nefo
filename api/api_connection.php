<?php
//  Offline Local:
	define("SITE_ROOT", "http://192.168.1.124/cannabisclub/");
	define("HOST_ROOT", $_SERVER['DOCUMENT_ROOT'] . "/");
	define("DATABASE_HOST", "localhost");
	
	$siteroot = SITE_ROOT; // Used for href, src, header(Location:)
	$hostroot = HOST_ROOT; // Used for includes --- and for uploads? CHECK!

	// Define constants for success/error messages
	define("MESSAGESUCCESS", "success");
	define("MESSAGEERROR", "error");
	
	define("USERNAME", "binal");
	define("PASSWORD", "");
	define("DATABASE_NAME", "ccs_demo");
	
	try	{

		//echo USERNAME . DATABASE_HOST . DATABASE_NAME . PASSWORD ; exit;
 		$pdo = new PDO('mysql:host='.DATABASE_HOST.';dbname='.DATABASE_NAME, USERNAME, PASSWORD);
 		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
 		$pdo->exec('SET NAMES "utf8"');
	}
	catch (PDOException $e)	{
		
  		$output = 'Unable to connect to the database server: ' . $e->getMessage();

 		echo $output;
 		exit();
 		
	}