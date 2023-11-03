<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Did this page re-submit with a form? If so, check & store details
	if (isset($_GET['act'])) {
		
		if ($_SESSION['userGroup'] > 1) {
			
			$_SESSION['errorMessage'] = $lang['only-admins'] . "<br /><br />";
			pageStart($lang['pre-registered'], NULL, $memberScript, "pmembership", NULL, $lang['pre-registered'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
			exit();
			
		}
		
		if ($_GET['act'] == 'activate') {
			
			// Query
			$query = "UPDATE systemsettings SET presignup = 1";
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
			
			// Email
			try {
				
			require_once 'PHPMailerAutoload.php';
			
			
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
			$mail->addAddress('info@cannabisclub.systems', 'CCSNube');
			$mail->Subject = "Club activated pre signup: " . $_SESSION['domain'];
			$mail->isHTML(true);
			$mail->Body = "Dear admin,<br /><br />Club " . $_SESSION['domain'] . " just activated pre signups!<br /><br />Please assign them a code and step in touch to inform them.";
			$mail->send();

			}
			catch (Exception $e)
			{
			   echo $e->errorMessage();
			   $_SESSION['errorMessage'] = "Error! Please try again / Por favor intentalo de nuevo.";
			}
			
			// Successmessage
			$_SESSION['successMessage'] = $lang['thanks-for-activating'];
			
			// Redirect
			header("Location: admin.php");
			exit();
			
		} else if ($_GET['act'] == 'reject') {
			
			// Query
			$query = "UPDATE systemsettings SET presignup = 2";
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
			
			// Email
			try {
				
			require_once 'PHPMailerAutoload.php';
			
			
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
			$mail->addAddress('info@cannabisclub.systems', 'CCSNube');
			$mail->Subject = "Club REJECTED pre signup: " . $_SESSION['domain'];
			$mail->isHTML(true);
			$mail->Body = "Dear admin,<br /><br />FYI: Club " . $_SESSION['domain'] . " just rejected pre signups!";
			$mail->send();

			}
			catch (Exception $e)
			{
			   echo $e->errorMessage();
			   $_SESSION['errorMessage'] = "Error! Please try again / Por favor intentalo de nuevo.";
			}
			
			// Successmessage
			$_SESSION['successMessage'] = $lang['thanks-for-rejecting'];
			
			// Redirect
			header("Location: admin.php");
			exit();
			
		} else {
			
			echo "ERROR en codigo. Por favor intentalo de nuevo";
			exit();
			
		}
		
		exit();
		
	}
	
	/***** FORM SUBMIT END *****/
	
	pageStart($lang['pre-registered'], NULL, $memberScript, "pmembership", NULL, $lang['pre-registered'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

	echo "<center>";
	if ($_SESSION['lang'] == 'es') {
		echo <<<EOD
<h1>¡Novedad importante!</h1><br />

<h2>Evita colas en recepción con nuestro Pre Registro.</h2><br />
<p>Una actualización completamente gratis, que minimiza el proceso para registrar nuev@s soci@s.</p><br />

<p><strong>¿Cómo funciona? Te lo explicamos en un tutorial:</strong></p>
 <video width="480" height="360" controls style='border: 3px solid green;'>
  <source src="https://ccsnube.com/registrar.mp4" type="video/mp4">
Your browser does not support this video type, please <a href='https://ccsnube.com/registrar.mp4'>follow this link</a> to see the tutorial.
</video> 
<br /><br />
<p><strong>¿Cómo activarlo? Es fácil, tienes dos opciones:</strong><br />
<p>Envíanos un e-mail con el nombre del club o actívalo con el boton abajo.</p>
<br />
<p>Si tienes preguntas no dudes en contactar <a href='mailto:soporte@cannabisclub.systems'>soporte@cannabisclub.systems</a> o 644 441 092.</p>

EOD;
	} else {
		echo <<<EOD
<h1>Important update!</h1><br />

<h2>Avoid queues at reception with our Pre Sign up.</h2><br />
<p>A completely free update, which minimizes the process to sign up new members.</p><br />

<p><strong>How does it work? We explain it to you in a tutorial:</strong></p>
 <video width="480" height="360" controls style='border: 3px solid green;'>
  <source src="https://ccsnube.com/registrar.mp4" type="video/mp4">
Your browser does not support this video type, please <a href='https://ccsnube.com/registrar.mp4'>follow this link</a> to see the tutorial.
</video> 
<br /><br />
<p><strong>How to activate it? It's easy, you have two options:</strong><br />
<p>Send us an e-mail with the name of the club or activate it in the Administration panel, with a click on PRE SING UPS (under MEMBERS).</p>
<br />
<p>If you have any questions, don’t hesitate to contact <a href='mailto:soporte@cannabisclub.systems'>soporte@cannabisclub.systems</a> or 644 441 092.</p>

EOD;
	}

	
echo "<br /><a href='?act=activate' class='cta1'>{$lang['activate']}</a> <a href='?act=reject' class='cta3' style='background-color: red;'>{$lang['reject']}</a>
</center>";

displayFooter();
