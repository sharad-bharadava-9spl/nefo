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

		pageStart("Passport / ID card scan", NULL, $timePicker, "pprofile", NULL, "Passport / ID card scan", $_SESSION['successMessage'], $lang['invalid-email'] . ":<span class='yellow'> $email </span>" . $lang['invalid-email2']);
		exit();

	} else {
		
		$query = "SELECT services, minorder, clubname, clubemail, clubphone FROM systemsettings";
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
			$services = $row['services'];
			$minorder = $row['minorder'];
			$clubname = $row['clubname'];
			$clubemail = $row['clubemail'];
			$clubphone = $row['clubphone'];
		
	}
	
	// Generate and crypt password
	$pwd = generateRandomString(8);
	$newpw = crypt($pwd, $email);

	$query = "UPDATE users SET userPass = '$newpw', citainvited = '1' WHERE user_id = $user_id";
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
	
Estimad@ $first_name $last_name,

<p>Como socio apreciado de $clubname, nos gustar�a invitarte a probar nuestro nuevo sistema para pedir citas.

<p>Aqu� abajo te facilitamos acceso y puedes pedirnos productos.
<p>Enlace: <a href='http://www.pedircita.club'>www.pedircita.club</a><br />
Usuario: $email<br />
Contrase�a: $pwd<br />

<p>�Mantente a salvo y feliz!

<p>Te deseamos todo lo mejor,<br />
$clubname
<br /><br />
***********************
<br />
<p>Dear $first_name $last_name,

<p>As an appreciated member of $clubname, we would like to invite you to our new system for booking appointments.

<p>Here you can log in and order products from us:

<p>Link: <a href='www.pedircita.club'>www.pedircita.club</a><br />
Username: $email<br />
Password: $pwd<br />

<p>Happy Smoking!

<p>All the best,<br />
$clubname

EOD;
		


	try {
		
		// Send e-mail(s)
		require_once 'PHPMailerAutoload.php';
	
		$mail = new PHPMailer(true);
		$mail->CharSet = 'UTF-8';
		$mail->SMTPDebug = 0;
		$mail->Debugoutput = 'html';
		$mail->isSMTP();
		$mail->Host = "mail.pediralgo.com";
		$mail->SMTPAuth = true;
		$mail->Username = "info@pediralgo.com";
		$mail->Password = "CaRi@*P^rKaY";
		$mail->SMTPSecure = 'ssl';
		$mail->Port = 465;
		$mail->addReplyTo("$clubemail", "$clubname");
		$mail->addAddress("$email", "$first_name $last_name");
		$mail->setFrom('info@pediralgo.com', "$clubname");
		$mail->Subject = $lang['invited-to-appointment'];
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
			$mail->CharSet = 'UTF-8';
			$mail->SMTPDebug = 0;
			$mail->Debugoutput = 'html';
			$mail->isSMTP();
			$mail->Host = "mail.pediralgo.com";
			$mail->SMTPAuth = true;
			$mail->Username = "info@pediralgo.com";
			$mail->Password = "CaRi@*P^rKaY";
			$mail->SMTPSecure = 'ssl';
			$mail->Port = 465;
			$mail->addReplyTo("$clubemail", "$clubname");
			$mail->addAddress("$email", "$first_name $last_name");
			$mail->setFrom('info@pediralgo.com', "$clubname");
			$mail->Subject = $lang['invited-to-appointment'];
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
				$mail->CharSet = 'UTF-8';
				$mail->SMTPDebug = 0;
				$mail->Debugoutput = 'html';
				$mail->isSMTP();
				$mail->Host = "mail.pediralgo.com";
				$mail->SMTPAuth = true;
				$mail->Username = "info@pediralgo.com";
				$mail->Password = "CaRi@*P^rKaY";
				$mail->SMTPSecure = 'ssl';
				$mail->Port = 465;
				$mail->addReplyTo("$clubemail", "$clubname");
				$mail->addAddress("$email", "$first_name $last_name");
				$mail->setFrom('info@pediralgo.com', "$clubname");
				$mail->Subject = $lang['invited-to-appointment'];
				$mail->isHTML(true);
				$mail->Body = $mailbody;
				$mail->send();
		
			}
			catch (Exception $e)
			{
			   echo $e->errorMessage();
			   $_SESSION['errorMessage'] = "ERROR SENDING MAIL. Please try again.<br />ERROR ENVIANDO EMAIL. Intentalo de nuevo.";
			   header("Location: profile.php?user_id=$user_id");
			   exit();
			}
		}
	}
	
	// On success: redirect.
	$_SESSION['successMessage'] = $lang['thank-you-invited'];
	header("Location: profile.php?user_id=$user_id");
