<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);

	// Get the card / user ID
	if ($_POST['cardid'] != '') {
		
		$cardid = $_POST['cardid'];
		
		
		if ($cardid == '') {
			
				$_SESSION['errorMessage'] = $lang['scan-error'];
			
		} else {
		
			// Query to look up user
			$rowCount = $pdo3->query("SELECT COUNT(user_id) FROM users WHERE cardid = '$cardid'")->fetchColumn();
			
			if ($rowCount == 0) {
				// Query to look up user
				$rowCount = $pdo3->query("SELECT COUNT(user_id) FROM users WHERE cardid2 = '{$cardid}'")->fetchColumn();
				
				if ($rowCount == 0) {
					// Query to look up user
					$rowCount = $pdo3->query("SELECT COUNT(user_id) FROM users WHERE cardid3 = '{$cardid}'")->fetchColumn();
					
					if ($rowCount == 0) {
				   		handleError($lang['error-keyfob'],"");
					} else {
						$result = $pdo3->prepare("SELECT user_id FROM users WHERE cardid3 = '{$cardid}'");
					}
					
				} else {
					$result = $pdo3->prepare("SELECT user_id FROM users WHERE cardid2 = '{$cardid}'");
				}
	
				
			} else {
				$result = $pdo3->prepare("SELECT user_id FROM users WHERE cardid = '{$cardid}'");
			}
			
					
			$result->execute();
			
			$row = $result->fetch();
				$aval = $row['user_id'];
				
			// Check if chip is registered more than once
			if ($rowCount > 1) {
				
				$_SESSION['errorMessage'] = $lang['chip-registered-more-than-once'];
				header("Location: duplicate-chip.php?cardid=$cardid");
				exit();
			
			}
		}
	}

	
	if (isset($_GET['user_id'])) {
		$user_id= $_GET['user_id'];
		$aval = $_GET['avalee'];
	} else {
		$user_id = $_POST['user_id'];
	}
	
	if (isset($_GET['twoavals'])) {
		
		$updateAval = "UPDATE users SET friend2 = $aval WHERE user_id = $user_id";
		
	} else {
		
		$updateAval = "UPDATE users SET friend = $aval WHERE user_id = $user_id";
		
	}
	echo $updateAval;
	

	try
	{
		$result = $pdo3->prepare("$updateAval")->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	
	$_SESSION['successMessage'] = $lang['aval-added'];
	header("Location: profile.php?user_id=" . $user_id);
	exit();
	
	
