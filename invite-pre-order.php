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
	
	if ($_SESSION['domain'] == 'amagi') {
	
		$mailbody = <<<EOD
	
Estimad@ $first_name $last_name,

<p>Como socio apreciado de AMAGI, nos gustar�a invitarte a probar nuestro nuevo sistema de pre-
pedidos. Aqu� abajo te facilitamos un acceso al men� y puedes realizar a tu gusto la retirada, recuerda que la cantidad m�nima es 50�.

<p>Enlace: <a href='http://www.pediralgo.com'>www.pediralgo.com</a><br />
Usuario: $email<br />
Contrase�a: $pwd<br />

<p>�Mantente a salvo y feliz!

<p>Te deseamos todo lo mejor,<br />
$clubname
<br /><br />
***********************
<br />
<p>Cher $first_name $last_name,

<p>En tant que partenaire de l'AMAGI, nous vous invitons � essayer notre nouvelle pr�- commandes. Nous vous donnons ci-dessous un acc�s au menu et vous pouvez effectuer le retrait comme vous le souhaitez, n'oubliez pas que le montant minimum est de 50�

<p>Lien: <a href='www.pediralgo.com'>www.pediralgo.com</a><br />
Utilisateur: $email<br />
Mot de passe: $pwd<br />

<p>Soyez heureux et en s�curit� !

<p>$clubname
<br /><br />
***********************
<br />
<p>Dear $first_name $last_name,

<p>AMAGI-ko kide bezela , aurre-eskaera sistema berria probatzera gonbidatzen zaitugu.
Hemen beheran menurako sarbidea,  zuen gustora aukeraketa egin dezazun. Gogogoan izan kopuru minimoa 50� dela.


<p>Sarbidea: <a href='www.pediralgo.com'>www.pediralgo.com</a><br />
Erabiltzailea: $email<br />
Pasahitza: $pwd<br />

<p>Pozik  izan eta zihurrean

<p>
$clubname
<br /><br />
***********************
<br />
<p>Dear $first_name $last_name,

<p>As an appreciated member of $clubname, we would like to invite you to our new pre-ordering system.

<p>Here you can log in and order products from us:

<p>Link: <a href='www.pediralgo.com'>www.pediralgo.com</a><br />
Username: $email<br />
Password: $pwd<br />

<p>Happy Smoking!

<p>All the best,<br />
$clubname
EOD;

	} else if ($_SESSION['domain'] == 'thebulldog' || $_SESSION['domain'] == 'cloud' || $_SESSION['domain'] == 'ccstest') {
	
		$mailbody = <<<EOD
	
Estimad@ $first_name $last_name,

<p>Como socio apreciado de $clubname, nos gustar�a invitarte a probar nuestro nuevo sistema de pre-pedidos.

<p>Haz tu pedido y obt�n un 25% de descuento en tu primer pago con Paypal + 1 a�o de cuota gratis!

<p>Aqu� abajo te facilitamos acceso y puedes pedirnos productos.
<p>Enlace: <a href='http://www.pediralgo.com'>www.pediralgo.com</a><br />
Usuario: $email<br />
Contrase�a: $pwd<br />

<p>�Mantente a salvo y feliz!

<p>Te deseamos todo lo mejor,<br />
$clubname
<br /><br />
***********************
<br />
<p>Dear $first_name $last_name,

<p>As an appreciated member of $clubname, we would like to invite you to our new pre-ordering system.

<p>Place your order and get 25% OFF your first Pay Pal payment + 1 year free membership!

<p>Here you can log in and order products from us:

<p>Link: <a href='www.pediralgo.com'>www.pediralgo.com</a><br />
Username: $email<br />
Password: $pwd<br />

<p>Happy Smoking!

<p>All the best,<br />
$clubname

EOD;
		
	} else {
	
		$mailbody = <<<EOD
	
Estimad@ $first_name $last_name,

<p>Como socio apreciado de $clubname, nos gustar�a invitarte a probar nuestro nuevo sistema de pre-pedidos.

<p>Aqu� abajo te facilitamos acceso y puedes pedirnos productos.
<p>Enlace: <a href='http://www.pediralgo.com'>www.pediralgo.com</a><br />
Usuario: $email<br />
Contrase�a: $pwd<br />

<p>�Mantente a salvo y feliz!

<p>Te deseamos todo lo mejor,<br />
$clubname
<br /><br />
***********************
<br />
<p>Dear $first_name $last_name,

<p>As an appreciated member of $clubname, we would like to invite you to our new pre-ordering system.

<p>Here you can log in and order products from us:

<p>Link: <a href='www.pediralgo.com'>www.pediralgo.com</a><br />
Username: $email<br />
Password: $pwd<br />

<p>Happy Smoking!

<p>All the best,<br />
$clubname

EOD;
		
	}

/*
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
		$mail->Subject = $lang['invited-to-pre-order'];
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
			$mail->Subject = $lang['invited-to-pre-order'];
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
				$mail->Subject = $lang['invited-to-pre-order'];
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
	*/
	
	// On success: redirect.
	$_SESSION['successMessage'] = $lang['thank-you-invited'];
	header("Location: profile.php?user_id=$user_id");
