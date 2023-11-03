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
	$this_directory = basename(dirname(__DIR__));
	$openingid = $_GET['oid'];
	$closingid = $_GET['cid'];
	$closer = $_GET['closer'];
	
	$member = getUser($closer);
	
	$closingtimeReal = date('Y-m-d H:i:s');
	
	tzo();
	$closingtime = date("H:i");
	
		$mailtoadminHeader = <<<EOD
<span style='color: #444; font-family: Tahoma, Verdana, sans-serif; font-size: 14px;'>{$lang['closeday-dearadmin']} @ $this_directory<br />
{$lang['closeday-closingprocedure']} $member at $closingtime<br /><br />
EOD;
		
		
	if ($_SESSION['closingMail'] == 1) {
		
		
		// Query to look up emails
		$selectEmails = "SELECT name, email FROM closing_mails";
		try
		{
			$result = $pdo3->prepare("$selectEmails");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user1: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
	while ($emailRes = $result->fetch()) {
			
			$name = $emailRes['name'];
			$email = $emailRes['email'];
			
			try {
				
			// Send e-mail(s)
			require_once '../PHPMailerAutoload.php';
			
			
			$mail = new PHPMailer(true);
			$mail->SMTPDebug = 0;
			$mail->Debugoutput = 'html';
			$mail->isSMTP();
			$mail->Host = "mail.cannabisclub.systems";
			$mail->SMTPAuth = true;
			$mail->Username = "info@cannabisclub.systems";
			$mail->Password = "Insjormafon9191";
			$mail->SMTPSecure = 'ssl'; 
			$mail->Port = 465;
			$mail->setFrom('info@cannabisclub.systems', 'CCSNube');
			$mail->addAddress("$email", "$name");
			$mail->Subject = $lang['closeday-closingovv'];
			$mail->isHTML(true);
			$mail->Body = $mailtoadminHeader . $_SESSION['fullMail'];
			$mail->send();

			}
			catch (Exception $e)
			{
			   echo $e->errorMessage();
			   $_SESSION['errorMessage'] = "HAU";
			}
			
		}
		
	}

	
	if ($_SESSION['openAndClose'] == 2) {
		
		if ($_SESSION['noCompare'] != 'true') {
	
		 	$query = "UPDATE closing SET dayOpened = 2, dayOpenedBy = $closer, currentClosing = 0 WHERE closingid = $openingid";
			try
			{
				$result = $pdo3->prepare("$query")->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user2: ' . $e->getMessage();
					echo $error;
					exit();
			}
				
		 	$query = "UPDATE closing SET closingtime = '$closingtimeReal', currentClosing = '0' WHERE closingid = $closingid";
			try
			{
				$result = $pdo3->prepare("$query")->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user3: ' . $e->getMessage();
					echo $error;
					exit();
			}
				
		} else {
			
		 	$query = "UPDATE closing SET dayClosed = 2, dayClosedBy = $closer, currentClosing = 0, closingtime = '$closingtimeReal' WHERE closingid = $closingid";
			try
			{
				$result = $pdo3->prepare("$query")->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user4: ' . $e->getMessage();
					echo $error;
					exit();
			}
			
		}
		
	} else if ($_SESSION['openAndClose'] > 2) {
		
		// Make changes to OPENING table
	  	$query = "UPDATE opening SET dayClosed = 2, dayClosedBy = $closer WHERE openingid = $openingid";
		try
		{
			$result = $pdo3->prepare("$query")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user5: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
	 	$query = "UPDATE closing SET currentClosing = '0', closingtime = '$closingtimeReal' WHERE closingid = $closingid";
		try
		{
			$result = $pdo3->prepare("$query")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user6: ' . $e->getMessage();
				echo $error;
				exit();
		}
	}

	// On success: redirect.
	$_SESSION['successMessage'] = $lang['dayclosed'];
	header("Location: ../admin.php");