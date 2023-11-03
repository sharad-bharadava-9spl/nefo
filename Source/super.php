<?php
	
	require_once 'cOnfig/connection-master.php';
	require_once 'cOnfig/view-loggedout.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();

	if ($_SESSION['user_id'] != 999999) {
		echo "No access";
		exit();
	}
	
	if (isset($_POST['choseclub']) && isset($_POST['domain'])) {
	
		$domain = $_POST['domain'];
		$email = 'super@user.com';
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

		
		// Check if user is registered in club's db - not just in masterdb
		$_SESSION['user_id'] = 999999;
		$_SESSION['username'] = $email;
		$_SESSION['memberno'] = 999999;
		$_SESSION['first_name'] = 'CCS';
		$_SESSION['userGroup'] = 1;
		$_SESSION['workStationAccess'] = 16;
		$_SESSION['cloud'] = 'ccsnubev2';
		$_SESSION['domain'] = $domain;
		
		$_SESSION['successMessage'] = $lang['index-loggedin'];
		header("Location: index.php");
		exit();
	}

	
	
	// Allow user to select domain
	try
	{
		$results = $pdo->prepare("SELECT DISTINCT domain FROM users ORDER BY domain ASC");
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
  <input type="hidden" name='domain' value='$domain'>
  <input type="hidden" name='choseclub' value='yes'>
  <button type='submit' class='linkStyle'>$domain<br /></button></form><br />
EOD;

	}
						
					exit();