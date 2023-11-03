<?php
	ob_start();
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	session_start();
/*	ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/
	$accessLevel = '3';
	// Authenticate & authorize
	authorizeUser($accessLevel);

	/** Include PHPExcel */
	require_once 'vendor/PHPExcel/Classes/PHPExcel.php';	

    $current_uri = $_SERVER['REQUEST_URI']; 
   
    $day_num = $_GET['day_id'];
    $week_num = $_GET['week_id'];
    $month_num = $_GET['month_id'];
    $seperator = '.';

	$getSeperator = "SELECT export_number_format from systemsettings";
	try
		{
			$result = $pdo3->prepare("$getSeperator");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	$exportrow = $result->fetch();	
	$seperator = $exportrow['export_number_format'];
?>



<?php

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
  // Add some data
$objPHPExcel->setActiveSheetIndex(0);
$objPHPExcel->getActiveSheet()
            ->setCellValue('B1', 'TOTAL');

if ($_SESSION['creditOrDirect'] == 1) {

		$objPHPExcel->getActiveSheet()
		            ->setCellValue('A1','');
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);  		
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('B1',$lang['title-donations']);
		$objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('C1',$lang['fees']);
		$objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);  	
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('D1',$lang['global-total']);
		$objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);  
		
} else {

		$objPHPExcel->getActiveSheet()
		            ->setCellValue('A1','');
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);  		
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('B1',$lang['title-donations']);
		$objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('C1',$lang['fees']);
		$objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);  	
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('D1',$lang['bar-sales-today']);
		$objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('E1',$lang['direct-dispenses']);
		$objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('F1',$lang['global-total']);
		$objPHPExcel->getActiveSheet()->getStyle('F1')->getFont()->setBold(true); 

}            
              
$objPHPExcel->getActiveSheet()
            ->setCellValue('A2', $lang['dispensary-daytoday']);
