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
            ->setCellValue('A1', $lang['dispensary-daytoday']);
$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);     
                     

		// Look up todays sales
		$selectSales = "SELECT COUNT(scanin) FROM newvisits WHERE DATE(scanin) = DATE(NOW())";
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
			$salesToday = $row['COUNT(scanin)'];
			$unitsToday = $row['SUM(units)'];
			$quantityToday = $row['SUM(realQuantity)'];
			
		// Look up daily sales -1
		$selectSales = "SELECT COUNT(scanin) FROM newvisits WHERE DATE(scanin) = DATE_ADD(DATE(NOW()), INTERVAL -1 DAY)";
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
			$salesTodayMinus1 = $row['COUNT(scanin)'];
			$unitsTodayMinus1 = $row['SUM(units)'];
			$quantityTodayMinus1 = $row['SUM(realQuantity)'];
			
		// Look up daily sales -2
		$selectSales = "SELECT COUNT(scanin) FROM newvisits WHERE DATE(scanin) = DATE_ADD(DATE(NOW()), INTERVAL -2 DAY)";
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
			$salesTodayMinus2 = $row['COUNT(scanin)'];
			$unitsTodayMinus2 = $row['SUM(units)'];
			$quantityTodayMinus2 = $row['SUM(realQuantity)'];
			
		// Look up daily sales -3
		$selectSales = "SELECT COUNT(scanin) FROM newvisits WHERE DATE(scanin) = DATE_ADD(DATE(NOW()), INTERVAL -3 DAY)";
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
			$salesTodayMinus3 = $row['COUNT(scanin)'];
			$unitsTodayMinus3 = $row['SUM(units)'];
			$quantityTodayMinus3 = $row['SUM(realQuantity)'];
			
		// Look up daily sales -4
		$selectSales = "SELECT COUNT(scanin) FROM newvisits WHERE DATE(scanin) = DATE_ADD(DATE(NOW()), INTERVAL -4 DAY)";
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
			$salesTodayMinus4 = $row['COUNT(scanin)'];
			$unitsTodayMinus4 = $row['SUM(units)'];
			$quantityTodayMinus4 = $row['SUM(realQuantity)'];
			
		// Look up daily sales -5
		$selectSales = "SELECT COUNT(scanin) FROM newvisits WHERE DATE(scanin) = DATE_ADD(DATE(NOW()), INTERVAL -5 DAY)";
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
			$salesTodayMinus5 = $row['COUNT(scanin)'];
			$unitsTodayMinus5 = $row['SUM(units)'];
			$quantityTodayMinus5 = $row['SUM(realQuantity)'];
			
		// Look up daily sales -6
		$selectSales = "SELECT COUNT(scanin) FROM newvisits WHERE DATE(scanin) = DATE_ADD(DATE(NOW()), INTERVAL -6 DAY)";
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
			$salesTodayMinus6 = $row['COUNT(scanin)'];
			$unitsTodayMinus6 = $row['SUM(units)'];
			$quantityTodayMinus6 = $row['SUM(realQuantity)'];

		// Look up daily sales -7
		$selectSales = "SELECT COUNT(scanin) FROM newvisits WHERE DATE(scanin) = DATE_ADD(DATE(NOW()), INTERVAL -7 DAY)";
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
			$salesTodayMinus7 = $row['COUNT(scanin)'];
			$unitsTodayMinus7 = $row['SUM(units)'];
			$quantityTodayMinus7 = $row['SUM(realQuantity)'];

			
			
			// AND NOW WEEK BY WEEK //
			
		// Look up this weeks sales
		$selectSales = "SELECT COUNT(scanin) FROM newvisits WHERE WEEK(scanin,1) = WEEK(NOW(),1) AND YEAR(scanin) = YEAR(NOW()) ";
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
			$salesWeek = $row['COUNT(scanin)'];
			$unitsWeek = $row['SUM(units)'];
			$quantityWeek = $row['SUM(realQuantity)'];
			
		// Look up weekly sales -1
		$selectSales = "SELECT COUNT(scanin) FROM newvisits WHERE WEEK(scanin,1) = WEEK(DATE_ADD((NOW()), INTERVAL -1 WEEK),1) AND YEAR(scanin) = YEAR(DATE_ADD((NOW()), INTERVAL -1 WEEK))";
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
			$salesWeekMinus1 = $row['COUNT(scanin)'];
			$unitsWeekMinus1 = $row['SUM(units)'];
			$quantityWeekMinus1 = $row['SUM(realQuantity)'];
			
		// Look up weekly sales -2
		$selectSales = "SELECT COUNT(scanin) FROM newvisits WHERE WEEK(scanin,1) = WEEK(DATE_ADD((NOW()), INTERVAL -2 WEEK),1) AND YEAR(scanin) = YEAR(DATE_ADD((NOW()), INTERVAL -2 WEEK))";
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
			$salesWeekMinus2 = $row['COUNT(scanin)'];
			$unitsWeekMinus2 = $row['SUM(units)'];
			$quantityWeekMinus2 = $row['SUM(realQuantity)'];
			
		// Look up weekly sales -3
		$selectSales = "SELECT COUNT(scanin) FROM newvisits WHERE WEEK(scanin,1) = WEEK(DATE_ADD((NOW()), INTERVAL -3 WEEK),1) AND YEAR(scanin) = YEAR(DATE_ADD((NOW()), INTERVAL -3 WEEK))";
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
			$salesWeekMinus3 = $row['COUNT(scanin)'];
			$unitsWeekMinus3 = $row['SUM(units)'];
			$quantityWeekMinus3 = $row['SUM(realQuantity)'];
			
		// Look up weekly sales -4
		$selectSales = "SELECT COUNT(scanin) FROM newvisits WHERE WEEK(scanin,1) = WEEK(DATE_ADD((NOW()), INTERVAL -4 WEEK),1) AND YEAR(scanin) = YEAR(DATE_ADD((NOW()), INTERVAL -4 WEEK))";
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
			$salesWeekMinus4 = $row['COUNT(scanin)'];
			$unitsWeekMinus4 = $row['SUM(units)'];
			$quantityWeekMinus4 = $row['SUM(realQuantity)'];
			
		// Look up weekly sales -5
		$selectSales = "SELECT COUNT(scanin) FROM newvisits WHERE WEEK(scanin,1) = WEEK(DATE_ADD((NOW()), INTERVAL -5 WEEK),1) AND YEAR(scanin) = YEAR(DATE_ADD((NOW()), INTERVAL -5 WEEK))";
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
			$salesWeekMinus5 = $row['COUNT(scanin)'];
			$unitsWeekMinus5 = $row['SUM(units)'];
			$quantityWeekMinus5 = $row['SUM(realQuantity)'];
			
		// Look up weekly sales -6
		$selectSales = "SELECT COUNT(scanin) FROM newvisits WHERE WEEK(scanin,1) = WEEK(DATE_ADD((NOW()), INTERVAL -6 WEEK),1) AND YEAR(scanin) = YEAR(DATE_ADD((NOW()), INTERVAL -6 WEEK))";
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
			$salesWeekMinus6 = $row['COUNT(scanin)'];
			$unitsWeekMinus6 = $row['SUM(units)'];
			$quantityWeekMinus6 = $row['SUM(realQuantity)'];

		// Look up weekly sales -7
		$selectSales = "SELECT COUNT(scanin) FROM newvisits WHERE WEEK(scanin,1) = WEEK(DATE_ADD((NOW()), INTERVAL -7 WEEK),1) AND YEAR(scanin) = YEAR(DATE_ADD((NOW()), INTERVAL -7 WEEK))";
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
			$salesWeekMinus7 = $row['COUNT(scanin)'];
			$unitsWeekMinus7 = $row['SUM(units)'];
			$quantityWeekMinus7 = $row['SUM(realQuantity)'];
			
			
			
			// AND NOW MONTH BY MONTH //
			
		// Look up this months sales
		$selectSales = "SELECT COUNT(scanin) FROM newvisits WHERE MONTH(scanin) = MONTH(NOW()) AND YEAR(scanin) = YEAR(NOW()) ";
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
			$salesMonth = $row['COUNT(scanin)'];
			$unitsMonth = $row['SUM(units)'];
			$quantityMonth = $row['SUM(realQuantity)'];
			
		// Look up monthly sales -1
		$selectSales = "SELECT COUNT(scanin) FROM newvisits WHERE MONTH(scanin) = MONTH(DATE_ADD((NOW()), INTERVAL -1 MONTH)) AND YEAR(scanin) = YEAR(DATE_ADD((NOW()), INTERVAL -1 MONTH))";
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
			$salesMonthMinus1 = $row['COUNT(scanin)'];
			$unitsMonthMinus1 = $row['SUM(units)'];
			$quantityMonthMinus1 = $row['SUM(realQuantity)'];
			
		// Look up monthly sales -2
		$selectSales = "SELECT COUNT(scanin) FROM newvisits WHERE MONTH(scanin) = MONTH(DATE_ADD((NOW()), INTERVAL -2 MONTH)) AND YEAR(scanin) = YEAR(DATE_ADD((NOW()), INTERVAL -2 MONTH))";
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
			$salesMonthMinus2 = $row['COUNT(scanin)'];
			$unitsMonthMinus2 = $row['SUM(units)'];
			$quantityMonthMinus2 = $row['SUM(realQuantity)'];
			
		// Look up monthly sales -3
		$selectSales = "SELECT COUNT(scanin) FROM newvisits WHERE MONTH(scanin) = MONTH(DATE_ADD((NOW()), INTERVAL -3 MONTH)) AND YEAR(scanin) = YEAR(DATE_ADD((NOW()), INTERVAL -3 MONTH))";
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
			$salesMonthMinus3 = $row['COUNT(scanin)'];
			$unitsMonthMinus3 = $row['SUM(units)'];
			$quantityMonthMinus3 = $row['SUM(realQuantity)'];
			
		// Look up monthly sales -4
		$selectSales = "SELECT COUNT(scanin) FROM newvisits WHERE MONTH(scanin) = MONTH(DATE_ADD((NOW()), INTERVAL -4 MONTH)) AND YEAR(scanin) = YEAR(DATE_ADD((NOW()), INTERVAL -4 MONTH))";
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
			$salesMonthMinus4 = $row['COUNT(scanin)'];
			$unitsMonthMinus4 = $row['SUM(units)'];
			$quantityMonthMinus4 = $row['SUM(realQuantity)'];
			
		// Look up monthly sales -5
		$selectSales = "SELECT COUNT(scanin) FROM newvisits WHERE MONTH(scanin) = MONTH(DATE_ADD((NOW()), INTERVAL -5 MONTH)) AND YEAR(scanin) = YEAR(DATE_ADD((NOW()), INTERVAL -5 MONTH))";
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
			$salesMonthMinus5 = $row['COUNT(scanin)'];
			$unitsMonthMinus5 = $row['SUM(units)'];
			$quantityMonthMinus5 = $row['SUM(realQuantity)'];
			
		// Look up monthly sales -6
		$selectSales = "SELECT COUNT(scanin) FROM newvisits WHERE MONTH(scanin) = MONTH(DATE_ADD((NOW()), INTERVAL -6 MONTH)) AND YEAR(scanin) = YEAR(DATE_ADD((NOW()), INTERVAL -6 MONTH))";
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
			$salesMonthMinus6 = $row['COUNT(scanin)'];
			$unitsMonthMinus6 = $row['SUM(units)'];
			$quantityMonthMinus6 = $row['SUM(realQuantity)'];

		// Look up monthly sales -7
		$selectSales = "SELECT COUNT(scanin) FROM newvisits WHERE MONTH(scanin) = MONTH(DATE_ADD((NOW()), INTERVAL -7 MONTH)) AND YEAR(scanin) = YEAR(DATE_ADD((NOW()), INTERVAL -7 MONTH))";
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
			$salesMonthMinus7 = $row['COUNT(scanin)'];
			$unitsMonthMinus7 = $row['SUM(units)'];
			$quantityMonthMinus7 = $row['SUM(realQuantity)'];
			

