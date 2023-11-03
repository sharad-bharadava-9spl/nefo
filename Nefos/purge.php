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
		$selectDonations = "SELECT donationTime, SUM(amount) from donations WHERE (donatedTo = 2) AND (DATE(donationTime) BETWEEN DATE('$fromDate') AND DATE('$untilDate')) GROUP BY DATE(donationTime) ORDER BY SUM(amount) DESC LIMIT 1";
		
		$donationResult = mysql_query($selectDonations)
			or handleError("ERR1","Error loading donations from db: " . mysql_error());
			
		$row = mysql_fetch_array($donationResult);
			$donations = $row['SUM(amount)'];
			
		$_SESSION['fromDate'] = $fromDate;
		$_SESSION['untilDate'] = $untilDate;
		$_SESSION['fromAmount'] = $donations;
				
		header("Location: purge-2.php");
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


	pageStart($lang['purge-data'], NULL, $validationScript, "pexpenses", NULL, $lang['purge-data'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
		

?>


<div class="actionbox">

<form id="registerForm" action="" method="POST">
<center>
<h2 class='yellow'><?php echo $lang['what-to-purge']; ?>?</h2>
<br />
<table>
 <tr>
  <td><input type="checkbox" name="toDelete[]" style="width: 12px; margin-bottom: 10px;" value="A" /></td>
  <td><?php echo $lang['global-donations']; ?><a href="uTil/excel-exp.php?exp=donations">&nbsp;&nbsp;<img src="images/excel.png" width="15" /></a></td>
 </tr>
 <tr>
  <td><input type="checkbox" name="toDelete[]" style="width: 12px; margin-bottom: 10px;" value="B" /></td>
  <td><?php echo $lang['memberfees']; ?><a href="uTil/excel-exp.php?exp=fees">&nbsp;&nbsp;<img src="images/excel.png" width="15" /></a></td>
 </tr>
<!-- <tr>
  <td><input type="checkbox" name="toDelete[]" style="width: 12px; margin-bottom: 10px;" value="C" /></td>
  <td><?php echo $lang['bar-sales-today']; ?><a href="uTil/excel-exp.php?exp=sales">&nbsp;&nbsp;<img src="images/excel.png" width="15" /></a></td>
 </tr>-->
 <tr>
  <td><input type="checkbox" name="toDelete[]" style="width: 12px; margin-bottom: 10px;" value="D" /></td>
  <td><a href="dispenses-dnd.php" style="color: white;"><?php echo $lang['global-dispenses']; ?></a><a href="uTil/excel-exp.php?exp=dispenses">&nbsp;&nbsp;<img src="images/excel.png" width="15" /></a></td>
 </tr>
 <tr>
  <td><input type="checkbox" name="toDelete[]" style="width: 12px; margin-bottom: 10px;" value="E" /></td>
  <td><?php echo $lang['log']; ?><a href="uTil/excel-exp.php?exp=log">&nbsp;&nbsp;<img src="images/excel.png" width="15" /></a></td>
 </tr>
 <tr>
  <td><input type="checkbox" name="toDelete[]" style="width: 12px; margin-bottom: 10px;" value="E" /></td>
  <td>Movimientos de producto<a href="uTil/excel-exp.php?exp=log">&nbsp;&nbsp;<img src="images/excel.png" width="15" /></a></td>
 </tr>
</table>

<br />
 <h2 class='yellow'>Desde fecha</h2>
 <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="sixDigit" placeholder="<?php echo $lang['choosedate']; ?>" />
 <h2 class='yellow'>Hasta fecha</h2>
 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="sixDigit" placeholder="<?php echo $lang['choosedate']; ?>" />
<br /><br />
 <button class='oneClick' name='oneClick' type="submit"><?php echo $lang['submit']; ?></button>

</center>

</div>