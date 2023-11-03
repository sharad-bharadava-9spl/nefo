<?php

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	
		// Did the user submit a form with username for login?
		if ($_POST['action'] == 'submit') {
					
			// Try to log the user in
			$email = mysql_real_escape_string(trim($_POST['email']));
			$password = mysql_real_escape_string(trim($_POST['password']));
			
			// Look up the provided credentials
			$query = sprintf("SELECT first_name, user_id, memberno, email, userGroup, workStation, domain FROM users WHERE email = '%s' AND userPass = '%s';",
			$email, crypt($password, $email));
			 
			$results = mysql_query($query)
				or handleError($lang['error-crederror'],"Error loading user credentials from db: " . mysql_error());

			if (mysql_num_rows($results) == 1) {
				$result = mysql_fetch_array($results);
				$_SESSION['user_id'] = $result['user_id'];
				$_SESSION['username'] = $result['email'];
				$_SESSION['memberno'] = $result['memberno'];
				$_SESSION['userGroup'] = $result['userGroup'];
				$_SESSION['first_name'] = $result['first_name'];
				$_SESSION['domain'] = $result['domain'];
				$_SESSION['workStationAccess'] = $result['workStation'];
				unset($_SESSION['workstation']);
				$_SESSION['successMessage'] = 'Operador cambiado con exito!';
				header("Location: index.php");
				exit();
			} else {
				$_SESSION['errorMessage'] = 'Contrase&ntilde;a incorrecto';
			}
		}


		if (isset($_GET['loggedinuser'])) {
			$newUser = $_GET['loggedinuser'];
			// Look up the provided credentials
			$query = sprintf("SELECT first_name, last_name, memberno, email, photoExt FROM users WHERE user_id = '%d';",
			$newUser);
			
			$results = mysql_query($query)
				or handleError("Error loading credentials from database.","Error loading user credentials from db: " . mysql_error());

				$row = mysql_fetch_array($results);
					$first_name = $row['first_name'];
					$last_name = $row['last_name'];
					$memberno = $row['memberno'];
					$email = $row['email'];
					$photoExt = $row['photoExt'];

			
	pageStart("Cambiar operador", NULL, $validationScript, "changeuser", NULL, "Cambiar operador", $_SESSION['successMessage'], $_SESSION['errorMessage']);

			echo "<center><div id='profilearea'><img src='images/members/$newUser.$photoExt' class='salesPagePic' /><br /><h4>#$memberno - $first_name $last_name</h4></div></center>";

			
		} else {
			handleError("No user specified","");
		}
		
?>
<br />
<center>
   <form id="registerForm" action="" method="POST">
     <input type="hidden" name='action' value='submit'>
     <input type="hidden" name='user_id' value='<?php echo $user_id; ?>'>
     <input type="hidden" name='email' value='<?php echo $email; ?>'>
     <label for="password">Contrase&ntilde;a:</label>
     <input type="password" name="password" /><br /><br />
      <button name='oneClick' class="visible" type="submit">Enviar</button>
   </form>
</center>