// day to day

			$objPHPExcel->getActiveSheet()
		                ->setCellValue('A3', $lang['dispensary-today'].':');
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B3', number_format($salesToday,0));
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('A4', $lang['dispensary-yesterday'].':');
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B4', number_format($salesTodayMinus1,0));
		                $objPHPExcel->getActiveSheet()
		                ->setCellValue('A5',  date("l", strtotime("-2 days")).':');
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B5', number_format($salesTodayMinus2,0));
		                $objPHPExcel->getActiveSheet()
		                ->setCellValue('A6',  date("l", strtotime("-3 days")).':');
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B6', number_format($salesTodayMinus3,0));
		                $objPHPExcel->getActiveSheet()
		                ->setCellValue('A7',  date("l", strtotime("-4 days")).':');
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B7', number_format($salesTodayMinus4,0));
		                $objPHPExcel->getActiveSheet()
		                ->setCellValue('A8',  date("l", strtotime("-5 days")).':');
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B8', number_format($salesTodayMinus5,0));
		                $objPHPExcel->getActiveSheet()
		                ->setCellValue('A9',  date("l", strtotime("-6 days")).':');
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B9', number_format($salesTodayMinus6,0));
		                $objPHPExcel->getActiveSheet()
		                ->setCellValue('A10',  date("l", strtotime("-7 days")).':');
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B10', number_format($salesTodayMinus7,0));
// week to week		                
$objPHPExcel->getActiveSheet()
            ->setCellValue('A11', $lang['dispensary-weektoweek']);
