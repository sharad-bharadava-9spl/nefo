<?php
	ob_start();
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
/*	ini_set("log_errors", TRUE);  
	  
	// setting the logging file in php.ini 
	ini_set('error_log', "error.log"); */
	$accessLevel = '3';
	$currencyoperator = $_SESSION['currencyoperator'];
	$currencyoperator = html_entity_decode($currencyoperator,ENT_QUOTES,'UTF-8');
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
	$domain = $_SESSION['domain'];
	
	getSettings();
	

	
	// Check if new Filter value was submitted, and assign query variable accordingly
	if (isset($_GET['filter']) && $_GET['filter'] != '') {
				
		$filterVar = $_GET['filter'];
		
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
		$filterVar = 100;
		$optionList = "<option value='100'>{$lang['last']} 100</option>
			<option value='250'>{$lang['last']} 250</option>
			<option value='500'>{$lang['last']} 500</option>";
	}
	
	// Check if 'entre fechas' was utilised
	if (isset($_GET['untilDate']) && $_GET['untilDate'] != '') {
		
		$fromDate = date("Y-m-d", strtotime($_GET['fromDate']));
		$untilDate = date("Y-m-d", strtotime($_GET['untilDate']));
		
		$timeLimit = "WHERE registertime BETWEEN '$fromDate' AND '$untilDate'";
			
	}
	
	if ($_SESSION['domain'] == 'dabulance') {
		// Query to look up expenses
		$selectExpenses = "SELECT expenseid, registertime, userid, expensetype, expense, moneysource, other, amount, shop, comment, receipt, invoice, expensecategory, photoext, currency FROM expenses $timeLimit ORDER by registertime DESC $limitVar";
	} else {
		// Query to look up expenses
		$selectExpenses = "SELECT expenseid, registertime, userid, expensetype, expense, moneysource, other, amount, shop, comment, receipt, invoice, expensecategory, photoext FROM expenses $timeLimit ORDER by registertime DESC $limitVar";
	}
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

		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('A1', $lang['pur-date']);
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);  		
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('B1',$lang['global-time']);
		$objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('C1',$lang['global-category']);
		$objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);  	
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('D1', $lang['global-expense']);
		$objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('E1',$lang['global-shop']);
		$objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('F1',$lang['global-member']);
		$objPHPExcel->getActiveSheet()->getStyle('F1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('G1',$lang['global-amount']);
		$objPHPExcel->getActiveSheet()->getStyle('G1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('H1', $lang['global-source']);
		$objPHPExcel->getActiveSheet()->getStyle('H1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('I1',$lang['global-receipt']);
		$objPHPExcel->getActiveSheet()->getStyle('I1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('J1', $lang['global-invoice']);
		$objPHPExcel->getActiveSheet()->getStyle('J1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('K1',$lang['global-comment']);
		$objPHPExcel->getActiveSheet()->getStyle('K1')->getFont()->setBold(true);  
		 
	?>
	  <?php
$startIndex = 2;
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
	$currency = '';
	
	if ($_SESSION['domain'] == 'dabulance') {
		if ($currency == 0) {
			$currency = "€";
		} else if ($currency == 1) {
			$currency = "$";
		}
	}
	if ($expense['comment'] != '') {
		
		$commentRead = "<span class='relativeitem'><img src='images/comments.png' id='comment$expenseid' /><div id='helpBox$expenseid' class='helpBox'>{$expense['comment']}</div>
		                <script>
		                  	$('#comment$expenseid').on({
						 		'mouseover' : function() {
								 	$('#helpBox$expenseid').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBox$expenseid').css('display', 'none');
							  	}
						  	});
						</script></span>
		                ";
		
	} else {
		
		$commentRead = "";
		
	}

	
	//$file = "images/_$domain/expenses/" . $expenseid . "." . $photoext;

	
		/*$object_exist =  object_exist($google_bucket, $google_root_folder."images/_$domain/expenses/" . $expenseid . "." . $photoext);
	if($object_exist){	
	  		$file = $google_root."images/_$domain/expenses/" . $expenseid . "." . $photoext;
	  		$invScan = "<a href='$file'><img src='images/receipt.png' /></a>";
	}else{
		// echo 'Caught exception: ',  $e->getMessage(), "\n";
		
		$invScan = "<a href='new-expense.php?expenseid=$expenseid'><img src='images/receipt-na.png' /></a>";
	}*/
/*	if (file_exists($file)) {
		$invScan = "<a href='$file'><img src='images/receipt.png' /></a>";
	} else {
		$invScan = "<a href='new-expense.php?expenseid=$expenseid'><img src='images/receipt-na.png' /></a>";
	}*/
	
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
		

	
	if ($moneysource == 1) {
		$source = $lang['global-till'];
	} else if ($moneysource == 2) {
		$source = $lang['global-bank'];
	} else if ($moneysource == 3) {
		$source = $other;
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
			$result = $pdo3->prepare("$userDetails");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($user = $result->fetch()) {
			$member = "#" . $user['memberno'] . " - " . $user['first_name'];
		}


	  	  	 // KONSTANT CODE UPDATE BEGIN
	  		$objPHPExcel->getActiveSheet()
		                ->setCellValue('A'.$startIndex, $formattedDate);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B'.$startIndex, $formattedTime);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('C'.$startIndex, $expenseCat);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('D'.$startIndex, $expense['expense']); 
            $objPHPExcel->getActiveSheet()
           				 ->setCellValue('E'.$startIndex, $expense['shop']); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('F'.$startIndex, $member); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('G'.$startIndex, $expense['amount']);  
			$objPHPExcel->getActiveSheet()
						->setCellValue('H'.$startIndex, $source); 
			 $objPHPExcel->getActiveSheet()
						->setCellValue('I'.$startIndex, $receipt);  
			$objPHPExcel->getActiveSheet()
						->setCellValue('J'.$startIndex, $invoice);  
			$objPHPExcel->getActiveSheet()
						->setCellValue('K'.$startIndex, $expense['comment']); 
		    $startIndex++; 
  }

 		  	ob_end_clean();
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			//header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename=expenses.xlsx');
            header("Content-Type: application/download");                        
			//header('Cache-Control: max-age = 0');
			$objWriter->save('php://output');die;
?>
	
