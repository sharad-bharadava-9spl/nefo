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
		$dropEmails = "TRUNCATE usergroups2";
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
			
			$name = str_replace('%', '&#37;', $mailRecipients['name']);
			
			if ($name != '') {
				
				// Query to insert e-mail
				$insertEmail = "INSERT INTO usergroups2 (name) VALUES ('$name')";
				
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
		
		$_SESSION['successMessage'] = "Grupos de usuarios actualizado con éxito!";
		header("Location: usergroups.php");
		exit();
		
	}
			
	
	
	// Query to look up emails
	$selectEmails = "SELECT id, name FROM usergroups2";
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
  
function delete_cuota(groupid) {
	if (confirm("Estas seguro?")) {
				window.location = "uTil/delete-usergroup.php?groupid=" + groupid;
				}
}
EOD;

  	pageStart($lang['usergroups'], NULL, $deleteEmailScript, "pexpenses", "admin", $lang['usergroups'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
?>
<form id="registerForm" action="" method="POST" >
	 <table class="default nonhover">
	  <thead>
	   <tr>
	    <th><?php echo $lang['global-name']; ?></th>
	    <th></th>
	   </tr>
	  </thead>
	  <tbody>
	  
<?php

	$i = 1;

		while ($emailRes = $results->fetch()) {

		$id = $emailRes['id'];
		$name = $emailRes['name'];

echo <<<EOD

	   <tr>
	    <td class='left'><input type="text" class='defaultinput-no-margin' name="mailRecipients[$i][name]" class="eightDigit" value="$name" /></td>
	    <input type="hidden" name="mailRecipients[$i][id]" value="$id" /></td>
	    <td><a href="javascript:delete_cuota($id)"><img src="images/delete.png" height='15' /></a></td>
	   </tr>


EOD;

	$i++;
	
	}
	
echo <<<EOD

	   <tr>
	    <td class='left'><input type="text" class='defaultinput-no-margin' id="name$i" name="mailRecipients[$i][name]" class="eightDigit" /></td>
<td></td>

	   </tr>
	   
EOD;

	$i++;

echo <<<EOD

	   <tr>
	    <td class='left'><input type="text" class='defaultinput-no-margin' id="name2" name="mailRecipients[$i][name]" class="eightDigit" /></td>
<td></td>
	   </tr>
	   
EOD;

	$i++;

echo <<<EOD

	   <tr>
	    <td class='left'><input type="text" class='defaultinput-no-margin' id="name3" name="mailRecipients[$i][name]" class="eightDigit" /></td>
<td></td>
	   </tr>
	   
EOD;

	$i++;

echo <<<EOD

	   <tr>
	    <td class='left'><input type="text" class='defaultinput-no-margin' id="name4" name="mailRecipients[$i][name]" class="eightDigit" /></td>
<td></td>
	   </tr>
	   
EOD;

	$i++;
	
echo <<<EOD

	   <tr>
	    <td class='left'><input type="text" class='defaultinput-no-margin' id="name5" name="mailRecipients[$i][name]" class="eightDigit" /></td>
<td></td>
	   </tr>
	   
EOD;

	$i++;
?>
	  </tbody>
	 </table>
	 <br />
<center>     <button class='cta1' name='oneClick' type="submit"><?php echo $lang['global-savechanges']; ?></button>

</form>
	 
	 
<?php displayFooter(); ?>
