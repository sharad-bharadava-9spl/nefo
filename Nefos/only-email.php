<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);	    
	    
	// Query to look up users
	$selectUsers = "SELECT email FROM users WHERE email <> ''";
	
	$result = mysql_query($selectUsers)
		or handleError($lang['error-usersload'],"Error loading users from db: " . mysql_error());
	
	pageStart($lang['email-list'], NULL, NULL, "pusers", "memberlist", $lang['email-list'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	

?>

<center>	  
	  <?php

while ($user = mysql_fetch_array($result)) {

	$user_row =	sprintf("
  	  %s, ",
	  $user['email']
	  );
	  echo $user_row;
  }
?>

	 </center>
	 
<?php  displayFooter(); ?>
