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
		$dropEmails = "TRUNCATE bagsizes";
	
		$result = mysql_query($dropEmails)
			or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());

		foreach($_POST['mailRecipients'] as $mailRecipients) {
			
			if ($mailRecipients['id'] == '') {
				$id = '';
			} else {
				$id = $mailRecipients['id'];
			}
			
			$amount = $mailRecipients['amount'];
			
			if ($amount != '') {
				
				// Query to insert e-mail
				$insertEmail = "INSERT INTO bagsizes (amount) VALUES ('$amount')";
				
			
				$result = mysql_query($insertEmail)
					or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
				
			}
			
		}
		
		$_SESSION['successMessage'] = "Tamaños actualizados";
		header("Location: admin.php");
		exit();
		
	}
			
	
	
	// Query to look up emails
	$selectSizes = "SELECT id, amount FROM bagsizes";

	$result = mysql_query($selectSizes)
		or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
		
  	    
	$deleteEmailScript = <<<EOD
function delete_email(emailid) {
	if (confirm("")) {
				window.location = "uTil/delete-email.php?emailid=" + emailid;
				}
}
EOD;

  	pageStart("Tamaño de bolsas", NULL, $deleteEmailScript, "pexpenses", "admin", "Tamaño de bolsas", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
?>
<form id="registerForm" action="" method="POST" >
	 <table class="default nonhover">
	  <thead>
	   <tr>
	    <th>Importe</th>
	    <th></th>
	   </tr>
	  </thead>
	  <tbody>
	  
<?php

	$i = 1;
	
	while ($emailRes = mysql_fetch_array($result)) {
	
		$id = $emailRes['id'];
		$amount = $emailRes['amount'];
	
echo <<<EOD

	   <tr>
	    <td class='left'><input type="number" name="mailRecipients[$i][amount]" class="twoDigit right" value="$amount" /> &euro;<input type="hidden" name="mailRecipients[$i][id]" value="$id" /></td>
	    <td><a href="javascript:delete_email($id)"><img src="images/delete.png" height='15' /></a></td>
	   </tr>


EOD;

	$i++;
	
	}
echo <<<EOD

	   <tr>
	    <td class='left'><input type="number" name="mailRecipients[$i][amount]" class="twoDigit right" /> &euro;</td>
	    <td></td>
	   </tr>
	   
EOD;

	$i++;

echo <<<EOD

	   <tr>
	    <td class='left'><input type="number" name="mailRecipients[$i][size]" class="twoDigit right" /> &euro;</td>
	    <td></td>
	   </tr>
	   
EOD;

	$i++;

echo <<<EOD

	   <tr>
	    <td class='left'><input type="number" name="mailRecipients[$i][amount]" class="twoDigit right" /> &euro;</td>
	    <td></td>
	   </tr>
	   
EOD;
	
?>
	  </tbody>
	 </table>
	 <br />
     <button class='oneClick' name='oneClick' type="submit"><?php echo $lang['global-savechanges']; ?></button>

</form>
	 
	 
<?php displayFooter(); ?>
