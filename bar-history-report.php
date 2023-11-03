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
if ($_SESSION['lang'] == 'es') {
     $exportname = 'Bar Retiradas';
} else {
     $exportname = 'Bar Dispensary';
}	


        
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
$objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);              
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
	
		// Look up todays sales
		$selectSales = "SELECT SUM(amount), SUM(unitsTot) from b_sales WHERE DATE(saletime) = $dateOperator";

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
			$b_salesToday = $row['SUM(amount)'];
			$unitsTotToday = $row['SUM(unitsTot)'];
			
		
	

		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('A'.$startIndex1, $timestamp.':');
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B'.$startIndex1, $unitsTotToday.'u.');
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('C'.$startIndex1, $b_salesToday.' '.$_SESSION['currencyoperator']);

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
			$timestamp = $lang['dispensary-thisweek'];
		} else if ($a == 1) {
			$dateOperator = "YEARWEEK(saletime,1) = YEARWEEK(DATE_ADD((NOW()), INTERVAL -$a WEEK),1)";
			$timestamp = $lang['dispensary-lastweek'];
		} else {
			$dateOperator = "YEARWEEK(saletime,1) = YEARWEEK(DATE_ADD((NOW()), INTERVAL -$a WEEK),1)";
			$timestamp = $lang['dispensary-weeksago-1'] . $a . $lang['dispensary-weeksago-2'];
		}
	
		// Look up todays sales
		$selectSales = "SELECT SUM(amount), SUM(unitsTot) from b_sales WHERE $dateOperator";

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
			$b_salesToday = $row['SUM(amount)'];
			$unitsTotToday = $row['SUM(unitsTot)'];
			

			
				$objPHPExcel->getActiveSheet()
			                ->setCellValue('A'.$startIndex2, $timestamp.':');
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('B'.$startIndex2, $unitsTotToday.'u.');
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('C'.$startIndex2, $b_salesToday.' '.$_SESSION['currencyoperator']);
		     
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
			$timestamp = $lang['dispensary-thismonth'];
		} else {
			$dateOperator = "MONTH(saletime) = MONTH(DATE_ADD((NOW()), INTERVAL -$a MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -$a MONTH))";
			$timestamp = date("m-Y", strtotime("-$a months", strtotime("first day of this month") ));
		}
	
		// Look up todays sales
		$selectSales = "SELECT SUM(amount), SUM(unitsTot) from b_sales WHERE $dateOperator";
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
			$b_salesToday = $row['SUM(amount)'];
			$unitsTotToday = $row['SUM(unitsTot)'];
			

		
				$objPHPExcel->getActiveSheet()
			                ->setCellValue('A'.$startIndex3, $timestamp.':');
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('B'.$startIndex3, $unitsTotToday.'u.');
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('C'.$startIndex3, $b_salesToday.' '.$_SESSION['currencyoperator']);
		     
			 $startIndex3++;
	}
	
		  	ob_end_clean();
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			//header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename=bar-history.xlsx');
            header("Content-Type: application/download");                        
			//header('Cache-Control: max-age = 0');
			$objWriter->save('php://output');die;
			//header("location:dispensary-history.php");
		
?>
