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
			$query = "UPDATE systemsettings SET setting3 = 1";
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
			$mail->Subject = "Club activated PRE-ORDERS: " . $_SESSION['domain'];
			$mail->isHTML(true);
			$mail->Body = "Dear admin,<br /><br />Club " . $_SESSION['domain'] . " just activated PRE-ORDERS.";
			$mail->send();

			}
			catch (Exception $e)
			{
			   echo $e->errorMessage();
			   //$_SESSION['errorMessage'] = "Error! Please try again / Por favor intentalo de nuevo.";
			}
						
			// Redirect
			header("Location: pre-order-1.php");
			exit();
			
		} else if ($_GET['act'] == 'reject') {
			
			// Query
			$query = "UPDATE systemsettings SET setting3 = 2";
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
			$mail->Subject = "Club REJECTED PRE-ORDERS: " . $_SESSION['domain'];
			$mail->isHTML(true);
			$mail->Body = "Dear admin,<br /><br />FYI: Club " . $_SESSION['domain'] . " just rejected PRE-ORDERS.";
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
				
				$_SESSION['successMessage'] = "Gracias. Hemos deshabilitado PRE-PEDIDOS, y no vuelve a aparecer en tu sistema.";
							
			} else {
				
				$_SESSION['successMessage'] = "Thank you. We have disabled PRE-ORDERS and you will no longer see it in your system.";
				
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
PRE-PEDIDOS
  </div>
  
   <div class='boxcontent'>

<center><h1></h1></center>

<p>Ahora puedes habilitar pre-pedidos para tu club. Esto te permite invitar socios por e-mail, para que inicien una sesión en un área privado para socios y pidan productos.
<p>El proceso es el siguiente:

<ol>
 <li>Se envía un e-mail al socio con los detalles para iniciar una sesión (enlace y contraseña).</li>
 <li>El socio accede al área privado.</li>
 <li>El socio puede elegir ENTREGA o RECOGIDA, y luego elegir el momento deseado para la recogida / entrega (puedes deshabilitar la opción Entrega en los ajustes del sistema).</li>
 <li>Recibirás un correo electrónico notificándote del pedido. También puedes ver los pedidos en un nuevo listado de PEDIDOS en el panel de administración.</li>
 <li>Cuando el socio ha recibido sus productos, lo marcas como Cumplido en el listado de PEDIDOS.</li>
</ol>    
    <p>En los ajustes del sistema, puedes elegir si deseas permitir entregas y también puede establecer una cantidad mínima para los pedidos.
    <p>Si deseas activar este nuevo módulo, presione SÍ a continuación para comenzar.
  </div>
  </div>

EOD;
	} else {
		echo <<<EOD
 <div id='mainbox'>
  <div id='mainboxheader'>
  PRE-ORDERING
  </div>
  
   <div class='boxcontent'>

<p>You can now enable pre-ordering for your club. This allows you to invite members via e-mail to login to a member area and order products.

<p>The process goes as follows:

<ol>
 <li>An e-mail is sent to the member with login details (link and password).</li>
 <li>The member logs in.</li>
 <li>The member can choose DELIVERY or COLLECTION (you can disable the Delivery option in your system settings), and they then choose a desired time for pickup / delivery.</li>
 <li>You receive an e-mail, notifying you of the order. You can also see orders in the new ORDERS screen in your administration panel.</li>
 <li>When the member has received their products, you mark it as Fulfilled in the ORDERS panel.</li>
</ol>

<p>In your system settings, you can choose whether to allow deliveries and you can also set a minimum order amount.

<p>If you wish to activate this new module, please press YES below to get started!
  </div>
  </div>
EOD;
	}

	
echo "<center><br /><a href='?act=activate' class='cta1'>{$lang['global-yes']}</a> <a href='?act=reject' class='cta2' style='background-color: red;'>{$lang['global-no']}</a>
</center>";

displayFooter();
