<?php

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/languages/common.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	if (isset($_GET['user_id'])) {	
		$user_id = $_GET['user_id'];
	} else if (isset($_POST['user_id'])) {	
		$user_id = $_POST['user_id'];
	} else {
		echo "<br />No user specified!";
		exit();
	}
		
	if ($_POST['plan'] == 'chosen') {
		
		$user_id = $_POST['user_id'];
		$usergroup2 = $_POST['usergroup2'];
		$free = $_POST['free'];
		
		if ($usergroup2 == 0) {
			$packageName = 'Test plan - 1 day in the barn';
			$packagePrice = 5;
		} else if ($usergroup2 == 1) {
			$packageName = 'Starter plan - 3 months';
			$packagePrice = 25;
		} else if ($usergroup2 == 2) {
			$packageName = 'Basic - 1 year';
			$packagePrice = 50;
		} else if ($usergroup2 == 3) {
			$packageName = 'Premium - 1 year';
			$packagePrice = 150;
		} else if ($usergroup2 == 4) {
			$packageName = 'VIP plan - 1 year';
			$packagePrice = 420;
		} else if ($usergroup2 == 5) {
			$packageName = 'Business package - 1 year';
			$packagePrice = 710;
		}
		
		$query = "SELECT paidUntil, first_name, email, hash FROM users WHERE user_id = $user_id";
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
			$paidUntil = $row['paidUntil'];
			$first_name = $row['first_name'];
			$email = $row['email'];
			$hash = $row['hash'];
		
		$paymentTime = date('Y-m-d');
		$old_date = date();
		
		if ($usergroup2 == 1) {
			$expiry = date('Y-m-d', strtotime($old_date. ' +30 days'));
		} else if ($usergroup2 == 2) {
			$expiry = date('Y-m-d', strtotime($old_date. ' +365 days'));
		} else if ($usergroup2 == 3) {
			$expiry = date('Y-m-d', strtotime($old_date. ' +365 days'));
		} else if ($usergroup2 == 4) {
			$expiry = date('Y-m-d', strtotime($old_date. ' +365 days'));
		} else if ($usergroup2 == 5) {
			$expiry = date('Y-m-d', strtotime($old_date. ' +365 days'));
		}
		
		// Free:
		if ($free == 1) {
			
			// Assign usergroup2 to member & paidUntil
			$query = "UPDATE users SET usergroup2 = $usergroup2, paidUntil = '$expiry', userGroup = 5 WHERE user_id = $user_id";
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
			
			// Insert into membership payments
			$query = sprintf("INSERT INTO memberpayments (paymentdate, userid, amountPaid, oldExpiry, newExpiry, paidTo, operator, free, package) VALUES ('%s', '%d', '%f', '%s', '%s', '%d', '%d', '%d', '%d');",
		  	$paymentTime, $user_id, 0, $paidUntil, $expiry, 4, $_SESSION['user_id'], 1, $usergroup2);
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
			
			// Send free mail
			try {
				
				// Send e-mail(s)
				require_once 'PHPMailerAutoload.php';
				
				
				$mail = new PHPMailer(true);
				$mail->SMTPDebug = 0;
				$mail->Debugoutput = 'html';
				$mail->isSMTP();
				$mail->Host = "mail.dabulance.com";
				$mail->SMTPAuth = true;
				$mail->Username = "acw@dabulance.com";
				$mail->Password = "dreWdabulance939_!";
				$mail->SMTPSecure = 'ssl'; 
				$mail->Port = 465;
				$mail->setFrom('acw@dabulance.com', 'Dabulance');
				$mail->addAddress("$email", "$first_name");
				$mail->Subject = "Your Dabulance application has been approved!";
				$mail->isHTML(true);
				$mail->Body = "Congratulations $first_name,<br /><br />On Behalf of the Team at Dabulance we are Happy to confirm that your Information has been reviewed and approved!<br /><br />
				You have been awarded a <strong>FREE</strong> membership worth $packagePrice USD: $packageName!<br />Welcome to the Family and we hope you enjoy your membership.<br /><br />
				We are still Hard at work preparing your private Online Lounge. This is where you will be able to see Calendars, Maps and access all sorts of services. For the time being you can <a href='https://ccsnube.com/join/dab-pwd.php?uid=$user_id&hash=$hash'>click here</a> to create your password for login. Here you will soon be able to access our store and book services.<br /><br />
We are doing some final tests and tweaks to the app on Android and Ios to give you the best experience possible. These steps are almost complete and once available for download you will be emailed notification of so.<br /><br />All the best,<br />The Dabulance crew.";
				$mail->send();
		
			}
			catch (Exception $e)
			{
			   echo $e->errorMessage();
				$_SESSION['errorMessage'] = "ERROR SENDING EMAIL!!!!!!!!";
			}
			
			$_SESSION['successMessage'] = "User updated successfully!";
			
			header("Location: ../profile.php?user_id=$user_id");
			
			exit();
			
			
		// Staff:
		} else if ($usergroup2 == 999) {
			
			$expiry = date('Y-m-d', strtotime($old_date. ' +365 days'));
			
			// Assign usergroup2 to member & paidUntil
			$query = "UPDATE users SET paidUntil = '$expiry', userGroup = 2 WHERE user_id = $user_id";
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
			
			// Insert into membership payments
			$query = sprintf("INSERT INTO memberpayments (paymentdate, userid, amountPaid, oldExpiry, newExpiry, paidTo, operator, free, package) VALUES ('%s', '%d', '%f', '%s', '%s', '%d', '%d', '%d', '%d');",
		  	$paymentTime, $user_id, 0, $paidUntil, $expiry, 4, $_SESSION['user_id'], 0, $usergroup2);
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
			
			// Send free mail
			try {
				
				// Send e-mail(s)
				require_once 'PHPMailerAutoload.php';
				
				
				$mail = new PHPMailer(true);
				$mail->SMTPDebug = 0;
				$mail->Debugoutput = 'html';
				$mail->isSMTP();
				$mail->Host = "mail.dabulance.com";
				$mail->SMTPAuth = true;
				$mail->Username = "acw@dabulance.com";
				$mail->Password = "dreWdabulance939_!";
				$mail->SMTPSecure = 'ssl'; 
				$mail->Port = 465;
				$mail->setFrom('acw@dabulance.com', 'Dabulance');
				$mail->addAddress("$email", "$first_name");
				$mail->Subject = "Your Dabulance application has been approved!";
				$mail->isHTML(true);
				$mail->Body = "Hi $first_name,<br /><br />On Behalf of the Team at Dabulance we are Happy to confirm that your Information has been reviewed and you have been added as a staff member!<br /><br />
				Welcome to the Family!<br /><br />
				The next step would be for you to <a href='https://ccsnube.com/join/dab-pwd.php?uid=$user_id&hash=$hash'>click here</a> to create your password for login.<br /><br />
All the best,<br />The Dabulance crew.";
				$mail->send();
		
			}
			catch (Exception $e)
			{
			   echo $e->errorMessage();
				$_SESSION['errorMessage'] = "ERROR SENDING EMAIL!!!!!!!!";
			}
			
			$_SESSION['successMessage'] = "User successfully changed to staff member, e-mail asking them to set their password has been sent!";
			
			header("Location: ../profile.php?user_id=$user_id");
			
			exit();
			
			
		// User to choose their own package:
		} else if ($usergroup2 == 99) {
			
			// Assign usergroup2 to member & paidUntil
			$query = "UPDATE users SET userGroup = 5 WHERE user_id = $user_id";
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
			
			// Send mail
			try {
				
				// Send e-mail(s)
				require_once 'PHPMailerAutoload.php';
				
				
				$mail = new PHPMailer(true);
				$mail->SMTPDebug = 0;
				$mail->Debugoutput = 'html';
				$mail->isSMTP();
				$mail->Host = "mail.dabulance.com";
				$mail->SMTPAuth = true;
				$mail->Username = "acw@dabulance.com";
				$mail->Password = "dreWdabulance939_!";
				$mail->SMTPSecure = 'ssl'; 
				$mail->Port = 465;
				$mail->setFrom('acw@dabulance.com', 'Dabulance');
				$mail->addAddress("$email", "$first_name");
				$mail->Subject = "Your Dabulance application has been approved!";
				$mail->isHTML(true);
				$mail->Body = "Congratulations $first_name,<br /><br />On Behalf of the Team at Dabulance we are Happy to confirm that your Information has been reviewed and approved! Welcome to the Family and we hope you enjoy your membership.<br /><br />We are still Hard at work preparing your private Online Lounge. This is where you will be able to see Calendars, Maps and access all sorts of services. For the time being you can <a href='https://ccsnube.com/join/dab-packages.php?uid=$user_id&hash=$hash'>click here</a> to choose which package best suits you. Once you’ve completed payment you will be sent an email to create your password for login. Here you will be able to access our store and book services.<br /><br />We are doing some final tests and tweaks to the app on Android and Ios to give you the best experience possible. These steps are almost complete and once available for download you will be emailed notification of so.<br /><br />Thankfully,<br />The Crew at Dabulance";
				$mail->send();
		
			}
			catch (Exception $e)
			{
			   echo $e->errorMessage();
				$_SESSION['errorMessage'] = "ERROR SENDING EMAIL!!!!!!!!";
			}
			
			$_SESSION['successMessage'] = "E-mail sent successfully!";
			
			header("Location: ../profile.php?user_id=$user_id");
			
			exit();
			
			
			
		// Pre-chosen package
		} else {
		
		// Look up user e-mail
		$query = "SELECT first_name, last_name, email FROM users WHERE user_id = '{$user_id}'";
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
		
		// Change status to SOCIO
		$query = "UPDATE users SET userGroup = 5 WHERE user_id = $user_id";
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
		
		// Send mail to member
		try {
			
			// Send e-mail(s)
			require_once 'PHPMailerAutoload.php';
			
			
			$mail = new PHPMailer(true);
			$mail->SMTPDebug = 0;
			$mail->Debugoutput = 'html';
			$mail->isSMTP();
			$mail->Host = "mail.dabulance.com";
			$mail->SMTPAuth = true;
			$mail->Username = "acw@dabulance.com";
			$mail->Password = "dreWdabulance939_!";
			$mail->SMTPSecure = 'ssl'; 
			$mail->Port = 465;
			$mail->setFrom('acw@dabulance.com', 'Dabulance');
			$mail->addAddress("$email", "$first_name");
			$mail->Subject = "Your Dabulance application has been approved!";
			$mail->isHTML(true);
			$mail->Body = "Congratulations $first_name,<br /><br />On Behalf of the Team at Dabulance we are Happy to confirm that your Information has been reviewed and approved!<br /><br />
				We have selected the <u>$packageName</u> membership for you, which has a cost of $packagePrice USD. Please proceed to make your payment by clicking <a href='https://ccsnube.com/join/dab-buy.php?uid=$user_id&hash=$hash&pck=$usergroup2'>this link</a>.<br /><br />Welcome to the Family and we hope you enjoy your membership!<br /><br />
				We are still Hard at work preparing your private Online Lounge. This is where you will be able to see Calendars, Maps and access all sorts of services.<br /><br />
We are doing some final tests and tweaks to the app on Android and Ios to give you the best experience possible. These steps are almost complete and once available for download you will be emailed notification of so.<br /><br />All the best,<br />The Dabulance crew.";
			$mail->send();
	
		}
		catch (Exception $e)
		{
		   echo $e->errorMessage();
			$_SESSION['errorMessage'] = "ERROR SENDING EMAIL!!!!!!!!";
		}
		
		$_SESSION['successMessage'] = "User approved successfully - payment link sent by mail to member.";
		
		header("Location: ../profile.php?user_id=$user_id");
		
		exit();
	
	}
}
	// Look up user e-mail
	$query = "SELECT first_name, last_name, email FROM users WHERE user_id = '{$user_id}'";
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
		
	pageStart("Approve member", NULL, $deleteDonationScript, "pmembership", NULL, "Approve member", $_SESSION['successMessage'], $_SESSION['errorMessage']);

echo "<center><div id='profilearea'><h4>$first_name $last_name</h4><div class='clearfloat'></div><br />";

?>
<form id="registerForm" action="" method="POST" onsubmit="return testInput()">
	<input type="hidden" name="user_id" value="<?php echo $user_id; ?>" />
	<input type="hidden" name="plan" value="chosen" />

  <select name="usergroup2" id="usergroup2">
   <option value=''>Choose member plan:</option>
<?php

      	// Query to look up usergroups
		$selectGroups = "SELECT id, name FROM usergroups2 ORDER by id ASC";
		try
		{
			$result = $pdo3->prepare("$selectGroups");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($group = $result->fetch()) {

				$group_row = sprintf("<option value='%d'>%d - %s</option>",
	  								 $group['id'], $group['id'], $group['name']);
	  			echo $group_row;

  		}
  			echo "<option value='99'>Let the user choose</option>";
  			echo "<option value='999'>STAFF</option>";
?>
  </select>
  <br /><br />
  <input type='checkbox' id='free' name='free' value='1' style='width: 15px;' />
  <label for='free'>Free membership?</label><br /><br />
 <button type="submit">Approve member</button>


<?php

exit();

