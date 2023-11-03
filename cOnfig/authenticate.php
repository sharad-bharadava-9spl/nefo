<?php

	/* LOKESH BEGIN
    function currentUrl( $trim_query_string = false ) {
	    $pageURL = (isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on') ? "https://" : "http://";
	    $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
	    if( ! $trim_query_string ) {
	        return $pageURL;
	    } else {
	        $url = explode( '?', $pageURL );
	        return $url[0];
	    }
	}

   // $current_page =  basename($_SERVER['PHP_SELF']);   

	$current_page_name = currentUrl(true); 

	if(strpos($siteroot, "ccsnube.com/ttt") !== false){
		$base_url = "http://ccsnube.com/ttt/";
	}else{
		$base_url = $siteroot;
	}

	$current_page = str_replace($base_url, "", $current_page_name);

	LOKESH END */
	
   /*$page_filter = str_replace(array($siteroot,$current_page), array("", ""), $url);

    if($page_filter != ''){
    	$current_page = $page_filter.$current_page;
    }*/

   //echo $current_page;
      
 	/* LOKESH BEGIN
   $getPages = "SELECT id FROM admin_page_details WHERE page_link = '".$current_page."'";

      try
      {
        $page_result = $pdo2->prepare("$getPages");
        $page_result->execute();
      }
      catch (PDOException $e)
      {
          $error = 'Error fetching user: ' . $e->getMessage();
          echo $error;
          exit();
      }
     $page_count = $page_result->rowCount();
     $page_id = 0;

     $accessLevel = null;
     $userGroup = $_SESSION['userGroup'];

     if($userGroup == 1){
     	$accessLevel  = 1;
     }else{

	     if($page_count > 0){
	     	$page_id_row = $page_result->fetch();

	     	$page_id = $page_id_row['id']; 
	     }

	     if($page_id != 0 ){
	     	 $getPageAccess = "SELECT access_level from admin_page_access WHERE page_id =".$page_id;

			    try
			      {
			        $page_access_result = $pdo3->prepare("$getPageAccess");
			        $page_access_result->execute();
			      }
			      catch (PDOException $e)
			      {
			          $error = 'Error fetching user: ' . $e->getMessage();
			          echo $error;
			          exit();
			      }

			      $access_result_row = $page_access_result->fetch();

			      $accessLevelArr = explode(",",$access_result_row['access_level']);

			     $accessLevel = end($accessLevelArr); 

	     }
 	}
 	
	LOKESH END */
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
			handleError("You are not allowed to access this page!", "You are not allowed to access this page!");
			exit();
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