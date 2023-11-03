<?php

	function authorizeUser($accessLevel = NULL) {
		
		global $pdo3;
		
		// Has the session been set?
		if ((!isset($_SESSION['user_id'])) || (!strlen($_SESSION['user_id']) > 0 )) {
			$_SESSION['errorMessage'] = "Restricted area. Please login below.";
			header("Location: index.php");
			exit();
		}
		
		// Check for domain eligibility
		try
		{
			$result = $pdo3->prepare("SELECT domain FROM systemsettings");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$siteDomain = $row['domain'];
			
		if ($siteDomain != $_SESSION['domain'] && $_SESSION['domain'] != 'superuser') {
   			handleError("You are not allowed access to this site!","User trying to access site(s) outside his domain!");
			exit();
		}
		
		
		// If no accesslevel is passed in, above auth is sufficient
		if ((is_null($accessLevel)) || (empty($accessLevel))) {
			return;
		}

		// If accesslevel is passed in, check for eligibility
		$user_id = $_SESSION['user_id'];
		
		// Backdoor access - skip verification
		
		if (($_SESSION['user_id'] == 999999) && ($_SESSION['memberno'] == 999999) && ($_SESSION['first_name'] == 'CCS')) {
			
		} else {
				
			try
			{
				$result = $pdo3->prepare("SELECT u.first_name, u.last_name, u.userGroup, ug.groupName FROM users u, usergroups ug WHERE u.user_id=$user_id AND u.userGroup <= $accessLevel AND u.userGroup = ug.userGroup");
				$result->execute();
				$data = $result->fetchAll();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
			
			if (!$data) {
				
	   			handleError("You are not authorized to see that page.","User has too low access level.");
				exit();
				
			} else {
				
				// Update userGroup in Session, in case of changes to user level.
				$_SESSION['userGroup'] = $data[0]['userGroup'];
				
			}
			
		}
			
	}
	
	function authorizeStation($station = NULL) {
		
		// If no accesslevel is passed in, above auth is sufficient
		if ((is_null($station)) || (empty($station))) {
			return;
		}
		
		$wSAccess = $_SESSION['workStationAccess'];
		
		// Grant administrators full access
		if ($_SESSION['userGroup'] > 1) {
		
			if ($station == 'reception') {
				if ($wSAccess == 1 || $wSAccess == 6 || $wSAccess == 11 || $wSAccess == 16) {
				} else {
		   			handleError("No tienes acceso para ver el puesto Recepción.","");
					exit();
				}
			} else if ($station == 'bar') {
				if ($wSAccess == 5 || $wSAccess == 6 || $wSAccess == 15 || $wSAccess == 16) {
				} else {
		   			handleError("No tienes acceso para ver el puesto Bar.","");
					exit();
				}
			} else if ($station == 'dispensary') {
				if ($wSAccess == 10 || $wSAccess == 11 || $wSAccess == 15 || $wSAccess == 16) {
				} else {
		   			handleError("No tienes acceso para ver el puesto Dispensario.","");
					exit();
				}
			}
		}
	}
	

	
?>