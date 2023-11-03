<?php

	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/viewv6.php';
	require_once '../cOnfig/authenticate.php';
	require_once '../cOnfig/languages/common.php';

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	
	if ($_POST['confirmed'] == 'yes') {
		
		$id = $_POST['id'];
		$comment = $_POST['comment'];
		$nowDate = date('Y-m-d H:i');
	
		// Delete the donation
		$query = "UPDATE feedback SET status = 5, closedby = 888888, closecomment = '$comment', closedat = '$nowDate' WHERE id = '$id'";
		try
		{
			$result = $pdo3->prepare("$query")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		$_SESSION['successMessage'] = "Ticket closed succesfully!";
		header("Location: ../feedback.php");
		exit();
		
	}
	
	// Get the purchase ID
	$id = $_GET['id'];

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

	
	pageStart("Close ticket", NULL, $deleteDonationScript, "pprofilenew", "donations fees", "Close ticket", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	

?>
<center>
<div id='donationholder2'>
 <form id="registerForm" action="" method="POST">

 <h4>Why are you closing this ticket?</h4>
 <br />

  <textarea name="comment" placeholder="<?php echo $lang['global-comment']; ?>?" style='width: 346px; height: 100px;'></textarea><br /><br />
  <input type='hidden' name='confirmed' value='yes' />
  <input type='hidden' name='id' value='<?php echo $id; ?>' />
  
  <button class='oneClick okbutton2' name='oneClick' type="submit" style='margin-left: -2px; width: 286px;'><?php echo $lang['global-confirm']; ?></button></td>

 </form>
</div>

