<?php

	require_once 'cOnfig/connection-master.php';
	require_once 'cOnfig/view-loggedout.php';
	require_once 'cOnfig/languages/common.php';

	session_start();
	
	if (isset($_POST['email'])) {
		
		$email = trim($_POST['email']);
				
		// Look up the provided credentials
		try
		{
			$result = $pdo->prepare("SELECT id, email FROM users WHERE email = :email");
			$result->bindValue(':email', $email);
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		$count = $result->fetchColumn();
		
		if ($count == 0) {
			
			$_SESSION['errorMessage'] = "E-mail address not found. Please try again.";
			
		} else {
			
			try
			{
				$result = $pdo->prepare("SELECT id, email FROM users WHERE email = :email");
				$result->bindValue(':email', $email);
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
			
			$row = $result->fetch();
				$user_id = $row['id'];	

			$resetHash = generateRandomString(20);
			
			// Send mail with instructions and link
			$mailSubject = "Cannabis Club Systems - password reset";
			
			$mailmsg = "&nbsp;&nbsp;<img src='https://ccsnubev2.com/images/logo.png' alt='Cannabis Club Systems' /><br /><br />";
	
			$mailmsg.= "We have received your request to reset your password.<br/><br/>";
			$mailmsg.= "To enter a new password, please <a href='https://localhost/reset-password.php?ad=$user_id&mh=$mailHash&pwr=yes&vid=h37c64jf7DBb'>click here</a>, or alternatively copy the link below and paste it into your browser's address field:<br/>";
			$mailmsg.= "https://localhost/reset-password.php?ad=$user_id&mh=$mailHash&pwr=yes&vid=h37c64jf7DBb <br/><br/>";
			$mailmsg.= "All the best,<br />Cannabis Club Systems.<br/>";
			
			require 'PHPMailerAutoload.php';
			
			$mail = new PHPMailer(true);
			$mail->SMTPDebug = 0;
			$mail->Debugoutput = 'html';
			$mail->isSMTP();
			$mail->Host = "smtp.serviciodecorreo.es";
			$mail->SMTPAuth = true;
			$mail->Username = "info@ccsnube.com";
			$mail->Password = "Rbt14x74";
			$mail->SMTPSecure = 'ssl'; 
			$mail->Port = 465;
			$mail->setFrom('info@ccsnube.com', 'Cannabis Club Systems');
			$mail->addAddress("$email", "$email");
			$mail->Subject = $mailSubject;
			$mail->isHTML(true);
			$mail->Body = $mailmsg;
			$mail->send();
			
			$_SESSION['successMessage'] = "Thank you!<br />We have sent instructions on how to reset your password to $email";
			header("Location: index.php");
			exit();
			
		}
		
	}
	
	$validationScript = <<<EOD
    $(document).ready(function() {
	    	    
	  $('#registerForm').validate({
		  rules: {
			  email: {
				  required: true
			  }
    	}, // end rules
		  errorPlacement: function(error, element) {
			  if ( element.is(":radio") || element.is(":checkbox")){
				 error.appendTo(element.parent());
			}
		},
    	  submitHandler: function() {
   $(".oneClick").attr("disabled", true);
   form.submit();
	    	  }
	  }); // end validate
  }); // end ready
EOD;

	
	// User not logged in - possibly submitted invalid credentials. (Re-)create the index page.
	require_once 'cOnfig/languages/common.php';
	
	pageStart($lang['title-login'], NULL, $validationScript, "pindex", "loggedOut", "", $_SESSION['successMessage'], $_SESSION['errorMessage']);

?>
<br />
<center>
   <div id='sectionText'>
    <p>
     Please enter your e-mail address below and we'll send you instructions on how to reset your password.
    </p>
   </div>
<br /><br />
 <form id="registerForm" action="" method="POST">
  <input type="hidden" name='action' value='submit'>
  <input type="email" name="email" autofocus value="<?php if (isset($email)) echo $email; ?>" placeholder="E-mail" tabindex="1" /><br /><br />
  <button name='oneClick' class="visible" type="submit" tabindex="4" >Submit</button>
  
 </form>
</center>