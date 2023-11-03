<?php

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';

	session_start();
	$accessLevel = '5';
		
	// Check if user is already logged in (SESSION variables set) - if so, redirect to main.php)
	if (isset($_SESSION['user_id']) &&  isset($_SESSION['username']) &&  isset($_SESSION['memberno']) && isset($_SESSION['first_name']) && $_SESSION['cloud'] == 'nefos' ) {
		
		header("Location: main.php");
		exit();
		
	// User not logged in - did he submit a form with username for login?
	} else if (isset($_POST['email'])) {
				
		if ($_SESSION['iPadReaders'] > 0) {
			$_SESSION['scanner'] = $_POST['scanner'];
		}
			
		$_SESSION['lang'] = $_POST['siteLanguage'];
		require_once 'cOnfig/languages/common.php';
	
		
		// Try to log the user in
		$email = trim($_POST['email']);
		$password = trim($_POST['password']);
		
		// Check if email exists
		try
		{
			$result = $pdo3->prepare("SELECT user_id, email, userPass FROM users WHERE email = :email");
			$result->bindValue(':email', $email);
			$result->execute();
			$data = $result->fetchAll();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

		// Email doesn't exist
		if (!$data) {

			$_SESSION['errorMessage'] = "E-mail doesn't exist / E-mail no existe!";

		// E-mail exists, let's check password
		} else {

			try
			{
				$result = $pdo3->prepare("SELECT user_id, email, memberno, domain, first_name, userGroup, workStation FROM users WHERE email = :email AND userPass = :userPass");
				$result->bindValue(':email', $email);
				$result->bindValue(':userPass', crypt($password, $email));
				$result->execute();
				$data = $result->fetchAll();
				
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}

			// Wrong pwd
			if (!$data) {

				$_SESSION['errorMessage'] = "Incorrect password / Contrase&ntilde;a erronea";

			// Correct pwd
			} else {

				$row = $data[0];
						$_SESSION['user_id'] = $row['user_id'];
						$_SESSION['username'] = $row['email'];
						$_SESSION['memberno'] = $row['memberno'];
						$_SESSION['first_name'] = $row['first_name'];
						$_SESSION['userGroup'] = $row['userGroup'];
						$_SESSION['workStationAccess'] = $row['workStation'];
						$_SESSION['domain'] = $row['domain'];
						$_SESSION['cloud'] = 'nefos';
		
						$_SESSION['successMessage'] = $lang['index-loggedin'];
						header("Location: ?");
						exit();
			}
		}
	}
		
	$validationScript = <<<EOD
    $(document).ready(function() {
	    	    
	  $('#registerForm').validate({
		  rules: {
			  email: {
				  required: true
			  },
			  password: {
				  required: true
			  },
			  siteLanguage: {
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
	
	pageStart($lang['title-login'], NULL, $validationScript, "pindex", "loggedOut", NULL, $_SESSION['successMessage'], $_SESSION['errorMessage']);

?>
<br />
<center> 
 <form id="registerForm" action="" method="POST">
  <input type="hidden" name='action' value='submit'>
  <input type="email" name="email" autofocus value="<?php if (isset($email)) echo $email; ?>" placeholder="E-mail" tabindex="1" /><br /><br />
  <input type="password" name="password" placeholder="Password / Contrase&ntilde;a" tabindex="2" /><br /><br />
<br />
  <button name='oneClick' class="visible" type="submit" tabindex="4" >Log in</button>
  <!--<a href="#" style="margin-left: 36px"><u>Forgot password?</u></a>-->
 </form>
</center>