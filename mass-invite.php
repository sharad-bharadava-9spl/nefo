<?php
	
	require_once 'cOnfig/ connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings();

	
	if (isset($_POST['bajaReady'])) {
		
		foreach($_POST['giveBaja'] as $toBaja) {
			
			$user_id = $toBaja;
			echo "userid: $user_id<br />";		
			
			// Generate pwd

		}
		echo "OK"; exit();
		
		$query = "SELECT services, minorder, clubname, clubemail, clubphone, mail1es, mail1en FROM systemsettings";
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
			$clubemail = $row['clubemail'];
			$minorder = $row['minorder'];
			$clubname = $row['clubname'];
			$clubemail = $row['clubemail'];
			$clubphone = $row['clubphone'];
		
	}
	
	// Generate and crypt password
	$pwd = generateRandomString(8);
	$newpw = crypt($pwd, $email);

	$query = "UPDATE users SET userPass = '$newpw' WHERE user_id = $user_id";
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

<p>Como socio apreciado de $clubname, nos gustaría invitarte a probar nuestro nuevo sistema de pre-pedidos.

<p>Aquí abajo te facilitamos acceso y puedes pedirnos productos.
<p>Enlace: <a href='www.pediralgo.com'>www.pediralgo.com</a><br />
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


		try {
			
			// Send e-mail(s)
			require_once 'PHPMailerAutoload.php';
		
			$mail = new PHPMailer(true);
			$mail->SMTPDebug = 0;
			$mail->Debugoutput = 'html';
			$mail->isSMTP();
			$mail->Host = "mail.pediralgo.com";
			$mail->SMTPAuth = true;
			$mail->Username = "info@pediralgo.com";
			$mail->Password = "CaRi@*P^rKaY";
			$mail->SMTPSecure = 'ssl'; 
			$mail->Port = 465;
			$mail->setFrom('info@pediralgo.com', "$clubname");
			$mail->addAddress("$email", "$first_name $last_name");
			$mail->Subject = $lang['invited-to-pre-order'];
			$mail->isHTML(true);
			$mail->Body = $mailbody;
			$mail->send();
	
		}
		catch (Exception $e)
		{
		   echo $e->errorMessage();
		   $_SESSION['errorMessage'] = "ERROR SENDING MAIL";
		}

		
		// On success: redirect.
		$_SESSION['successMessage'] = $lang['thank-you-invited2'];
		header("Location: pre-order.php");
		exit();
		
	}
	
	
	
	// Query to look up users
	$selectUsers = "SELECT u.user_id, u.memberno, u.first_name, u.paidUntil, u.registeredSince, u.userGroup, u.last_name, u.credit, u.email, u.telephone, SUM(s.amount) FROM users u, sales s WHERE u.user_id = s.userid GROUP BY user_id ORDER by SUM(s.amount) DESC LIMIT 200";
	try
	{
		$results = $pdo3->prepare("$selectUsers");
		$results->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	

	pageStart($lang['invite'], NULL, $memberScript, "pmembership", NULL, $lang['invite'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	echo "<center><div style='width: 500px; border: 3px solid #5aa242; padding: 10px;'>{$lang['invite-instructions']}</strong></center>";
?>

<br />
	<form action='' method='POST' name='registerForm'>
	 <table class='default' id='mainTable'>
	  <thead>	
	   <tr style='cursor: pointer;'>
	    <th><?php echo $lang['invite']; ?></th>
	    <th>#</th>
	    <th><?php echo $lang['global-name']; ?></th>
	    <th><?php echo $lang['member-lastnames']; ?></th>
	    <th>E-mail</th>
	    <th><?php echo $lang['member-telephone']; ?></th>
<?php if ($_SESSION['creditOrDirect'] == 1) { ?>
	    <th><?php echo $lang['global-credit']; ?></th>
<?php } ?>
	    <th><?php echo $lang['global-registered']; ?></th>
	    <th><?php echo $lang['global-type']; ?></th>
	    <th><?php echo $lang['member-group']; ?></th>
<?php if ($_SESSION['membershipFees'] == 1) { ?>
	    <th><?php echo $lang['expiry']; ?></th>
<?php } ?>
	    <th><?php echo $lang['last-dispense']; ?></th>
	    <th><?php echo $lang['closeday-dispensed']; ?></th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php

		while ($user = $results->fetch()) {

			$paidUntil = $user['paidUntil'];
			$telephone = $user['telephone'];
			$email = $user['email'];
			
			// Check email validity
			if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$email = "<span style='color: red; font-weight: 800;'>$email</a>";
				$checkOrNot = "<input type='checkbox' name='giveBaja[%d]' value='%d' style='width: 12px; display: none;'/>";
			} else {
				$email = $email;
				$checkOrNot = "<input type='checkbox' name='giveBaja[%d]' value='%d' style='width: 12px;' />";
			}

	

		$dispQuery = "SELECT groupName FROM usergroups WHERE userGroup = {$user['userGroup']}";
		try
		{
			$resultC = $pdo3->prepare("$dispQuery");
			$resultC->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		$rowC = $resultC->fetch();
			$groupName = $rowC['groupName'];
	
	// Look up last dispense date
	$dispQuery = "SELECT saletime FROM sales WHERE userid = {$user['user_id']} ORDER BY saletime DESC LIMIT 1";
		try
		{
			$result = $pdo3->prepare("$dispQuery");
			$result->execute();
			$data = $result->fetchAll();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		if (!$data) {
		
		$lastDispense = "<span class='white'>00-00-0000</span>";
		
	} else {
		
		$rowD = $data[0];
			$lastDispense = date("d-m-Y", strtotime($rowD['saletime']));
			
	}
	
	if ($user['usageType'] == '1') {
		$usageType = "<img src='images/medical.png' width='16' /><span style='display:none'>1</span>";
	} else {
		$usageType = '';
	}
	
		$memberExp = date('y-m-d', strtotime($paidUntil));
		$memberExpReadable = date('d-m-Y', strtotime($paidUntil));
		$timeNow = date('y-m-d');

	if ($user['userGroup'] > 4 && $exento == 0) {
		
		if (strtotime($memberExp) == strtotime($timeNow)) {
			$membertill = "<span class='mid'>$memberExpReadable</span>";
	  	} else if (strtotime($memberExp) < strtotime($timeNow)) {
		  	$membertill = "<span class='negative'>$memberExpReadable</span>";
		} else if (strtotime($memberExp) > strtotime($timeNow)) {
		  	$membertill = "<span class='positive'>$memberExpReadable</span>";
		}
		
	} else {
		
		$membertill = "<span class='white'>00-00-0000</span>";
		
	}
	
	echo sprintf("
  	  <tr>
  	   <td>$checkOrNot</td>
  	   <td class='clickableRowNew' href='profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRowNew' href='profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRowNew' href='profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRowNew' href='profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRowNew' href='profile.php?user_id=%d'>%s</td>",
	  $user['user_id'], $user['user_id'], $user['user_id'], $user['memberno'], $user['user_id'], $user['first_name'], $user['user_id'], $user['last_name'], $user['user_id'], $email, $user['user_id'], $telephone);
	  

if ($_SESSION['creditOrDirect'] == 1) {
	
	echo sprintf("
  	   <td class='clickableRowNew right' href='profile.php?user_id=%d'>%0.1f {$_SESSION['currencyoperator']}</td>",
  	  $user['user_id'], $user['credit']);
  	  
}

	echo sprintf("
  	   <td class='clickableRowNew' href='profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRowNew' style='text-align: center;' href='profile.php?user_id=%d'>%s</td>
  	   <td class='clickableRowNew' href='profile.php?user_id=%d'>%s</td>",
  	  $user['user_id'], date("d-m-Y",strtotime($user['registeredSince'])), $user['user_id'], $usageType, $user['user_id'], $groupName);

if ($_SESSION['membershipFees'] == 1) {
	  
	echo sprintf("<td class='clickableRowNew %s' href='profile.php?user_id=%d'>%s</td>",
   $paidClass, $user['user_id'], $membertill);
	    
}

	echo sprintf("<td class='clickableRowNew' href='profile.php?user_id=%d'>%s</td><td class='clickableRowNew right' href='profile.php?user_id=%d'><strong>%s {$_SESSION['currencyoperator']}</strong></td>",
   $user['user_id'], $lastDispense, $user['user_id'], $user['SUM(s.amount)']);


}
?>

	 </tbody>
	 </table>
	 <input type='hidden' name='bajaReady' />
	 <br />
	 <center><input type='submit' class='cta' value="<?php echo $lang['invite']; ?>" /></center>
	</form>

<?php  displayFooter(); ?>
