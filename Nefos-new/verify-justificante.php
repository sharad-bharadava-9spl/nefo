<?php

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$invno = $_GET['invno'];
	$period = '202010';
	$nowDate = date('Y-m-d H:i');
	
	if (isset($_GET['approve'])) {
		
		$invno = $_POST['invno'];
		
		
		// Look up all invoices related to this justificante
		$query = "SELECT customer, justificante FROM invoices WHERE invno = '$invno'";
		try
		{
			$result = $pdo->prepare("$query");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$justificante = $row['justificante'];
			$customer = $row['customer'];
			
		// Look up all invoices related to this justificante
		$query = "SELECT invno FROM invoices WHERE justificante = '$justificante'";
		try
		{
			$results = $pdo->prepare("$query");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($row = $results->fetch()) {
			
			$invno = $row['invno'];
			
			// Set verified = 1 in invoices database - for ALL invoices
			$query = "UPDATE invoices SET verified = 1, justificanteamount = '{$_POST['amountpaidApprove']}' WHERE invno = '$invno'";
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
			
			// Add cutoff comment
			$query = sprintf("INSERT INTO cutoffcomments (time, customer, comment, period, invno, operator) VALUES ('%s', '%s', '%s', '%s', '%s', '%d');",
		  	 $nowDate, $customer, "<em>Justificante approved.</em>", $period, $invno, $_SESSION['user_id']);
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

		
		}
			
		// Remove warning
		$query = "UPDATE db_access SET warning = 0 WHERE customer = '$customer'";
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
		
		$_SESSION['successMessage'] = "Justificante approved & warning removed succesfully!";
		if ($_GET['src'] == 'justificantes') {
			header("Location: justificantes.php");
		} else {
			header("Location: cutoff.php");
		}
		exit();
		
	} else if (isset($_GET['reject'])) {
		
		$invno = $_POST['invno'];
		$reason = $_POST['reason'];
		$amountpaid = $_POST['amountpaid'];
		$totAmount = $_POST['totAmount'];
		
		if ($reason == 1) {
			
			$reasonEng = "You have deposited the payment to the wrong account number.";
			$reasonEsp = "Has ingresado a una cuenta erroneo.";
			
		} else if ($reason == 2) {
			
			$reasonEng = "The amount you have deposited ($amountpaid &euro;) does not correspond with the invoice amount ($totAmount &euro;).";
			$reasonEsp = "El importe que has ingresado ($amountpaid &euro;) no corresponde con el importe del factura ($totAmount &euro;).";
			
		} else if ($reason == 3) {
			
			$reasonEng = "The receipt you have uploaded is not related to the pending invoice(s).";
			$reasonEsp = "La justificante no esta relacionada a tu factura(s) pendiente(s).";
			
		} else if ($reason == 4) {
			
			$reasonEng = "The receipt you have uploaded does not correspond to the pending invoice(s).";
			$reasonEsp = "La justificante no esta relacionada a tu factura(s) pendiente(s).";
			
		}
		
		// Look up all invoices related to this justificante
		$query = "SELECT customer, justificante FROM invoices WHERE invno = '$invno'";
		try
		{
			$result = $pdo->prepare("$query");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$justificante = $row['justificante'];
			$customer = $row['customer'];
			
		// Look up all invoices related to this justificante
		$query = "SELECT invno FROM invoices WHERE justificante = '$justificante'";
		try
		{
			$results = $pdo->prepare("$query");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($row = $results->fetch()) {
			
			$invno = $row['invno'];
			
			// Set verified = 2 in invoices database - for ALL invoices
			$query = "UPDATE invoices SET verified = 2 WHERE invno = '$invno'";
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
			
			// Add cutoff comment
			$query = sprintf("INSERT INTO cutoffcomments (time, customer, comment, period, invno, operator) VALUES ('%s', '%s', '%s', '%s', '%s', '%d');",
		  	 $nowDate, $customer, "<em>Justificante rejected: $reasonEng</em>", $period, $invno, $_SESSION['user_id']);
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

		
		}
				
		// Send mail to client IF club mail is valid
		$query = "SELECT email, shortName FROM customers WHERE number = '$customer'";
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
			$email = $row['email'];
			$shortName = $row['shortName'];
			
		// Check if valid
		if ($email == '') {
			
			$_SESSION['errorMessage'] = "Justificante rejected - but no e-mail was sent as there's no e-mail registered for this client. Please update the Nefos tool.";
			
		} else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			
			$_SESSION['errorMessage'] = "Justificante rejected - but no e-mail was sent as their registered e-mail is invalid: $email. Please update the Nefos tool.";
			
		} else {
			
			// Send e-mail to client
			try {
				
			// Send e-mail(s)
			require_once '../PHPMailerAutoload.php';
			
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
			$mail->setFrom('info@cannabisclub.systems', 'CCSNube');
			$mail->addAddress("$email", "$shortName");
			$mail->Subject = "Justifante rechazada / Proof of payment rejected!";
			$mail->isHTML(true);
			$mail->Body = "Estimad@ $shortName,<br /><br />Hemos recibido tu justificante, pero teniamos que rechazarlo debido a este razon:<br />$reasonEsp<br /><br />Por favor intentar que subir otro justificante.<br /><br />Saludos,<br />El equipo CCS.<br /><br />**<br /><br />Dear $shortName,<br /><br />We have received your proof of payment, but we had to decline it due to the following error:<br />$reasonEng<br /><br />Please try submitting your proof of payment again.<br /><br />All the best,<br />The CCS Team.";
			$mail->send();

			}
			catch (Exception $e)
			{
			   echo $e->errorMessage();
			   $_SESSION['errorMessage'] = "Error sending mail!!";
			}
			
			$_SESSION['successMessage'] = "Justificante rejected & mail sent to client succesfully!";
			
		}
			
		if ($_GET['src'] == 'justificantes') {
			header("Location: justificantes.php");
		} else {
			header("Location: cutoff.php");
		}
		exit();
		
	}
	
	
	$query = "SELECT customer, justificante FROM invoices WHERE invno = '$invno'";
	try
	{
		$result = $pdo->prepare("$query");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$justificante = $row['justificante'];		
		$customer = $row['customer'];
		
	$ext = substr($justificante, -3);
	
	if ($ext == 'pdf') {
		
		$justifanteShow = "<embed src='https://ccsnubev2.com/v6/$justificante' height='600' width='800' />";
		
	} else {
		
		$justifanteShow = "<img src='https://ccsnubev2.com/v6/$justificante' style='max-height: 800px; max-width: 800px;' />";
		
	}
	
	pageStart("Justificante", NULL, $validationScript, "pmembership", NULL, "Justificante", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
?>

<div id='mainbox-new-club' style='width: initial;'>
 <div id='mainboxheader'>
  <center>
   Justificante
  </center>
 </div>
 <div class='boxcontent'>
  <center><strong>INVOICE(S):</strong>
   <table class='default noborder' style='font-size: 16px !important;'>
<?php
	$query = "SELECT invno, invdate, action, cutoffdate, promise, paid, amount FROM invoices WHERE justificante = '$justificante'";
	try
	{
		$results = $pdo->prepare("$query");
		$results->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$i = 0;
	while ($row = $results->fetch()) {
		
		$invno2 = $row['invno'];
		$invdate = date("d-m-Y", strtotime($row['invdate']));
		$amount = $row['amount'];
		$cutoffdate = date("d-m-Y", strtotime($row['cutoffdate']));
		$promise = date("d-m-Y", strtotime($row['promise']));
		$totAmount = $totAmount + $amount;
		
		if ($invno2 == $invno) {
			$checked = 'checked';
		} else {
			$checked = '';
		}
		
		echo "<tr><td>$invno2</td><td>$invdate</td><td class='right'>$amount &euro;</td></tr>";
		
		$i++;
		
	}
	
		echo "<tr><td><strong>TOTAL:</strong></td><td></td><td class='right'><strong>$totAmount &euro;</strong></td></tr>";
	
?>
   </table>
   <br />
  <center><strong>ACCOUNT NUMBERS:</strong>
  <br /><br />
HW / ES7401820981400203192038<br />

SW / ES9401820981490203183962  
  <br /><br /><br />

<center><strong>JUSTIFICANTE:</strong>
  <br /><br />
  <?php echo $justifanteShow; ?>
  <br />
  <br />
  
  
 
  <table class="default noborder">
  <tr>
   <td style='vertical-align: bottom; border-right: 2px solid #ccc; font-size: 16px;'>
<form id="registerForm" action="?approve<?php if ($_GET['src'] == 'justificantes') { echo '&src=justificantes'; } ?>" method="POST">
Amount paid: <input type='text' name='amountpaidApprove' placeholder="&euro;" class='defaultinput twoDigit' /><br />
   <!--<a href="?approve&invno=<?php echo $invno; ?>" class="cta1">APPROVE</a></td>-->
  <input type='hidden' name='totAmount' value='<?php echo $totAmount; ?>' />
  <input type='hidden' name='invno' value='<?php echo $invno; ?>' />
  <button class='cta1' type="submit">Approve</button>
</form>
   <td>
   
 <form id="registerForm" action="?reject<?php if ($_GET['src'] == 'justificantes') { echo '&src=justificantes'; } ?>" method="POST">

 	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Wrong account number
	  <input type="radio" name="reason" value="1" />
	  <div class="fakebox"></div>
	 </label>
	</div>
<br />
<br />
 	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Amount too low:
	  <input type="radio" name="reason" value="2" />
	  <div class="fakebox"></div>
	 </label>
	</div>
  <input type='text' name='amountpaid' placeholder="&euro;" class='defaultinput twoDigit' />
<br />
<br />
 	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Old justificante
	  <input type="radio" name="reason" value="3" />
	  <div class="fakebox"></div>
	 </label>
	</div>
<br />
<br />
 	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Wrong justificante
	  <input type="radio" name="reason" value="4" />
	  <div class="fakebox"></div>
	 </label>
	</div>
<br />
  <input type='hidden' name='totAmount' value='<?php echo $totAmount; ?>' />
  <input type='hidden' name='invno' value='<?php echo $invno; ?>' />
  <button class='cta3' type="submit">Reject</button>
   </td>
  </tr>
 </table>
 
 </form>
  
  </center>
 </div>
</div>

<?php

displayFooter();