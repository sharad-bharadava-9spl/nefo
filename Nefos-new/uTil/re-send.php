<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/view.php';
	require_once '../cOnfig/authenticate.php';
	require_once '../cOnfig/languages/common.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);

	$hash = $_GET['hash'];
	
	// Lookup name and e-mail
	$query = "SELECT name, email FROM contacts WHERE hash = '$hash'";
	try
	{
		$result = $pdo2->prepare("$query");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$name = $row['name'];	
		$email = $row['email'];
		
	try {
		
	// Send e-mail(s)
	require_once '../../PHPMailerAutoload.php';
	
	$mail = new PHPMailer(true);
	$mail->CharSet = 'UTF-8';
	$mail->SMTPDebug = 0;
	$mail->Debugoutput = 'html';
	$mail->isSMTP();
	$mail->Host = "mail.cannabisclub.systems";
	$mail->SMTPAuth = true;
	$mail->Username = "info@cannabisclub.systems";
	$mail->Password = "Insjormafon9191";
	$mail->SMTPSecure = 'ssl'; 
	$mail->Port = 465;
	$mail->setFrom('info@cannabisclub.systems', 'Cannabis Club Systems');
	$mail->addAddress("$email", "$name");
	$mail->isHTML(true);
	
	$mail->Subject = "Tú enlace para el CCS demo video / Your link to the CCS demo video";
	$mail->Body = <<<EOD
Hola $name,<br /><br />
Gracias por tu interés en Cannabis Club Systems!<br /><br />
<a href='https://ccsnube.com/demo-2.php?h=$hash'>Pincha aquí</a> para ver el demo.<br /><br />Un saludo,<br /><strong>Cannabis Club Systems</strong><br /><br />
****************<br /><br />
Dear $name,<br /><br />
Thank you for registering with Cannabis Club Systems!<br /><br />
<a href='https://ccsnube.com/demo-2.php?h=$hash'>Click here</a> to watch the demo video.<br /><br />All the best,<br /><strong>Cannabis Club Systems</strong>
EOD;

		$mail->send();

		}
		catch (Exception $e)
		{
		   	echo $e->errorMessage();
			   $_SESSION['errorMessage'] = "Error sending e-mail.<br />Please try again later.";
				pageStart("CCS demo", NULL, $validationScript, "pprofile", "club-launch", "CCS demo", $_SESSION['successMessage'], $_SESSION['errorMessage']);
				exit();
		}	
			
		$_SESSION['successMessage'] = "E-mail sent succesfully!";
		header("Location: ../demo-views.php");
		