$objPHPExcel->getActiveSheet()->getStyle('A11')->getFont()->setBold(true); 
			$objPHPExcel->getActiveSheet()
		                ->setCellValue('A12', $lang['dispensary-thisweek']);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B12', number_format($salesWeek,0)); 
		 $evolution = (($salesWeek - $salesWeekMinus1) /  $salesWeekMinus1) * 100;   
		   if ($salesWeek > $salesWeekMinus1) {           
		   $objPHPExcel->getActiveSheet()
		                ->setCellValue('C12', number_format($evolution,0) . "% +");
		            }else if ($salesWeek < $salesWeekMinus1) {
		  $objPHPExcel->getActiveSheet()
		                ->setCellValue('C12', number_format($evolution,0) . "% -");
	  }			
	  $objPHPExcel->getActiveSheet()
		                ->setCellValue('A13', $lang['dispensary-lastweek']);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B13', number_format($salesWeekMinus1,0)); 
		 $evolution = (($salesWeekMinus1 - $salesWeekMinus2) /  $salesWeekMinus2) * 100;
		   if ($salesWeekMinus1 > $salesWeekMinus2) {    
		   $objPHPExcel->getActiveSheet()
		                ->setCellValue('C13', number_format($evolution,0) . "% +");
		            }else if ($salesWeekMinus1 < $salesWeekMinus2) {
		  $objPHPExcel->getActiveSheet()
		                ->setCellValue('C13', number_format($evolution,0) . "% -");
	  }			
	  $objPHPExcel->getActiveSheet()
		                ->setCellValue('A14', $lang['dispensary-twoweeksago']);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B14', number_format($salesWeekMinus2,0)); 
		 $evolution = (($salesWeekMinus2 - $salesWeekMinus3) /  $salesWeekMinus3) * 100;   
		   if ($salesWeekMinus2 > $salesWeekMinus3) {           
		   $objPHPExcel->getActiveSheet()
		                ->setCellValue('C14', number_format($evolution,0) . "% +");
		            }else if ($salesWeekMinus2 < $salesWeekMinus3) {
		  $objPHPExcel->getActiveSheet()
		                ->setCellValue('C14', number_format($evolution,0) . "% -");
	  }			
	  $objPHPExcel->getActiveSheet()
		                ->setCellValue('A15', $lang['dispensary-threeweeksago']);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B15', number_format($salesWeekMinus3,0)); 
		 $evolution = (($salesWeekMinus3 - $salesWeekMinus4) /  $salesWeekMinus4) * 100;   
		   if ($salesWeekMinus3 > $salesWeekMinus4) {           
		   $objPHPExcel->getActiveSheet()
		                ->setCellValue('C15', number_format($evolution,0) . "% +");
		            }else if ($salesWeekMinus3 < $salesWeekMinus4) {
		  $objPHPExcel->getActiveSheet()
		                ->setCellValue('C15', number_format($evolution,0) . "% -");
	  }			
	  $objPHPExcel->getActiveSheet()
		                ->setCellValue('A16', $lang['dispensary-fourweeksago']);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B16', number_format($salesWeekMinus4,0)); 
		 $evolution = (($salesWeekMinus4 - $salesWeekMinus5) /  $salesWeekMinus5) * 100;   
		   if ($salesWeekMinus4 > $salesWeekMinus5) {           
		   $objPHPExcel->getActiveSheet()
		                ->setCellValue('C16', number_format($evolution,0) . "% +");
		            }else if ($salesWeekMinus4 < $salesWeekMinus5) {
		  $objPHPExcel->getActiveSheet()
		                ->setCellValue('C16', number_format($evolution,0) . "% -");
	  }			
	  $objPHPExcel->getActiveSheet()
		                ->setCellValue('A17', $lang['dispensary-fiveweeksago']);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B17', number_format($salesWeekMinus5,0)); 
		 $evolution = (($salesWeekMinus5 - $salesWeekMinus6) /  $salesWeekMinus6) * 100;   
		   if ($salesWeekMinus5 > $salesWeekMinus6) {           
		   $objPHPExcel->getActiveSheet()
		                ->setCellValue('C17', number_format($evolution,0) . "% +");
		            }else if ($salesWeekMinus5 < $salesWeekMinus6) {
		  $objPHPExcel->getActiveSheet()
		                ->setCellValue('C17', number_format($evolution,0) . "% -");
	  }			
	  $objPHPExcel->getActiveSheet()
		                ->setCellValue('A18', $lang['dispensary-sixweeksago']);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B18', number_format($salesWeekMinus6,0)); 
		 $evolution = (($salesWeek - $salesWeekMinus1) /  $salesWeekMinus1) * 100;   
		   if ($salesWeekMinus6 > $salesWeekMinus7) {           
		   $objPHPExcel->getActiveSheet()
		                ->setCellValue('C18', number_format($evolution,0) . "% +");
		            }else if ($salesWeekMinus6 < $salesWeekMinus7) {
		  $objPHPExcel->getActiveSheet()
		                ->setCellValue('C18', number_format($evolution,0) . "% -");
	  }			
	  $objPHPExcel->getActiveSheet()
		                ->setCellValue('A19', $lang['dispensary-sevenweeksago']);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B19', number_format($salesWeekMinus7,0)); 
	// month to month
