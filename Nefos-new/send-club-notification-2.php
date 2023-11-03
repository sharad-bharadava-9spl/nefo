<?php
// Begin code by sagar
//  manually send notification (club)
require_once 'cOnfig/connection.php';
require_once 'cOnfig/view.php';
require_once 'cOnfig/authenticate.php';
require_once 'cOnfig/languages/common.php';

session_start();
$accessLevel = '3';
// Authenticate & authorize
authorizeUser($accessLevel);
require_once '../PHPMailerAutoload.php';
$accepted_date = date("Y-m-d H:i:s");

// Multi-Selected Data Hendling Start
$id  = $_POST['userSelect'];
$ids = implode(",",array_map('intval', $id));
$type  = $_POST['type'];
$url  = $_POST['url'];
$notification  = $_POST['notification'];
$notification_es  = $_POST['notification_es'];
$notification_nl  = $_POST['notification_nl'];
$notification_it  = $_POST['notification_it'];
$notification_fr  = $_POST['notification_fr'];
$notification_ca  = $_POST['notification_ca'];

// Multi-Selected Data Hendling Start
$numbers = implode(",",array_column($result_data,'number'));
if(!empty($_POST['group'])){
    $group = implode(',',$_POST['group']);
} else {
    $group = null;
}

	// For each ID, grab database connection values and use pdoX for connections
	$queryL = "SELECT id, number FROM customers WHERE id IN ($ids)";
	try
	{
		$resultsL = $pdo3->prepare("$queryL");
		$resultsL->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	while ($rowL = $resultsL->fetch()) {
		
		$cid = $rowL['id'];
		
		// Look up domain via number
		$queryX = "SELECT db_pwd, customer, domain FROM db_access WHERE customer = '{$rowL['number']}'";
		try
		{
			$resultX = $pdo->prepare("$queryX");
			$resultX->execute();
			$dataX = $resultX->fetchAll();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

		$row = $dataX[0];
			$db_pwd = $row['db_pwd'];
			$customer = $row['customer'];
			$domain = $row['domain'];

		$db_name = "ccs_" . $domain;
		$db_user = $db_name . "u";
	
		// Create pdo5
		try	{
	 		$pdo5 = new PDO('mysql:host='.DATABASE_HOST.';dbname='.$db_name, $db_user, $db_pwd);
	 		$pdo5->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	 		$pdo5->exec('SET NAMES "utf8"');
		}
		catch (PDOException $e)	{
	  		$output = 'Unable to connect to the database server: ' . $e->getMessage();
	
	 		echo $output;
	 		exit();
		}
		
		// Look up all users within the selected usergroup
		$queryC = "SELECT user_id FROM users WHERE userGroup IN ($group)";
		try
		{
			$resultsC = $pdo5->prepare("$queryC");
			$resultsC->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($rowC = $resultsC->fetch()) {
			
			$uid = $rowC['user_id'];
			
			// Add notification here
			$query = "INSERT INTO notifications (customer, user_id, type, url, notification, notification_es, notification_ca, notification_it, notification_fr, notification_nl) VALUES ('$cid', '$uid', '$type', '$url', '$notification', '$notification_es', '$notification_ca', '$notification_it', '$notification_fr', '$notification_nl')";
			try
			{
				$result = $pdo5->prepare("$query")->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
			
			$notids .= $pdo5->lastInsertId() . ", ";
			
		}
		
		$notids = substr($notids, 0, -2);
		
		// Add to Nefos tool too
		$query = "INSERT INTO notifications (customer, userGroups, type, url, notification, notification_es, notification_ca, notification_it, notification_fr, notification_nl, operator, ids) VALUES ('$cid', '$group', '$type', '$url', '$notification', '$notification_es', '$notification_ca', '$notification_it', '$notification_fr', '$notification_nl', '{$_SESSION['user_id']}', '$notids')";
		echo $query;
		try
		{
			$result = $pdo2->prepare("$query")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
	}
	
	$_SESSION['successMessage'] = "Notification sent succesfully!";
	header("Location: admin.php");
	exit();
