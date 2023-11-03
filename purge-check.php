<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '1';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	if (isset($_POST['untilDate'])) {
		
		$fromDate = date("Y-m-d", strtotime($_POST['fromDate']));
		$untilDate = date("Y-m-d", strtotime($_POST['untilDate']));
		
		$operationDate = date("Y-m-d", strtotime($fromDate));
		
		// First run a check, ensure that the 'fromAmount' is NOT smaller than any day's bankDonations
		$selectDonations = "SELECT donationTime, SUM(amount) from donations WHERE (donatedTo = 2) AND (DATE(donationTime) BETWEEN DATE('$fromDate') AND DATE('$untilDate')) GROUP BY DATE(donationTime) ORDER BY SUM(amount) DESC";
		
		$donationResult = mysql_query($selectDonations)
			or handleError("ERR1","Error loading donations from db: " . mysql_error());
			
	pageStart("DATAFONO", NULL, $validationScript, "pexpenses", NULL, "DATAFONO", $_SESSION['successMessage'], $_SESSION['errorMessage']);
		echo "<table class='default'><tr><th>Fecha</th><th>Importe</th></tr>";
		
		while ($expenseTot = mysql_fetch_array($donationResult)) {
			
			$amount = $expenseTot['SUM(amount)'];
			$donationTime = date("d-m-Y", strtotime($expenseTot['donationTime']));
			
			echo "<tr><td>$donationTime</td><td>$amount</td></tr>";
			
		}
		
		echo "</table>";
		
			
		exit();
			
	}

	
	$validationScript = <<<EOD
    $(document).ready(function() {
	    
	  $( function() {
	    $( "#datepicker" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });
	  });
	  $( function() {
	    $( "#datepicker2" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });
	  });

	    	    
	  $('#registerForm').validate({
		  rules: {
			  fromDate: {
				  required: true
			  },		  
			  untilDate: {
				  required: true
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


	pageStart("DATAFONO", NULL, $validationScript, "pexpenses", NULL, "DATAFONO", $_SESSION['successMessage'], $_SESSION['errorMessage']);
		

?>

  <script>
  </script>
<center>  
<div class="actionbox-np2">
	<div class="boxcontent">
		<form id="registerForm" action="" method="POST">
			<center>
			<br />
			 <span class='smallgreen'>Desde fecha</span>
			 <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="sixDigit defaultinput" placeholder="<?php echo $lang['choosedate']; ?>" />
			 <span class='smallgreen'>Hasta fecha</span>
			 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="sixDigit defaultinput" placeholder="<?php echo $lang['choosedate']; ?>" />
			<br /><br />
			 <button class='oneClick cta4' name='oneClick' type="submit"><?php echo $lang['submit']; ?></button>

			</center>
		</form>
	</div>
</div>
</center>
