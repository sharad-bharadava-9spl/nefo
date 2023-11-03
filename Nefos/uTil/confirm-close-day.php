<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';
	require_once '../cOnfig/languages/common.php';
	
	$accessLevel = '3';

	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Retrieve System settings
	getSettings();
	
	// Get info
	$openingid = $_GET['oid'];
	$closingid = $_GET['cid'];
	$closer = $_GET['closer'];
	
	$member = getUser($closer);
	
	$closingtimeReal = date('Y-m-d H:i:s');
	
	tzo();
	$closingtime = date("H:i");
	
		// Compose e-mail
		
		$headers = "From: CCS <info@cannabisclub.systems>\r\n";
		$headers .= 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$mailSubject = $lang['closeday-closingovv'];


		$mailtoadminHeader = <<<EOD
<span style='color: #444; font-family: Tahoma, Verdana, sans-serif; font-size: 14px;'>{$lang['closeday-dearadmin']} @ Demo Club<br />
{$lang['closeday-closingprocedure']} $member at $closingtime<br /><br />
EOD;

		$mailbody = $mailtoadminHeader . $_SESSION['fullMail'];
		
	if ($_SESSION['closingMail'] == 1) {
		
		
		// Query to look up emails
		$selectEmails = "SELECT name, email FROM closing_mails";
	
		$result = mysql_query($selectEmails)
			or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
			
		while ($emailRes = mysql_fetch_array($result)) {
			
			$name = $emailRes['name'];
			$email = $emailRes['email'];
			
			// Send e-mails
			mail ("$name <$email>", $mailSubject, $mailbody, $headers);
		}
		
	}

	// Send e-mail to sysadmin
	mail ("CCS Admin <andreas@cannabisclub.systems>", $mailSubject, $mailbody, $headers);

	

	
	if ($_SESSION['openAndClose'] == 2) {
		
		if ($_SESSION['noCompare'] != 'true') {
	
		 	$query = "UPDATE closing SET dayOpened = 2, dayOpenedBy = $closer WHERE closingid = $openingid";
	
			mysql_query($query)
				or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
				
		 	$query = "UPDATE closing SET closingtime = '$closingtimeReal', currentClosing = '0' WHERE closingid = $closingid";
	
			mysql_query($query)
				or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
				
		} else {
			
		 	$query = "UPDATE closing SET dayClosed = 2, dayClosedBy = $closer, currentClosing = '0', closingtime = '$closingtimeReal' WHERE closingid = $closingid";
	
			mysql_query($query)
				or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
			
		}
		
	} else if ($_SESSION['openAndClose'] == 3) {
		
		// Make changes to OPENING table
	  	$query = "UPDATE opening SET dayClosed = 2, dayClosedBy = $closer WHERE openingid = $openingid";
	  	
		mysql_query($query)
			or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
	 	$query = "UPDATE closing SET currentClosing = '0', closingtime = '$closingtimeReal' WHERE closingid = $closingid";

		mysql_query($query)
			or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
			
	}
	


	// On success: redirect.
	$_SESSION['successMessage'] = $lang['dayclosed'];
	header("Location: ../admin.php");
	exit();
		  	
