<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '1';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	if (isset($_POST['fromAmount'])) {
		
		$fromAmount = $_POST['fromAmount'];
		$toAmount = $_POST['toAmount'];
		
		$fromDate = date("Y-m-d", strtotime($_POST['fromDate']));
		$untilDate = date("Y-m-d", strtotime($_POST['untilDate']));
		
		$operationDate = date("Y-m-d", strtotime($fromDate));
		
		// DO NOT DELETE BANK DONATIONS!!!!!!!!!!!!!!!!!!!!!!!!!!!! / CUOtaS!!!!!!!!!!!!!!!!!!
		
		// First run a check, ensure that the 'fromAmount' is NOT smaller than any day's bankDonations
		
		while ($operationDate <= $untilDate) {
			
			// For each day, let's do this:
			// Look up total, see if it's within fromAmt and toAmt.
			//$selectDonations = "SELECT SUM(amount) from sales WHERE DATE(saletime) = DATE('$operationDate')";
			$selectDonations = "SELECT SUM(amount) from donations WHERE DATE(donationTime) = DATE('$operationDate')";
			try
			{
				$result = $pdo3->prepare("$selectDonations");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
				$donations = $row['SUM(amount)'];
				
			while ($donations > $toAmount) {
				
				// Select a random donation from that day.
				//$selectDonation = "SELECT saleid, amount FROM sales WHERE DATE(saletime) = DATE('$operationDate') ORDER BY RAND() LIMIT 1";
				$selectDonation = "SELECT donationid AS saleid, amount FROM donations WHERE DATE(donationTime) = DATE('$operationDate') ORDER BY RAND() LIMIT 1";
				try
				{
					$result = $pdo3->prepare("$selectDonation");
					$result->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			
				$row = $result->fetch();
					$saleid = $row['saleid'];
					$amount = $row['amount'];
										
				if (($donations - $donation) > $fromAmount) {
					
					//$deleteRow = "DELETE FROM sales WHERE saleid = $saleid";
					$deleteRow = "DELETE FROM donations WHERE donationid = $saleid";
					try
					{
						$result = $pdo3->prepare("$deleteRow")->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
						
					$donations = $donations - $amount;
					
					//echo "donations: $donations<br />Operationdate: $operationDate<br />Operationdate: $untilDate<br />$deleteRow<br /><br />";

					$_SESSION['successMessage'] = "Datos purgado con &eacute;xito!";
					header("Location: index.php");
					
					
				}
				
				
			}
			
			
			$operationDate = date("Y-m-d", strtotime($operationDate . " +1 days"));
		}
		echo "FIN";
		exit();
			echo "$selectDonations <br/>";
		
/*
		
		$userPass = $_POST['userPass'];
		$userId = $_SESSION['user_id'];
		
		// Look up email for password crypt
		$query = "SELECT email FROM users WHERE user_id = $userId";
		
		$result = mysql_query($query)
			or handleError($lang['error-userload'],"Error loading user: " . mysql_error());
			
		$row = mysql_fetch_array($result);
			$email = $row['email'];
		
		// Check pwd first
		$query = sprintf("SELECT memberno FROM users WHERE email = '$email' AND userPass = '%s';",
			crypt($userPass, $email));
		
		$results = mysql_query($query)
			or handleError($lang['error-crederror'],"Error loading user credentials from db: " . mysql_error());

		if (mysql_num_rows($results) == 0) {
			
			$_SESSION['errorMessage'] = "Contraseña incorrecta!";
				
		} else {
		
		
			$fromDate = date("Y-m-d", strtotime($_POST['fromDate']));
			$untilDate = date("Y-m-d", strtotime($_POST['untilDate']));
			
			foreach($_POST['toDelete'] as $toDelete) {
	    		$delOrNot .=  $toDelete;
	  		}
	  		
	  		if (strpos($delOrNot, 'A') !== false) {
		  		
	    		$query = "DELETE FROM donations WHERE saletime < '$untilDate'";
	    		
	    		mysql_query($query)
					or handleError($lang['error-deleting'],"Error loading user: " . mysql_error());
					
				$_SESSION['successMessage'] .= $lang['donations-deleted'] . "!<br />";
	
			}
			
			if (strpos($delOrNot, 'B') !== false) {
				
	    		$query = "DELETE FROM memberpayments WHERE paymentdate < '$untilDate'";
	    		
	    		mysql_query($query)
					or handleError($lang['error-deleting'],"Error loading user: " . mysql_error());
					
				$_SESSION['successMessage'] .= $lang['fees-deleted'] . "!<br />";
	
			}
			
			if (strpos($delOrNot, 'C') !== false) {
				
				// Find salesid to enable deleting from salesdetails too
				$query = "SELECT saleid FROM b_sales WHERE saletime < '$untilDate' ORDER by saletime DESC LIMIT 1";
				
	    		$result = mysql_query($query)
					or handleError($lang['error-deleting'],"Error loading user: " . mysql_error());
					
				$row = mysql_fetch_array($result);
					$saleid = $row['saleid'];
					
				$toDelete = $saleid + 1;
									
	    		$query = "DELETE FROM b_sales WHERE saletime < '$untilDate'";
	    		
	    		mysql_query($query)
					or handleError($lang['error-deleting'],"Error loading user: " . mysql_error());
					
	    		$query = "DELETE FROM b_salesdetails WHERE saleid < $toDelete";
	    		
	    		mysql_query($query)
					or handleError($lang['error-deleting'],"Error loading user: " . mysql_error());
					
				$_SESSION['successMessage'] .= $lang['bar-sales-deleted'] . "!<br />";
	
			}
			
			if (strpos($delOrNot, 'D') !== false) {
				
				// Find salesid to enable deleting from salesdetails too
				$query = "SELECT saleid FROM sales WHERE saletime < '$untilDate' ORDER by saletime DESC LIMIT 1";
				
	    		$result = mysql_query($query)
					or handleError($lang['error-deleting'],"Error loading user: " . mysql_error());
					
				$row = mysql_fetch_array($result);
					$saleid = $row['saleid'];
					
				$toDelete = $saleid + 1;
				
	    		$query = "DELETE FROM sales WHERE saletime < '$untilDate'";
	    		
	    		mysql_query($query)
					or handleError($lang['error-deleting'],"Error loading user: " . mysql_error());
					
	    		$query = "DELETE FROM salesdetails WHERE saleid < $toDelete";
	    		
	    		mysql_query($query)
					or handleError($lang['error-deleting'],"Error loading user: " . mysql_error());
					
				$_SESSION['successMessage'] .= $lang['sales-deleted'] . "!<br />";
	
			}
			
			if (strpos($delOrNot, 'E') !== false) {
				
	    		$query = "DELETE FROM log WHERE logtime < '$untilDate'";
	    		
	    		mysql_query($query)
					or handleError($lang['error-deleting'],"Error loading user: " . mysql_error());
					
				$_SESSION['successMessage'] .= $lang['log-deleted'] . "!<br />";
	
			}
			
		}
		*/
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
			  userPass: {
				  required: true
			  },
			  fromDate: {
				  required: true
			  },		  
			  untilDate: {
				  required: true
			  },		  
			  fromAmount: {
				  required: true
			  },		  
			  toAmount: {
				  required: true
			  },		  
			  "toDelete[]": {
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

  <script>
  </script>
<div class="actionbox">

<form id="registerForm" action="" method="POST">

<!--<h2 class='yellow'><?php echo $lang['what-to-purge']; ?>?</h2>
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
<tr>
  <td><input type="checkbox" name="toDelete[]" style="width: 12px; margin-bottom: 10px;" value="C" /></td>
  <td><?php echo $lang['bar-sales-today']; ?><a href="uTil/excel-exp.php?exp=sales">&nbsp;&nbsp;<img src="images/excel.png" width="15" /></a></td>
 </tr>
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
</table>-->
<br />
<center>
 
<?php

?>
 <h2 class='yellow'>Desde fecha:</h2>
 <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="sixDigit" placeholder="<?php echo $lang['choosedate']; ?>" />
 <h2 class='yellow'>Hasta fecha:</h2>
 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="sixDigit" placeholder="<?php echo $lang['choosedate']; ?>" /><br /><br />
 <h2 class='yellow'>Importe diario:</h2>
 Desde: <input type="number" name="fromAmount" class="sixDigit" value="" /><br /><br />
 Hasta: <input type="number" name="toAmount" class="sixDigit" value="" /><br /><br />
 <button class='oneClick' name='oneClick' type="submit"><?php echo $lang['submit']; ?></button>

</center>

</div>
