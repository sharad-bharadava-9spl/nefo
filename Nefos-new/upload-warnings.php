<?php

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	if ($_POST['ready'] == 'yes') {
		
		$cutoff = date("Y-m-d", strtotime($_POST['cutoff']));
		$clients = explode (",", $_POST['clients']);
		
		
		
		foreach ($clients as $number) {
			
			// Check if any client already has warning / has been cut off.
			
			$query = "SELECT warning FROM db_access WHERE customer = '$number'";
			try
			{
				$result = $pdo->prepare("$query");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
				$warning = $row['warning'];
				
			if ($warning > 0) {
				
				if ($warning == 1) {
					
					$warningText = "Soft warning";
					
				} else if ($warning == 2) {
					
					$warningText = "Final warning";
					
				} else if ($warning == 3) {
					
					$warningText = "Cut off";
					
				}
				
				$notadded .= "Warning NOT added for $number!! Client already has warning status: $warningText<br />";
				
			} else {
			
				$query = "UPDATE db_access SET warning = 1, cutoff = '$cutoff' WHERE customer = '$number'";
				try
				{
					$result = $pdo->prepare("$query")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
				
				$added .= "Warning added for customer $number.<br />";
				
			}
			
		}

		$_SESSION['successMessage'] = "Warnings set - cutoff date: " . $_POST['cutoff'];
		pageStart("Upload warnings", NULL, $validationScript, "pprofile", NULL, "Upload warnings", $_SESSION['successMessage'], $_SESSION['errorMessage']);
		echo <<<EOD
 <div id='mainbox-new-club'>
  <div id='mainboxheader'>
   <center>
    Results
   </center>
  </div>
  <div class='boxcontent'>
EOD;
		echo "<span style='color: red;'>$notadded</span>";
		echo $added;
		echo "</div></div>";
		exit();
					
	}

	
	$validationScript = <<<EOD
	
	  $( function() {
	    $( "#datepicker" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });
	  });
EOD;
		
	pageStart("Upload warnings", NULL, $validationScript, "pprofile", NULL, "Upload warnings", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	

?>
<form id="step3" action="" method="POST">
<input type="hidden" name="ready" value="yes" />
<input type="hidden" name="number" value="<?php echo $number; ?>" />
<input type="hidden" name="notid" value="<?php echo $notid; ?>" />
<input type="hidden" name="hash" value="<?php echo $hash; ?>" />
 <div id='mainbox-new-club'>
  <div id='mainboxheader'>
   <center>
    Upload warnings
   </center>
  </div>
  <div class='boxcontent'>
<center>
   <textarea name="clients" class="defaultinput" placeholder="Paste comma-separated list of customer numbers here" style="width: 240px; height: 150px;"></textarea><br />
   <input type="text" name="cutoff" value="<?php echo date('02-m-Y', strtotime('first day of next month')); ?>" class="defaultinput sixDigit" id="datepicker" /><br />
</center>
   
  </div>
 </div>
<center>
 <button type="submit" name='step3_sub' class='cta1'>Submit</button>
</center>
</form>

<?php

displayFooter();