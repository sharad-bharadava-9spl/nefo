<?php
	ob_start();
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	require_once  'vendor/PHPExcel/Classes/PHPExcel.php';	
	// Create new PHPExcel object
	$objPHPExcel = new PHPExcel();
	// Set document properties
	$objPHPExcel->getProperties()->setCreator("Lokesh Nayak")
	                             ->setLastModifiedBy("Lokesh Nayak")
	                             ->setTitle("Test Document")
	                             ->setSubject("Test Document")
	                             ->setDescription("Test document for PHPExcel")
	                             ->setKeywords("office")
	                             ->setCategory("Test result file");
	getSettings();
	
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
			$timeLimit2 = "AND MONTH(registertime) = $month AND YEAR(registertime) = $year";
			
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
		$timeLimit2 = "AND registertime BETWEEN '$fromDate' AND '$untilDate'";
			
	}

	
	// Query to look up expenses
	$selectExpenseTot = "SELECT SUM(amount) FROM expenses $timeLimit";
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
		
	// Query to look up expenses
	$selectExpenseTot = "SELECT e.expensecategory, SUM(e.amount) FROM expenses e, expensecategories c WHERE e.expensecategory = c.categoryid $timeLimit2 GROUP BY expensecategory ORDER BY c.nameen ASC";
		try
		{
			$resultsX = $pdo3->prepare("$selectExpenseTot");
			$resultsX->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	

	// Create month-by-month split
	$findStartDate = "SELECT registertime FROM expenses ORDER BY registertime ASC LIMIT 1";
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

			$('#mainTable').tablesorter({
				usNumberFormat: true,
				headers: {
					0: {
						sorter: "dates"
					},
					5: {
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
	pageStart($lang['title-expenses'], NULL, $deleteExpenseScript, "pexpenses", "admin", $lang['global-expensescaps'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
?>

<center>
	 <div id="filterbox">
		 <div id="mainboxheader">
		 Filtrar lista </div>
		        
			<div class="boxcontent">
				
		        <form action='' method='POST' style='margin-top: 3px;'>
			     <select id='filter' name='filter' class='defaultinput' onchange='this.form.submit()'>
			      <?php echo $optionList; ?>
				 </select>
		        </form>
		        <form action='' method='POST'>
		<?php
			if (isset($_POST['fromDate'])) {
				
				echo <<<EOD
				 <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="sixDigit defaultinput" value="{$_POST['fromDate']}" />
				 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="sixDigit defaultinput" value="{$_POST['untilDate']}" onchange='this.form.submit()' />
				 <button type="submit" class="cta2" style='display: inline-block; width: 40px;'>OK</button>
		EOD;
				
			} else {
				
				echo <<<EOD
				 <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="sixDigit defaultinput" placeholder="Desde fecha" />
				 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="sixDigit defaultinput" placeholder="Hasta fecha" onchange='this.form.submit()' />
				 <button type="submit"  class="cta2"  style='display: inline-block; width: 40px;'>OK</button>
		EOD;

			}
		?>
		        </form>
		     </div>
		 </div>
		</center>


<br />

<h3 class='title'>EXPENSE SUMMARY</h3> <br></br><br></br>
	 <table class="default" id='mainTable'>
	  <thead>
	   <tr>
	    <th style="position: relative;"><?php echo $lang['global-category']; ?>
	    	 <a href="#" id="xllink"><img src="images/excel-new.png" style='position: absolute; top: 0; left: 10px; margin-top: -66px;'/></a><br /><br />
	    </th>
	    <th><?php echo $lang['global-amount']; ?></th>
	   </tr>
	  </thead>
	  <tbody>
	   <?php
		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('A1', $lang['global-category']);
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);  		
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('B1',$lang['global-amount']);
		$objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);  
	?>
<?php
$startIndex = 2;
$rowCount = $resultsX->rowCount();
		while ($expenseTot = $resultsX->fetch()) {
	
	$amount = $expenseTot['SUM(e.amount)'];
	$expenseCat = $expenseTot['expensecategory'];

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
	
	echo "
		
	   <tr>
	    <td class='left'>$expenseCat</td>
	    <td class='right'>$amount {$_SESSION['currencyoperator']}</td>
	   </tr>";
	 	  	  	  // KONSTANT CODE UPDATE BEGIN
	  		$objPHPExcel->getActiveSheet()
		                ->setCellValue('A'.$startIndex, $expenseCat);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B'.$startIndex, $amount." ".$_SESSION['currencyoperator']);
		    $startIndex++; 
}

	echo "
	   <tr>
	    <td class='left' style='border-top: 2px solid black !important; border-bottom: 3px solid black;'><strong>TOTAL</strong></td>
	    <td class='right' style='border-top: 2px solid black !important; border-bottom: 3px solid black;'><strong>$totalAmtExp {$_SESSION['currencyoperator']}</strong></td>
	   </tr>
	  </tbody>
	 </table>";
	 $lastIndex = $rowCount + 2;
	 	$objPHPExcel->getActiveSheet()
		                ->setCellValue('A'.$lastIndex, 'TOTAL');
		$objPHPExcel->getActiveSheet()->getStyle('A'.$lastIndex)->getFont()->setBold(true);                 
		$objPHPExcel->getActiveSheet()
		                ->setCellValue('B'.$lastIndex, $totalAmtExp." ".$_SESSION['currencyoperator']);
		$objPHPExcel->getActiveSheet()->getStyle('B'.$lastIndex)->getFont()->setBold(true);                  

if(isset($_GET['action'])){
    ob_end_clean();
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    //header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename='.$lang['title-expenses'].'.xlsx');
    header("Content-Type: application/download");
    //header('Cache-Control: max-age = 0');
    $objWriter->save('php://output');
    header("location:expenses-summary.php");  // KONSTANT CODE UPDATE END 
    die;
}	                
?>
<br />	 
	 
<?php  displayFooter(); ?>
<script type="text/javascript">
	$("#xllink").click(function(){
	    $("#load").show();
	    window.location.href = "expenses-summary.php?action=xls"; 
	    setTimeout(function () {
	        $("#load").hide();
	    }, 2000);     
	 });
</script>