$objPHPExcel->getActiveSheet()->getStyle('A2')->getFont()->setBold(true);     
$startIndex1 = 3;                      

	
	for ($a = 0; $a < $day_num; $a++) {
		
		if ($a == 0) {
			$dateOperator = "DATE(NOW())";
			$timestamp = date("d-m-Y");
		} else {
			$dateOperator = "DATE_ADD(DATE(NOW()), INTERVAL -$a DAY)";
			$timestamp = date("d-m-Y", strtotime("-$a days"));
		}
	
		// Look up todays donations
		$selectDonations = "SELECT SUM(amount) FROM donations WHERE donatedTo <> 3 AND DATE(donationTime) = $dateOperator";
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
			$donationsToday = $row['SUM(amount)'];
			
		// And now membership fees
		$selectFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE DATE(paymentdate) = $dateOperator";
		try
		{
			$result = $pdo3->prepare("$selectFees");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$feesToday = $row['SUM(amountPaid)'];
			
		// Look up direct dispensed today
		$selectSales = "SELECT SUM(amount) from sales WHERE DATE(saletime) = $dateOperator AND direct < 3";
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
			
		// Look up direct bar sales today
		$selectSales = "SELECT SUM(amount) from b_sales WHERE DATE(saletime) = $dateOperator AND direct < 3";
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
		
		$totalToday = $donationsToday + $feesToday + $salesTodayCash + $salesTodayBarCash;
			
			if ($_SESSION['creditOrDirect'] == 1) {
					
					$day_row .= <<<EOD
			 <tr>
			  <td class="first">$timestamp:</td>
			  <td>{$expr(number_format($donationsToday,2))} <span class="smallerfont">{$_SESSION['currencyoperator']}</span></td>
			  <td>{$expr(number_format($feesToday,2))} <span class="smallerfont">{$_SESSION['currencyoperator']}</span></td>
			  <td>{$expr(number_format($totalToday,2))} <span class="smallerfont">{$_SESSION['currencyoperator']}</span></td>
			 </tr>
			EOD;

				$objPHPExcel->getActiveSheet()
			                ->setCellValue('A'.$startIndex1, $timestamp.':');
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('B'.$startIndex1, number_format($donationsToday,2).' '.$_SESSION['currencyoperator']);
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('C'.$startIndex1, number_format($feesToday,2).' '.$_SESSION['currencyoperator']);
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('D'.$startIndex1, number_format($totalToday,2).' '.$_SESSION['currencyoperator']);

				

			} else {
					$day_row .= <<<EOD
			 <tr>
			  <td class="first">$timestamp:</td>
			  <td>{$expr(number_format($donationsToday,2))} <span class="smallerfont">{$_SESSION['currencyoperator']}</span></td>
			  <td>{$expr(number_format($feesToday,2))} <span class="smallerfont">{$_SESSION['currencyoperator']}</span></td>
			  <td>{$expr(number_format($salesTodayBarCash,2))} <span class="smallerfont">{$_SESSION['currencyoperator']}</span></td>
			  <td>{$expr(number_format($salesTodayCash,2))} <span class="smallerfont">{$_SESSION['currencyoperator']}</span></td>
			  <td>{$expr(number_format($totalToday,2))} <span class="smallerfont">{$_SESSION['currencyoperator']}</span></td>
			 </tr>
			EOD;

				$objPHPExcel->getActiveSheet()
			                ->setCellValue('A'.$startIndex1, $timestamp.':');
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('B'.$startIndex1, number_format($donationsToday,2).' '.$_SESSION['currencyoperator']);
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('C'.$startIndex1, number_format($feesToday,2).' '.$_SESSION['currencyoperator']);
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('D'.$startIndex1, number_format($salesTodayBarCash,2).' '.$_SESSION['currencyoperator']); 
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('E'.$startIndex1, number_format($salesTodayCash,2).' '.$_SESSION['currencyoperator']); 
			   $objPHPExcel->getActiveSheet()
			                ->setCellValue('F'.$startIndex1, number_format($totalToday,2).' '.$_SESSION['currencyoperator']);

				
			}
			 $startIndex1++;
	}


	$week_total_title = $day_num + 4;
	$objPHPExcel->getActiveSheet()
		                ->setCellValue('A'.$week_total_title, $lang['dispensary-weektoweek']);
	$objPHPExcel->getActiveSheet()->getStyle('A'.$week_total_title)->getFont()->setBold(true);
	$startIndex2 = $week_total_title + 1;  
	for ($a = 0; $a < $week_num; $a++) {
		
				if ($a == 0) {
					$dateOperator = "YEARWEEK(saletime,1) = YEARWEEK(NOW(),1)";
					$dateOperator2 = "YEARWEEK(paymentdate,1) = YEARWEEK(NOW(),1)";
					$dateOperator3 = "YEARWEEK(donationTime,1) = YEARWEEK(NOW(),1)";
					$timestamp = $lang['dispensary-thisweek'];
				} else if ($a == 1) {
					$dateOperator = "YEARWEEK(saletime,1) = YEARWEEK(DATE_ADD((NOW()), INTERVAL -$a WEEK),1)";
					$dateOperator2 = "YEARWEEK(paymentdate,1) = YEARWEEK(DATE_ADD((NOW()), INTERVAL -$a WEEK),1)";
					$dateOperator3 = "YEARWEEK(donationTime,1) = YEARWEEK(DATE_ADD((NOW()), INTERVAL -$a WEEK),1)";
					$timestamp = $lang['dispensary-lastweek'];
				} else {
					$dateOperator = "YEARWEEK(saletime,1) = YEARWEEK(DATE_ADD((NOW()), INTERVAL -$a WEEK),1)";
					$dateOperator2 = "YEARWEEK(paymentdate,1) = YEARWEEK(DATE_ADD((NOW()), INTERVAL -$a WEEK),1)";
					$dateOperator3 = "YEARWEEK(donationTime,1) = YEARWEEK(DATE_ADD((NOW()), INTERVAL -$a WEEK),1)";
					$timestamp = $lang['dispensary-weeksago-1'] . $a . $lang['dispensary-weeksago-2'];
				}
			
			
				// Look up todays donations
				$selectDonations = "SELECT SUM(amount) FROM donations WHERE donatedTo <> 3 AND $dateOperator3";
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
					$donationsToday = $row['SUM(amount)'];
					
				// And now membership fees
				$selectFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE $dateOperator2";
				try
				{
					$result = $pdo3->prepare("$selectFees");
					$result->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			
				$row = $result->fetch();
					$feesToday = $row['SUM(amountPaid)'];
					
				// Look up direct dispensed today
				$selectSales = "SELECT SUM(amount) from sales WHERE $dateOperator AND direct < 3";
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
					
				// Look up direct bar sales today
				$selectSales = "SELECT SUM(amount) from b_sales WHERE $dateOperator AND direct < 3";
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
				
				$totalToday = $donationsToday + $feesToday + $salesTodayCash + $salesTodayBarCash;
					
		if ($_SESSION['creditOrDirect'] == 1) {


				 $objPHPExcel->getActiveSheet()
			                ->setCellValue('A'.$startIndex2, $timestamp.':');
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('B'.$startIndex2, number_format($donationsToday,2).' '.$_SESSION['currencyoperator']);
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('C'.$startIndex2, number_format($feesToday,2).' '.$_SESSION['currencyoperator']);
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('D'.$startIndex2, number_format($totalToday,2).' '.$_SESSION['currencyoperator']);
		} else {


				$objPHPExcel->getActiveSheet()
			                ->setCellValue('A'.$startIndex2, $timestamp.':');
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('B'.$startIndex2, number_format($donationsToday,2).' '.$_SESSION['currencyoperator']);
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('C'.$startIndex2, number_format($feesToday,2).' '.$_SESSION['currencyoperator']);
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('D'.$startIndex2, number_format($salesTodayBarCash,2).' '.$_SESSION['currencyoperator']); 
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('E'.$startIndex2, number_format($salesTodayCash,2).' '.$_SESSION['currencyoperator']); 
			   $objPHPExcel->getActiveSheet()
			                ->setCellValue('F'.$startIndex2, number_format($totalToday,2).' '.$_SESSION['currencyoperator']);
		}
		$startIndex2++;
	}

	$month_total_title =  $week_total_title + $week_num + 2;
	
	$objPHPExcel->getActiveSheet()
            ->setCellValue('A'.$month_total_title, $lang['dispensary-monthtomonth']);
    $objPHPExcel->getActiveSheet()->getStyle('A'.$month_total_title)->getFont()->setBold(true);  
    $startIndex3 = $month_total_title + 1; 
	for ($a = 0; $a < $month_num; $a++) {
				
				if ($a == 0) {
					$dateOperator = "MONTH(saletime) = MONTH(NOW()) AND YEAR(saletime) = YEAR(NOW())";
					$dateOperator2 = "MONTH(paymentdate) = MONTH(NOW()) AND YEAR(paymentdate) = YEAR(NOW())";
					$dateOperator3 = "MONTH(donationTime) = MONTH(NOW()) AND YEAR(donationTime) = YEAR(NOW())";
					$timestamp = $lang['dispensary-thismonth'];
				} else {
					$dateOperator = "MONTH(saletime) = MONTH(DATE_ADD((NOW()), INTERVAL -$a MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -$a MONTH))";
					$dateOperator2 = "MONTH(paymentdate) = MONTH(DATE_ADD((NOW()), INTERVAL -$a MONTH)) AND YEAR(paymentdate) = YEAR(DATE_ADD((NOW()), INTERVAL -$a MONTH))";
					$dateOperator3 = "MONTH(donationTime) = MONTH(DATE_ADD((NOW()), INTERVAL -$a MONTH)) AND YEAR(donationTime) = YEAR(DATE_ADD((NOW()), INTERVAL -$a MONTH))";
					$timestamp = date("m-Y", strtotime("-$a months", strtotime("first day of this month") ));
				}
			
				// Look up todays donations
				$selectDonations = "SELECT SUM(amount) FROM donations WHERE donatedTo <> 3 AND $dateOperator3";
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
					$donationsToday = $row['SUM(amount)'];
					
				// And now membership fees
				$selectFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE $dateOperator2";
				try
				{
					$result = $pdo3->prepare("$selectFees");
					$result->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			
				$row = $result->fetch();
					$feesToday = $row['SUM(amountPaid)'];
					
				// Look up direct dispensed today
				$selectSales = "SELECT SUM(amount) from sales WHERE $dateOperator AND direct < 3";
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
					
				// Look up direct bar sales today
				$selectSales = "SELECT SUM(amount) from b_sales WHERE $dateOperator AND direct < 3";
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
				
				$totalToday = $donationsToday + $feesToday + $salesTodayCash + $salesTodayBarCash;
					
				
		if ($_SESSION['creditOrDirect'] == 1) {

				$objPHPExcel->getActiveSheet()
			                ->setCellValue('A'.$startIndex3, $timestamp.':');
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('B'.$startIndex3, number_format($donationsToday,2).' '.$_SESSION['currencyoperator']);
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('C'.$startIndex3, number_format($feesToday,2).' '.$_SESSION['currencyoperator']);
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('D'.$startIndex3, number_format($totalToday,2).' '.$_SESSION['currencyoperator']);
		} else {


				$objPHPExcel->getActiveSheet()
			                ->setCellValue('A'.$startIndex3, $timestamp.':');
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('B'.$startIndex3, number_format($donationsToday,2).' '.$_SESSION['currencyoperator']);
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('C'.$startIndex3, number_format($feesToday,2).' '.$_SESSION['currencyoperator']);
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('D'.$startIndex3, number_format($salesTodayBarCash,2).' '.$_SESSION['currencyoperator']); 
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('E'.$startIndex3, number_format($salesTodayCash,2).' '.$_SESSION['currencyoperator']); 
			   $objPHPExcel->getActiveSheet()
			                ->setCellValue('F'.$startIndex3, number_format($totalToday,2).' '.$_SESSION['currencyoperator']);
		}
			$startIndex3++;
	}
	
	
		  	ob_end_clean();
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			//header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename=revenue-history.xlsx');
            header("Content-Type: application/download");                        
			//header('Cache-Control: max-age = 0');
			$objWriter->save('php://output');die;
			//header("location:dispensary-history.php");
		
?>
