<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$client = $_GET['client'];
	
	if ($_POST['confirmed'] == 'yes') {

		$day = $_POST['day'];
		$month = $_POST['month'];
		$year = $_POST['year'];
		$hour = $_POST['hour'];
		$minute = $_POST['minute'];
		$client = $_POST['client'];
		$comment = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['comment'])));
		
		$registertime = "$year-$month-$day $hour:$minute:00";

		// Query to add new sale to Sales table - 6 arguments
		$query = sprintf("INSERT INTO comments (customer, user_id, time, comment) VALUES ('%d', '%d', '%s', '%s');",
		  $client, $_SESSION['user_id'], $registertime, $comment);
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
			
		// On success: redirect.
		$_SESSION['successMessage'] = "Comment added succesfully!";
		header("Location: prospects.php");
		exit();
		
	}
	/***** FORM SUBMIT END *****/
	
	$validationScript = <<<EOD
    $(document).ready(function() {
	    	    
	  $('#registerForm').validate({
		  rules: {
			  day: {
				  required: true
			  },
			  month: {
				  required: true
			  },
			  year: {
				  required: true
			  },
			  hour: {
				  required: true
			  },
			  minute: {
				  required: true
			  },
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

	
	pageStart("Nefos tool", NULL, $deleteDonationScript, "pprofilenew", "donations fees", $lang['delete-fee-payment'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	

?>
<center>
<div id='donationholder2'>
 <form id="registerForm" action="" method="POST">

<br /> <input type="number" lang="nb" name="day" id="day" class="twoDigit" maxlength="2" placeholder="dd" value="<?php echo date('d'); ?>" />
 <input type="number" lang="nb" name="month" id="month" class="twoDigit" maxlength="2" placeholder="mm" value="<?php echo date('m'); ?>" />
 <input type="number" lang="nb" name="year" id="year" class="fourDigit" maxlength="4" placeholder="<?php echo $lang['member-yyyy']; ?>" value="<?php echo date('Y'); ?>" />
 @
 <input type="number" lang="nb" name="hour" id="hour" class="twoDigit" maxlength="2" placeholder="h" value="<?php echo date('H'); ?>" />
 :
 <input type="number" lang="nb" name="minute" id="minute" class="twoDigit" maxlength="2" placeholder="m" value="<?php echo date('i'); ?>" />
<br /><br />

  <textarea name="comment" placeholder="<?php echo $lang['global-comment']; ?>?" style='width: 346px; height: 100px;'></textarea><br /><br />
  <input type='hidden' name='confirmed' value='yes' />
  <input type='hidden' name='client' value='<?php echo $client; ?>' />
        

  <button class='oneClick okbutton2' name='oneClick' type="submit" style='margin-left: -2px; width: 286px;'><?php echo $lang['global-confirm']; ?></button></td>

 </form>
</div>

