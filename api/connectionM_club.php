<?php
//  Offline Local:
	define("SITE_ROOT", "https://ccsnube.com/ttt");
	define("HOST_ROOT", $_SERVER['DOCUMENT_ROOT'] . "/");
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
		//echo USERNAME . DATABASE_HOST . DATABASE_NAME . PASSWORD ; exit;
 		$pdo = new PDO('mysql:host='.DATABASE_HOST.';dbname='.DATABASE_NAME, USERNAME, PASSWORD);
 		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
 		$pdo->exec('SET NAMES "utf8"');
	}
	catch (PDOException $e)	{
		$response = array('flag'=>'0', 'message' => $e->getMessage());
	   	echo json_encode($response); 
 		
	}

	$nefos_db_name = "admin_nefos";
	$nefos_db_user = "admin_nefosu";
	$nefos_db_pwd = "5T8mHFvfQVIlCrg3";

	
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
