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
		$reason = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['reason'])));
		
		// Look up user e-mail
		$query = "SELECT first_name, email FROM users WHERE user_id = '{$user_id}'";
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
			$email = $row['email'];
		
		// Change status to SOCIO
		$query = "UPDATE users SET userGroup = 12, rejectComment = '$reason' WHERE user_id = $user_id";
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
			$mail->Password = "2beorNOT2be2020!";
			$mail->SMTPSecure = 'ssl'; 
			$mail->Port = 465;
			$mail->setFrom('acw@dabulance.com', 'Dabulance');
			$mail->addAddress("$email", "$first_name");
			$mail->Subject = "Your Dabulance application has been rejected";
			$mail->isHTML(true);
			$mail->Body = "Dear $first_name,<br /><br />While we appreciate your interest in the Dabulance membership, upon reviewing your recent application we regret to inform you that we have had to deny your request.<br /><br />
			Reason: $reason.<br /><br />All the best,<br />The Dabulance crew.";
			$mail->send();
	
		}
		catch (Exception $e)
		{
		   echo $e->errorMessage();
			$_SESSION['errorMessage'] = "ERROR SENDING EMAIL!!!!!!!!";
		}
		
		$_SESSION['successMessage'] = "User rejected successfully!";
		
		header("Location: ../profile.php?user_id=$user_id");
		
		exit();
	
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
		
	pageStart("Reject member", NULL, $deleteDonationScript, "pmembership", NULL, "Reject member", $_SESSION['successMessage'], $_SESSION['errorMessage']);

echo "<center><div class='actionbox-np2'><div class='boxcontent'><div class='mainboxheader'>$first_name $last_name</div><div class='clearfloat'></div>";

?>
<form id="registerForm" action="" method="POST" onsubmit="return testInput()">
	<input type="hidden" name="user_id" value="<?php echo $user_id; ?>" />
	<input type="hidden" name="plan" value="chosen" />

  <br />
  <textarea class='defaultinput' name='reason' placeholder='Reason for rejecting'></textarea><br /><br />
 <button class='cta1' type="submit">Reject member</button>

</div>
</div>
<?php


exit();

