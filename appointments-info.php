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
			$query = "UPDATE systemsettings SET appointments = 1";
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
			$mail->Subject = "Club accepted appointments module: " . $_SESSION['domain'];
			$mail->isHTML(true);
			$mail->Body = "Dear admin,<br /><br />FYI: Club " . $_SESSION['domain'] . " just accepted the new Appointments module.";
			$mail->send();

			}
			catch (Exception $e)
			{
			   echo $e->errorMessage();
			   //$_SESSION['errorMessage'] = "Error! Please try again / Por favor intentalo de nuevo.";
			}
			
			// Successmessage
			if ($_SESSION['lang'] == 'es') {
				$_SESSION['successMessage'] = "Gracias por activar el botón CITAS!";			
			} else {
				$_SESSION['successMessage'] = "Thank you for activating the new APPOINTMENTS button!";
			}
			
			// Redirect
			header("Location: index.php");
			exit();
			
		} else if ($_GET['act'] == 'reject') {
			
			// Query
			$query = "UPDATE systemsettings SET appointments = 2";
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
			$mail->Subject = "Club REJECTED appointments module: " . $_SESSION['domain'];
			$mail->isHTML(true);
			$mail->Body = "Dear admin,<br /><br />FYI: Club " . $_SESSION['domain'] . " just rejected the new Appointments module.";
			$mail->send();

			}
			catch (Exception $e)
			{
			   echo $e->errorMessage();
			   //$_SESSION['errorMessage'] = "Error! Please try again / Por favor intentalo de nuevo.";
			}
			
			// Successmessage
			// Successmessage
			if ($_SESSION['lang'] == 'es') {
				
				$_SESSION['successMessage'] = "Gracias. Hemos deshabilitado el botón CITAS, y no vuelve a aparecer en tu sistema.";
							
			} else {
				
				$_SESSION['successMessage'] = "Thank you. We have disabled the new APPOINTMENTS button and you will no longer see it in your system.";
				
			}
			
			// Redirect
			header("Location: index.php");
			exit();
			
		} else {
			
			echo "ERROR en codigo. Por favor intentalo de nuevo";
			exit();
			
		}
		
		exit();
		
	}
	
	/***** FORM SUBMIT END *****/
	
	pageStart("CCS", NULL, $memberScript, "pmembership", NULL, "", $_SESSION['successMessage'], $_SESSION['errorMessage']);

	if ($_SESSION['lang'] == 'es') {
		echo <<<EOD
 <div id='mainbox'>
  <div id='mainboxheader'>
  CITAS DE SOCIO
  </div>
  
   <div class='boxcontent'>

<p>Ahora puedes habilitar el modulo CITAS para tu club. Esto te permite invitar socios por e-mail, para que inicien una sesión en un área privado para socios y pidan citas.<br /><br />
Es una manera facil para controlar las visitas del socios en tu club!<br /><br />

<p>El proceso es el siguiente:

<ol>
 <li>Se envía un e-mail al socio con los detalles para iniciar una sesión (enlace y contraseña).</li>
 <li>El socio accede al área privado.</li>
 <li>El socio puede pedir una cita en una fecha y hora concreta.</li>
 <li>Recibirás un correo electrónico notificándote del petición. También puedes ver todas las citas en un nuevo listado de CITAS en el panel de administración.</li>
 <li>Puedes aceptar, rechazar o cambiar el petición - y el socio recibe un e-mail con este información.</li>
</ol>    
    <p>Si deseas comenzar a usar este módulo, seleccione SÍ a continuación. Si no está interesado, haga clic en NO y el botón desaparecerá.
	<p>Si tienes preguntas no dudes en contactar <a href='mailto:soporte@cannabisclub.systems'>soporte@cannabisclub.systems</a> o 644 441 092.</p>
  </div>
  </div>

EOD;
	} else {
		echo <<<EOD
 <div id='mainbox'>
  <div id='mainboxheader'>
  MEMBER APPOINTMENTS
  </div>
  
   <div class='boxcontent'>
   
The new APPOINTMENTS module is a way for your members to book an appointment before coming to the club.<br /><br />
This makes it easy for you to control the number of visitors in your club.<br /><br />

The process goes as follows:<br /><br />

<ol>
 <li>An e-mail is sent to the member with login details (link and password).</li>
 <li>The member logs in.</li>
 <li>The member can book an appointment by choosing date and time.</li>
 <li>You receive an e-mail, notifying you of the request. You can also see all appointments in the APPOINTMENTS section in your administration panel.</li>
 <li>You have the option to accept, reject or modify the appointment - and the member will get notified accordingly.</li>
</ol>

    <p>If you want to start using this module, select YES below. Should you not be interested, click NO, and the button goes away.
	<p>If you have any questions, don’t hesitate to contact <a href='mailto:soporte@cannabisclub.systems'>soporte@cannabisclub.systems</a> or 644 441 092.</p>
  </div>
  </div>

EOD;
	}

	
echo "<center><br /><a href='?act=activate' class='cta1'>{$lang['global-yes']}</a> <a href='?act=reject' class='cta2' style='background-color: red;'>{$lang['global-no']}</a>
</center>";

displayFooter();
