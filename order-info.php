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
			$query = "UPDATE systemsettings SET setting4 = 1";
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
			$mail->Subject = "Club activated the new ORDER button: " . $_SESSION['domain'];
			$mail->isHTML(true);
			$mail->Body = "Dear admin,<br /><br />Club " . $_SESSION['domain'] . " just activated the new ORDER button.";
			$mail->send();

			}
			catch (Exception $e)
			{
			   echo $e->errorMessage();
			   //$_SESSION['errorMessage'] = "Error! Please try again / Por favor intentalo de nuevo.";
			}
			
			// Successmessage
			if ($_SESSION['lang'] == 'es') {
				$_SESSION['successMessage'] = "Gracias por activar el botón PEDIDOS!";			
			} else {
				$_SESSION['successMessage'] = "Thank you for activating the new ORDER button!";
			}
			
			// Redirect
			header("Location: index.php");
			exit();
			
		} else if ($_GET['act'] == 'reject') {
			
			// Query
			$query = "UPDATE systemsettings SET setting4 = 2";
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
			$mail->Subject = "Club REJECTED the new ORDER button: " . $_SESSION['domain'];
			$mail->isHTML(true);
			$mail->Body = "Dear admin,<br /><br />FYI: Club " . $_SESSION['domain'] . " just rejected the new ORDER button.";
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
				
				$_SESSION['successMessage'] = "Gracias. Hemos deshabilitado el botón PEDIDOS, y no vuelve a aparecer en tu sistema.";
							
			} else {
				
				$_SESSION['successMessage'] = "Thank you. We have disabled the new ORDERS button and you will no longer see it in your system.";
				
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
  BOTÓN PEDIDOS
  </div>
  
   <div class='boxcontent'>

<p>El nuevo botón PEDIDOS facilita a los clubes registrar pedidos, de forma diferente al dispensario regular.
<p>Al hacer clic en este botón, funciona exactamente igual que el botón normal de ‘Dispensar’, con tres diferencias importantes:

<ol>
 <li>Durante el Check out, ahora podrás elegir entre ENTREGA y RECOGIDA<br />&nbsp;</li>
 <li>Para clubes que usan el sistema de Saldo: puedes registrar un pedido incluso si el socio no tiene suficiente saldo. Esto significa que su crédito entrará en negativo. Como tal, cuando el socio recoge el pedido, se debe registrar una Aportación correspondiente.<br />&nbsp;</li>
 <li>Estos pedidos no aparecerán en la pantalla "Dispensario" en el panel de Administración. En su lugar, aparecerán en una nueva lista llamada "Pedidos". En esta lista, puedes marcar los pedidos como "cumplidos" haciendo clic en el botón "No" en la columna "Cumplidos", y el "No" se convertirá en una marca de verificación verde:<br />&nbsp;</li>
</ol>
<img src="images/orders-es.png" />  
    <p><strong>Al usar este nuevo botón ORDEN, aún puedes realizar un seguimiento preciso del stock y el historial de dispensario de los socios.</strong><br />&nbsp;
    <p>Si deseas comenzar a usar este módulo, seleccione SÍ a continuación. Si no está interesado, haga clic en NO y el botón desaparecerá.
	<p>Si tienes preguntas no dudes en contactar <a href='mailto:soporte@cannabisclub.systems'>soporte@cannabisclub.systems</a> o 644 441 092.</p>
  </div>
  </div>

EOD;
	} else {
		echo <<<EOD
 <div id='mainbox'>
  <div id='mainboxheader'>
  ORDERS BUTTON
  </div>
  
   <div class='boxcontent'>

The new ORDERS button is a way for clubs to register orders, differently than regular dispenses.
Clicking this button works in the exact same way as the normal dispense button, with three important differences:

<ol>
 <li>During checkout, you now have the option to choose between DELIVERY and COLLECTION.<br />&nbsp;</li>
 <li>For clubs using the Credit system: You can register an order even if the member doesn't have enough credit. This means his credit will go into negative. As such, when the member collects the order, a corresponding Donation should be registered.<br />&nbsp;</li>
 <li>These orders will not appear on the "Dispenses" screen in your admin panel. Instead, they will appear in a new list called "Orders". In this list, you can mark the orders as "fulfilled" by clicking the "No" button in the "Fulfilled" column, and the "No" will turn into a green checkmark:<br />&nbsp;</li>
</ol>
<img src="images/orders.png" />  

    <p><strong>By using this new ORDER button, you can still keep accurate track of your stock and your members' dispense history.</strong><br />&nbsp;
    <p>If you want to start using this module, select YES below. Should you not be interested, click NO, and the button goes away.
	<p>If you have any questions, don’t hesitate to contact <a href='mailto:soporte@cannabisclub.systems'>soporte@cannabisclub.systems</a> or 644 441 092.</p>
  </div>
  </div>

EOD;
	}

	
echo "<center><br /><a href='?act=activate' class='cta1'>{$lang['global-yes']}</a> <a href='?act=reject' class='cta2' style='background-color: red;'>{$lang['global-no']}</a>
</center>";

displayFooter();
