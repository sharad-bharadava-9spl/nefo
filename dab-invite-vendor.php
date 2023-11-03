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
	
	// Get info
	$user_id = $_GET['user_id'];
	$domain = $_SESSION['domain'];
	
	echo $domain; exit();
	
	// Look up user info
	$query = "SELECT first_name, last_name, email FROM users WHERE user_id = $user_id";
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
		$last_name = $row['last_name'];
		$email = $row['email'];	
	
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

		pageStart("E-mail error", NULL, $timePicker, "pprofile", NULL, "E-mail error", $_SESSION['successMessage'], $lang['invalid-email'] . ":<span class='yellow'> $email </span>" . $lang['invalid-email2']);
		
		exit();

	}
	
	// Generate and crypt password
	$pwd = generateRandomString(8);
	$newpw = crypt($pwd, $email);

	$query = "UPDATE users SET userPass = '$newpw', invited = '1' WHERE user_id = $user_id";
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
	
	$query = "INSERT INTO `users` (`email`, `password`, `domain`) VALUES ('$email', '$newpw', '$domain')";
	try
	{
		$result = $pdo->prepare("$query")->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	
	$mailbody = <<<EOD
	

<br />
<p>Dear $first_name $last_name,

<p>As an appreciated member of $clubname, we would like to invite you to our new pre-ordering system.

<p>Here you can log in and order products from us:

<p>Link: <a href='www.dabulance.com/vendor'>www.dabulance.com/vendor</a><br />
Username: $email<br />
Password: $pwd<br />

<p>Happy Smoking!

<p>All the best,<br />
Dabulance

EOD;


	try {
		
		// Send e-mail(s)
		require_once 'PHPMailerAutoload.php';
	
		$mail = new PHPMailer(true);
		$mail->SMTPDebug = 0;
		$mail->Debugoutput = 'html';
		$mail->isSMTP();
		$mail->Host = "mail.dabulance.com";
		$mail->SMTPAuth = true;
		$mail->Username = "acw@dabulance.com";
		$mail->Password = "2beorNOT2be2020!";
		$mail->SMTPSecure = 'ssl'; 
		$mail->Port = 465;
		$mail->setFrom('acw@dabulance.com', 'Dabulance');
		$mail->addAddress("$email", "$first_name");
		$mail->Subject = "You've been invited to become a Vendor!";
		$mail->isHTML(true);
		$mail->Body = $mailbody;
		$mail->send();

	}
	catch (Exception $e)
	{
		sleep(3);
		try {
		
			// Send e-mail(s)
			require_once 'PHPMailerAutoload.php';
		
			$mail = new PHPMailer(true);
			$mail->SMTPDebug = 0;
			$mail->Debugoutput = 'html';
			$mail->isSMTP();
			$mail->Host = "mail.dabulance.com";
			$mail->SMTPAuth = true;
			$mail->Username = "acw@dabulance.com";
			$mail->Password = "2beorNOT2be2020!";
			$mail->SMTPSecure = 'ssl'; 
			$mail->Port = 465;
			$mail->setFrom('acw@dabulance.com', 'Dabulance');
			$mail->addAddress("$email", "$first_name");
			$mail->Subject = "You've been invited to become a Vendor!";
			$mail->isHTML(true);
			$mail->Body = $mailbody;
			$mail->send();
	
		}
		catch (Exception $e)
		{
			sleep(3);
			try {
			
				// Send e-mail(s)
				require_once 'PHPMailerAutoload.php';
			
				$mail = new PHPMailer(true);
				$mail->SMTPDebug = 0;
				$mail->Debugoutput = 'html';
				$mail->isSMTP();
				$mail->Host = "mail.dabulance.com";
				$mail->SMTPAuth = true;
				$mail->Username = "acw@dabulance.com";
				$mail->Password = "2beorNOT2be2020!";
				$mail->SMTPSecure = 'ssl'; 
				$mail->Port = 465;
				$mail->setFrom('acw@dabulance.com', 'Dabulance');
				$mail->addAddress("$email", "$first_name");
				$mail->Subject = "You've been invited to become a Vendor!";
				$mail->isHTML(true);
				$mail->Body = $mailbody;
				$mail->send();
		
			}
			catch (Exception $e)
			{
			   echo $e->errorMessage();
			   $_SESSION['errorMessage'] = "ERROR SENDING MAIL. Please try again.";
			   header("Location: profile.php?user_id=$user_id");
			   exit();
			}
		}
	}
	
	// On success: redirect.
	$_SESSION['successMessage'] = $lang['thank-you-invited'];
	header("Location: profile.php?user_id=$user_id");
