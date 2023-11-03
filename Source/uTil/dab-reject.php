<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';
	require_once '../cOnfig/languages/common.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$user_id = $_GET['user_id'];
	
	// Look up user e-mail
	$query = "SELECT first_name, email FROM users WHERE user_id = $user_id";
	try
	{
		$result = $pdo3->prepare("$query");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$first_name = $row['first_name'];
		$email = $row['email'];

	// Change status to SOCIO
	$query = "UPDATE users SET userGroup = 12 WHERE user_id = $user_id";
	try
	{
		$result = $pdo3->prepare("$query")->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	
	// Send mail to member
	try {
		
		// Send e-mail(s)
		require_once '../PHPMailerAutoload.php';
		
		
		$mail = new PHPMailer(true);
		$mail->SMTPDebug = 0;
		$mail->Debugoutput = 'html';
		$mail->isSMTP();
		$mail->Host = "mail.dabulance.com";
		$mail->SMTPAuth = true;
		$mail->Username = "acw@dabulance.com";
		$mail->Password = "dreWdabulance939_!";
		$mail->SMTPSecure = 'ssl'; 
		$mail->Port = 465;
		$mail->setFrom('acw@dabulance.com', 'Dabulance');
		$mail->addAddress("$email", "$first_name");
		$mail->Subject = "Your Dabulance application has been approved!";
		$mail->isHTML(true);
		$mail->Body = "Dear $first_name,<br /><br />Unfortunately we've had to reject your Dabulance application.<br /><br />Reason: $reason<br /><br />All the best,<br />The Dabulance crew.";
		$mail->send();

	}
	catch (Exception $e)
	{
	   echo $e->errorMessage();
		$_SESSION['errorMessage'] = "ERROR SENDING EMAIL!!!!!!!!";
	}
	
	$_SESSION['successMessage'] = "User rejected successfully!";
	
	header("Location: ../profile.php?user_id=$user_id");