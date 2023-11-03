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
    	$macAddress = utf8_decode(urldecode($_REQUEST['macAddress']));
    	$macAddress = substr($macAddress, 0, -1);
    	
    	$log  = "User Mac: ".$macAddress.' - '.date("F j, Y, g:i a").PHP_EOL."-------------------------".PHP_EOL;
//Save string to log, use FILE_APPEND to append.
		file_put_contents('log_'.date("j.n.Y").'.log', $log, FILE_APPEND);
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
		// check mac address to access mac
		$mac_address_flag = 1;

		$checkMacAddressAll = "SELECT * FROM moblie_macaddress";
		$mac_result = $pdo->prepare("$checkMacAddressAll");
		$mac_result->execute();
		if($mac_result->rowCount() > 0){
			$checkMacAddress = "SELECT * FROM moblie_macaddress WHERE SUBSTRING(mac_address, 1, LENGTH(mac_address) -1) = '$macAddress'";
			$mac_res = $pdo->prepare("$checkMacAddress");
			$mac_res->execute();
		}

		if($mac_result->rowCount() == 0){
			$mac_address_flag = 1;
		}
		if($mac_result->rowCount() > 0){
			if($mac_res->rowCount() == 0){
				$mac_address_flag = 0;
			}
		}
		
		if(!$mac_address_flag && 1 == 2){
			$lang = $_POST['language'];
			if($lang == 'en' || $lang == ''){
				$alertMessage = "You are only allowed to use the app if you're connected to the club wi-fi!";
			}else if($lang == 'es'){
				$alertMessage = "Solo puedes utilizar la aplicación si estás conectado al wifi del club.";
			}
			$response = array('flag'=>0, 'message' =>  $alertMessage);
			echo json_encode($response);
			die();
		}
	}
	else
	{
		$response = array('flag'=>'0', 'message' => "Club name is required.");
		echo json_encode($response);
	}