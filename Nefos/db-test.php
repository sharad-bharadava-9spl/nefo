<?php

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
     ini_set('display_errors','on');
		// Crete database & structure
	/*$get_dbuser = "select user from mysql.user";

  	try
	{
		$result = $pdo->prepare("$get_dbuser");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
   while( $db_users = $result->fetch()){
   		$db_userarr[] = $db_users['user'];
   }*/
/*echo "<pre>";
print_r($db_userarr);*/
   		// Crete database & structure
/*	$get_database = "SHOW DATABASES;";

  	try
	{
		$db_result = $pdo->prepare("$get_database");
		$db_result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
  $db_list = $db_result->fetchAll();
//print_r($db_list); 


	die; */	
/*    $create_dbuser = "create user 'lokesh_db_test_user1'@'' identified by '123456'";   

	  	try
		{
			$result = $pdo->prepare("$create_dbuser")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching create user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	  // crete Db 
	   $create_database = "create database lokesh_db_test1"; 
		try
		{
			$result = $pdo->prepare("$create_database");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error creating database: ' . $e->getMessage();
				echo $error;
				exit();
		}

     // grant priviledges to dtabse
		$grant_permission = "GRANT ALL PRIVILEGES ON lokesh_db_test1.* TO 'lokesh_db_test_user1'@'';";
		try
		{
			$result = $pdo->prepare("$grant_permission");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error creting database: ' . $e->getMessage();
				echo $error;
				exit();
		}*/
		



	$db_connectionStatus = $pdo->getAttribute(constant("PDO::ATTR_CONNECTION_STATUS"));

	$db_connectionStatus  = explode(" " , $db_connectionStatus);
	$db_server = $db_connectionStatus[0];

/*		$selectpass = "SELECT * from db_access order by id desc limit 20";
	try
		{
			$result = $pdo->prepare("$selectpass");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

		$pass = $result->fetchAll();
		echo "<pre>";
		print_r($pass);*/


		// grant priviledges to dtabse
		/*$grant_permission = "GRANT ALL PRIVILEGES ON $dbName.* TO '$db_user'@'$db_server';";
		try
		{
			$result = $pdo->prepare("$grant_permission");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error creting database: ' . $e->getMessage();
				echo $error;
				exit();
		}*/


        $dbName = 'lokesh_db_test1';
        $db_user = 'lokesh_db_test_user1';
        $db_pass = '123456';

			define("DATABASE_HOST_NEW", $db_server);
		try	{
	 		$newPDO = new PDO('mysql:host='.DATABASE_HOST_NEW.';dbname='.$dbName, $db_user, $db_pass);
	 		$newPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	 		$newPDO->exec('SET NAMES "utf8"');
	 		echo 'connected';
	 		die;
		}
		catch (PDOException $e)	{
	  		$output = 'Unable to connect to the database server: ' . $e->getMessage();

	 		echo $output;
	 		exit();
		}