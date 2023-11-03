<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Check if an image was submitted
	if (isset($_POST['mydata'])) {
		
		$expenseid = $_GET['expenseid'];
		
		$encoded_data = $_POST['mydata'];
		$binary_data = base64_decode( $encoded_data );
		
		$imgname = 'images/expenses-mkl/' . $expenseid . '.jpg';
		// save to server (beware of permissions)
		$result = file_put_contents( $imgname, $binary_data );
		
		if (!$result) die($lang['error-imagesave']);
		
		
		$updateExt = "UPDATE expenses_mklnew SET photoext = 'jpg' WHERE expenseid = $expenseid";
		try
		{
			$result = $pdo3->prepare("$updateExt")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

		
		$_SESSION['successMessage'] = $lang['receipt-saved'];

		
		$_SESSION['successMessage'] = $lang['receipt-saved'];

		
	} else if (isset($_GET['saveimg'])) {
		
		$expenseid = $_GET['expenseid'];
		
		$image_fieldname = "fileToUpload";
	
		
		// Potential PHP upload errors
		$php_errors = array(1 => $lang['imgError1'],
							2 => $lang['imgError1'],
							3 => $lang['imgError2'],
							4 => $lang['imgError3']);
						
		// Check for any upload errors
		if ($_FILES[$image_fieldname]['error'] != 0) {
			$_SESSION['errorMessage'] = $php_errors[$_FILES[$image_fieldname]['error']] . " " . $lang['try-again'];
			header("Location: new-receipt-upload-mkl.php?expenseid=$expenseid");
			exit();
		}
		
		// Check if a real file was uploaded
		if (is_uploaded_file($_FILES[$image_fieldname]['tmp_name'])) {
			
		} else {
			$_SESSION['errorMessage'] = $lang['imgError4'];
			header("Location: new-receipt-upload-mkl.php?expenseid=$expenseid");
			exit();
		}
		
		// Is this actually an image?
		if (getimagesize($_FILES[$image_fieldname]['tmp_name'])) {
			
		} else {
			$_SESSION['errorMessage'] = $lang['imgError5'];
			header("Location: new-receipt-upload-mkl.php?expenseid=$expenseid");
			exit();
		}
		
		// Save the file and store the extension for later db entry
		$extension = pathinfo($_FILES[$image_fieldname]['name'], PATHINFO_EXTENSION);
		$upload_filename = "images/expenses-mkl/" . $expenseid . "." . $extension;
		
		if (move_uploaded_file($_FILES[$image_fieldname]['tmp_name'], $upload_filename)) {
			
		} else {
			$_SESSION['errorMessage'] = $lang['imgError6'];
			header("Location: new-receipt-upload-mkl.php?expenseid=$expenseid");
			exit();
		}
		
		$updateExt = "UPDATE expenses_mklnew SET photoext = '$extension' WHERE expenseid = $expenseid";
		try
		{
			$result = $pdo3->prepare("$updateExt")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

		
		$_SESSION['successMessage'] = $lang['receipt-saved'];

	}
	
	
	// Check if new Filter value was submitted, and assign query variable accordingly
	if (isset($_POST['filter'])) {
				
		$filterVar = $_POST['filter'];
		
		if ($filterVar == 100) {
			
			$limitVar = "LIMIT 100";
			$optionList = "<option value='$filterVar'>{$lang['last']} 100</option>
			<option value='250'>{$lang['last']} 250</option>
			<option value='500'>{$lang['last']} 500</option>";
			
		} else if ($filterVar == 250) {
			
			$limitVar = "LIMIT 250";
			$optionList = "<option value='$filterVar'>{$lang['last']} 250</option>
			<option value='100'>{$lang['last']} 100</option>
			<option value='500'>{$lang['last']} 500</option>";
			
		} else if ($filterVar == 500) {
			
			$limitVar = "LIMIT 500";
			$optionList = "<option value='$filterVar'>{$lang['last']} 500</option>
			<option value='100'>{$lang['last']} 100</option>
			<option value='250'>{$lang['last']} 250</option>";
			
		} else {
			
			// Grab month and year number
			$month = substr($filterVar, 0, strrpos($filterVar, '-'));	
			$year = substr($filterVar, strrpos($filterVar, '-') + 1);
			
			$timeLimit = "WHERE MONTH(registertime) = $month AND YEAR(registertime) = $year";
			
			$optionList = "<option value='filterVar'>$filterVar</option>
				<option value='100'>{$lang['last']} 100</option>
				<option value='250'>{$lang['last']} 250</option>
				<option value='500'>{$lang['last']} 500</option>";

		}
			
	} else {
		
		$limitVar = "LIMIT 100";
		
		$optionList = "<option value=''>{$lang['filter']}</option>
			<option value='100'>{$lang['last']} 100</option>
			<option value='250'>{$lang['last']} 250</option>
			<option value='500'>{$lang['last']} 500</option>";
	}

	
	// Check if 'entre fechas' was utilised
	if (isset($_POST['untilDate'])) {
		
		$fromDate = date("Y-m-d", strtotime($_POST['fromDate']));
		$untilDate = date("Y-m-d", strtotime($_POST['untilDate']));
		
		$timeLimit = "WHERE registertime BETWEEN '$fromDate' AND '$untilDate'";
		
		$limitVar = '';
			
	}
	// Query to look up expenses
	$selectExpenses = "SELECT expenseid, registertime, userid, expensetype, expense, moneysource, other, amount, shop, comment, receipt, invoice, expensecategory, photoext, vat, vatamt, brand, paymentdate, type, status, country, subcat, personal, refundable, refunded, hwsw FROM expenses_mklnew $timeLimit ORDER by registertime DESC $limitVar";
		try
		{
			$results = $pdo3->prepare("$selectExpenses");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

	// Create month-by-month split
	$findStartDate = "SELECT registertime FROM expenses_mklnew ORDER BY registertime ASC LIMIT 1";
		try
		{
			$result = $pdo3->prepare("$findStartDate");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$startDate = date('01-m-Y', strtotime($row['registertime']));
		$endDate = date('01-m-Y');
		$endDateShort = date('m-Y', strtotime($endDate));
		
		
	if ($endDateShort != $filterVar) {
		$optionList .= "<option value='$endDateShort'>$endDateShort</option>";
	}
	
	$genDateFull = date('01-m-Y', strtotime($endDate));
	$genDate = date('m-Y', strtotime($genDateFull));
	
	while (strtotime($genDateFull) > strtotime($startDate)) {
		
		$genDateFull = date('01-m-Y', strtotime("$genDateFull - 1 month"));
		$genDate = date('m-Y', strtotime($genDateFull));
		
		// Exclude option if already selected
		if ($genDate != $filterVar) {
			$optionList .= "<option value='$genDate'>$genDate</option>";
		}

	}
	



	$deleteExpenseScript = <<<EOD
	
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

$("#xllink").click(function(){

	  $("#mainTable").table2excel({
	    // exclude CSS class
	    exclude: ".noExl",
	    name: "Retiradas",
	    filename: "Retiradas" //do not include extension

	  });

	});
		    
		    
		    
			$('#cloneTable').width($('#mainTable').width());
			
			

			
			$('#mainTable').tablesorter({
				usNumberFormat: true,
				headers: {
					1: {
						sorter: "dates"
					},
					2: {
						sorter: "dates"
					},
					8: {
						sorter: "currency"
					}
				}
			}); 

		});
		
		$(window).resize(function() {
			$('#cloneTable').width($('#mainTable').width());
		});

		
function delete_expense(expenseid) {
	if (confirm("{$lang['expense-deleteconfirm']}")) {
				window.location = "uTil/delete-expense-mkl.php?expenseid=" + expenseid + "&source=expenses";
				}
}
EOD;
	pageStart($lang['title-expenses'], NULL, $deleteExpenseScript, "pexpenses", "admin", $lang['global-expensescaps'] . " mkl", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
?>

<center><a href="new-expense-mkl.php" class="cta"><?php echo $lang['expense-newexpense']; ?></a> <a href="expenses-summary-mkl.php" class="cta"><?php echo $lang['summary']; ?></a></center>

	 <table style='margin: 0; margin-left: 20px; text-align: left;'>
      <tr class='nonhover'>
       <td colspan='13' style='border-bottom: 0;'>
         <a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});"><img src="images/excel.png" style='margin: 0 0 -5px 8px;'/></a><br /><br />
		<div style='border: 2px solid #5aa242; padding: 10px;'>
		&nbsp;<strong>Filter:</strong><br /> 
        <form action='' method='POST' style='margin-top: 3px;'>
	     <select id='filter' name='filter' onchange='this.form.submit()'>
	      <?php echo $optionList; ?>
		 </select>
        </form>
        <form action='' method='POST'>
<?php
	if (isset($_POST['fromDate'])) {
		
		echo <<<EOD
		 <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="sixDigit" value="{$_POST['fromDate']}" />
		 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="sixDigit" value="{$_POST['untilDate']}" onchange='this.form.submit()' />
		 <button type="submit" style='display: inline-block; width: 40px; height: 27px;'>OK</button>
EOD;
		
	} else {
		
		echo <<<EOD
		 <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="sixDigit" placeholder="Desde fecha" />
		 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="sixDigit" placeholder="Hasta fecha" onchange='this.form.submit()' />
		 <button type="submit" style='display: inline-block; width: 40px; height: 27px;'>OK</button>
EOD;

	}
?>
        </form>
        </div>
       </td>
      </tr>
     </table>


<br />
	 <table class="default" id="mainTable">
	  <thead>
	   <tr style='cursor: pointer;'>
	    <th>Brand</th>
	    <th>HW/SW</th>
	    <th>Invoice date</th>
	    <th>Accounting date</th>
	    <th><?php echo $lang['global-time']; ?></th>
	    <th><?php echo $lang['global-category']; ?></th>
	    <th>Subcategory</th>
	    <th><?php echo $lang['global-expense']; ?></th>
	    <th>Type</th>
	    <th><?php echo $lang['global-shop']; ?></th>
	    <th>Status</th>
	    <th>Country</th>
	    <th><?php echo $lang['global-member']; ?></th>
	    <th><?php echo $lang['global-amount']; ?></th>
	    <th>VAT</th>
	    <th><?php echo $lang['global-source']; ?></th>
	    <th><?php echo $lang['global-receipt']; ?></th>
	    <th><?php echo $lang['global-invoice']; ?></th>
	    <th>Deduct?</th>
	    <th>Refundable?</th>
	    <th class='noExl'>Scan</th>
	    <th class='noExl'></th>
	    <th class='noExl'><?php echo $lang['global-actions']; ?></th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php

while ($expense = $results->fetch()) {
	
	$userid = $expense['userid']; // find member
	$moneysource = $expense['moneysource'];
	$receipt = $expense['receipt'];
	$invoice = $expense['invoice'];
	$other = $expense['other'];
	$expenseCat = $expense['expensecategory'];
	$photoext = $expense['photoext'];
	$formattedDate = date("d-m-Y", strtotime($expense['registertime'] . "+$offsetSec seconds"));
	$formattedTime = date("H:i", strtotime($expense['registertime'] . "+$offsetSec seconds"));
	$expenseid = $expense['expenseid'];
	$vat = $expense['vatamt'];
	$paymentdate = $expense['paymentdate'];
	$type = $expense['type'];
	$status = $expense['status'];
	$country = $expense['country'];
	$subcat = $expense['subcat'];
	$personal = $expense['personal'];
	$refundable = $expense['refundable'];
	$refunded = $expense['refunded'];
	$hwsw = $expense['hwsw'];
	
	
	
	if ($paymentdate != '0000-00-00 00:00:00' && $paymentdate != NULL && $paymentdate != '1970-01-01 01:00:00' ) {
		$formattedDate2 = date("d-m-Y", strtotime($paymentdate . "+$offsetSec seconds"));
	} else {
		$formattedDate2 = '';
	}
		
	
	$brand = $expense['brand'];
	
	if ($brand == 0) {
		$brandName = 'Nefos';
	} else {
		$brandName = 'CCS';
	}
	if ($vat == 0 || $vat == '') {
		
		$vatAmount = '';
		
	} else {
		
		$vatAmount = $vat . "<span class='smallerfont'>&euro;</span>";
		
	}
	
	
	
	if ($expense['comment'] != '') {
		
		$commentRead = "
		                <img src='images/comments.png' id='comment$expenseid' /><div id='helpBox$expenseid' class='helpBox'>{$expense['comment']}</div>
		                <script>
		                  	$('#comment$expenseid').on({
						 		'mouseover' : function() {
								 	$('#helpBox$expenseid').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBox$expenseid').css('display', 'none');
							  	}
						  	});
						</script>
		                ";
		
	} else {
		
		$commentRead = "";
		
	}

	
	$file = 'images/expenses-mkl/' . $expenseid . '.' . $photoext;

	if (file_exists($file)) {
		$invScan = "<a href='$file'><img src='images/receipt.png' /></a>";
	} else {
		$invScan = "<a href='new-expense-mkl.php?expenseid=$expenseid'><img src='images/receipt-na.png' /></a>";
	}
	
	if ($expenseCat == NULL) {
		$expenseCat = '';
	} else {
		if ($_SESSION['lang'] == 'es') {
			$selectExpenseCat = "SELECT namees FROM expensecategories WHERE categoryid = $expenseCat";
		try
		{
			$result = $pdo3->prepare("$selectExpenseCat");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		  	    $expenseCat = $row['namees'];
		} else {
			$selectExpenseCat = "SELECT nameen FROM expensecategories WHERE categoryid = $expenseCat";
		try
		{
			$result = $pdo3->prepare("$selectExpenseCat");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		  	    $expenseCat = $row['nameen'];
		}
	}
		
	if ($subcat == NULL) {
		$subcat = '';
	} else {
			$selectExpenseCat = "SELECT nameen FROM expensecategories WHERE categoryid = $subcat";
		try
		{
			$result = $pdo3->prepare("$selectExpenseCat");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		$row = $result->fetch();
		  	    $subcat = $row['nameen'];
	}
		

	
	if ($moneysource == 1) {
		$source = "Cash";
	} else if ($moneysource == 2) {
		$source = "Bank transfer";
	} else if ($moneysource == 6) {
		$source = "Direct Debit";
	} else if ($moneysource == 7) {
		$source = "MKL BBVA SW debit card";
	} else if ($moneysource == 8) {
		$source = "MKL BBVA HW debit card";
	} else if ($moneysource == 9) {
		$source = "MKL BBVA SW credit card";
	} else if ($moneysource == 10) {
		$source = "MKL BBVA HW credit card";
	} else if ($moneysource == 14) {
		$source = "Amazon";
	} else {
		$source = 'ERROR';
	}
	
	if ($receipt == 1) {
		$recClass = "";
		$receipt = $lang['global-yes'];
	} else if ($receipt == 2) {
		$recClass = "negative";
		$receipt = $lang['global-no'];
	}
	
	if ($invoice == 1) {
		$invClass = "";
		$invoice = $lang['global-yes'];
	} else if ($invoice == 0) {
		$invClass = "negative";
		$invoice = $lang['global-no'];
	}
	
		$userDetails = "SELECT memberno, first_name from users WHERE user_id = $userid";
		try
		{
			$resultsU = $pdo3->prepare("$userDetails");
			$resultsU->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($user = $resultsU->fetch()) {
			$member = "#" . $user['memberno'] . " - " . $user['first_name'];
		}
		
	if ($type == 1) {
		$VATtype = "Goods";
	} else if ($type == 2) {
		$VATtype = "Services";
	} else {
		$VATtype = "";
	}
	
	if ($status == 1) {
		$VATstatus = "Business";
	} else if ($status == 2) {
		$VATstatus = "Private";
	} else {
		$VATstatus = "";
	}

	if ($personal == 1) {
		$personal = 'Yes';
	} else {
		$personal = '';
	}
	
	if ($refunded == 1) {
		$refundable = 'Refunded';
	} else {
		if ($refundable == 1) {
			$refundable = 'Yes';
		} else {
			$refundable = '';
		}
	}
	
	
	$expense_row =	sprintf("
  	  <tr>
  	   <td class='clickableRow left' href='expense.php?expenseid=%d'>MKL</td>
  	   <td class='clickableRow left' href='expense.php?expenseid=%d'>$hwsw</td>
  	   <td class='clickableRow left' href='expense-mkl.php?expenseid=%d'>%s</td>
  	   <td class='clickableRow left' href='expense-mkl.php?expenseid=%d'>%s</td>
  	   <td class='clickableRow left' href='expense-mkl.php?expenseid=%d'>%s</td>
  	   <td class='clickableRow left' href='expense-mkl.php?expenseid=%d'>%s</td>
  	   <td class='clickableRow left' href='expense-mkl.php?expenseid=%d'>%s</td>
  	   <td class='clickableRow left' href='expense-mkl.php?expenseid=%d'>%s</td>
  	   <td class='clickableRow left' href='expense-mkl.php?expenseid=%d'>%s</td>
  	   <td class='clickableRow left' href='expense-mkl.php?expenseid=%d'>%s</td>
  	   <td class='clickableRow left' href='expense-mkl.php?expenseid=%d'>%s</td>
  	   <td class='clickableRow left' href='expense-mkl.php?expenseid=%d'>%s</td>
  	   <td class='clickableRow left' href='expense-mkl.php?expenseid=%d'>%s</td>
  	   <td style='text-align: right;' class='clickableRow' href='expense-mkl.php?expenseid=%d'>%0.2f <span class='smallerfont'>&euro;</span></td>
  	   <td style='text-align: right;' class='clickableRow' href='expense-mkl.php?expenseid=%d'>%s</td>
  	   <td class='clickableRow' href='expense-mkl.php?expenseid=%d'>%s</td>
  	   <td class='clickableRow %s' href='expense-mkl.php?expenseid=%d'>%s</td>
  	   <td class='clickableRow %s' href='expense-mkl.php?expenseid=%d'>%s</td>
	   <td>%s</td>
	   <td>%s</td>
  	   <td class='noExl'>%s </td>
	   <td class='relative noExl'>$commentRead</td>
  	   <td class='noExl' style='text-align: center;'><a href='edit-expense-mkl.php?expenseid=%d'><img src='images/edit.png' height='15' title='{$lang['expense-edit']}' /></a>&nbsp;&nbsp;<a href='javascript:delete_expense(%d)'><img src='images/delete.png' height='15' title='{$lang['expense-delete']}' /></a></td>
	  </tr>",
	  $expense['expenseid'], $expense['expenseid'], $expense['expenseid'], $formattedDate, $expense['expenseid'], $formattedDate2, $expense['expenseid'], $formattedTime, $expense['expenseid'], $expenseCat, $expense['expenseid'], $subcat, $expense['expenseid'], $expense['expense'], $expense['expenseid'], $VATtype, $expense['expenseid'], $expense['shop'], $expense['expenseid'], $VATstatus, $expense['expenseid'], $expense['country'], $expense['expenseid'], $member, $expense['expenseid'], $expense['amount'], $expense['expenseid'], $vatAmount, $expense['expenseid'], $source, $recClass, $expense['expenseid'], $receipt, $invClass, $expense['expenseid'], $invoice, $personal, $refundable, $invScan, $expense['expenseid'], $expense['expenseid']
	  );
	  echo $expense_row;
  }
?>

	 </tbody>
	 </table>
	 
	 
<?php  displayFooter(); ?>
