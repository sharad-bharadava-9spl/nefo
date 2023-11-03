<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/view.php';
	require_once '../cOnfig/authenticate.php';
	require_once '../cOnfig/languages/common.php';

	session_start();
	//$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	if ($_POST['confirmed'] == 'yes') {
		
		// Get the purchase ID
		$paymentid = $_POST['paymentid'];
		$comment = $_POST['comment'];
			
		// Lookup old expiry date
		$oldExpiry = "SELECT userid, oldExpiry, newExpiry, amountPaid, paidTo FROM entrancepayments WHERE paymentid = $paymentid";
		
		try
		{
			$result = $pdo3->prepare("$oldExpiry");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$userid = $row['userid'];
			$oldExpiry = $row['oldExpiry'];
			$newExpiry = $row['newExpiry'];
			$amount = $row['amountPaid'];
			$paidTo = $row['paidTo'];
			
			if ($oldExpiry == '' || $oldExpiry == NULL) {
				$oldExpiry = date('Y-m-d H:i:s');
			}
			
			// Adjust member credit if he paid from saldo
			if ($paidTo == 3) {
				
				// Look up user to find credit balance
				$userDetails = "SELECT credit FROM users WHERE user_id = $userid";
						
				try
				{
					$result = $pdo3->prepare("$userDetails");
					$result->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			
				$row = $result->fetch();
					$credit = $row['credit'];
					
				$newCredit = $credit + $amount;
				
				// Update user table
				$updateUser = sprintf("UPDATE users SET credit = '%f' WHERE user_id = '%d';",
					$newCredit,	$userid);
		
				try
				{
					$result = $pdo3->prepare("$updateUser")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
				
			}
				
		
		
			// Update user table
			$updateUser = sprintf("UPDATE users SET entrancePaidUntil = '%s' WHERE user_id = '%d';",
			$oldExpiry, $userid);
	
				try
				{
					$result = $pdo3->prepare("$updateUser")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
		
			// Delete the donation
			$deleteDonation = sprintf("DELETE FROM entrancepayments WHERE paymentid = '%d';", $paymentid);
			
				try
				{
					$result = $pdo3->prepare("$deleteDonation")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
					
					
		
			// On success: redirect.
			$_SESSION['successMessage'] = "Entrance Payment deleted succesfully!";
			
			if ($_POST['paymentscreen'] == 'yes') {
				header("Location: ../entrance-payments.php");
			} else {
				header("Location: ../pay-entrance.php?user_id=$userid");
			}
			
			exit();

	}


	
	// Get the purchase ID
	$paymentid = $_GET['paymentid'];
	
	$validationScript = <<<EOD
    $(document).ready(function() {
	    	    
	  $('#registerForm').validate({
		  rules: {
			  comment: {
				  required: true,
				  minlength: 2
			  }
    	}, // end rules
		  errorPlacement: function(error, element) {
			  if ( element.is(":radio") || element.is(":checkbox")){
				 error.appendTo(element.parent());
			} else {
				return true;
			}
		},
    	  submitHandler: function() {
   $(".oneClick").attr("disabled", true);
   form.submit();
	    	  }
	  }); // end validate

  }); // end ready
EOD;

	
	pageStart("Delete Entrance fees", NULL, $deleteDonationScript, "pprofilenew", "donations fees", "Delete Entrance fees", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	

?>
<center>
<div id='donationholder2'>
 <form id="registerForm" action="" method="POST">

 <h4><?php echo $lang['delete-reason']; ?></h4>
 <br />

  <textarea name="comment" placeholder="<?php echo $lang['global-comment']; ?>?" style='width: 346px; height: 100px;'></textarea><br /><br />
  <input type='hidden' name='confirmed' value='yes' />
  <input type='hidden' name='paymentid' value='<?php echo $paymentid; ?>' />
<?php
	if (isset($_GET['paymentscreen'])) {
		echo "<input type='hidden' name='paymentscreen' value='yes' />";
	}
?>
        

  <button class='oneClick okbutton2' name='oneClick' type="submit" style='margin-left: -2px; width: 286px;'><?php echo $lang['global-confirm']; ?></button></td>

 </form>
</div>

