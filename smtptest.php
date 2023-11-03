<?php

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	$accessLevel = '3';

	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Retrieve System settings
	getSettings();
	
	echo "HERE WE GO:<br />";


	try {
		
		// Send e-mail(s)
		require_once 'PHPMailerAutoload.php';
	
		$mail = new PHPMailer(true);
		$mail->SMTPDebug = 2;
		$mail->Timeout  =   10;
		$mail->Debugoutput = 'html';
		$mail->isSMTP();
		$mail->Host = "mail.pediralgo.com";
		$mail->SMTPAuth = true;
		$mail->Username = "info@pediralgo.com";
		$mail->Password = "CaRi@*P^rKaY";
		$mail->SMTPSecure = 'ssl';
		$mail->Port = 465;
		$mail->addAddress("andreas@nefosolutions.com", "Andreas Nilsen");
		$mail->setFrom('info@pediralgo.com', "Club");
		$mail->Subject = "Just a test";
		$mail->isHTML(true);
		$mail->Body = "Testing mail sending";
		$mail->send();

	}
	catch (Exception $e)
	{
			   echo $e->errorMessage();
			   echo "ERROR";
			   exit();
	}
	
	echo "OK";
