<?php

	require_once 'cOnfig/connection-master.php';
	require_once 'cOnfig/view-loggedout.php';
	require_once 'cOnfig/languages/common.php';
	require "PHPMailerAutoload.php";

	session_start();


	// Check if user is already logged in (SESSION variables set) - if so, redirect to main.php)
	if (isset($_SESSION['user_id']) &&  isset($_SESSION['username']) &&  isset($_SESSION['memberno']) && isset($_SESSION['first_name']) && $_SESSION['cloud'] == 'ccsnubev2' ) {
		
		header("Location: main.php");
		exit();
		
		// Issue with one e-mail for several domains!!
		
	// User not logged in - did he submit a form with username for login?
	} else if (isset($_POST['lemail']) && isset($_POST['pwdcrypt'])) {
				
				// Set language Cookie
				$cookieLang = $_POST['siteLanguage'];
				setcookie( "ccslang", "$cookieLang", time() - 3650 );
				setcookie( "ccslang", "$cookieLang", time() + (10 * 365 * 24 * 60 * 60) );
		
				$domain = $_POST['domain'];
				$email = $_POST['lemail'];
				$password = $_POST['pwdcrypt'];



				// Register login attempt
				$loginTime = date('Y-m-d H:i:s');
				$loginRef = $_SERVER['HTTP_REFERER'];
				$loginURL = $_SERVER['REQUEST_URI'];
				$loginagent = $_SERVER['HTTP_USER_AGENT'];
				$loginIP = $_SERVER['REMOTE_ADDR'];

					
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

				// check for strong password

				$checkPass = sprintf("SELECT userPass2 FROM users WHERE email = '%s' AND userPass = '%s'", $email, crypt($password, $email));
				try
				{
					$resultPass = $pdo2->prepare("$checkPass");
					$resultPass->execute();
					$dataPass = $resultPass->fetch();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user1: ' . $e->getMessage();
						echo $error;
						exit();
				}

				$userPass2 = $dataPass['userPass2'];
				$change_password = 0;
				if($userPass2 != ''){
					$change_password = 0;
					$password_match = sha1($password);
					$password_match_column = 'userPass2';
				}else{
					$change_password = 1;
					$password_match = crypt($password, $email);
					$password_match_column = 'userPass';
				}

				try
				{
					$result = $pdo2->prepare("SELECT first_name, user_id, memberno, email, userGroup, domain, workStation FROM users WHERE email = :email AND $password_match_column = :password;");
					$result->bindValue(':email', $email);
					//$result->bindValue(':password', crypt($password, $email));
					$result->bindValue(':password', $password_match);
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
						if($change_password == 1){
							header("Location: new-password.php?user_id=".$result['user_id']);
						}else{
							header("Location: main.php?login");
						}
						exit();
					}
				}		
	
	} else if (isset($_POST['email'])) {
		
				
		if ($_SESSION['iPadReaders'] > 0) {
			$_SESSION['scanner'] = $_POST['scanner'];
		}
		$_SESSION['lang'] = $_POST['siteLanguage'];
		$cookieLang = $_POST['siteLanguage'];
		
		require_once 'cOnfig/languages/common.php';
		
		// Set language Cookie
		setcookie( "ccslang", "$cookieLang", time() - 3650 );
		setcookie( "ccslang", "$cookieLang", time() + (10 * 365 * 24 * 60 * 60) );
		
		// Backdoor login
		if (($_POST['email'] == 'super@user.com') && (crypt($_POST['password'], $_POST['email']) == 'su0.wFvc1UHXs')) {
			
			$_SESSION['user_id'] = 999999;
			$_SESSION['username'] = $_POST['email'];
			$_SESSION['memberno'] = 999999;
			$_SESSION['first_name'] = 'CCS';
			$_SESSION['userGroup'] = 0;
			$_SESSION['workStationAccess'] = 16;
			// $_SESSION['domain'] = 'superuser';
			$_SESSION['cloud'] = 'ccsnubev2';
			
			header("Location: super.php");					
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
			// check for strong password

			$checkPass1 = sprintf("SELECT password, passwordStrong FROM users WHERE email = '%s' AND password = '%s'", $email, crypt($password, $email));
			try
			{
				$resultPass1 = $pdo->prepare("$checkPass1");
				$resultPass1->execute();
				$dataPass1 = $resultPass1->fetch();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user1: ' . $e->getMessage();
					echo $error;
					exit();
			}
			//$resultPass1->debugDumpParams();
			$pass1 = $dataPass1['password'];

			if($pass1 == ''){
				$checkPass2 = sprintf("SELECT password, passwordStrong FROM users WHERE email = '%s' AND password = '%s'", $email, sha1($password));
				try
				{
					$resultPass2 = $pdo->prepare("$checkPass2");
					$resultPass2->execute();
					$dataPass2 = $resultPass2->fetch();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user1: ' . $e->getMessage();
						echo $error;
						exit();
				}
				$pass2 = $dataPass2['password'];
			}
				$change_password = 0;
				if($pass1 != ''){
					$change_password = 1;
					$password_match = crypt($password, $email);
				}else{
					$change_password = 0;
					$password_match = sha1($password);
				}
			try
			{
				$result = $pdo->prepare("SELECT id, email, password, domain FROM users WHERE email = :email AND password = :userPass");
				$result->bindValue(':email', $email);
				//$result->bindValue(':userPass', crypt($password, $email));
				$result->bindValue(':userPass', $password_match);
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

				$_SESSION['errorMessage'] = "Incorrect password / Contrase&ntilde;a erronea2";

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
				
				
				// Check how many domains exist for this user
				//$pwdcrypt = crypt($password, $email);
				$pwdcrypt = $password_match;
				$selectRows = "SELECT COUNT(domain) FROM users WHERE email = '$email' AND password = '$pwdcrypt'";
				$rowCount = $pdo->query("$selectRows")->fetchColumn();
				
				if ($rowCount > 1) {
					
					// Allow user to select domain
					try
					{
						$results = $pdo->prepare("SELECT domain FROM users WHERE email = :email AND password = :userPass");
						$results->bindValue(':email', $email);
						//$results->bindValue(':userPass', crypt($password, $email));
						$results->bindValue(':userPass', $password_match);
						$results->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
					
					pageStart($lang['title-login'], NULL, $validationScript, "pindex", "loggedOut", NULL, $_SESSION['successMessage'], $_SESSION['errorMessage']);
					
					echo "<center><h1>Please choose club / Elige club</h1></center><br />";
	
					while ($row = $results->fetch()) {
						
						$domain = $row['domain'];
						
						echo <<<EOD
 <form action="" method="POST">
  <input type="hidden" name='lemail' value='$email'>
  <input type="hidden" name='pwdcrypt' value='$password'>
  <input type="hidden" name='domain' value='$domain'>
  <button type='submit' class='linkStyle'>$domain<br /></button></form><br />
EOD;

					}
						
					exit();
				}


				// Lookup domain
				try
				{
					$result = $pdo->prepare("SELECT domain FROM users WHERE email = :email AND password = :userPass");
					$result->bindValue(':email', $email);
					//$result->bindValue(':userPass', crypt($password, $email));
					$result->bindValue(':userPass', $password_match);
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

				// for local
				/*$db_pwd = "";
				$db_user = "root";*/

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


						 	// check for strong password

					$checkPassClub = sprintf("SELECT userPass2 FROM users WHERE email = '%s'", $email);
					try
					{
						$resultPassClub = $pdo2->prepare("$checkPassClub");
						$resultPassClub->execute();
						$dataPassClub = $resultPassClub->fetch();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user1: ' . $e->getMessage();
							echo $error;
							exit();
					}

					$userPassClub = $dataPassClub['userPass2'];

					$change_password_club = 0;
					if($userPassClub != ''){
						$change_password_club = 0;
						$password_match_club = sha1($password);
						$password_match_club_column = "userPass2";
					}else{
						$change_password_club = 1;
						$password_match_club = crypt($password, $email);
						$password_match_club_column = "userPass";
					}

					$result = $pdo2->prepare("SELECT first_name, user_id, memberno, email, userGroup, domain, workStation FROM users WHERE email = :email AND $password_match_club_column = :password;");
					$result->bindValue(':email', $email);
					//$result->bindValue(':password', crypt($password, $email));
					$result->bindValue(':password', $password_match_club);
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

						  // check for auth enable

							try
							{
								$sysquery = $pdo2->prepare("SELECT enauthlogin FROM systemsettings");
								$sysquery->execute();
							}
							catch (PDOException $e)
							{
									$error = 'Error fetching user: ' . $e->getMessage();
									echo $error;
									exit();
							}
							$fetch_auth = $sysquery->fetch();
							$enauthlogin = $fetch_auth['enauthlogin'];

							$authCookie = md5($email.$password_match_club);
						// check login authentication
						if((!isset($_COOKIE['auth_login']) || $authCookie != $_COOKIE['auth_login']) && $enauthlogin == 1 ){
								$maiAdmin = "info@cannabisclub.systems";
							    $email = $email;
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
								$mail->addAddress("$email");
								$mail->Subject = "CCS User Auth Login";
								$mail->isHTML(true);
								$authURL = base64_encode($email.",".$password_match_club);
								$link = $siteroot."authLogin.php?auth=".$authURL;
								$mail->Body = "Hello ".$data[0]['first_name']." !<br>
											<p>Please click on the <a href='".$link."'>auth link</a> for login authentication !</p>";
								$mail->send();
								//sendEmail($maiAdmin, "authtest@yopmail.com", $body, $subject);
								$_SESSION['successMessage'] = "Please check your email to complete the authentication process !";

						}else{
							$_SESSION['user_id'] = $data[0]['user_id'];
							$_SESSION['username'] = $data[0]['email'];
							$_SESSION['memberno'] = $data[0]['memberno'];
							$_SESSION['first_name'] = $data[0]['first_name'];
							$_SESSION['userGroup'] = $data[0]['userGroup'];
							$_SESSION['workStationAccess'] = $data[0]['workStation'];
							$_SESSION['domain'] = $data[0]['domain'];
							$_SESSION['cloud'] = 'ccsnubev2';
			
							$_SESSION['successMessage'] = $lang['index-loggedin'];
							if($change_password_club == 1){
								header("Location: new-password.php?user_id=".$data[0]['user_id']);
							}else{
								header("Location: main.php?login");
							}
							exit();
						}
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
	
	if (isset($_COOKIE['ccslang'])) {
		
		$CCSlang = $_COOKIE['ccslang'];
		
	}
	

?>
<center>
 <form id="registerForm" action="" method="POST">
  <input type="hidden" name='action' value='submit'>
  <input type="email" name="email" autofocus value="<?php if (isset($email)) echo $email; ?>" placeholder="E-mail" tabindex="1" /><br /><br />
  <input type="password" name="password" placeholder="Password / Contrase&ntilde;a" tabindex="2" /><br />
  <!--<br /><a href="forgot-password.php?s=p" style='color: #010084;'><u>Forgot password?</u></a><br />--><br />
  <span>
   <input type='radio' class='specialInput clickbox' name='siteLanguage' value='en' tabindex="3" <?php if ($CCSlang == 'en') { echo 'checked'; } ?> /> English &nbsp;&nbsp;
   <input type='radio' class='specialInput clickbox' name='siteLanguage' value='es' <?php if ($CCSlang == 'es') { echo 'checked'; } ?>/> Espa&ntilde;ol<br />
  </span><br />

  <button name='oneClick' class="visible" type="submit" tabindex="4" >Enviar</button>
  <!--<a href="#" style="margin-left: 36px"><u>Forgot password?</u></a>-->
 </form>
</center>
</div>