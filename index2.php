<?php

	require_once 'cOnfig/connection-master.php';
	require_once 'cOnfig/view-loggedout.php';
	require_once 'cOnfig/languages/common.php';

	session_start();
	
	// Check if user is already logged in (SESSION variables set) - if so, redirect to main.php)
	if (isset($_SESSION['user_id']) &&  isset($_SESSION['username']) &&  isset($_SESSION['memberno']) && isset($_SESSION['first_name']) && $_SESSION['cloud'] == 'ccsnubev2' ) {
		
		header("Location: main.php");
		exit();
		
		// Issue with one e-mail for several domains!!
		
	// User not logged in - did he submit a form with username for login?
	} else if (isset($_POST['email'])) {
				
		if ($_SESSION['iPadReaders'] > 0) {
			$_SESSION['scanner'] = $_POST['scanner'];
		}
			
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
			$_SESSION['cloud'] = 'ccsnubev2';
			
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
			$_SESSION['cloud'] = 'ccsnubev2';
			
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
			$_SESSION['cloud'] = 'ccsnubev2';
			
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

			// Register login attempt
			$loginTime = date('Y-m-d H:i:s');
			$loginRef = $_SERVER['HTTP_REFERER'];
			$loginURL = $_SERVER['REQUEST_URI'];
			$loginagent = $_SERVER['HTTP_USER_AGENT'];
			$loginIP = $_SERVER['REMOTE_ADDR'];
			// $loginCountry = ip_info("Visitor", "Country");
			// $loginState = ip_info("Visitor", "State");
			// $loginCity = ip_info("Visitor", "City");

			try
			{
				// $result = $pdo->prepare("INSERT INTO logins (time, success, email, agent, referrer, url, ip, country, state, city, comment) VALUES (:loginTime, 0, :email, :loginagent, :loginRef, :loginURL, :loginIP, :loginCountry, :loginState, :loginCity, 'wrong email')");
				$result = $pdo->prepare("INSERT INTO logins (time, success, email, agent, referrer, url, ip, comment) VALUES (:loginTime, 0, :email, :loginagent, :loginRef, :loginURL, :loginIP, 'wrong email')");
				$result->bindValue(':loginTime', $loginTime);
				$result->bindValue(':email', $email);
				$result->bindValue(':loginagent', $loginagent);
				$result->bindValue(':loginRef', $loginRef);
				$result->bindValue(':loginURL', $loginURL);
				$result->bindValue(':loginIP', $loginIP);
				//$result->bindValue(':loginCountry', $loginCountry);
				//$result->bindValue(':loginState', $loginState);
				//$result->bindValue(':loginCity', $loginCity);
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}

			$_SESSION['errorMessage'] = "E-mail doesn't exist / E-mail no existe!";

		// E-mail exists, let's check password
		} else {

			try
			{
				$result = $pdo->prepare("SELECT id, email, password, domain FROM users WHERE email = :email AND password = :userPass");
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

				// Register login attempt
				$loginTime = date('Y-m-d H:i:s');
				$loginRef = $_SERVER['HTTP_REFERER'];
				$loginURL = $_SERVER['REQUEST_URI'];
				$loginagent = $_SERVER['HTTP_USER_AGENT'];
				$loginIP = $_SERVER['REMOTE_ADDR'];
				// $loginCountry = ip_info("Visitor", "Country");
				// $loginState = ip_info("Visitor", "State");
				// $loginCity = ip_info("Visitor", "City");

				try
				{
					// $result = $pdo->prepare("INSERT INTO logins (time, success, email, agent, referrer, url, ip, country, state, city, comment) VALUES (:loginTime, 0, :email, :loginagent, :loginRef, :loginURL, :loginIP, :loginCountry, :loginState, :loginCity, 'wrong email')");
					$result = $pdo->prepare("INSERT INTO logins (time, success, email, agent, referrer, url, ip, comment) VALUES (:loginTime, 0, :email, :loginagent, :loginRef, :loginURL, :loginIP, 'wrong password')");
					$result->bindValue(':loginTime', $loginTime);
					$result->bindValue(':email', $email);
					$result->bindValue(':loginagent', $loginagent);
					$result->bindValue(':loginRef', $loginRef);
					$result->bindValue(':loginURL', $loginURL);
					$result->bindValue(':loginIP', $loginIP);
					//$result->bindValue(':loginCountry', $loginCountry);
					//$result->bindValue(':loginState', $loginState);
					//$result->bindValue(':loginCity', $loginCity);
					$result->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}

				$_SESSION['errorMessage'] = "Incorrect password / Contrase&ntilde;a erronea";

			// Correct pwd
			} else {

				// Register login attempt
				$loginTime = date('Y-m-d H:i:s');
				$loginRef = $_SERVER['HTTP_REFERER'];
				$loginURL = $_SERVER['REQUEST_URI'];
				$loginagent = $_SERVER['HTTP_USER_AGENT'];
				$loginIP = $_SERVER['REMOTE_ADDR'];
				// $loginCountry = ip_info("Visitor", "Country");
				// $loginState = ip_info("Visitor", "State");
				// $loginCity = ip_info("Visitor", "City");

				// Lookup domain
				try
				{
					$result = $pdo->prepare("SELECT domain FROM users WHERE email = :email AND password = :userPass");
					$result->bindValue(':email', $email);
					$result->bindValue(':userPass', crypt($password, $email));
					$result->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}

				$row = $result->fetch();
					$domain = $row['domain'];
					
				try
				{
					// $result = $pdo->prepare("INSERT INTO logins (time, success, email, agent, referrer, url, ip, country, state, city, comment) VALUES (:loginTime, 0, :email, :loginagent, :loginRef, :loginURL, :loginIP, :loginCountry, :loginState, :loginCity, 'wrong email')");
					$result = $pdo->prepare("INSERT INTO logins (time, success, email, agent, referrer, url, ip, comment, domain) VALUES (:loginTime, 0, :email, :loginagent, :loginRef, :loginURL, :loginIP, 'logged in', :domain)");
					$result->bindValue(':loginTime', $loginTime);
					$result->bindValue(':email', $email);
					$result->bindValue(':loginagent', $loginagent);
					$result->bindValue(':loginRef', $loginRef);
					$result->bindValue(':loginURL', $loginURL);
					$result->bindValue(':loginIP', $loginIP);
					$result->bindValue(':domain', $domain);
					// $result->bindValue(':loginCountry', $loginCountry);
					// $result->bindValue(':loginState', $loginState);
					// $result->bindValue(':loginCity', $loginCity);
					$result->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
				
				
					
				try
				{
					$result = $pdo->prepare("SELECT db_pwd FROM db_access WHERE domain = :domain");
					$result->bindValue(':domain', $domain);
					$result->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}

				$row = $result->fetch();
					$db_pwd = $row['db_pwd'];

				$db_name = "ccs_" . $domain;
				$db_user = $db_name . "u";

				$_SESSION['domain'] = $domain;
				$_SESSION['db_name'] = $db_name;
				$_SESSION['db_user'] = $db_user;
				$_SESSION['db_pwd'] = $db_pwd;

				try	{
			 		$pdo2 = new PDO('mysql:host='.DATABASE_HOST.';dbname='.$db_name, $db_user, $db_pwd);
			 		$pdo2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			 		$pdo2->exec('SET NAMES "utf8"');
				}
				catch (PDOException $e)	{
			  		$output = 'Unable to connect to the database server: ' . $e->getMessage();

			 		echo $output;
			 		exit();
				}
				try
				{
					$result = $pdo2->prepare("SELECT first_name, user_id, memberno, email, userGroup, domain, workStation FROM users WHERE email = :email AND userPass = :password;");
					$result->bindValue(':email', $email);
					$result->bindValue(':password', crypt($password, $email));
					$result->execute();
					$data = $result->fetchAll(PDO::FETCH_ASSOC);

				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
				
				// Check if user is registered in club's db - not just in masterdb
				if (!$data) {
					$_SESSION['errorMessage'] = "User not recognised in club database!";
				} else {
				
					// Check if user has permissions to log in (extra layer of security)
					if ($data[0]['userGroup'] > 3) {
						$_SESSION['errorMessage'] = "User does not have the required access level!";
					} else {
							
						$_SESSION['user_id'] = $data[0]['user_id'];
						$_SESSION['username'] = $data[0]['email'];
						$_SESSION['memberno'] = $data[0]['memberno'];
						$_SESSION['first_name'] = $data[0]['first_name'];
						$_SESSION['userGroup'] = $data[0]['userGroup'];
						$_SESSION['workStationAccess'] = $data[0]['workStation'];
						$_SESSION['domain'] = $data[0]['domain'];
						$_SESSION['cloud'] = 'ccsnubev2';
		
						$_SESSION['successMessage'] = $lang['index-loggedin'];
						header("Location: ?");
						exit();
					}
				}
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

	pageStart($lang['title-login'], NULL, $validationScript, "pindex", "loggedOut", NULL, $_SESSION['successMessage'], $_SESSION['errorMessage']);

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
  <!--<br /><a href="forgot-password.php?s=p" style='color: #010084;'><u>Forgot password?</u></a><br />--><br />
  <span>
   <input type='radio' class='specialInput clickbox' name='siteLanguage' value='en' tabindex="3" <?php if ($_SESSION['language'] == 2) { echo 'checked'; } ?> /> English &nbsp;&nbsp;
   <input type='radio' class='specialInput clickbox' name='siteLanguage' value='es' <?php if ($_SESSION['language'] == 1) { echo 'checked'; } ?>/> Espa&ntilde;ol<br />
  </span><br />

  <button name='oneClick' class="visible" type="submit" tabindex="4" >Enviar</button>
  <!--<a href="#" style="margin-left: 36px"><u>Forgot password?</u></a>-->
 </form>
</center>
</div>