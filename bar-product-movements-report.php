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
					            ->setCellValue('B1',$lang['global-category']);
					$objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);  		
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('C1',$lang['global-product']);
					$objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);  
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('D1',$lang['extracts-description']);
					$objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);  
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('E1',$lang['global-quantity']);
					$objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true); 
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('F1',$lang['global-comment']);
					$objPHPExcel->getActiveSheet()->getStyle('F1')->getFont()->setBold(true); 
					
				// FIND OUT HOW MANY TABLES TO EXPORT
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
		
		$optionList = "<option value=''>{$lang['filter']}</option>
			<option value='100'>{$lang['last']} 100</option>
			<option value='250'>{$lang['last']} 250</option>
			<option value='500'>{$lang['last']} 500</option>";		
	}
	
	// Query to look up movements
	$selectExpenses = "SELECT movementid, movementtime, type, purchaseid, quantity, movementTypeid, comment FROM b_productmovements $timeLimit ORDER by movementtime DESC $limitVar";
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
	$findStartDate = "SELECT movementtime FROM b_productmovements ORDER BY movementtime ASC LIMIT 1";
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
	


?>

<?php

	$y = 0;
			$datai = 2;
		$x = 3;
		while ($donation = $results->fetch()) {
	
	$dTime = date("d-m-Y", strtotime($donation['movementtime']));
	$dTimeSQL = date("Y-m-d", strtotime('-1 day', strtotime($donation['movementtime'])));
	
	if ($dTime != $currDate) {
		
		$nDate = date("Y-m-d", strtotime($currDate));
		
		if($datai > 2){
	   	    $datai  = $datai+1;
	   	    $x = $x+1;


	   }
				$objPHPExcel->getActiveSheet()
		                ->setCellValue('C'.$datai, $dTime);
		   $objPHPExcel->getActiveSheet()->getStyle('C'.$datai)->getFont()->setBold(true);
	  	
		
		

			
	  		$currDate =  date("d-m-Y", strtotime($donation['movementtime']));

	}
	
	
	$donationid = $donation['movementid'];
	$donationtime = $donation['movementtime'];
	$type = $donation['type'];
	$purchaseid = $donation['purchaseid'];
	$quantity = $donation['quantity'];
	$donationTypeid = $donation['movementTypeid'];
	$formattedDate = date("d M H:i", strtotime($donation['movementtime'] . "+$offsetSec seconds"));
	
	$selectProdID = "SELECT category, productid FROM b_purchases WHERE purchaseid = '$purchaseid'";
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

		
		$selectName = "SELECT name FROM b_products WHERE productid = '$productid'";
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
			
		$selectCatName = "SELECT name FROM b_categories WHERE id = '$category'";
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

	
	$donation_row =	sprintf("
  	  <tr%s>
  	   <td class='clickableRow' href='b_purchase.php?bar-purchase=%d'>%s</td>
  	   <td class='left clickableRow' href='bar-purchase.php?purchaseid=%d'>%s</td>
  	   <td class='left clickableRow' href='bar-purchase.php?purchaseid=%d'>%s</td>
  	   <td class='left clickableRow' href='bar-purchase.php?purchaseid=%d'>%s</td>
  	   <td class='clickableRow' style='text-align: right;' href='bar-purchase.php?purchaseid=%d'>%0.02f g</td>
  	   <td class='centered clickableRow' href='bar-purchase.php?purchaseid=%d'><span class='relativeitem'>$commentRead</span<</td>
	  </tr>",
	  $rowclass, $purchaseid, $formattedDate, $purchaseid, $categoryName, $purchaseid, $name, $purchaseid, $donationName, $purchaseid, $quantity, $purchaseid
	  );
	 
	  $objPHPExcel->getActiveSheet()
		                ->setCellValue('A'.$x, $formattedDate);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B'.$x, $categoryName);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('C'.$x,  $name);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('D'.$x, $donationName); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('E'.$x, $quantity." g"); 
            $objPHPExcel->getActiveSheet()
           				 ->setCellValue('F'.$x, $donation['comment']); 
	  $datai++;
	  $x++;
	  $y++;
	  
  }
?>

	 </tbody>
	 </table>

   
<?php displayFooter();

	ob_end_clean();
			    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			    //header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			    header('Content-type: application/vnd.ms-excel');
			    header('Content-Disposition: attachment;filename=bar-product-movements.xlsx');
			    header("Content-Type: application/download");
			    //header('Cache-Control: max-age = 0');
			    $objWriter->save('php://output');
    			die;
