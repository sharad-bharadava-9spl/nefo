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
	$opener = $_GET['closer'];
	
	$member = getUser($opener);
	
	$openingtime = date('Y-m-d H:i:s');
	
	
/*		// Compose e-mail
		$headers = "From: CCS <info@cannabisclub.systems>\r\n";
		$headers .= 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$mailSubject = $lang['closeday-closingovv'];


		$mailtoadminHeader = <<<EOD
<span style='color: #444; font-family: Tahoma, Verdana, sans-serif; font-size: 14px;'>{$lang['closeday-dearadmin']} @ Demo Club<br />
{$lang['openday-openingprocedure']} $member at $openingtime<br /><br />
EOD;

		$mailbody = $mailtoadminHeader . $_SESSION['fullMail'];
		
	if ($_SESSION['openingMail'] == 1) {
		
		
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

	*/
	
	if ($_SESSION['noCompare'] != 'true') {
		
		// Make changes to CLOSING table
	  	$query = "UPDATE closing SET dayOpened = 2, dayOpenedBy = $opener, dayOpenedNo = $openingid WHERE closingid = $closingid";
	  	
	  	
		mysql_query($query)
			or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
			
	}
	
	if ($_SESSION['firstOpening'] == 'true') {
		
		$query = "UPDATE opening SET firstDayOpen = 2, firstDayOpenBy = $opener WHERE openingid = $openingid";
		
		mysql_query($query)
			or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
	}
	
		$query = "UPDATE opening SET openedby = $opener, openingtime = '$openingtime' WHERE openingid = $openingid";
		
		mysql_query($query)
			or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
	unset($_SESSION['firstOpening']);
	unset($_SESSION['noCompare']);

	
	// On success: redirect.
	$_SESSION['successMessage'] = $lang['dayopened'];
	header("Location: ../admin.php");
	exit();
		  	