$objPHPExcel->getActiveSheet()
            ->setCellValue('A20', $lang['dispensary-monthtomonth']);
$objPHPExcel->getActiveSheet()->getStyle('A20')->getFont()->setBold(true); 
			$objPHPExcel->getActiveSheet()
		                ->setCellValue('A21', $lang['dispensary-thismonth']);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B21', number_format($salesWeek,0)); 
		 $evolution = (($salesMonth - $salesMonthMinus1) /  $salesMonthMinus1) * 100;   
		   if ($salesMonth > $salesMonthMinus1) {           
			   $objPHPExcel->getActiveSheet()
			                ->setCellValue('C21', number_format($evolution,0) . "% +");
			            }else if ($salesWeek < $salesWeekMinus1) {
			  $objPHPExcel->getActiveSheet()
			                ->setCellValue('C21', number_format($evolution,0) . "% -");
	  	}			
	  $objPHPExcel->getActiveSheet()
		                ->setCellValue('A22', date("F", strtotime("first day of last month")));
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B22', number_format($salesMonthMinus1,0)); 
		 $evolution = (($salesMonthMinus1 - $salesMonthMinus2) /  $salesMonthMinus2) * 100;
		   if ($salesMonthMinus1 > $salesMonthMinus2) {    
		   $objPHPExcel->getActiveSheet()
		                ->setCellValue('C22', number_format($evolution,0) . "% +");
		            }else if ($salesMonthMinus1 < $salesMonthMinus2) {
		  $objPHPExcel->getActiveSheet()
		                ->setCellValue('C22', number_format($evolution,0) . "% -");
	  }			
	  $objPHPExcel->getActiveSheet()
		                ->setCellValue('A23', date("F", strtotime("-1 months", strtotime("first day of last month") )));
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B23', number_format($salesMonthMinus2,0)); 
		 $evolution = (($salesMonthMinus2 - $salesMonthMinus3) /  $salesMonthMinus3) * 100;   
		   if ($salesMonthMinus2 > $salesMonthMinus3) {           
		   $objPHPExcel->getActiveSheet()
		                ->setCellValue('C23', number_format($evolution,0) . "% +");
		            }else if ($salesMonthMinus2 < $salesMonthMinus3) {
		  $objPHPExcel->getActiveSheet()
		                ->setCellValue('C23', number_format($evolution,0) . "% -");
	  }			
	  $objPHPExcel->getActiveSheet()
		                ->setCellValue('A24', date("F", strtotime("-2 months", strtotime("first day of last month") )));
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B24', number_format($salesMonthMinus3,0)); 
		 $evolution = (($salesMonthMinus3 - $salesMonthMinus4) /  $salesMonthMinus4) * 100;   
		   if ($salesMonthMinus3 > $salesMonthMinus4) {           
		   $objPHPExcel->getActiveSheet()
		                ->setCellValue('C24', number_format($evolution,0) . "% +");
		            }else if ($salesMonthMinus3 < $salesMonthMinus4) {
		  $objPHPExcel->getActiveSheet()
		                ->setCellValue('C24', number_format($evolution,0) . "% -");
	  }			
	  $objPHPExcel->getActiveSheet()
		                ->setCellValue('A25', date("F", strtotime("-3 months", strtotime("first day of last month") )));
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B25', number_format($salesMonthMinus4,0)); 
		 $evolution = (($salesMonthMinus4 - $salesMonthMinus5) /  $salesMonthMinus5) * 100;   
		   if ($salesMonthMinus4 > $salesMonthMinus5) {           
		   $objPHPExcel->getActiveSheet()
		                ->setCellValue('C25', number_format($evolution,0) . "% +");
		            }else if ($salesMonthMinus4 < $salesMonthMinus5) {
		  $objPHPExcel->getActiveSheet()
		                ->setCellValue('C25', number_format($evolution,0) . "% -");
	  }			
	  $objPHPExcel->getActiveSheet()
		                ->setCellValue('A26', date("F", strtotime("-4 months", strtotime("first day of last month") )));
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B26', number_format($salesMonthMinus5,0)); 
		 $evolution = (($salesMonthMinus5 - $salesMonthMinus6) /  $salesMonthMinus6) * 100;   
		   if ($salesMonthMinus5 > $salesMonthMinus6) {           
		   $objPHPExcel->getActiveSheet()
		                ->setCellValue('C26', number_format($evolution,0) . "% +");
		            }else if ($salesMonthMinus5 < $salesMonthMinus6) {
		  $objPHPExcel->getActiveSheet()
		                ->setCellValue('C26', number_format($evolution,0) . "% -");
	  }			
	  $objPHPExcel->getActiveSheet()
		                ->setCellValue('A27', date("F", strtotime("-5 months", strtotime("first day of last month") )));
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B27', number_format($salesMonthMinus6,0)); 
		 $evolution = (($salesMonthMinus6 - $salesMonthMinus7) /  $salesMonthMinus7) * 100;   
		   if ($salesMonthMinus6 > $salesMonthMinus7) {           
		   $objPHPExcel->getActiveSheet()
		                ->setCellValue('C27', number_format($evolution,0) . "% +");
		            }else if ($salesMonthMinus6 < $salesMonthMinus7) {
		  $objPHPExcel->getActiveSheet()
		                ->setCellValue('C27', number_format($evolution,0) . "% -");
	  }			
	  $objPHPExcel->getActiveSheet()
		                ->setCellValue('A19', date("F", strtotime("-6 months", strtotime("first day of last month") )));
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B19', number_format($salesMonthMinus7,0)); 

			 ob_end_clean();
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			//header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename=visit-history.xlsx');
            header("Content-Type: application/download");                        
			//header('Cache-Control: max-age = 0');
			$objWriter->save('php://output');die;	  
