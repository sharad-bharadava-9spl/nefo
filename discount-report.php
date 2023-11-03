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
   
    $day = $_GET['day_id'];
    $totrows = $_GET['row_id'];
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
 	$firstday = $day + 1;
	
	 $selectUsers = "SELECT u.user_id, u.memberno, u.first_name, u.last_name, u.registeredSince, u.dni, u.gender, u.day, u.month, u.year, u.doorAccess, u.paidUntil, u.userGroup, ug.groupName, ug.groupDesc, u.form1, u.form2, u.credit, u.usageType, u.creditEligible, u.dniscan, u.dniext1, u.starCat, u.discount, u.usergroup2, u.discountBar FROM users u, usergroups ug WHERE u.userGroup = ug.userGroup AND memberno <> '0' AND u.userGroup < 7 ORDER by u.memberno ASC LIMIT $day, 1000"; 
	
	try
	{
		$results = $pdo3->prepare("$selectUsers");
		$results->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('A1','C');
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);  		
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('B1','#');
		$objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('C1',$lang['global-name']);
		$objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('D1',$lang['global-lastnames']);
		$objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('E1',$lang['global-type']);
		$objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('F1',$lang['global-dispensary']);
		$objPHPExcel->getActiveSheet()->getStyle('F1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('G1',$lang['global-category']);
		$objPHPExcel->getActiveSheet()->getStyle('G1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('H1',$lang['global-product']);
		$objPHPExcel->getActiveSheet()->getStyle('H1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('I1','Usergroup');
		$objPHPExcel->getActiveSheet()->getStyle('I1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('J1',$lang['bar']);
		$objPHPExcel->getActiveSheet()->getStyle('J1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('K1',$lang['global-category']);
		$objPHPExcel->getActiveSheet()->getStyle('K1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('L1',$lang['global-product']);
		$objPHPExcel->getActiveSheet()->getStyle('L1')->getFont()->setBold(true);  
	$startIndex = 2;
while ($user = $results->fetch()) {
			
	$checkCatDiscount = "SELECT SUM(discount) from catdiscounts WHERE user_id = {$user['user_id']}";
	try
	{
		$result = $pdo3->prepare("$checkCatDiscount");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$catDiscount = $row['SUM(discount)'];
		$catDiscountRaw = $row['SUM(discount)'];
		
	if ($catDiscount > 0 || $catDiscount < 0) {
		$catDiscount = $lang['global-yes'];
	} else {
		$catDiscount = '';
	}

	$checkIndDiscount = "SELECT SUM(discount) from inddiscounts WHERE user_id = {$user['user_id']}";
	try
	{
		$result = $pdo3->prepare("$checkIndDiscount");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$indDiscount = $row['SUM(discount)'];
		$indDiscountRaw = $row['SUM(discount)'];
		
	if ($indDiscount > 0 || $indDiscount < 0) {
		$indDiscount = $lang['global-yes'];
	} else {
		$indDiscount = '';
	}

	$checkCatDiscountBar = "SELECT SUM(discount) from b_catdiscounts WHERE user_id = {$user['user_id']}";
	try
	{
		$result = $pdo3->prepare("$checkCatDiscountBar");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
		$catDiscountBar = $row['SUM(discount)'];
		$catDiscountBarRaw = $row['SUM(discount)'];
		
	if ($catDiscountBar > 0 || $catDiscountBar < 0) {
		$catDiscountBar = $lang['global-yes'];
	} else {
		$catDiscountBar = '';
	}

	$checkIndDiscountBar = "SELECT SUM(discount) from b_inddiscounts WHERE user_id = {$user['user_id']}";
	try
	{
		$result = $pdo3->prepare("$checkIndDiscountBar");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
		$indDiscountBar = $row['SUM(discount)'];
		$indDiscountBarRaw = $row['SUM(discount)'];
		
	if ($indDiscountBar > 0 || $indDiscountBar < 0) {
		$indDiscountBar = $lang['global-yes'];
	} else {
		$indDiscountBar = '';
	}
	
	if ($user['usageType'] == '1') {
		$usageType = "<img src='images/medical.png' width='16' /><span style='display:none'>1</span>";
	} else {
		$usageType = '';
	}
	// usergroup discount
	 $checkUsergroupDiscount = "SELECT discount_percentage from usergroup_discounts WHERE usergroup_id = {$user['usergroup2']} order by id DESC";

	
	try
	{
		$result = $pdo3->prepare("$checkUsergroupDiscount");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	 $row = $result->fetch();
		$usergroupDiscount = $row['discount_percentage'];
		
	if ($usergroupDiscount > 0) {
		$usergroupDiscount = $usergroupDiscount;
	} else {
		$usergroupDiscount = '';
	}
	
	if ($starCat == 1) {
   		$userStar = "<img src='images/star-yellow.png' width='16' /><span style='display:none'>1</span>";
	} else if ($starCat == 2) {
   		$userStar = "<img src='images/star-black.png' width='16' /><span style='display:none'>2</span>";
	} else if ($starCat == 3) {
   		$userStar = "<img src='images/star-green.png' width='16' /><span style='display:none'>3</span>";
	} else if ($starCat == 4) {
   		$userStar = "<img src='images/star-red.png' width='16' /><span style='display:none'>4</span>";
	} else {
   		$userStar = "<span style='display:none'>0</span>";
	}
	
	$discountSum = $catDiscountRaw + $indDiscountRaw + $catDiscountBarRaw + $indDiscountBarRaw + $user['discount'] + $user['discountBar'] + $usergroupDiscount;

	if ($discountSum > 0) {
	
	  		$objPHPExcel->getActiveSheet()
		                ->setCellValue('A'.$startIndex, $starCat);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B'.$startIndex, $user['memberno']);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('C'.$startIndex, $user['first_name']);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('D'.$startIndex,  $user['last_name']); 
            $objPHPExcel->getActiveSheet()
           				 ->setCellValue('E'.$startIndex, $usageType);
            $objPHPExcel->getActiveSheet()
           				 ->setCellValue('F'.$startIndex, $user['discount']); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('G'.$startIndex, $catDiscount); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('H'.$startIndex, $indDiscount." %"); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('I'.$startIndex, $usergroupDiscount." %"); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('J'.$startIndex, $user['discountBar']." %"); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('K'.$startIndex, $catDiscountBar." %"); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('L'.$startIndex, $indDiscountBar." %");		    
		    $startIndex++; 
	  
  	}
  		  // KONSTANT CODE UPDATE BEGIN
  	//   
	  
  }
  
  	ob_end_clean();
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	//header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $lang['discounts'] . '.xlsx"');
    header("Content-Type: application/download");                        
	//header('Cache-Control: max-age = 0');
	$objWriter->save('php://output');die;


