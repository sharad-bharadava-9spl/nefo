<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings();
	require_once 'vendor/PHPExcel/Classes/PHPExcel.php';

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

					$objPHPExcel->setActiveSheetIndex(0);
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('A1','');
					$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);  
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('B1',$lang['cash']);
					$objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);  		
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('C1',$lang['bank-card']);
					$objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);  
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('D1','TOTAL');
					$objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);  
					
	// If form submits, there will ALAWYS be a untilDate set!
	
	// Check if 'entre fechas' was utilised

	if (isset($_GET['untilDate']) && $_GET['untilDate'] !='') {
		
		
		
		$fromDate = date("Y-m-d", strtotime($_GET['fromDate']));
		$untilDate = date("Y-m-d", strtotime($_GET['untilDate']));
		
		$timeLimit = "AND DATE(donationTime) BETWEEN DATE('$fromDate') AND DATE('$untilDate')";
		$timeLimit2 = "AND DATE(paymentdate) BETWEEN DATE('$fromDate') AND DATE('$untilDate')";
		$timeLimit3 = "DATE(donationTime) BETWEEN DATE('$fromDate') AND DATE('$untilDate')";
		$timeLimit4 = "DATE(paymentdate) BETWEEN DATE('$fromDate') AND DATE('$untilDate')";
		$timeLimit5 = "AND DATE(saletime) BETWEEN DATE('$fromDate') AND DATE('$untilDate')";
		$timeLimit6 = "DATE(saletime) BETWEEN DATE('$fromDate') AND DATE('$untilDate')";
		
		
		if ($_GET['cashBox'] != 'a') {
			
			$cashLimit = "AND donatedTo = 2";
			$cashLimit2 = "AND paidTo = 2";
			$cashLimit3 = "AND direct = 2";
			
		}
		
		if ($_GET['cardBox'] != 'a') {
			
			$cashLimit .= " AND (donatedTo < 2 OR donatedTo = 4)";
			$cashLimit2 .= " AND (paidTo < 2 OR paidTo = 4)";
			$cashLimit3 .= " AND direct < 2";
			
		}

			
	} else {
		
		
		
		$nowDate = date("d-m-Y");
		
		$timeLimit = "AND DATE(donationTime) = DATE(NOW())";
		$timeLimit2 = "AND DATE(paymentdate) = DATE(NOW())";
		$timeLimit3 = "DATE(donationTime) = DATE(NOW())";
		$timeLimit4 = "DATE(paymentdate) = DATE(NOW())";
		$timeLimit5 = "AND DATE(saletime) = DATE(NOW())";
		$timeLimit6 = "DATE(saletime) = DATE(NOW())";
		
	}
		
	// Look up todays donations
	$selectDonations = "SELECT SUM(amount) from donations WHERE (donatedTo < 2 OR donatedTo = 4) $timeLimit $cashLimit";
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
		
	// Look up today's membership fees
	$selectMembershipFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE (paidTo < 2 OR paidTo = 4) $timeLimit2 $cashLimit2";
		try
		{
			$result = $pdo3->prepare("$selectMembershipFees");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$membershipFees = $row['SUM(amountPaid)'];
		
	// Look up todays bank donations
	$selectDonations = "SELECT SUM(amount) from donations WHERE donatedTo = 2 $timeLimit $cashLimit";
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
		$bankDonations = $row['SUM(amount)'];
		
		
	// Look up today's membership fees Bank
	$selectMembershipFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE paidTo = 2 $timeLimit2 $cashLimit2";
		try
		{
			$result = $pdo3->prepare("$selectMembershipFees");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$membershipfeesBank = $row['SUM(amountPaid)'];
		
		// Direct Dispensing
	if ($_SESSION['creditOrDirect'] == 0) {
		$selectExpenses = "
	SELECT '1' AS type, donationTime AS time, userid, amount, donatedTo FROM donations WHERE $timeLimit3 $cashLimit UNION ALL 
	SELECT '2' AS type, paymentdate AS time, userid, amountPaid AS amount, paidTo AS donatedTo FROM memberpayments WHERE $timeLimit4 $cashLimit2 UNION ALL 
	SELECT '3' AS type, saletime AS time, userid, amount, direct AS donatedTo FROM sales WHERE $timeLimit6 UNION ALL 
	SELECT '4' AS type, saletime AS time, userid, amount, direct AS donatedTo FROM b_sales WHERE $timeLimit6 ORDER BY time DESC";

	} else {
	// Query to look up list of donations & membership fees
	 $selectExpenses = "SELECT '1' AS type, donationTime AS time, userid, amount, donatedTo FROM donations WHERE $timeLimit3 $cashLimit UNION ALL SELECT '2' AS type, paymentdate AS time, userid, amountPaid AS amount, paidTo AS donatedTo FROM memberpayments WHERE $timeLimit4 $cashLimit2"; 
	
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
		
		// Direct Dispensing
		if ($_SESSION['creditOrDirect'] == 0) {
							
			// Look up dispensed today cash
			$selectSales = "SELECT SUM(amount) from sales WHERE direct < 2 $timeLimit5";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$salesTodayCash = $row['SUM(amount)'];
		
			// Look up dispensed today bank
			$selectSales = "SELECT SUM(amount) from sales WHERE direct = 2 $timeLimit5 $cashLimit3";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$salesTodayBank = $row['SUM(amount)'];
					
			// Look up bar sales today cash
			$selectSales = "SELECT SUM(amount) from b_sales WHERE direct < 2 $timeLimit5";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$salesTodayBarCash = $row['SUM(amount)'];
		
			// Look up bar sales today bank
			$selectSales = "SELECT SUM(amount) from b_sales WHERE direct = 2 $timeLimit5";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$salesTodayBarBank = $row['SUM(amount)'];
					
				
		}
	

     $objPHPExcel->getActiveSheet()
		                ->setCellValue('A2', $lang['fees']);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B2', number_format($membershipFees,2)." ".$_SESSION['currencyoperator']);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('C2',  number_format($membershipfeesBank,2)." ".$_SESSION['currencyoperator']);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('D2', number_format($membershipFees + $membershipfeesBank,2)." ".$_SESSION['currencyoperator']); 	        
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('A3', $lang['global-donations']);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B3', number_format($donations,2)." ".$_SESSION['currencyoperator']);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('C3',  number_format($bankDonations,2)." ".$_SESSION['currencyoperator']);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('D3', number_format($donations + $bankDonations,2)." ".$_SESSION['currencyoperator']); 
		     if ($_SESSION['creditOrDirect'] == 0) {
				    $objPHPExcel->getActiveSheet()
				                ->setCellValue('A4', $lang['direct-dispenses']);
				    $objPHPExcel->getActiveSheet()
				                ->setCellValue('B4', number_format($salesTodayCash,2)." ".$_SESSION['currencyoperator']);
				    $objPHPExcel->getActiveSheet()
				                ->setCellValue('C4',  number_format($salesTodayBank,2)." ".$_SESSION['currencyoperator']);
				    $objPHPExcel->getActiveSheet()
				                ->setCellValue('D4', number_format($salesTodayCash + $salesTodayBank,2)." ".$_SESSION['currencyoperator']); 	
				    $objPHPExcel->getActiveSheet()
				                ->setCellValue('A5', $lang['direct-bar-sales']);
				    $objPHPExcel->getActiveSheet()
				                ->setCellValue('B5', number_format($salesTodayBarCash,2)." ".$_SESSION['currencyoperator']);
				    $objPHPExcel->getActiveSheet()
				                ->setCellValue('C5',  number_format($salesTodayBarBank,2)." ".$_SESSION['currencyoperator']);
				    $objPHPExcel->getActiveSheet()
				                ->setCellValue('D5', number_format($salesTodayBarCash + $salesTodayBarBank,2)." ".$_SESSION['currencyoperator']); 		
				    $objPHPExcel->getActiveSheet()
				                ->setCellValue('A6', 'Total');
				    $objPHPExcel->getActiveSheet()
				                ->setCellValue('B6', number_format($membershipFees + $donations + $salesTodayCash + $salesTodayBarCash,2)." ".$_SESSION['currencyoperator']);
				    $objPHPExcel->getActiveSheet()
				                ->setCellValue('C6',  number_format($membershipfeesBank + $bankDonations + $salesTodayBank + $salesTodayBarBank,2)." ".$_SESSION['currencyoperator']);
				    $objPHPExcel->getActiveSheet()
				                ->setCellValue('D6', number_format($membershipfeesBank + $bankDonations + $membershipFees + $donations + $salesTodayCash + $salesTodayBarCash + $salesTodayBank + $salesTodayBarBank,2)." ".$_SESSION['currencyoperator']); 

		     }else{
		     		$objPHPExcel->getActiveSheet()
				                ->setCellValue('A4', 'Total');
				    $objPHPExcel->getActiveSheet()
				                ->setCellValue('B4', number_format($membershipFees + $donations + $salesTodayCash + $salesTodayBarCash,2)." ".$_SESSION['currencyoperator']);
				    $objPHPExcel->getActiveSheet()
				                ->setCellValue('C4',  number_format($membershipfeesBank + $bankDonations + $salesTodayBank + $salesTodayBarBank,2)." ".$_SESSION['currencyoperator']);
				    $objPHPExcel->getActiveSheet()
				                ->setCellValue('D4', number_format($membershipfeesBank + $bankDonations + $membershipFees + $donations + $salesTodayCash + $salesTodayBarCash + $salesTodayBank + $salesTodayBarBank,2)." ".$_SESSION['currencyoperator']); 
		     }
     if ($_SESSION['creditOrDirect'] == 0) {
     	$x = 7;
     	$startIndex = $x + 1;
     }else{
     	$x = 5;
     	$startIndex = $x+1;
     }       

			$objPHPExcel->getActiveSheet()
			            ->setCellValue('A'.$x,$lang['global-time']);
			$objPHPExcel->getActiveSheet()->getStyle('A'.$x)->getFont()->setBold(true);  
			$objPHPExcel->getActiveSheet()
			            ->setCellValue('B'.$x,$lang['global-type']);
			$objPHPExcel->getActiveSheet()->getStyle('B'.$x)->getFont()->setBold(true);  		
			$objPHPExcel->getActiveSheet()
			            ->setCellValue('C'.$x,$lang['paid-by']);
			$objPHPExcel->getActiveSheet()->getStyle('C'.$x)->getFont()->setBold(true);  
			$objPHPExcel->getActiveSheet()
			            ->setCellValue('D'.$x,'#');
			$objPHPExcel->getActiveSheet()->getStyle('D'.$x)->getFont()->setBold(true);  
			$objPHPExcel->getActiveSheet()
			            ->setCellValue('E'.$x,$lang['global-member']);
			$objPHPExcel->getActiveSheet()->getStyle('E'.$x)->getFont()->setBold(true); 
			$objPHPExcel->getActiveSheet()
			            ->setCellValue('F'.$x,$lang['global-amount']);
			$objPHPExcel->getActiveSheet()->getStyle('F'.$x)->getFont()->setBold(true); 

		while ($donation = $results->fetch()) {
	
	$id = $donation['id'];
	$donationTime = date("d-m-Y H:i", strtotime($donation['time'] . "+$offsetSec seconds"));
	$user_id = $donation['userid'];
	$amount = $donation['amount'];
	$type = $donation['type'];
	$donatedTo = $donation['donatedTo'];
	
	if ($donatedTo == '2') {
		$donatedTo = $lang['bank-card'];
	} else if ($donatedTo == '3') {
		$donatedTo = '';
	} else {
		$donatedTo = $lang['cash'];
	}
	
	if ($type == 1) {
		$movementType = $lang['donation-donation'];
	} else if ($type == 2) {
		$movementType = $lang['memberfees'];
	} else if ($type == 3) {
		$movementType = $lang['global-dispense'];
	} else if ($type == 4) {
		$movementType = "Bar";
	} else {
		$movementType = "N/A";
	}
	
	// Look up user details for showing profile on the Sales page
	$userDetails = "SELECT memberno, first_name, last_name FROM users WHERE user_id = $user_id";
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
	
		$row = $result->fetch();
		$memberno = $row['memberno'];
		$first_name = $row['first_name'];
		$last_name = $row['last_name'];

/*	$expense_row = sprintf("
  	  <tr>
  	   <td class='left' style='padding: 10px;'>%s</td>
  	   <td class='left' style='padding: 10px;'>%s</td>
  	   <td class='left' style='padding: 10px;'>%s</td>
  	   <td class='left' style='padding: 10px;'>%s</td>
  	   <td class='left' style='padding: 10px;'>%s %s</td>
  	   <td class='right' style='padding: 10px;'>%0.02f $_SESSION['currencyoperator']</td>
	  </tr>",
	  $donationTime, $movementType, $donatedTo, $memberno, $first_name, $last_name, $amount
	  );*/
			

	 	   $objPHPExcel->getActiveSheet()
		                ->setCellValue('A'.$startIndex, $donationTime);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B'.$startIndex, $movementType);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('C'.$startIndex,  $donatedTo);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('D'.$startIndex, $memberno); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('E'.$startIndex, $first_name." ".$last_name); 
            $objPHPExcel->getActiveSheet()
           				 ->setCellValue('F'.$startIndex, $amount." ".$_SESSION['currencyoperator']); 

	  $startIndex++;
}
	ob_end_clean();
			    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			    //header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			    header('Content-type: application/vnd.ms-excel');
			    header('Content-Disposition: attachment;filename=financial-summary.xlsx');
			    header("Content-Type: application/download");
			    //header('Cache-Control: max-age = 0');
			    $objWriter->save('php://output');
    			die;
  
