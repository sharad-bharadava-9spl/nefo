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
					            ->setCellValue('A1',$lang['global-time']);
					$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);  
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('B1',$lang['donated-to']);
					$objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);  		
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('C1',"#");
					$objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);  
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('D1',$lang['global-member']);
					$objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);  
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('E1',$lang['global-amount']);
					$objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true); 
					
					
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
			
			$timeLimit = "WHERE MONTH(time) = $month AND YEAR(time) = $year";
			
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
	
	
	
	// Query to look up past payments
	$selectExpenses = "SELECT id, time, userid, amount FROM card_purchase $timeLimit ORDER by time DESC $limitVar";
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
	$findStartDate = "SELECT time FROM card_purchase ORDER BY time ASC LIMIT 1";
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
		$startDate = date('01-m-Y', strtotime($row['time']));
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

	
	
	
	


?>
  
<?php

	$y = 0;
$datai = 2;
		$x = 3;
		while ($donation = $results->fetch()) {
	
	$dTime = date("d-m-Y", strtotime($donation['time']));
	$dTimeSQL = date("Y-m-d", strtotime('-1 day', strtotime($donation['time'])));
	
	if ($dTime != $currDate) {
		
		$nDate = date("Y-m-d", strtotime($currDate));
		if($datai > 2){
	   	    $datai  = $datai+1;
	   	    $x = $x+1;


	   }
		
		
		if ($y > 0) {
			// Query total for THIS date
			$donationTotal = "SELECT SUM(amount) FROM card_purchase WHERE DATE(time) = DATE('$nDate')";
		try
		{
			$result = $pdo3->prepare("$donationTotal");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$amountRow = $result->fetch();
				$amountToday = $amountRow['SUM(amount)'];
			
			
		}
		

		
$objPHPExcel->getActiveSheet()
		                ->setCellValue('C'.$datai, $dTime);
		   $objPHPExcel->getActiveSheet()->getStyle('C'.$datai)->getFont()->setBold(true);

	}
	
	
	$donationid = $donation['id'];
	$time = date("d-m-Y H:i", strtotime($donation['time'] . "+$offsetSec seconds"));
	$amount = $donation['amount'];
	$user_id = $donation['userid'];
		
	if ($donatedTo == '2') {
		$donatedTo = $lang['global-bank'];
	} else if ($donatedTo == '3') {
		$donatedTo = '';
	} else {
		$donatedTo = $lang['global-till'];
	}
	
		//$deleteOrNot = "<td class='noExl' style='text-align: center;'><a href='javascript:delete_donation($donationid,$amount,$user_id)'><img src='images/delete.png' height='15' title='{$lang['donation-deletedonation']}' /></a></td>";
		
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
			
	/*$expense_row =	sprintf("
  	  <tr>
  	   <td class='left'>%s</td>
  	   <td class='left'>%s</td>
  	   <td class='left'>%s</td>
  	   <td class='left'>%s %s</td>
  	   <td class='right' style='text-align: right;'>%0.02f $_SESSION['currencyoperator']</td>
  	   %s
 	   
	  </tr>",
	  $time, $donatedTo, $memberno, $first_name, $last_name, $amount, $deleteOrNot
	  );*/
			


	 
	  
	if ($dTime != $currDate) {
	  		$currDate =  date("d-m-Y", strtotime($donation['time']));
	}
  $objPHPExcel->getActiveSheet()
		                ->setCellValue('A'.$x, $time);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B'.$x, $donatedTo);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('C'.$x,  $memberno);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('D'.$x, $first_name." ".$last_name); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('E'.$x, $amount." ".$_SESSION['currencyoperator']); 
            
       $datai++;
       $x++;    				 
	  $y++;
  }

	ob_end_clean();
			    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			    //header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			    header('Content-type: application/vnd.ms-excel');
			    header('Content-Disposition: attachment;filename=card-purchases.xlsx');
			    header("Content-Type: application/download");
			    //header('Cache-Control: max-age = 0');
			    $objWriter->save('php://output');
    			die;

