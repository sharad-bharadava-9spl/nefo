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
     $exportname = 'Retiradas';
} else {
     $exportname = 'Dispensary';
}	


        
	/** Include PHPExcel */
	require_once 'vendor/PHPExcel/Classes/PHPExcel.php';	

    $current_uri = $_SERVER['REQUEST_URI']; 
   
    $day_num = $_GET['day_id'];
    $week_num = $_GET['week_id'];
    $month_num = $_GET['month_id'];
    $seperator = '.';
	//pageStart($lang['title-dispensary'], NULL, $newExcelSCript, "pdispensary", "product admin", $lang['global-dispensary'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

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
		$selectSales = "SELECT SUM(amount), SUM(units), SUM(realQuantity) from sales WHERE DATE(saletime) = $dateOperator";

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
			$sales = number_format($row['SUM(amount)'],0, '', $seperator);
			$units = number_format($row['SUM(units)'],0);
			$quantity = number_format($row['SUM(realQuantity)'],1);
			
		
	

		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('A'.$startIndex1, $timestamp.':');
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B'.$startIndex1, $quantity.'g.');
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('C'.$startIndex1, $units.'u.');
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('D'.$startIndex1, $sales.' €');

			 $startIndex1++;
		}


	$week_total_title = $day_num + 4;
	$objPHPExcel->getActiveSheet()
		                ->setCellValue('A'.$week_total_title, $lang['dispensary-weektoweek']);
	$objPHPExcel->getActiveSheet()->getStyle('A'.$week_total_title)->getFont()->setBold(true);
	$startIndex2 = $week_total_title + 1;  
	for ($a = 0; $a < $week_num; $a++) {
		
		if ($a == 0) {
			$dateOperator = "WEEK(saletime,1) = WEEK(NOW(),1) AND YEAR(saletime) = YEAR(NOW())";
			$timestamp = $lang['dispensary-thisweek'];
		} else if ($a == 1) {
			$dateOperator = "WEEK(saletime,1) = WEEK(DATE_ADD((NOW()), INTERVAL -$a WEEK),1) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -$a WEEK))";
			$timestamp = $lang['dispensary-lastweek'];
		} else {
			$dateOperator = "WEEK(saletime,1) = WEEK(DATE_ADD((NOW()), INTERVAL -$a WEEK),1) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -$a WEEK))";
			$timestamp = $lang['dispensary-weeksago-1'] . $a . $lang['dispensary-weeksago-2'];
		}
	
		// Look up todays sales
		$selectSales = "SELECT SUM(amount), SUM(units), SUM(realQuantity) from sales WHERE $dateOperator";

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
			$sales = number_format($row['SUM(amount)'],0, '', $seperator);
			$units = number_format($row['SUM(units)'],0);
			$quantity = number_format($row['SUM(realQuantity)'],1);
			

			
				$objPHPExcel->getActiveSheet()
			                ->setCellValue('A'.$startIndex2, $timestamp.':');
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('B'.$startIndex2, $quantity.'g.');
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('C'.$startIndex2, $units.'u.');
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('D'.$startIndex2, $sales.' €');
		     
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
		$selectSales = "SELECT SUM(amount), SUM(units), SUM(realQuantity) from sales WHERE $dateOperator";
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
			$sales = number_format($row['SUM(amount)'],0, '', $seperator);
			$units = number_format($row['SUM(units)'],0);
			$quantity = number_format($row['SUM(realQuantity)'],1);
			

		
				$objPHPExcel->getActiveSheet()
			                ->setCellValue('A'.$startIndex3, $timestamp.':');
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('B'.$startIndex3, $quantity.'g.');
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('C'.$startIndex3, $units.'u.');
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('D'.$startIndex3, $sales.' €');
		     
			 $startIndex3++;
	}
	


	// DAY BY DAY FIRST - FLOWERS ONLY
    $flower_title = $month_total_title + $month_num + 2;
    $flower_day_title = $flower_title + 1;

	$objPHPExcel->getActiveSheet()
		                ->setCellValue('B'.$flower_title, $lang['global-flowerscaps']);
	 $objPHPExcel->getActiveSheet()->getStyle('B'.$flower_title)->getFont()->setBold(true); 
	 	$objPHPExcel->getActiveSheet()
		                ->setCellValue('A'.$flower_day_title, $lang['dispensary-daytoday']);
		        $objPHPExcel->getActiveSheet()->getStyle('A'.$flower_day_title)->getFont()->setBold(true);         
	$startIndex4 = $flower_day_title + 1;
	for ($a = 0; $a < 8; $a++) {
		
		if ($a == 0) {
			$dateOperator = "DATE(NOW())";
			$timestamp = date("d-m-Y");
		} else {
			$dateOperator = "DATE_ADD(DATE(NOW()), INTERVAL -$a DAY)";
			$timestamp = date("d-m-Y", strtotime("-$a days"));
		}
	
		// Look up todays sales
		$selectSales = "SELECT SUM(d.amount), SUM(d.realQuantity) from sales s, salesdetails d WHERE d.saleid = s.saleid AND d.category = 1 AND DATE(saletime) = $dateOperator";

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
			$sales = number_format($row['SUM(d.amount)'],0, '', $seperator);
			$quantity = number_format($row['SUM(d.realQuantity)'],1);
			
		

		
				$objPHPExcel->getActiveSheet()
			                ->setCellValue('A'.$startIndex4, $timestamp.':');
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('B'.$startIndex4, $quantity.'g.');
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('C'.$startIndex4, $sales.' €');
		     
			 $startIndex4++;
	}
	
		




	// THEN WEEK TO WEEK - FLOWERS ONLY
    $flower_week_title = $flower_day_title + 10;

	$objPHPExcel->getActiveSheet()
            ->setCellValue('A'.$flower_week_title, $lang['dispensary-weektoweek']);
    $objPHPExcel->getActiveSheet()->getStyle('A'.$flower_week_title)->getFont()->setBold(true);  
    $startIndex5 =  $flower_week_title + 1;
	for ($a = 0; $a < 8; $a++) {
		
		if ($a == 0) {
			$dateOperator = "WEEK(saletime,1) = WEEK(NOW(),1) AND YEAR(saletime) = YEAR(NOW())";
			$timestamp = $lang['dispensary-thisweek'];
		} else if ($a == 1) {
			$dateOperator = "WEEK(saletime,1) = WEEK(DATE_ADD((NOW()), INTERVAL -$a WEEK),1) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -$a WEEK))";
			$timestamp = $lang['dispensary-lastweek'];
		} else {
			$dateOperator = "WEEK(saletime,1) = WEEK(DATE_ADD((NOW()), INTERVAL -$a WEEK),1) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -$a WEEK))";
			$timestamp = $lang['dispensary-weeksago-1'] . $a . $lang['dispensary-weeksago-2'];
		}
	
		// Look up todays sales
		$selectSales = "SELECT SUM(d.amount), SUM(d.realQuantity) from sales s, salesdetails d WHERE d.saleid = s.saleid AND d.category = 1 AND $dateOperator";

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
			$sales = number_format($row['SUM(d.amount)'],0, '', $seperator);
			$quantity = number_format($row['SUM(d.realQuantity)'],1);
			
		

			
				$objPHPExcel->getActiveSheet()
			                ->setCellValue('A'.$startIndex5, $timestamp.':');
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('B'.$startIndex5, $quantity.'g.');
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('C'.$startIndex5, $sales.' €');
		     
			 $startIndex5++;
	}
	



	// THEN MONTH TO MONTH - FLOWERS ONLY
	 $flower_month_title = $flower_week_title + 10;
	$objPHPExcel->getActiveSheet()
            ->setCellValue('A'.$flower_month_title, $lang['dispensary-monthtomonth']);
    $objPHPExcel->getActiveSheet()->getStyle('A'.$flower_month_title)->getFont()->setBold(true);  
	$startIndex6 = $flower_month_title + 1;
	for ($a = 0; $a < 8; $a++) {
		
		if ($a == 0) {
			$dateOperator = "MONTH(saletime) = MONTH(NOW()) AND YEAR(saletime) = YEAR(NOW())";
			$timestamp = $lang['dispensary-thismonth'];
		} else {
			$dateOperator = "MONTH(saletime) = MONTH(DATE_ADD((NOW()), INTERVAL -$a MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -$a MONTH))";
			$timestamp = date("m-Y", strtotime("-$a months", strtotime("first day of this month") ));
		}
	
		// Look up todays sales
		$selectSales = "SELECT SUM(d.amount), SUM(d.realQuantity) from sales s, salesdetails d WHERE d.saleid = s.saleid AND d.category = 1 AND $dateOperator";

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
			$sales = number_format($row['SUM(d.amount)'],0, '', $seperator);
			$quantity = number_format($row['SUM(d.realQuantity)'],1);
			
		


			
				$objPHPExcel->getActiveSheet()
			                ->setCellValue('A'.$startIndex6, $timestamp.':');
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('B'.$startIndex6, $quantity.'g.');
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('C'.$startIndex6, $sales.' €');
		     
			 $startIndex6++;
		
	}
	




	// DAY BY DAY FIRST - EXTRACTS ONLY
	 $extract_title = $flower_month_title + 10;
	 $extract_day_title = $extract_title + 1;  

	$objPHPExcel->getActiveSheet()
		                ->setCellValue('B'.$extract_title, $lang['global-extractscaps']);
	 $objPHPExcel->getActiveSheet()->getStyle('B'.$extract_title)->getFont()->setBold(true); 
	 $objPHPExcel->getActiveSheet()
		                ->setCellValue('A'.$extract_day_title, $lang['dispensary-daytoday']);
	$objPHPExcel->getActiveSheet()->getStyle('A'.$extract_day_title)->getFont()->setBold(true); 

	 $startIndex7 = $extract_day_title + 1;
	for ($a = 0; $a < 8; $a++) {
		
		if ($a == 0) {
			$dateOperator = "DATE(NOW())";
			$timestamp = date("d-m-Y");
		} else {
			$dateOperator = "DATE_ADD(DATE(NOW()), INTERVAL -$a DAY)";
			$timestamp = date("d-m-Y", strtotime("-$a days"));
		}
	
		// Look up todays sales
		$selectSales = "SELECT SUM(d.amount), SUM(d.realQuantity) from sales s, salesdetails d WHERE d.saleid = s.saleid AND d.category = 2 AND DATE(saletime) = $dateOperator";

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
			$sales = number_format($row['SUM(d.amount)'],0, '', $seperator);
			$quantity = number_format($row['SUM(d.realQuantity)'],1,'', $seperator);
			
		

			
				$objPHPExcel->getActiveSheet()
			                ->setCellValue('A'.$startIndex7, $timestamp.':');
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('B'.$startIndex7, $quantity.'g.');
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('C'.$startIndex7, $sales.' €');
		     
			 $startIndex7++;
	}
	




	// THEN WEEK TO WEEK - EXTRACTS ONLY
 	$extract_week_title = $extract_day_title + 10;
	$objPHPExcel->getActiveSheet()
            ->setCellValue('A'.$extract_week_title, $lang['dispensary-weektoweek']);
    $objPHPExcel->getActiveSheet()->getStyle('A'.$extract_week_title)->getFont()->setBold(true); 
	$startIndex8 = $extract_week_title + 1;

	for ($a = 0; $a < 8; $a++) {
		
		if ($a == 0) {
			$dateOperator = "WEEK(saletime,1) = WEEK(NOW(),1) AND YEAR(saletime) = YEAR(NOW())";
			$timestamp = $lang['dispensary-thisweek'];
		} else if ($a == 1) {
			$dateOperator = "WEEK(saletime,1) = WEEK(DATE_ADD((NOW()), INTERVAL -$a WEEK),1) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -$a WEEK))";
			$timestamp = $lang['dispensary-lastweek'];
		} else {
			$dateOperator = "WEEK(saletime,1) = WEEK(DATE_ADD((NOW()), INTERVAL -$a WEEK),1) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -$a WEEK))";
			$timestamp = $lang['dispensary-weeksago-1'] . $a . $lang['dispensary-weeksago-2'];
		}
	
		// Look up todays sales
		$selectSales = "SELECT SUM(d.amount), SUM(d.realQuantity) from sales s, salesdetails d WHERE d.saleid = s.saleid AND d.category = 2 AND $dateOperator";

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
			$sales = number_format($row['SUM(d.amount)'],0,'', $seperator);
			$quantity = number_format($row['SUM(d.realQuantity)'],1);
			
					
			
				$objPHPExcel->getActiveSheet()
			                ->setCellValue('A'.$startIndex8, $timestamp.':');
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('B'.$startIndex8, $quantity.'g.');
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('C'.$startIndex8, $sales.' €');
		     
			 $startIndex8++;
		
	}
	





	// THEN MONTH TO MONTH - EXTRACTS ONLY
	$extract_month_title = $extract_week_title + 10;
	$objPHPExcel->getActiveSheet()
            ->setCellValue('A'.$extract_month_title, $lang['dispensary-monthtomonth']);
    $objPHPExcel->getActiveSheet()->getStyle('A'.$extract_month_title)->getFont()->setBold(true);   
	$startIndex9 = $extract_month_title + 1;
	for ($a = 0; $a < 8; $a++) {
		
		if ($a == 0) {
			$dateOperator = "MONTH(saletime) = MONTH(NOW()) AND YEAR(saletime) = YEAR(NOW())";
			$timestamp = $lang['dispensary-thismonth'];
		} else {
			$dateOperator = "MONTH(saletime) = MONTH(DATE_ADD((NOW()), INTERVAL -$a MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -$a MONTH))";
			$timestamp = date("m-Y", strtotime("-$a months", strtotime("first day of this month") ));
		}
	
		// Look up todays sales
		$selectSales = "SELECT SUM(d.amount), SUM(d.realQuantity) from sales s, salesdetails d WHERE d.saleid = s.saleid AND d.category = 2 AND $dateOperator";

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
			$sales = number_format($row['SUM(d.amount)'],0, '', $seperator);
			$quantity = number_format($row['SUM(d.realQuantity)'],1);
			
		


				$objPHPExcel->getActiveSheet()
			                ->setCellValue('A'.$startIndex9, $timestamp.':');
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('B'.$startIndex9, $quantity.'g.');
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('C'.$startIndex9, $sales.' €');
		     
			 $startIndex9++;


		
		}
	
	





		// Query to look up categories, then products in each category
		$selectCats = "SELECT id, name, description, type from categories WHERE id > 2 ORDER by id ASC";
		try
		{
			$resultCats = $pdo3->prepare("$selectCats");
			$resultCats->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		$i = 1;
	$new_index = 0; 
	$catstartIndex = $extract_month_title + 10;

while ($category = $resultCats->fetch()) {
		
			$new_index = $new_index + 28;
			
			$categoryname = $category['name'];
			$categoryid = $category['id'];
			$type = $category['type'];
			$day_title = $catstartIndex + 1;
	        $week_title = $catstartIndex + 10;
	        $month_title =  $catstartIndex + 19;
			$objPHPExcel->getActiveSheet()
			            ->setCellValue('B'.$catstartIndex, $categoryname);
			$objPHPExcel->getActiveSheet()->getStyle('B'.$catstartIndex)->getFont()->setBold(true);              
			$objPHPExcel->getActiveSheet()
			            ->setCellValue('A'.$day_title, $lang['dispensary-daytoday']);
			$objPHPExcel->getActiveSheet()->getStyle('A'.$day_title)->getFont()->setBold(true);  			
			$objPHPExcel->getActiveSheet()
			            ->setCellValue('A'.$week_title, $lang['dispensary-weektoweek']);
			$objPHPExcel->getActiveSheet()->getStyle('A'.$week_title)->getFont()->setBold(true);  			
			$objPHPExcel->getActiveSheet()
			            ->setCellValue('A'.$month_title, $lang['dispensary-monthtomonth']);
			$objPHPExcel->getActiveSheet()->getStyle('A'.$month_title)->getFont()->setBold(true);   
			
	if ($type == 1) {

	// DAY BY DAY FIRST - OTHER CATEGORIES
	$startIndex10 = $catstartIndex + 2;
	for ($a = 0; $a < 8; $a++) {
		
		if ($a == 0) {
			$dateOperator = "DATE(NOW())";
			$timestamp = date("d-m-Y");
		} else {
			$dateOperator = "DATE_ADD(DATE(NOW()), INTERVAL -$a DAY)";
			$timestamp = date("d-m-Y", strtotime("-$a days"));
		}
	
		// Look up todays sales
		$selectSales = "SELECT SUM(d.amount), SUM(d.realQuantity) from sales s, salesdetails d WHERE d.saleid = s.saleid AND d.category = $categoryid AND DATE(saletime) = $dateOperator";

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
			$sales = number_format($row['SUM(d.amount)'],0,'', $seperator);
			$quantity = number_format($row['SUM(d.realQuantity)'],1);
			
				$objPHPExcel->getActiveSheet()
			                ->setCellValue('A'.$startIndex10, $timestamp.':');
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('B'.$startIndex10, $quantity.'g.');
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('C'.$startIndex10, $sales.' €');
		     
			 $startIndex10++;
     
	}





	// THEN WEEK TO WEEK - OTHER CATEGORIES

	$startIndex11 = $catstartIndex + 11;
	for ($a = 0; $a < 8; $a++) {
		
		if ($a == 0) {
			$dateOperator = "WEEK(saletime,1) = WEEK(NOW(),1) AND YEAR(saletime) = YEAR(NOW())";
			$timestamp = $lang['dispensary-thisweek'];
		} else if ($a == 1) {
			$dateOperator = "WEEK(saletime,1) = WEEK(DATE_ADD((NOW()), INTERVAL -$a WEEK),1) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -$a WEEK))";
			$timestamp = $lang['dispensary-lastweek'];
		} else {
			$dateOperator = "WEEK(saletime,1) = WEEK(DATE_ADD((NOW()), INTERVAL -$a WEEK),1) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -$a WEEK))";
			$timestamp = $lang['dispensary-weeksago-1'] . $a . $lang['dispensary-weeksago-2'];
		}
	
		// Look up todays sales
		$selectSales = "SELECT SUM(d.amount), SUM(d.realQuantity) from sales s, salesdetails d WHERE d.saleid = s.saleid AND d.category = $categoryid AND $dateOperator";

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
			$sales = number_format($row['SUM(d.amount)'],0,'', $seperator);
			$quantity = number_format($row['SUM(d.realQuantity)'],1);

				$objPHPExcel->getActiveSheet()
			                ->setCellValue('A'.$startIndex11, $timestamp.':');
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('B'.$startIndex11, $quantity.'g.');
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('C'.$startIndex11, $sales.' €');
		     
			 $startIndex11++;
		


	}
	


	// THEN MONTH TO MONTH - OTHER CATEGORIES

	$startIndex12 = $catstartIndex + 20;
	for ($a = 0; $a < 8; $a++) {
		
		if ($a == 0) {
			$dateOperator = "MONTH(saletime) = MONTH(NOW()) AND YEAR(saletime) = YEAR(NOW())";
			$timestamp = $lang['dispensary-thismonth'];
		} else {
			$dateOperator = "MONTH(saletime) = MONTH(DATE_ADD((NOW()), INTERVAL -$a MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -$a MONTH))";
			$timestamp = date("m-Y", strtotime("-$a months", strtotime("first day of this month") ));
		}
	
		// Look up todays sales
		$selectSales = "SELECT SUM(d.amount), SUM(d.realQuantity) from sales s, salesdetails d WHERE d.saleid = s.saleid AND d.category = $categoryid AND $dateOperator";

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
			$sales = number_format($row['SUM(d.amount)'],0, '', $seperator);
			$quantity = number_format($row['SUM(d.realQuantity)'],1);
			
				$objPHPExcel->getActiveSheet()
			                ->setCellValue('A'.$startIndex12, $timestamp.':');
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('B'.$startIndex12, $quantity.'g.');
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('C'.$startIndex12, $sales.' €');
		     
			 $startIndex12++;


	}
	
		


  

	} else {
		
	// DAY BY DAY FIRST - OTHER CATEGORIES


	$startIndex10 = $catstartIndex + 2;
	for ($a = 0; $a < 8; $a++) {
		
		if ($a == 0) {
			$dateOperator = "DATE(NOW())";
			$timestamp = date("d-m-Y");
		} else {
			$dateOperator = "DATE_ADD(DATE(NOW()), INTERVAL -$a DAY)";
			$timestamp = date("d-m-Y", strtotime("-$a days"));
		}
	
		// Look up todays sales
		$selectSales = "SELECT SUM(d.amount), SUM(d.realQuantity) from sales s, salesdetails d WHERE d.saleid = s.saleid AND d.category = $categoryid AND DATE(saletime) = $dateOperator";

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
			$sales = number_format($row['SUM(d.amount)'],0, '', $seperator);
			$quantity = number_format($row['SUM(d.realQuantity)'],1);
			
				$objPHPExcel->getActiveSheet()
			                ->setCellValue('A'.$startIndex10, $timestamp.':');
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('B'.$startIndex10, $quantity.'g.');
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('C'.$startIndex10, $sales.' €');
		     
			 $startIndex10++;


	}
	
		


	// THEN WEEK TO WEEK - OTHER CATEGORIES

	$startIndex11 = $catstartIndex + 11;
	for ($a = 0; $a < 8; $a++) {
		
		if ($a == 0) {
			$dateOperator = "WEEK(saletime,1) = WEEK(NOW(),1) AND YEAR(saletime) = YEAR(NOW())";
			$timestamp = $lang['dispensary-thisweek'];
		} else if ($a == 1) {
			$dateOperator = "WEEK(saletime,1) = WEEK(DATE_ADD((NOW()), INTERVAL -$a WEEK),1) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -$a WEEK))";
			$timestamp = $lang['dispensary-lastweek'];
		} else {
			$dateOperator = "WEEK(saletime,1) = WEEK(DATE_ADD((NOW()), INTERVAL -$a WEEK),1) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -$a WEEK))";
			$timestamp = $lang['dispensary-weeksago-1'] . $a . $lang['dispensary-weeksago-2'];
		}
	
		// Look up todays sales
		$selectSales = "SELECT SUM(d.amount), SUM(d.realQuantity) from sales s, salesdetails d WHERE d.saleid = s.saleid AND d.category = $categoryid AND $dateOperator";

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
			$sales = number_format($row['SUM(d.amount)'],0, '', $seperator);
			$quantity = number_format($row['SUM(d.realQuantity)'],1);
			
				$objPHPExcel->getActiveSheet()
			                ->setCellValue('A'.$startIndex11, $timestamp.':');
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('B'.$startIndex11, $quantity.'g.');
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('C'.$startIndex11, $sales.' €');
		     
			 $startIndex11++;


	}
	
		



	// THEN MONTH TO MONTH - OTHER CATEGORIES

	$startIndex12 = $catstartIndex + 20;
	for ($a = 0; $a < 8; $a++) {
		
		if ($a == 0) {
			$dateOperator = "MONTH(saletime) = MONTH(NOW()) AND YEAR(saletime) = YEAR(NOW())";
			$timestamp = $lang['dispensary-thismonth'];
		} else {
			$dateOperator = "MONTH(saletime) = MONTH(DATE_ADD((NOW()), INTERVAL -$a MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -$a MONTH))";
			$timestamp = date("m-Y", strtotime("-$a months", strtotime("first day of this month") ));
		}
	
		// Look up todays sales
		$selectSales = "SELECT SUM(d.amount), SUM(d.realQuantity) from sales s, salesdetails d WHERE d.saleid = s.saleid AND d.category = $categoryid AND $dateOperator";

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
			$sales = number_format($row['SUM(d.amount)'],0,'', $seperator);
			$quantity = number_format($row['SUM(d.realQuantity)'],1);

				$objPHPExcel->getActiveSheet()
			                ->setCellValue('A'.$startIndex12, $timestamp.':');
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('B'.$startIndex12, $quantity.'g.');
			    $objPHPExcel->getActiveSheet()
			                ->setCellValue('C'.$startIndex12, $sales.' €');
		     
			 $startIndex12++;
		


	}
	
		




	}
		$catstartIndex = $catstartIndex + 28;
 }

	  
		  	ob_end_clean();
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			//header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-type: application/vnd.ms-excel');
                        header('Content-Disposition: attachment;filename="' . $exportname . '.xlsx"');                        
			//header('Cache-Control: max-age = 0');
			$objWriter->save('php://output');
			header("location:dispensary-history.php");
		
?>

<?php displayFooter(); ?>
