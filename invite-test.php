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

	/*
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
	*/
	
	
	if ($_SESSION['domain'] == 'amagi') {
	
		$mailbody = <<<EOD
	
Estimad@ $first_name $last_name,

<p>Como socio apreciado de AMAGI, nos gustaría invitarte a probar nuestro nuevo sistema de pre-
pedidos. Aquí abajo te facilitamos un acceso al menú y puedes realizar a tu gusto la retirada, recuerda que la cantidad mínima es 50€.

<p>Enlace: <a href='http://www.pediralgo.com'>www.pediralgo.com</a><br />
Usuario: $email<br />
Contraseña: $pwd<br />

<p>¡Mantente a salvo y feliz!

<p>Te deseamos todo lo mejor,<br />
$clubname
<br /><br />
***********************
<br />
<p>Cher $first_name $last_name,

<p>En tant que partenaire de l'AMAGI, nous vous invitons à essayer notre nouvelle pré- commandes. Nous vous donnons ci-dessous un accès au menu et vous pouvez effectuer le retrait comme vous le souhaitez, n'oubliez pas que le montant minimum est de 50€

<p>Lien: <a href='www.pediralgo.com'>www.pediralgo.com</a><br />
Utilisateur: $email<br />
Mot de passe: $pwd<br />

<p>Soyez heureux et en sécurité !

<p>$clubname
<br /><br />
***********************
<br />
<p>Dear $first_name $last_name,

<p>AMAGI-ko kide bezela , aurre-eskaera sistema berria probatzera gonbidatzen zaitugu.
Hemen beheran menurako sarbidea,  zuen gustora aukeraketa egin dezazun. Gogogoan izan kopuru minimoa 50€ dela.


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

	} else if ($_SESSION['domain'] == 'thebulldog' || $_SESSION['domain'] == 'cloud') {
	
		$mailbody = <<<EOD
	
Estimad@ $first_name $last_name,

<p>Como socio apreciado de $clubname, nos gustaría invitarte a probar nuestro nuevo sistema de pre-pedidos.

<p>Haz tu pedido y obtén un 25% de descuento en tu primer pago con Paypal + 1 año de cuota gratis!

<p>Aquí abajo te facilitamos acceso y puedes pedirnos productos.
<p>Enlace: <a href='http://www.pediralgo.com'>www.pediralgo.com</a><br />
Usuario: $email<br />
Contraseña: $pwd<br />

<p>¡Mantente a salvo y feliz!

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

<p>Como socio apreciado de $clubname, nos gustaría invitarte a probar nuestro nuevo sistema de pre-pedidos.

<p>Aquí abajo te facilitamos acceso y puedes pedirnos productos.
<p>Enlace: <a href='http://www.pediralgo.com'>www.pediralgo.com</a><br />
Usuario: $email<br />
Contraseña: $pwd<br />

<p>¡Mantente a salvo y feliz!

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
			pageStart("CCS", NULL, $timePicker, "pprofile", NULL, "CCS", $_SESSION['successMessage'], $_SESSION['errorMessage']);

?>

<form id="registerForm" action="https://nefosolutions.com/smtptest.php" method="POST">

<center>
<input type="hidden" name="user_id" value="<?php echo $user_id; ?>" />
Name: <input type="text" name="name" value="<?php echo $first_name . " " . $last_name; ?>" class='tenDigit defaultinput' placeholder="" /><br />
E-mail: <input type="text" name="email" value="<?php echo $email; ?>" class='tenDigit defaultinput' placeholder="" /><br />
Subject: <input type="text" name="subject" value="<?php echo $lang['invited-to-pre-order']; ?>" class='tenDigit defaultinput' placeholder="" /><br />
Body: <textarea name="body" style='height: 300px; width: 500px;'><?php echo $mailbody; ?></textarea><br />
<button type="submit" class='cta4'>Send</button></center>


</form>
<script>
$(document).ready(function(){
     $("#registerForm").submit();
});
</script>
