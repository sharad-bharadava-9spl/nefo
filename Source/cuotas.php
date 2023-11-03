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
		$dropEmails = "TRUNCATE cuotas";
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
			
			$cuota = str_replace('%', '&#37;', $mailRecipients['cuota']);
			$days = str_replace('%', '&#37;', $mailRecipients['days']);
			$name = str_replace('%', '&#37;', $mailRecipients['name']);
			
			if ($cuota != '') {
				
				// Query to insert e-mail
				$insertEmail = "INSERT INTO cuotas (name, cuota, days) VALUES ('$name', '$cuota', '$days')";
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
		
		$_SESSION['successMessage'] = $lang['memberfees-updated'];
		header("Location: sys-settings.php");
		exit();
		
	}
			
	
	
	// Query to look up emails
	$selectEmails = "SELECT id, name, cuota, days FROM cuotas";
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
	
    $(document).ready(function() {
	    	    
	  $('#registerForm').validate({
		  rules: {

			  days1: {
				  required: function () {
                	return $('#name1').val().length > 0;
            	  }
			  }
    	}, // end rules
    	errorPlacement: function(error, element) { }
	  }); // end validate
  }); // end ready
  
function delete_cuota(cuotaid) {
	if (confirm("")) {
				window.location = "uTil/delete-cuota.php?cuotaid=" + cuotaid;
				}
}
EOD;

  	pageStart($lang['memberfees'], NULL, $deleteEmailScript, "pexpenses", "admin", $lang['memberfees'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
?>
<form id="registerForm" action="" method="POST" >
	 <table class="default nonhover">
	  <thead>
	   <tr>
	    <th><?php echo $lang['global-name']; ?></th>
	    <th><?php echo $lang['global-amount']; ?></th>
	    <th><?php echo $lang['period-in-days']; ?></th>
	    <th></th>
	   </tr>
	  </thead>
	  <tbody>
	  
<?php

	$i = 1;

		while ($emailRes = $results->fetch()) {

		$id = $emailRes['id'];
		$name = $emailRes['name'];
		$cuota = $emailRes['cuota'];
		$days = $emailRes['days'];

echo <<<EOD

	   <tr>
	    <td class='left'><input type="text" name="mailRecipients[$i][name]" class="eightDigit defaultinput-no-margin" value="$name" /></td>
	    <td class='left'><input type="number" name="mailRecipients[$i][cuota]" class="twoDigit defaultinput-no-margin" value="$cuota" /></td>
	    <td class='left'><input type="number" name="mailRecipients[$i][days]" class="twoDigit defaultinput-no-margin" value="$days" />
	    <input type="hidden" name="mailRecipients[$i][id]" value="$id" /></td>
	    <td><a href="javascript:delete_cuota($id)"><img src="images/delete.png" height='15' /></a></td>
	   </tr>


EOD;

	$i++;
	
	}
	
echo <<<EOD

	   <tr>
	    <td class='left'><input type="text" id="name$i" name="mailRecipients[$i][name]" class="eightDigit defaultinput-no-margin" placeholder="{$lang['example-one-month']}" /></td>
	    <td class='left'><input type="number" id="cuota$i" name="mailRecipients[$i][cuota]" class="twoDigit defaultinput-no-margin abc" placeholder="€" /></td>
	    <td class='left'><input type="number" id="days$i" name="mailRecipients[$i][days]" class="twoDigit defaultinput-no-margin" placeholder="#" />
<script>
    $(document).ready(function() {
	    
		$('#name$i').rules('add', {
		  required: function () {
                	return $('#cuota$i').val().length > 0;
            	  }
		});
		$('#days$i').rules('add', {
		  required: function () {
                	return $('#cuota$i').val().length > 0;
            	  }
		});
		$('#cuota$i').rules('add', {
		  required: function () {
                	return $('#days$i').val().length > 0;
            	  }
		});

  }); // end ready
</script>
</td>
<td></td>
	   </tr>
	   
EOD;

	$i++;

echo <<<EOD

	   <tr>
	    <td class='left'><input type="text" id="name2" name="mailRecipients[$i][name]" class="eightDigit defaultinput-no-margin" placeholder="{$lang['example-30-days']}" /></td>
	    <td class='left'><input type="number" id="cuota2" name="mailRecipients[$i][cuota]" class="twoDigit defaultinput-no-margin" placeholder="€" /></td>
	    <td class='left'><input type="number" id="days2" name="mailRecipients[$i][days]" class="twoDigit defaultinput-no-margin" placeholder="#" />
<td></td>
	   </tr>
	   
EOD;

	$i++;

echo <<<EOD

	   <tr>
	    <td class='left'><input type="text" id="name3" name="mailRecipients[$i][name]" class="eightDigit defaultinput-no-margin" placeholder="{$lang['example-quarter']}" /></td>
	    <td class='left'><input type="number" id="cuota3" name="mailRecipients[$i][cuota]" class="twoDigit defaultinput-no-margin" placeholder="€" /></td>
	    <td class='left'><input type="number" id="days3" name="mailRecipients[$i][days]" class="twoDigit defaultinput-no-margin" placeholder="#" />
<td></td>
	   </tr>
	   
EOD;

	$i++;

echo <<<EOD

	   <tr>
	    <td class='left'><input type="text" id="name4" name="mailRecipients[$i][name]" class="eightDigit defaultinput-no-margin" placeholder="{$lang['example-semester']}" /></td>
	    <td class='left'><input type="number" id="cuota4" name="mailRecipients[$i][cuota]" class="twoDigit defaultinput-no-margin" placeholder="€" /></td>
	    <td class='left'><input type="number" id="days4" name="mailRecipients[$i][days]" class="twoDigit defaultinput-no-margin" placeholder="#" />
<td></td>
	   </tr>
	   
EOD;

	$i++;
	
echo <<<EOD

	   <tr>
	    <td class='left'><input type="text" id="name5" name="mailRecipients[$i][name]" class="eightDigit defaultinput-no-margin" placeholder="{$lang['example-yearly']}" /></td>
	    <td class='left'><input type="number" id="cuota5" name="mailRecipients[$i][cuota]" class="twoDigit defaultinput-no-margin" placeholder="€" /></td>
	    <td class='left'><input type="number" id="days5" name="mailRecipients[$i][days]" class="twoDigit defaultinput-no-margin" placeholder="#" />
<td></td>
	   </tr>
	   
EOD;

	$i++;
?>
	  </tbody>
	 </table>
	 <br />
     <center><button class='cta1' name='oneClick' type="submit"><?php echo $lang['global-savechanges']; ?></button>

</form>
	 
	 
<?php displayFooter(); ?>
