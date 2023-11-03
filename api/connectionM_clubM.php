<?php
//  Offline Local:
	define("SITE_ROOT", "https://ccsnube.com/ttt");
	define("HOST_ROOT", $_SERVER['DOCUMENT_ROOT'] . "/");
	define("DATABASE_HOST", "127.0.0.1:3306");

/*	ini_set("log_errors", TRUE);  
	  
	// setting the logging file in php.ini 
	ini_set('error_log', "error.log"); */
	
	$siteroot = SITE_ROOT; // Used for href, src, header(Location:)
	$hostroot = HOST_ROOT; // Used for includes --- and for uploads? CHECK!

	// Define constants for success/error messages
	define("MESSAGESUCCESS", "success");
	define("MESSAGEERROR", "error");
	
	define("USERNAME", "ccs_masterdbu");
	define("PASSWORD", "GMjq8iG8mEkPMJRf");
	define("DATABASE_NAME", "ccs_masterdb");

	//for local
	// define("USERNAME", "root");
	// define("PASSWORD", "");
	// define("DATABASE_NAME", "ccs_masterdb");
	
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
	if(isset($_REQUEST['club_name']) && $_REQUEST['club_name'] != '' )
    {

		try
		{
			$result = $pdo->prepare("SELECT db_pwd FROM db_access WHERE domain = :domain");
			$result->bindValue(':domain', $_REQUEST['club_name']);
			$result->execute();
			//print_r($results); exit;
		}
		catch (PDOException $e)
		{
			$response = array('flag'=>'0', 'message' => $e->getMessage());
		   	echo json_encode($response); 
		}

		$row = $result->fetch();
	    $db_name = 'ccs_'.$_REQUEST['club_name'];
		//echo $db_user = $db_name . "u"; echo "<br>";
		//echo $db_pwd = $row['db_pwd']; exit;
		$db_user = "ccs_masterdbu";
		$db_pwd = "GMjq8iG8mEkPMJRf";

		//for local
		// $db_user = "root";
		// $db_pwd = "";

	    try	{
			//echo USERNAME . DATABASE_HOST . DATABASE_NAME . PASSWORD ; exit;
	 		$pdo = new PDO('mysql:host='.DATABASE_HOST.';dbname='.$db_name, $db_user, $db_pwd);
	 		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	 		$pdo->exec('SET NAMES "utf8"');
		}
		catch (PDOException $e)	{
			
	  		$response = array('flag'=>'0', 'message' => $e->getMessage());
			echo json_encode($response);
	 		
		}
	}
	else
	{
		$response = array('flag'=>'0', 'message' => "Club name is required.");
		echo json_encode($response);
	}