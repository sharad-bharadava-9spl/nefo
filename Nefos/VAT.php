<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// IF country is set, apply country.
	if ($_POST['country'] <> '') {
		
		$country = $_POST['country'];
		
		$timeLimit = " AND country = '$country' ";
		$timeLimit2 = " WHERE country = '$country' ";
			
	}
	
	// If daterange is set, apply daterange.
	if ($_POST['untilDate'] <> '') {
		
		$fromDate = date("Y-m-d", strtotime($_POST['fromDate']));
		$untilDate = date("Y-m-d", strtotime($_POST['untilDate']));
		
		$timeLimit .= "AND registertime BETWEEN '$fromDate' AND '$untilDate'";
		$timeLimit2 .= "WHERE registertime BETWEEN '$fromDate' AND '$untilDate'";
			
	// If not, check if new Filter value was submitted, and assign query variable accordingly
	} else if ($_POST['filter'] <> '') {
		
		$filterVar = $_POST['filter'];
			
		// Grab month and year number
		$month = substr($filterVar, 0, strrpos($filterVar, '-'));	
		$year = substr($filterVar, strrpos($filterVar, '-') + 1);
		
		$timeLimit .= " AND MONTH(registertime) = $month AND YEAR(registertime) = $year ";
		$timeLimit2 .= " WHERE MONTH(registertime) = $month AND YEAR(registertime) = $year ";
		
		$optionList = "<option value='filterVar'>$filterVar</option>
			<option value='100'>{$lang['last']} 100</option>
			<option value='250'>{$lang['last']} 250</option>
			<option value='500'>{$lang['last']} 500</option>";
			
		$limitVar = '';

			
	} else {
		
		$optionList = "<option value=''>Choose month</option>";
		
	}
	
	// Query to look up expenses
	$selectExpenseTot = "SELECT SUM(amount), SUM(vatamt) FROM expenses_nefos WHERE type = 0 $timeLimit $limitVar";
	try
	{
		$result = $pdo3->prepare("$selectExpenseTot");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$rowR = $result->fetch();
		$totalAmtExpNone = $rowR['SUM(amount)'];
		$totalVATNone = $rowR['SUM(vatamt)'];
		
	$selectExpenseTot = "SELECT SUM(amount), SUM(vatamt) FROM expenses_nefos WHERE type = 1 $timeLimit $limitVar";
	try
	{
		$result = $pdo3->prepare("$selectExpenseTot");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$rowR = $result->fetch();
		$totalAmtExpGoods = $rowR['SUM(amount)'];
		$totalVATGoods = $rowR['SUM(vatamt)'];
		
	$selectExpenseTot = "SELECT SUM(amount), SUM(vatamt) FROM expenses_nefos WHERE type = 2 $timeLimit $limitVar";
	try
	{
		$result = $pdo3->prepare("$selectExpenseTot");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$rowR = $result->fetch();
		$totalAmtExpServices = $rowR['SUM(amount)'];
		$totalVATServices = $rowR['SUM(vatamt)'];
		
	$totalAmt = $totalAmtExpNone + $totalAmtExpGoods + $totalAmtExpServices;
	$totalVAT = $totalVATNone + $totalVATGoods + $totalVATServices;
	
		
	// Query to look up expenses
	$selectExpenseTot = "SELECT SUM(amount), SUM(vatamt) FROM expenses_nefos WHERE type <> 99 $timeLimit $limitVar";
	try
	{
		$result = $pdo3->prepare("$selectExpenseTot");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$rowR = $result->fetch();
		$totalAmtExp = $rowR['SUM(amount)'];
		$totalVAT = $rowR['SUM(vatamt)'];

	
		
	// Query to look up expenses
	$selectExpenseTot = "SELECT e.expensecategory, SUM(e.amount), SUM(e.vatamt), brand, type FROM expenses_nefos e, expensecategories c WHERE e.expensecategory = c.categoryid $timeLimit GROUP BY type, expensecategory ORDER BY type DESC $limitVar";
		try
		{
			$results = $pdo3->prepare("$selectExpenseTot");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	

	// Create month-by-month split
	$findStartDate = "SELECT registertime FROM expenses_nefos ORDER BY registertime ASC LIMIT 1";
	try
	{
		$result = $pdo3->prepare("$selectExpenseTot");
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
		    
		    
		    
			
			
			$('#mainTable').tablesorter({
				usNumberFormat: true,
				headers: {
					2: {
						sorter: "currency"
					},
					3: {
						sorter: "currency"
					}
				}
			}); 

		});
		

		
function delete_expense(expenseid) {
	if (confirm("{$lang['expense-deleteconfirm']}")) {
				window.location = "uTil/delete-expense.php?expenseid=" + expenseid + "&source=expenses";
				}
}
EOD;
	pageStart($lang['title-expenses'], NULL, $deleteExpenseScript, "pexpenses", "admin", $lang['global-expensescaps'] . " NEFOS", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
?>


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
		 <br />
<?php
	if (isset($_POST['fromDate'])) {
		
		echo <<<EOD
		 <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="sixDigit" value="{$_POST['fromDate']}" />
		 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="sixDigit" value="{$_POST['untilDate']}" onchange='this.form.submit()' />
		 <button type="submit" style='display: inline-block; width: 40px; height: 27px;'>OK</button>
EOD;
		
	} else {
		
		echo <<<EOD
		 <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="sixDigit" placeholder="From date" />
		 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="sixDigit" placeholder="To date" onchange='this.form.submit()' />
		 <button type="submit" style='display: inline-block; width: 40px; height: 27px;'>OK</button>
EOD;

	}

	echo "<br /><select name='country' onchange='this.form.submit()'>";
	
	if ($_POST['country'] <> '') {
		
		$countryOrig = $_POST['country'];
		echo "<option value='$countryOrig'>$countryOrig</option>";
		
		// Query to look up countries
		$selectCTR = "SELECT DISTINCT country FROM expenses_nefos WHERE country <> '' AND country <> '$countryOrig' ";
	
	} else {
		
		echo "<option value=''>Country</option>";
	
		// Query to look up countries
		$selectCTR = "SELECT DISTINCT country FROM expenses_nefos WHERE country <> '' $timeLimit";
		
	}
		try
		{
			$results = $pdo3->prepare("$selectCTR");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($ctr = $results->fetch()) {

		
		$country = $ctr['country'];

		
		echo "<option value='$country'>$country</option>";
		
	}
	
?>
		 
		</select>
        </form>
        </div>
       </td>
      </tr>
     </table>


<br />

<h1>EXPENSE SUMMARY</h1>
	 <table class="default" id='mainTable'>
	  <thead>
	   <tr>
	    <th>Type</th>
	    <th><?php echo $lang['global-category']; ?></th>
	    <th><?php echo $lang['global-amount']; ?></th>
	    <th>VAT</th>
	   </tr>
	  </thead>
	  <tbody>
	  
<?php

		while ($expenseTot = $results->fetch()) {
	
	$amount = $expenseTot['SUM(e.amount)'];
	$vatamt = $expenseTot['SUM(e.vatamt)'];
	$expenseCat = $expenseTot['expensecategory'];
	$brand = $expenseTot['brand'];
	$type = $expenseTot['type'];
	
	if ($brand == 0) {
		$brandName = 'Nefos';
	} else {
		$brandName = 'CCS';
	}

	if ($type == 1) {
		$typeName = 'Goods';
	} else if ($type == 2) {
		$typeName = 'Services';
	} else {
		$typeName = '';
	}

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
	
	if ($type == 0) {
		
		$lineNone .= "
		
	   <tr>
	    <td class='left'>$typeName</td>
	    <td class='left'>$expenseCat</td>
	    <td class='right'>$amount &euro;</td>
	    <td class='right'>$vatamt &euro;</td>
	   </tr>";
	   
	} else if ($type == 1) {
		
		$lineGoods .= "
		
	   <tr>
	    <td class='left'>$typeName</td>
	    <td class='left'>$expenseCat</td>
	    <td class='right'>$amount &euro;</td>
	    <td class='right'>$vatamt &euro;</td>
	   </tr>";
	   
	} else {
		
		$lineServices .= "
		
	   <tr>
	    <td class='left'>$typeName</td>
	    <td class='left'>$expenseCat</td>
	    <td class='right'>$amount &euro;</td>
	    <td class='right'>$vatamt &euro;</td>
	   </tr>";
		
	}
	
	 
}

	echo $lineGoods;

	echo "
	   <tr>
	    <td class='left' style='border-top: 2px solid black !important; border-bottom: 3px solid black;'><strong>Goods</strong></td>
	    <td class='left' style='border-top: 2px solid black !important; border-bottom: 3px solid black;'><strong>TOTAL</strong></td>
	    <td class='right' style='border-top: 2px solid black !important; border-bottom: 3px solid black;'><strong>$totalAmtExpGoods &euro;</strong></td>
	    <td class='right' style='border-top: 2px solid black !important; border-bottom: 3px solid black;'><strong>$totalVATGoods &euro;</strong></td>
	   </tr>";
	   
	echo $lineServices;
	
	echo "
	   <tr>
	    <td class='left' style='border-top: 2px solid black !important; border-bottom: 3px solid black;'><strong>Services</strong></td>
	    <td class='left' style='border-top: 2px solid black !important; border-bottom: 3px solid black;'><strong>TOTAL</strong></td>
	    <td class='right' style='border-top: 2px solid black !important; border-bottom: 3px solid black;'><strong>$totalAmtExpServices &euro;</strong></td>
	    <td class='right' style='border-top: 2px solid black !important; border-bottom: 3px solid black;'><strong>$totalVATServices &euro;</strong></td>
	   </tr>";
	   
	echo $lineNone;
	
	echo "
	   <tr>
	    <td class='left' style='border-top: 2px solid black !important; border-bottom: 3px solid black;'><strong></strong></td>
	    <td class='left' style='border-top: 2px solid black !important; border-bottom: 3px solid black;'><strong>TOTAL</strong></td>
	    <td class='right' style='border-top: 2px solid black !important; border-bottom: 3px solid black;'><strong>$totalAmtExpNone &euro;</strong></td>
	    <td class='right' style='border-top: 2px solid black !important; border-bottom: 3px solid black;'><strong>$totalVATNone &euro;</strong></td>
	   </tr>";
	
	echo "
	   <tr>
	    <td class='left' style='border-top: 2px solid black !important; border-bottom: 3px solid black;'><strong>ALL TYPES</strong></td>
	    <td class='left' style='border-top: 2px solid black !important; border-bottom: 3px solid black;'><strong>TOTAL</strong></td>
	    <td class='right' style='border-top: 2px solid black !important; border-bottom: 3px solid black;'><strong>$totalAmtExp &euro;</strong></td>
	    <td class='right' style='border-top: 2px solid black !important; border-bottom: 3px solid black;'><strong>$totalVAT &euro;</strong></td>
	   </tr>
	  </tbody>
	 </table>";
?>
<br />	 
	 
<?php  displayFooter(); ?>
