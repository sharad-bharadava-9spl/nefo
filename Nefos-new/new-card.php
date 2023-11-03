<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	if (isset($_POST['user_id'])) {
		$user_id = $_POST['user_id'];
	} else if (isset($_GET['user_id'])) {
		$user_id = $_GET['user_id'];
	} else {
		handleError($lang['error-nouserid'],"");
	}

	$findScan = "SELECT chip FROM newscan ORDER BY scanid DESC LIMIT 1";
	
	$result = mysql_query($findScan)
			or handleError($lang['error-savedata'],"Error inserting user: " . mysql_error());
			
	if (mysql_num_rows($result) == 0) {
		
		pageStart("Añadir tarjeta", NULL, $deleteNoteScript, "pprofilenew", NULL, "Añadir tarjeta", $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
		echo <<<EOD
<script>
setTimeout(function () { window.location.reload(); }, 2000);
</script>
<center>
     <h2 style="font-size: 20px;">Pasa la tarjeta para continuar.</h2>
     <br />
     <img src="images/tarjetascan.png" />

</center>
EOD;

	} else {
		
		$row = mysql_fetch_array($result);
			$chip = $row['chip'];
			
		// Add card to user
		$updateUser = "UPDATE users SET cardid = '$chip' WHERE user_id = $user_id";
		
		mysql_query($updateUser)
			or handleError($lang['error-savedata'],"Error inserting user: " . mysql_error());
			
			
		// Delete scans
		$deleteScans = "DELETE FROM newscan";
		
		mysql_query($deleteScans)
			or handleError($lang['error-savedata'],"Error inserting user: " . mysql_error());
			
		$_SESSION['successMessage'] = "Tarjeta añadido con éxito!";
		
		header("Location: profile.php?user_id=$user_id");
		
	}

displayFooter();