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
					            ->setCellValue('A1', $lang['summary']);
					$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);  

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
			
			$timeLimit = "WHERE MONTH(movementtime) = $month AND YEAR(movementtime) = $year";
			
			$optionList = "<option value='filterVar'>$filterVar</option>
				<option value='100'>{$lang['last']} 100</option>
				<option value='250'>{$lang['last']} 250</option>
				<option value='500'>{$lang['last']} 500</option>";		
		}
			
	} else {
		
		$limitVar = "LIMIT 100";
		
		$optionList = "<option value='100'>{$lang['last']} 100</option>
			<option value='250'>{$lang['last']} 250</option>
			<option value='500'>{$lang['last']} 500</option>";		
	}
	
	// Check if 'entre fechas' was utilised
	if (isset($_GET['untilDate']) && $_GET['untilDate'] != '') {
		
		$limitVar = "";
		
		$fromDate = date("Y-m-d", strtotime($_GET['fromDate']));
		$untilDate = date("Y-m-d", strtotime($_GET['untilDate']));
		
		$timeLimit = "WHERE DATE(movementtime) BETWEEN DATE('$fromDate') AND DATE('$untilDate')";
		$limitVar = "";
			
	}
	
	// Query to look up movements
	$selectExpenses = "SELECT movementid, movementtime, type, purchaseid, quantity, movementTypeid, comment, user_id FROM productmovements $timeLimit ORDER by movementtime DESC $limitVar";
		try
		{
			$resultsL = $pdo3->prepare("$selectExpenses");
			$resultsL->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		
	// Create month-by-month split
	$findStartDate = "SELECT movementtime FROM productmovements ORDER BY movementtime ASC LIMIT 1";
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
		$startDate = date('01-m-Y', strtotime($row['movementtime']));
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
	
	$objPHPExcel->getActiveSheet()
					            ->setCellValue('A2',$lang['global-time']);
					$objPHPExcel->getActiveSheet()->getStyle('A2')->getFont()->setBold(true);  
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('B2',$lang['global-member']);
					$objPHPExcel->getActiveSheet()->getStyle('B2')->getFont()->setBold(true);  		
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('C2',$lang['global-category']);
					$objPHPExcel->getActiveSheet()->getStyle('C2')->getFont()->setBold(true);  
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('D2',$lang['global-product']);
					$objPHPExcel->getActiveSheet()->getStyle('D2')->getFont()->setBold(true);  
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('E2',$lang['global-description']);
					$objPHPExcel->getActiveSheet()->getStyle('E2')->getFont()->setBold(true);  
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('F2',$lang['global-quantity']);
					$objPHPExcel->getActiveSheet()->getStyle('F2')->getFont()->setBold(true);  
					


?>



<?php

	$query = "SELECT id, name, type FROM categories WHERE id > 2 ORDER BY type DESC";
	try
	{
		$results = $pdo3->prepare("$query");
		$results->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	
		$catlist[] = 1;
		$catlist[] = 2;

	while ($row = $results->fetch()) {
		
		$id = $row['id'];
		$name = $row['name'];
		$type = $row['type'];
		
		if ($type == 1) {
			$type = "(g)";
		} else {
			$type = "(u)";
		}
		
		$catlist[] = $id;
		
		
	
		$noOfCats++;
	}
	
		// $catlist[] = 0;
	

	

		
	if ($_SESSION['lang'] == 'en') {
		
		$query = "SELECT movementTypeid, type, movementNameen AS movementname FROM productmovementtypes WHERE movementTypeid < 16 OR movementTypeid = 21 OR movementTypeid = 22 ORDER BY type ASC";
		
	} else {
		
		$query = "SELECT movementTypeid, type, movementNamees AS movementname FROM productmovementtypes WHERE movementTypeid < 16 OR movementTypeid = 21 OR movementTypeid = 22 ORDER BY type ASC";
		
	}
	
		try
		{
			$results = $pdo3->prepare("$query");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

		while ($row = $results->fetch()) {
						
			$id = $row['movementTypeid'];
			$type = $row['type'];
			$movementname = $row['movementname'];
			
			if ($type == 1) {
				$rowclass = " class='green' ";
			} else if ($type == 2) {
				$rowclass = " class='red' ";
			}
			
			
			
			
			// Query to look up movements - one TD per category. First 1, then 2, then all categories.
			foreach($catlist as $item) {
			
				if ($timeLimit == '') {
					
					$query2 = "SELECT SUM(quantity) FROM productmovements WHERE movementTypeid = '$id' AND category = '$item' ORDER by movementtime DESC $limitVar";
					
				} else {
					
					$query2 = "SELECT SUM(quantity) FROM productmovements $timeLimit AND movementTypeid = '$id' AND category = '$item' ORDER by movementtime DESC $limitVar";
					
				}
				
				try
				{
					$results2 = $pdo3->prepare("$query2");
					$results2->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
				$row2 = $results2->fetch();
					$quantity = $row2['SUM(quantity)'];
					
				
				
			}
			
				
				
				
			
		}
			
$startIndex = 3;
	while ($donation = $resultsL->fetch()) {
	
		$donationid = $donation['movementid'];
		$donationtime = $donation['movementtime'];
		$type = $donation['type'];
		$purchaseid = $donation['purchaseid'];
		$quantity = $donation['quantity'];
		$donationTypeid = $donation['movementTypeid'];
		$user = $donation['user_id'];		
		$formattedDate = date("d M H:i", strtotime($donation['movementtime'] . "+$offsetSec seconds"));
		
		
		if ($user == '0' || $user == '999999') {
			
			$operator = '';
			
		} else {
			
			$operator = getOperator($user);
			
		}
		
		$selectProdID = "SELECT category, productid FROM purchases WHERE purchaseid = '$purchaseid'";
		try
		{
			$result = $pdo3->prepare("$selectProdID");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$fRow = $result->fetch();
		$productid = $fRow['productid'];
		$category = $fRow['category'];
		
	if ($category == 1) {
		
		$selectName = "SELECT name, breed2 FROM flower WHERE flowerid = '$productid'";
		try
		{
			$result = $pdo3->prepare("$selectName");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$nRow = $result->fetch();
			$name = $nRow['name'];
			$breed2 = $nRow['breed2'];
			$categoryName = $lang['global-flower'];
		
		if ($breed2 != '') {
			$name = $name . " x " . $breed2;
		} else {
			$name = $name;
		}

			
	} else if ($category == 2) {
		
		$selectName = "SELECT name FROM extract WHERE extractid = '$productid'";
		try
		{
			$result = $pdo3->prepare("$selectName");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$nRow = $result->fetch();
			$name = $nRow['name'];		
			$categoryName = $lang['global-extracts'];
		
	} else {
		
		$selectName = "SELECT name FROM products WHERE productid = '$productid'";
		try
		{
			$result = $pdo3->prepare("$selectName");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$nRow = $result->fetch();
			$name = $nRow['name'];
			
		$selectCatName = "SELECT name FROM categories WHERE id = '$category'";
		try
		{
			$result = $pdo3->prepare("$selectCatName");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$cRow = $result->fetch();
			$categoryName = $cRow['name'];
		
	}

	
	if ($donationTypeid == 17 || $donationTypeid == 18 || $donationTypeid == 19 || $donationTypeid == 20 ) {
		$rowclass = " class='grey' ";
	} else if ($type == 1) {
		$rowclass = " class='green' ";
	} else if ($type == 2) {
		$rowclass = " class='red' ";
	} else {
		$rowclass = "";
	}
	
	if ($donation['comment'] != '') {
		
		$commentRead = "
		                <img src='images/comments.png' id='comment$donationid' /><div id='helpBox$donationid' class='helpBox'>{$donation['comment']}</div>
		                <script>
		                  	$('#comment$donationid').on({
						 		'mouseover' : function() {
								 	$('#helpBox$donationid').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBox$donationid').css('display', 'none');
							  	}
						  	});
						</script>
		                ";
		
	} else {
		
		$commentRead = "";
		
	}


	
	// Look up movement name
      	if ($_SESSION['lang'] == 'es') {
			$selectMovementName = "SELECT movementNamees FROM productmovementtypes WHERE movementTypeid = '$donationTypeid'";
		try
		{
			$result = $pdo3->prepare("$selectMovementName");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$donationName = $row['movementNamees'];
		} else {
			$selectMovementName = "SELECT movementNameen FROM productmovementtypes WHERE movementTypeid = '$donationTypeid'";
		try
		{
			$result = $pdo3->prepare("$selectMovementName");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$donationName = $row['movementNameen'];
		}

	
	/*$donation_row =	sprintf("
  	  <tr%s>
  	   <td class='clickableRow' href='purchase.php?purchaseid=%d'>%s</td>
  	   <td class='left clickableRow' href='purchase.php?purchaseid=%d'>%s</td>
  	   <td class='left clickableRow' href='purchase.php?purchaseid=%d'>%s</td>
  	   <td class='left clickableRow' href='purchase.php?purchaseid=%d'>%s</td>
  	   <td class='left clickableRow' href='purchase.php?purchaseid=%d'>%s</td>
  	   <td class='clickableRow' style='text-align: right;' href='purchase.php?purchaseid=%d'>%0.02f g</td>
  	   <td class='centered clickableRow' href='purchase.php?purchaseid=%d'><span class='relativeitem'>$commentRead</span></td>
	  </tr>",
	  $rowclass, $purchaseid, $formattedDate, $purchaseid, $operator, $purchaseid, $categoryName, $purchaseid, $name, $purchaseid, $donationName, $purchaseid, $quantity, $purchaseid
	  );*/
	  $objPHPExcel->getActiveSheet()
		                ->setCellValue('A'.$startIndex, $formattedDate);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B'.$startIndex, $operator);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('C'.$startIndex,  $categoryName);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('D'.$startIndex, $name); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('E'.$startIndex, $donationName); 
            $objPHPExcel->getActiveSheet()
           				 ->setCellValue('F'.$startIndex, $quantity." g"); 
	  $startIndex++;
	  
	  $y++;
	  
  }


		ob_end_clean();
			    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			    //header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			    header('Content-type: application/vnd.ms-excel');
			    header('Content-Disposition: attachment;filename=product-movements.xlsx');
			    header("Content-Type: application/download");
			    //header('Cache-Control: max-age = 0');
			    $objWriter->save('php://output');
    			die;
