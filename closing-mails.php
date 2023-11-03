<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '1';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);

	$flag = 0;
	if(isset($_POST['name']) || $_POST['name'] == 'admin_email'){
		$flag = 1;
	}
	
	// Did this page re-submit with a form? If so, check & store details
	if (isset($_POST['mailRecipients'])) {
		
		// Query to look up emails
		// Query to look up emails
		$dropEmails = "DELETE FROM closing_mails;";
		try
		{
			$result = $pdo3->prepare("$dropEmails")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user delete: ' . $e->getMessage();
				echo $error;
				exit();
		}
		$dropEmails = "ALTER TABLE closing_mails AUTO_INCREMENT=1;";
		try
		{
			$result = $pdo3->prepare("$dropEmails")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user alter: ' . $e->getMessage();
				echo $error;
				exit();
		}

		foreach($_POST['mailRecipients'] as $mailRecipients) {
			
			if ($mailRecipients['id'] == '') {
				$id = '';
			} else {
				$id = $mailRecipients['id'];
			}
			
			$name = $mailRecipients['name'];
			$email = $mailRecipients['email'];
			
			if ($email != '') {
				
				// Query to insert e-mail
				$insertEmail = "INSERT INTO closing_mails (name, email) VALUES ('$name', '$email')";
		try
		{
			$result = $pdo3->prepare("$insertEmail")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
				
			}
			
		}
		
/*		$_SESSION['successMessage'] = $lang['emails-updated'];
		header("Location: sys-settings.php");
		exit();*/
			$response_msg  = array("successMessage" => $lang['emails-updated']);
			echo json_encode($response_msg);
			die;
		
	}
			
	
	
	// Query to look up emails
	$selectEmails = "SELECT id, name, email FROM closing_mails";
		try
		{
			$results = $pdo3->prepare("$selectEmails");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		
  	    
	$deleteEmailScript = <<<EOD
function delete_email(emailid) {
	if (confirm("")) {
				window.location = "uTil/delete-email.php?emailid=" + emailid;
				}
}
EOD;
  if($flag == 0){
  	pageStart("E-mail", NULL, $deleteEmailScript, "pexpenses", "admin", "E-MAIL", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	}
	

$admin_data =  "<form id='registerForm2' action='".$_SERVER['PHP_SELF']."' method='POST' >
	 <table class='default nonhover'>
	  <thead>
	   <tr>
	    <th>".$lang['global-name']."</th>
	    <th>".$lang['member-email']."</th>
	    <th></th>
	   </tr>
	  </thead>
	  <tbody>";


	$i = 1;
	
		while ($emailRes = $results->fetch()) {
	
		$id = $emailRes['id'];
		$name = $emailRes['name'];
		$email = $emailRes['email'];
	
$admin_data .="
	   <tr>
	    <td class='left'><input type='text' name='mailRecipients[$i][name]' class='eightDigit defaultinput-no-margin' value='$name' /></td>
	    <td class='left'><input type='email' name='mailRecipients[$i][email]' class='eightDigit defaultinput-no-margin' value='$email' /><input type='hidden' name='mailRecipients[$i][id]' value='$id' /></td>
	    <td><a href='javascript:delete_email($id)'><img src='images/delete.png' height='15' /></a></td>
	   </tr>";
	$i++;
	
	}
$admin_data .="

	   <tr>
	    <td class='left'><input type='text' name='mailRecipients[$i][name]' class='eightDigit defaultinput-no-margin' /></td>
	    <td class='left'><input type='email' name='mailRecipients[$i][email]' class='eightDigit defaultinput-no-margin' /></td>
	    <td></td>
	   </tr>";


	$i++;

$admin_data .="

	   <tr>
	    <td class='left'><input type='text' name='mailRecipients[$i][name]' class='eightDigit defaultinput-no-margin' /></td>
	    <td class='left'><input type='email' name='mailRecipients[$i][email]' class='eightDigit defaultinput-no-margin' /></td>
	    <td></td>
	   </tr>";
	   

	$i++;

$admin_data .="

	   <tr>
	    <td class='left'><input type='text' name='mailRecipients[$i][name]' class='eightDigit defaultinput-no-margin' /></td>
	    <td class='left'><input type='email' name='mailRecipients[$i][email]' class='eightDigit defaultinput-no-margin' /></td>
	    <td></td>
	   </tr>";
	   

	

$admin_data .="</tbody>
	 </table>
	 <br />
     <center><button class='cta1' name='oneClick' type='submit'>".$lang['global-savechanges']."</button>

</form>";

$response = array();
if($flag == 1){
	$response  = array("admin_data" => $admin_data);
	echo json_encode($response);
	die;
}else{
	echo $admin_data;
	displayFooter();
}
