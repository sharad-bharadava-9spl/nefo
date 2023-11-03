<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '1';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Did this page re-submit with a form? If so, check & store details
	if (isset($_POST['mailRecipients'])) {
		
		// Query to look up emails
		$dropEmails = "TRUNCATE closing_mails";
		try
		{
			$result = $pdo3->prepare("$dropEmails")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
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
		
		$_SESSION['successMessage'] = $lang['emails-updated'];
		header("Location: sys-settings.php");
		exit();
		
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

  	pageStart("E-mail", NULL, $deleteEmailScript, "pexpenses", "admin", "E-MAIL", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
?>
<form id="registerForm" action="" method="POST" >
	 <table class="default nonhover">
	  <thead>
	   <tr>
	    <th><?php echo $lang['global-name']; ?></th>
	    <th><?php echo $lang['member-email']; ?></th>
	    <th></th>
	   </tr>
	  </thead>
	  <tbody>
	  
<?php

	$i = 1;
	
		while ($emailRes = $results->fetch()) {
	
		$id = $emailRes['id'];
		$name = $emailRes['name'];
		$email = $emailRes['email'];
	
echo <<<EOD

	   <tr>
	    <td class='left'><input type="text" name="mailRecipients[$i][name]" class="eightDigit defaultinput-no-margin" value="$name" /></td>
	    <td class='left'><input type="email" name="mailRecipients[$i][email]" class="eightDigit defaultinput-no-margin" value="$email" /><input type="hidden" name="mailRecipients[$i][id]" value="$id" /></td>
	    <td><a href="javascript:delete_email($id)"><img src="images/delete.png" height='15' /></a></td>
	   </tr>


EOD;

	$i++;
	
	}
echo <<<EOD

	   <tr>
	    <td class='left'><input type="text" name="mailRecipients[$i][name]" class="eightDigit defaultinput-no-margin" /></td>
	    <td class='left'><input type="email" name="mailRecipients[$i][email]" class="eightDigit defaultinput-no-margin" /></td>
	    <td></td>
	   </tr>
	   
EOD;

	$i++;

echo <<<EOD

	   <tr>
	    <td class='left'><input type="text" name="mailRecipients[$i][name]" class="eightDigit defaultinput-no-margin" /></td>
	    <td class='left'><input type="email" name="mailRecipients[$i][email]" class="eightDigit defaultinput-no-margin" /></td>
	    <td></td>
	   </tr>
	   
EOD;

	$i++;

echo <<<EOD

	   <tr>
	    <td class='left'><input type="text" name="mailRecipients[$i][name]" class="eightDigit defaultinput-no-margin" /></td>
	    <td class='left'><input type="email" name="mailRecipients[$i][email]" class="eightDigit defaultinput-no-margin" /></td>
	    <td></td>
	   </tr>
	   
EOD;
	
?>
	  </tbody>
	 </table>
	 <br />
     <center><button class='cta1' name='oneClick' type="submit"><?php echo $lang['global-savechanges']; ?></button>

</form>
	 
	 
<?php displayFooter(); ?>
