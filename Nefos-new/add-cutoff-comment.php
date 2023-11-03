<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	if ($_POST['confirmed'] == 'yes') {

		$day = $_POST['day'];
		$month = $_POST['month'];
		$year = $_POST['year'];
		$hour = $_POST['hour'];
		$minute = $_POST['minute'];
		$client = $_POST['client'];
		$period = $_POST['period'];
		$invno = $_POST['invno'];
		$day2 = $_POST['day2'];
		$month2 = $_POST['month2'];
		$year2 = $_POST['year2'];
		$warning = $_POST['warning'];
		$comment = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['comment'])));
		$nowDate = date('Y-m-d H:i');
		
		$registertime = "$year-$month-$day $hour:$minute:00";
		
		foreach ($_POST['invoices'] as $inv) {
			
			$invno2 = $inv['invno'];
			
			// If warning level has changed
			if ($warning != '') {
				
 				if ($warning == 0) {
	 				$warningText = 'No warning';
 				} else if ($warning == 1) {
	 				$warningText = 'Soft warning';
 				} else if ($warning == 2) {
	 				$warningText = 'Final warning';
 				} else if ($warning == 3) {
	 				$warningText = 'CUT OFF';
 				}
 				
				$comment = $comment . "<br /><em>Warning changed to: $warningText</em>";
				
				$query = "UPDATE db_access SET warning = '$warning' WHERE customer = '$client'";
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
				
				// if cutoff, also update cutoff date!
				if ($warning == 3) {
					
					$query = "UPDATE invoices SET cutoffdate = '$nowDate' WHERE customer = '$client' AND period = '$period'";
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
					
				}
				
				
			}
			
			if ($day2 != '') {
				
				$promise = "$year2-$month2-$day2";
				
				$comment = $comment . "<br /><em>Payment promise added: " . date('d-m-Y', strtotime($promise)) . "</em>";
				
				$query = "UPDATE invoices SET promise = '$promise' WHERE invno = '$invno2'";
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
						
				$query = sprintf("INSERT INTO cutoffcomments (time, customer, comment, operator, period, invno, promise) VALUES ('%s', '%s', '%s', '%d', '%s', '%s', '%s');",
			  	 $registertime, $client, $comment, $_SESSION['user_id'], $period, $invno2, $promise);
			  	 
			} else {
				
				$query = sprintf("INSERT INTO cutoffcomments (time, customer, comment, operator, period, invno) VALUES ('%s', '%s', '%s', '%d', '%s', '%s');",
			  	 $registertime, $client, $comment, $_SESSION['user_id'], $period, $invno2);
			  	 
			}
	
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
			
		}
		
			
		// On success: redirect.
		$_SESSION['successMessage'] = "Comment added succesfully!";
		header("Location: cutoff.php");
		exit();
		
	}
	/***** FORM SUBMIT END *****/
	
	$client = $_GET['client'];
	$period = $_GET['period'];
	$invno = $_GET['invno'];
	
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
	
	$query = "SELECT warning FROM db_access WHERE customer = '$client'";
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

?>
<center>
<div id='donationholder2'>
 <form id="registerForm" action="" method="POST">
<strong>Comment date</strong><br /><br />
<input type="number" lang="nb" name="day" id="day" class="defaultinput twoDigit" maxlength="2" placeholder="dd" value="<?php echo date('d'); ?>" />
 <input type="number" lang="nb" name="month" id="month" class="defaultinput twoDigit" maxlength="2" placeholder="mm" value="<?php echo date('m'); ?>" />
 <input type="number" lang="nb" name="year" id="year" class="defaultinput fourDigit" maxlength="4" placeholder="<?php echo $lang['member-yyyy']; ?>" value="<?php echo date('Y'); ?>" />
 @
 <input type="number" lang="nb" name="hour" id="hour" class="defaultinput twoDigit" maxlength="2" placeholder="h" value="<?php echo date('H'); ?>" />
 :
 <input type="number" lang="nb" name="minute" id="minute" class="defaultinput twoDigit" maxlength="2" placeholder="m" value="<?php echo date('i'); ?>" />
<br /><br /><br />
<strong>Payment promise?</strong><br /><br />

<input type="number" lang="nb" name="day2" id="day" class="defaultinput twoDigit" maxlength="2" placeholder="dd" />
 <input type="number" lang="nb" name="month2" id="month" class="defaultinput twoDigit" maxlength="2" placeholder="mm" />
 <input type="number" lang="nb" name="year2" id="year" class="defaultinput fourDigit" maxlength="4" placeholder="<?php echo $lang['member-yyyy']; ?>" />
<br /><br /><br />

<strong>Manual warning flag?</strong><br /><br />
<select name='warning' class="defaultinput" style='width: 150px;'>
<?php

	if ($warning == 0) {
		echo "<option value=''>Automatic</option>";
	} else if ($warning == 1) {
		echo "<option value='1'>Soft warning</option>";
	} else if ($warning == 2) {
		echo "<option value='2'>Final warning</option>";
	} else if ($warning == 3) {
		echo "<option value='3'>CUT OFF</option>";
	}

?>
 
 <option value='0'>No warning</option>
 <option value='1'>Soft warning</option>
 <option value='2'>Final warning</option>
 <option value='3'>CUT OFF</option>
</select>
<br /><br /><br />

<strong>Apply to which invoices?</strong><br /><br />
<table class='default noborder'>
<?php

	$query = "SELECT invno, invdate, action, cutoffdate, promise, paid, amount FROM invoices WHERE period = '$period' AND customer = '$client'";
	try
	{
		$results = $pdo->prepare("$query");
		$results->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$i = 0;
	while ($row = $results->fetch()) {
		
		$invno2 = $row['invno'];
		$invdate = date("d-m-Y", strtotime($row['invdate']));
		$amount = $row['amount'];
		$cutoffdate = date("d-m-Y", strtotime($row['cutoffdate']));
		$promise = date("d-m-Y", strtotime($row['promise']));
		
		if ($invno2 == $invno) {
			$checked = 'checked';
		} else {
			$checked = '';
		}
		
		echo "<tr><td><input type='checkbox' name='invoices[$i][invno]' value='$invno2' $checked /></td><td>$invno2</td><td>$invdate</td><td class='right'>$amount &euro;</td></tr>";
		
		$i++;
		
	}

?>
</table>
<br /><br />


  <textarea name="comment" placeholder="<?php echo $lang['global-comment']; ?>?" style='width: 346px; height: 100px;'></textarea><br /><br />
  <input type='hidden' name='confirmed' value='yes' />
  <input type='hidden' name='client' value='<?php echo $client; ?>' />
  <input type='hidden' name='period' value='<?php echo $period; ?>' />
  <input type='hidden' name='invno' value='<?php echo $invno; ?>' />
        

  <button class='oneClick okbutton2' name='oneClick' type="submit" style='margin-left: -2px; width: 286px;'><?php echo $lang['global-confirm']; ?></button></td>

 </form>
</div>

<?php displayFooter();