<?php
	ob_start();
	//ini_set("memory_limit","2048M");
	//ini_set('max_execution_time', '0'); // for infinite time of execution 
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	session_start();

	$accessLevel = '3';
	$currencyoperator = $_SESSION['currencyoperator'];
	$currencyoperator = html_entity_decode($currencyoperator,ENT_QUOTES,'UTF-8');
	// Authenticate & authorize
	authorizeUser($accessLevel);
	require_once  'vendor/PHPExcel/Classes/PHPExcel.php';

	$objPHPExcel = new PHPExcel();
	$objPHPExcel->getProperties()->setCreator("Lokesh Nayak")
		                             ->setLastModifiedBy("Lokesh Nayak")
		                             ->setTitle("Test Document")
		                             ->setSubject("Test Document")
		                             ->setDescription("Test document for PHPExcel")
		                             ->setKeywords("office")
		                             ->setCategory("Test result file");
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
			$optionList = "<option value='100'>{$lang['last']} 100</option>
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
			
			$timeLimit = "AND MONTH(saletime) = $month AND YEAR(saletime) = $year";
			
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
	if (!empty($_GET['untilDate']) && $_GET['untilDate'] != '') {
		
		$limitVar = "";
		
		$fromDate = date("Y-m-d", strtotime($_GET['fromDate']));
		$untilDate = date("Y-m-d", strtotime($_GET['untilDate']));
		
		$timeLimit = "AND DATE(saletime) BETWEEN DATE('$fromDate') AND DATE('$untilDate')";
		$limitVar = "";
			
	}else{
		$timeLimit = '';
	}
	// usergroup filter
	$selectedUsergroup = "1,2,3";
		if(isset($_GET['submitted']) && $_GET['submitted'] != ''){
			$firstSelect = 'false';
			//  code to filter the sales from usergroups
			if(isset($_GET['cashBox']) && $_GET['cashBox'] != ''){
				$selectedUsergroup = $_GET['cashBox'];
				//$selectedUsergroup = implode(",", $selectedUserArr);
				$getUsers = "SELECT user_id FROM users WHERE userGroup IN ($selectedUsergroup)";
				$result = $pdo3->prepare("$getUsers");
				$result->execute();
				while($user_ids = $result->fetch()){
					$userArr[] = $user_ids['user_id'];
				}
				if(!empty($userArr)){
					array_push($userArr, 999999);
				}
				$selectedUsers = implode(',',$userArr);
				if(empty($selectedUsers) || $selectedUsers ==''){
					$selectedUsers = -1;
				}
				$user_limit = "AND operatorid IN ($selectedUsers)";
			}else{
				$user_limit = 'AND operatorid IN (0)';
			}
		}else{
			 $firstSelect = 'true';
			  $getUsers = "SELECT user_id FROM users WHERE userGroup IN ($selectedUsergroup)";
				$result = $pdo3->prepare("$getUsers");
				$result->execute();
				while($user_ids = $result->fetch()){
					$userArr[] = $user_ids['user_id'];
				}
				array_push($userArr, 999999);
			    $selectedUsers = implode(',',$userArr);
			    if(empty($selectedUsers) || $selectedUsers == ''){
					$selectedUsers = -1;		    	
			    }else{
			    	$selectedUsers .= ',0';
			    }

			   $user_limit = "AND operatorid IN ($selectedUsers)";
		}
	
if ($_SESSION['realWeight'] == 0) {
	
	/*if ($_SESSION['userGroup'] == 1 || ($_SESSION['userGroup'] == 2 && $_SESSION['domain'] == 'amagi')) {
	
		// Query to look up sales
		$selectSales = "SELECT saleid, saletime, userid, amount, amountpaid, quantity, units, adminComment, creditBefore, creditAfter, discount, direct FROM sales WHERE 1 $timeLimit $user_limit ORDER by saletime DESC $limitVar";
	
	} else {
		
		// Query to look up sales
		$selectSales = "SELECT saleid, saletime, userid, amount, amountpaid, quantity, units, adminComment, creditBefore, creditAfter, discount, direct FROM sales WHERE DATE(saletime) = DATE(NOW()) $user_limit ORDER by saletime DESC $limitVar";
		
	}

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
	*/	
	// if(!empty($_GET['filter']) || !empty($_GET['untilDate'])){
     	//echo $selectSales; die;

     	       $rows=array();
				$page_size=1000;
				$count = $_GET['totalCount'];
				$fileExcel = "dispenses-{$_SESSION['domain']}.xlsx";
				
		    if($_GET['count'] == 0){
				// excel count query
				$excelCountQuery  =   "SELECT saleid, saletime, userid, amount, amountpaid, quantity, units, adminComment, creditBefore, creditAfter, discount, direct FROM sales WHERE 1 $timeLimit ORDER by saletime DESC";
			   //$query = "select id from shipment Limit ".$page_size." OFFSET ".$offset_var;
			   $countResults= $pdo3->prepare("$excelCountQuery");
			   $countResults->execute();

				$total_records=$countResults->rowCount();

                $count=ceil($total_records/$page_size);

				$filepath = "excel/".$fileExcel;

				if(file_exists($filepath)){
					unlink($filepath);
				}
				//$count =4;
			}
			/*	for($i=0; $i<=$count; $i++) {
				   $offset_var = $i * $page_size;
				    $query  =   "SELECT saleid, saletime, userid, amount, amountpaid, quantity, units, adminComment, creditBefore, creditAfter, discount, direct FROM sales WHERE 1 $timeLimit $user_limit ORDER by saletime DESC Limit ".$page_size." OFFSET ".$offset_var;
				   //$query = "select id from shipment Limit ".$page_size." OFFSET ".$offset_var;
				   $results= $pdo3->prepare("$query");
				   $results->execute();
				     while ($row = $results->fetch()) {
				       $rows[]= $row;
				     }
				}*/
				
				//echo "<pre>";print_r($rows);echo "</pre>";

				//die;
    // }
		
	// Create month-by-month split
	$findStartDate = "SELECT saletime FROM sales ORDER BY saletime ASC LIMIT 1";
	
	try
	{
		$startResult = $pdo3->prepare("$findStartDate");
		$startResult->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $startResult->fetch();
	$startDate = date('01-m-Y', strtotime($row['saletime']));
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


	if($_GET['count'] == 0){
        $objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('A1',$lang['global-time']);
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);  		
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('B1',$lang['global-member']);
		$objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('C1',$lang['global-category']);
		$objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('D1',$lang['global-product']);
		$objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('E1', $lang['global-quantity']);
		$objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('F1',"Full price". $currencyoperator);
		$objPHPExcel->getActiveSheet()->getStyle('F1')->getFont()->setBold(true);

		$objPHPExcel->getActiveSheet()
		            ->setCellValue('G1',"Discount price". $currencyoperator);
		$objPHPExcel->getActiveSheet()->getStyle('G1')->getFont()->setBold(true);

		$objPHPExcel->getActiveSheet()
		            ->setCellValue('H1',"Discount applied");
		$objPHPExcel->getActiveSheet()->getStyle('H1')->getFont()->setBold(true);

		$objPHPExcel->getActiveSheet()
		            ->setCellValue('I1','Total g');
		$objPHPExcel->getActiveSheet()->getStyle('I1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('J1','Total u');
		$objPHPExcel->getActiveSheet()->getStyle('J1')->getFont()->setBold(true);

		$objPHPExcel->getActiveSheet()
		            ->setCellValue('K1','Total full price '. $currencyoperator);
		$objPHPExcel->getActiveSheet()->getStyle('K1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
					->setCellValue('L1',"Checkout discount %");
		$objPHPExcel->getActiveSheet()->getStyle('L1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('M1',"Total discounted price".$currencyoperator);
		$objPHPExcel->getActiveSheet()->getStyle('M1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('N1',$lang['dispense-oldcredit']);
		$objPHPExcel->getActiveSheet()->getStyle('N1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('O1',$lang['dispense-newcredit']);
		$objPHPExcel->getActiveSheet()->getStyle('O1')->getFont()->setBold(true);
		 if ($_SESSION['creditOrDirect'] != 1) { 
		 		$objPHPExcel->getActiveSheet()
		            ->setCellValue('P1',$lang['paid-by']);
				$objPHPExcel->getActiveSheet()->getStyle('P1')->getFont()->setBold(true);
				$objPHPExcel->getActiveSheet()
		            ->setCellValue('Q1',$lang['global-comment']);
				$objPHPExcel->getActiveSheet()->getStyle('Q1')->getFont()->setBold(true);
		 }else{
				$objPHPExcel->getActiveSheet()
				            ->setCellValue('P1',$lang['global-comment']);
				$objPHPExcel->getActiveSheet()->getStyle('P1')->getFont()->setBold(true);
		}
	}



	  

$discount_type_name=array('1'=>'Individual','2'=>'General Medical','3'=>'Happy Hour','4'=>'Usergroup','5'=>'5','6'=>'Volume Discounts','7'=>'Gift');		   
$countItem = $_GET['count'];
if($_GET['count'] <= $_GET['totalCount']){
   	$offset_var = $countItem * $page_size;
   	$query  =   "SELECT saleid, saletime, userid, amount, amountpaid, quantity, units, adminComment, creditBefore, creditAfter, discount, direct, discounteur, operatorid FROM sales WHERE 1 $timeLimit  ORDER by saletime DESC Limit ".$page_size." OFFSET ".$offset_var;
   	//$query = "select id from shipment Limit ".$page_size." OFFSET ".$offset_var;
   	$results= $pdo3->prepare("$query");
   	$results->execute();

    $startIndex = 2; 
	$ex = 0; 
   	if(file_exists("excel/$fileExcel")){
   		$objPHPExcel = PHPExcel_IOFactory::load("excel/$fileExcel");
   		$startIndex = $objPHPExcel->getActiveSheet()->getHighestRow()+1;
   	}
	while ($sale = $results->fetch()) {
		$formattedDate = date("d-m-Y H:i:s", strtotime($sale['saletime']."+$offsetSec seconds"));
		$saleid = $sale['saleid'];
		$userid = $sale['userid'];
		$quantity = $sale['quantity'];
		$units = $sale['units'];
		$credit = $sale['creditBefore'];
		$newcredit = $sale['creditAfter'];
		$discount = number_format($sale['discount'],0);
		$direct = $sale['direct'];
		$discounteur = $sale['discounteur'];
		$operatorID = $sale['operatorid'];
		
		if ($operatorID == 0) {
			$operator = '';
		} else {
			$operator = getOperator($operatorID);
		}
		
		if ($direct == 3) {
			$paymentMethod = $lang['global-credit'];
		} else if ($direct == 2) {
			$paymentMethod = "{$lang['card']}";
		} else if ($direct == 1) {
			$paymentMethod = "{$lang['cash']}";
		} else {
			$paymentMethod = '';
		}
		
		$amount = $sale['amount'];
		$amountpaid = $sale['amountpaid'];
		
		$userLookup = "SELECT first_name, memberno FROM users WHERE user_id = {$userid}";
		try
		{
			$userResult = $pdo3->prepare("$userLookup");
			$userResult->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $userResult->fetch();
			$first_name = $row['first_name'];
			$memberno = $row['memberno'];
		
		if ($sale['adminComment'] != '') {
			$commentRead = "
			                <img src='images/comments.png' id='comment$saleid' /><div id='helpBox$saleid' class='helpBox'>{$sale['adminComment']}</div>
			                <script>
			                  	$('#comment$saleid').on({
							 		'mouseover' : function() {
									 	$('#helpBox$saleid').css('display', 'inline-block');
							  		},
							  		'mouseout' : function() {
									 	$('#helpBox$saleid').css('display', 'none');
								  	}
							  	});
							</script>
			                ";
		} else {
			$commentRead = "";
		}

			
		$selectoneSale = "SELECT d.category, d.productid, d.quantity, d.amount, d.discountType, d.purchaseid FROM salesdetails d, sales s WHERE d.saleid = {$saleid} and s.saleid = d.saleid";
		try
		{
			$onesaleResult = $pdo3->prepare("$selectoneSale");
			$onesaleResult->execute();
			$totResult = $onesaleResult->fetchAll();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		// Make unpaid rows red:
		if ($amountpaid < $amount) {
			echo "<tr class='negative'>";
		} else {
			echo "<tr>";
		}
   
		/*echo "
	   <td class='clickableRow' href='dispense.php?saleid={$saleid}'>";*/
	   
	 //   	$o = 0;
  // 	   	foreach ($totResult as $onesale) {	
  // 	   		if ($o == 0) {
		// 		//echo "$formattedDate<br/>";
		// 	} else {
		// 		//echo "<span class='white'>$formattedDate</span><br/>";
		// 	}
			
		// 	$o++;
		// }

			/*echo "</td>
  	   <td class='clickableRow' href='dispense.php?saleid={$saleid}'>";*/
  	   
	 //   	$p = 0;
  // 	   	foreach ($totResult as $onesale) {	
  // 	   		if ($p == 0) {
		// 		//echo "#$memberno - $first_name<br/>";
		// 	} else {
		// 		//echo "<span class='white'>#$memberno - $first_name</span><br/>";
		// 	}
			
		// 	$p++;
		// }

/*echo "
  	   </td>
  	   <td class='clickableRow' href='dispense.php?saleid={$saleid}'>";*/
  	   	$ca = 0;
	  	foreach ($totResult as $onesale) {	
			if ($onesale['category'] == 1) {
				$category = $lang['global-flower'];
			} else if ($onesale['category'] == 2) {
				$category = $lang['global-extract'];
			} else {
				// Query to look for category
				$categoryDetails = "SELECT name FROM categories WHERE id = {$onesale['category']}";
				try {
					$resultCat = $pdo3->prepare("$categoryDetails");
					$resultCat->execute();
				} catch (PDOException $e) {
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
				$row = $resultCat->fetch();
				$category = $row['name'];
			}
			$catarr[$ex][$ca] = $category;
			$catIndex = $startIndex + $ca;
			$objPHPExcel->getActiveSheet()
		                ->setCellValue('A'.$catIndex, $formattedDate);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B'.$catIndex, "#".$memberno." - ".$first_name);
			$objPHPExcel->getActiveSheet()
		                ->setCellValue('C'.$catIndex, $category);
			$ca++;
		}
		$catex = implode("\n", $catarr[$ex]);
			
		//$objPHPExcel->getActiveSheet()->getStyle('C'.$startIndex)->getAlignment()->setWrapText(true);

		//echo "</td><td class='clickableRow' href='dispense.php?saleid={$saleid}'>";
		$na = 0;
	  	foreach ($totResult as $onesale) {	
			$productid = $onesale['productid'];
			
			// Determine product type, and assign query variables accordingly
			if ($onesale['category'] == 1) {
				$purchaseCategory = 'Flower';
				$queryVar = ', breed2';
				$prodSelect = 'flower';
				$prodJoin = 'flowerid';
			} else if ($onesale['category'] == 2) {
				$purchaseCategory = 'Extract';
				$queryVar = '';
				$prodSelect = 'extract';
				$prodJoin = 'extractid';
			} else {
				$purchaseCategory = $category;
				$queryVar = '';
				$prodSelect = 'products';
				$prodJoin = "productid";
			}
	
			$selectProduct = "SELECT name{$queryVar} FROM {$prodSelect} WHERE ({$prodJoin} = {$productid})";
			try
			{
				$productResult = $pdo3->prepare("$selectProduct");
				$productResult->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $productResult->fetch();
			
			if ($row['breed2'] != '') {
				$name = $row['name'] . " x " . $row['breed2'];
			} else {
				$name = $row['name'];
			}

			//$nameex .=  $name."\n";
			$namearr[$ex][$na] = $name;
			$namIndex = $startIndex + $na;
			$objPHPExcel->getActiveSheet()
		                ->setCellValue('D'.$namIndex, $name);
			$na++;
		}
		$nameex = implode("\n", $namearr[$ex]);
			
		// $objPHPExcel->getActiveSheet()->getStyle('D'.$startIndex)->getAlignment()->setWrapText(true);
		//echo "</td><td class='clickableRow right' href='dispense.php?saleid={$saleid}'>";
		$qu = 0;
	  	foreach ($totResult as $onesale) {	
			if ($onesale['category'] > 2) {
				
				// Query to look for category
				$categoryDetailsC = "SELECT name, type FROM categories WHERE id = {$onesale['category']}";
				
				try
				{
					$resultC = $pdo3->prepare("$categoryDetailsC");
					$resultC->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			
				$rowC = $resultC->fetch();
					$category = $rowC['name'];
					$type = $rowC['type'];
			}

			if ($onesale['category'] < 3 || $type == 1) {
				$quantarr[$ex][$qu] = number_format($onesale['quantity'],2) . " g";
				$quant = number_format($onesale['quantity'],2) . " g";

			} else {
				$quant = number_format($onesale['quantity'],2) . " u";
			}

			$quantIndex = $startIndex + $qu;
			$objPHPExcel->getActiveSheet()
		                ->setCellValue('E'.$quantIndex, $quant);
			$qu++;
		}
		$quantex = implode("\n", $quantarr[$ex]);

		$am1 = 0;
		$totalFullPrice = 0;
	  	foreach ($totResult as $onesale) {	
			$prodJoins = "SELECT salesPrice FROM purchases WHERE purchaseid = {$onesale['purchaseid']}";
			try
			{
				$salePrice_result = $pdo3->prepare("$prodJoins");
				$salePrice_result->execute();
				$salePrice_row = $salePrice_result->fetch();
			}
			catch (PDOException $e)
			{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
			}
			$totalamt = $onesale['quantity'] * $salePrice_row['salesPrice'];
			$totalFullPrice = $totalFullPrice + $totalamt;
			$amountIndex1 = $startIndex + $am1;
			$objPHPExcel->getActiveSheet()
		                ->setCellValue('F'.$amountIndex1, number_format($totalamt,2) . "{$currencyoperator}");
			$am1++;
		}
		$totalFullPrice = number_format($totalFullPrice,2);
		
		   // $objPHPExcel->getActiveSheet()->getStyle('E'.$startIndex)->getAlignment()->setWrapText(true);
		//echo "</td><td class='clickableRow right' href='dispense.php?saleid={$saleid}'>";
		$am = 0;
	  	foreach ($totResult as $onesale) {	
			
			$amountarr[$ex][$am] =  number_format($onesale['amount'],2) . "{$currencyoperator}";
			$amountIndex = $startIndex + $am;
			$objPHPExcel->getActiveSheet()
		                ->setCellValue('G'.$amountIndex, number_format($onesale['amount'],2) . "{$currencyoperator}");
			$am++;
		}
		$amountex = implode("\n", $amountarr[$ex]);

		$am2 = 0;
	  	foreach ($totResult as $onesale) {	
			
			$amountIndex2 = $startIndex + $am2;
			$objPHPExcel->getActiveSheet()
		                ->setCellValue('H'.$amountIndex2, $discount_type_name[$onesale['discountType']]);
			$am2++;
		}
		
		    //$objPHPExcel->getActiveSheet()->getStyle('F'.$startIndex)->getAlignment()->setWrapText(true);
		//echo "</td>";
		
		$quantity = number_format($quantity,2);
		$amount = number_format($amount,2);
			
		if ($_SESSION['domain'] == 'headbanger') {
			if ($_SESSION['userGroup'] < 3) {
				$deleteOrNot = "<td class='noExl' style='text-align: center;'><a href='javascript:delete_sale({$saleid})'><img src='images/delete.png' height='15' title='{$lang['dispenses-deletesale']}' /></a></td>";
			} else {
				$deleteOrNot = "<td class='noExl' style='text-align: center;'></td>";
			}
		} else if ($_SESSION['domain'] == 'drjoe') {
			
			if ($_SESSION['userGroup'] < 3) {
				$deleteOrNot = "<td class='noExl' style='text-align: center;'><a href='javascript:delete_sale({$saleid})'><img src='images/delete.png' height='15' title='{$lang['dispenses-deletesale']}' /></a></td>";
			} else {
				$deleteOrNot = "<td class='noExl' style='text-align: center;'></td>";
			}
		
		} else if ($_SESSION['domain'] == 'crtfd') {
			
			if ($_SESSION['userGroup'] == 1) {
				$deleteOrNot = "<td class='noExl' style='text-align: center;'><a href='javascript:delete_sale({$saleid})'><img src='images/delete.png' height='15' title='{$lang['dispenses-deletesale']}' /></a></td>";
			} else {
				$deleteOrNot = "<td class='noExl' style='text-align: center;'></td>";
			}
		
		} else {
			$deleteOrNot = "<td class='noExl' style='text-align: center;'><a href='javascript:delete_sale({$saleid})'><img src='images/delete.png' height='15' title='{$lang['dispenses-deletesale']}' /></a></td>";
		}
	
		if ($credit == NULL && $oldcredit == NULL) {
				
				/*echo "
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$quantity} g</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$units} u</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$amount} {$currencyoperator}</strong></td>
				<td class='clickableRow centered' href='dispense.php?saleid={$saleid}'><strong>{$discount}%</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'></td>";
if ($_SESSION['creditOrDirect'] != 1) {
				echo "<td class='left'>$paymentMethod</td>";
}
				echo "
				<td class='centered'><span class='relativeitem'>$commentRead</span></td>
				<td class='noExl' style='text-align: center;'><a href='javascript:delete_sale({$saleid})'><img src='images/delete.png' height='15' title='{$lang['dispenses-deletesale']}' /></a></td></tr>
				";*/

            $objPHPExcel->getActiveSheet()
            			->setCellValue('I'.$startIndex, $quantity." g"); 
            $objPHPExcel->getActiveSheet()->getStyle('I'.$startIndex)->getFont()->setBold(true);			
            $objPHPExcel->getActiveSheet()
            			->setCellValue('J'.$startIndex, $units." u");
            $objPHPExcel->getActiveSheet()->getStyle('J'.$startIndex)->getFont()->setBold(true); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('K'.$startIndex, $totalFullPrice." ".$currencyoperator); 
            $objPHPExcel->getActiveSheet()->getStyle('K'.$startIndex)->getFont()->setBold(true);
			if($discount == '0.00' && $discounteur !='0.00'){
				$objPHPExcel->getActiveSheet()
							->setCellValue('L'.$startIndex, $discounteur." ".$currencyoperator); 
				$objPHPExcel->getActiveSheet()->getStyle('L'.$startIndex)->getFont()->setBold(true);
				}else{
				$objPHPExcel->getActiveSheet()
						->setCellValue('L'.$startIndex, $discount." %"); 
				$objPHPExcel->getActiveSheet()->getStyle('L'.$startIndex)->getFont()->setBold(true);
				}
			$objPHPExcel->getActiveSheet()
				->setCellValue('M'.$startIndex, $amount." ".$currencyoperator); 
			$objPHPExcel->getActiveSheet()->getStyle('M'.$startIndex)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()
            			->setCellValue('N'.$startIndex, ""); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('O'.$startIndex, ""); 
           		if ($_SESSION['creditOrDirect'] != 1) {
            		$objPHPExcel->getActiveSheet()
            			->setCellValue('P'.$startIndex, $paymentMethod);
            		$objPHPExcel->getActiveSheet()
            			->setCellValue('Q'.$startIndex, $sale['adminComment']);

            		}else{
            			$objPHPExcel->getActiveSheet()
            			->setCellValue('P'.$startIndex, $sale['adminComment']);
            		}

				
			} else {
				
/*				echo "
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$quantity} g</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$units} u</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$amount} {$currencyoperator}</strong></td>
				<td class='clickableRow centered' href='dispense.php?saleid={$saleid}'><strong>{$discount}%</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'>{$credit} {$currencyoperator}</td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'>{$newcredit} {$currencyoperator}</td>";
				if ($_SESSION['creditOrDirect'] != 1) {
								echo "<td class='left'>$paymentMethod</td>";
				}
				echo "
				<td class='centered'><span class='relativeitem'>$commentRead</span></td>
				$deleteOrNot</tr>
				";*/

				$objPHPExcel->getActiveSheet()
	            			->setCellValue('I'.$startIndex, $quantity." g"); 
	            $objPHPExcel->getActiveSheet()->getStyle('I'.$startIndex)->getFont()->setBold(true);			
	            $objPHPExcel->getActiveSheet()
	            			->setCellValue('J'.$startIndex, $units." u");
	            $objPHPExcel->getActiveSheet()->getStyle('J'.$startIndex)->getFont()->setBold(true); 
				$objPHPExcel->getActiveSheet()
				->setCellValue('K'.$startIndex, $totalFullPrice." ".$currencyoperator); 
	$objPHPExcel->getActiveSheet()->getStyle('K'.$startIndex)->getFont()->setBold(true);
	if($discount == '0.00' && $discounteur !='0.00'){
	$objPHPExcel->getActiveSheet()
				->setCellValue('L'.$startIndex, $discounteur." ".$currencyoperator); 
	$objPHPExcel->getActiveSheet()->getStyle('L'.$startIndex)->getFont()->setBold(true);
		}else{
	$objPHPExcel->getActiveSheet()
			->setCellValue('L'.$startIndex, $discount." %"); 
	$objPHPExcel->getActiveSheet()->getStyle('L'.$startIndex)->getFont()->setBold(true);
		}
	$objPHPExcel->getActiveSheet()
		->setCellValue('M'.$startIndex, $amount." ".$currencyoperator); 
	$objPHPExcel->getActiveSheet()->getStyle('M'.$startIndex)->getFont()->setBold(true);
	            $objPHPExcel->getActiveSheet()
	            			->setCellValue('N'.$startIndex, $credit." ".$currencyoperator); 
	            $objPHPExcel->getActiveSheet()
	            			->setCellValue('O'.$startIndex, $newcredit." ".$currencyoperator); 
           		if ($_SESSION['creditOrDirect'] != 1) {
	        		$objPHPExcel->getActiveSheet()
	        			->setCellValue('P'.$startIndex, $paymentMethod);
	        		$objPHPExcel->getActiveSheet()
        			->setCellValue('Q'.$startIndex, $sale['adminComment']);

        		}else{
        			$objPHPExcel->getActiveSheet()
        			->setCellValue('P'.$startIndex, $sale['adminComment']);
        		}
			
			}
			if($ca > 1){
				$startIndex = $startIndex + $ca -1;
			}
		    $startIndex++; 
		    $ex++;
		}
        ob_end_clean();
		$cacheMethod = PHPExcel_CachedObjectStorageFactory:: cache_to_phpTemp;
		$cacheSettings = array( ' memoryCacheSize ' => '1024MB');
		PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
       	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

		//$objWriterLoop = $objWriter.$excelIndex; 
	    //header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	   	// header('Content-type: application/vnd.ms-excel');
	   	// header('Content-Disposition: attachment;filename=dispenses.xlsx');
	    //header("Content-Type: application/download");
	    //header('Cache-Control: max-age = 0');
	    $objWriter->save("excel/$fileExcel");
	    $countItem++;
	    $redirectURL = 'dispenses-report.php?fromDate='.$_GET['fromDate'].'&untilDate='.$_GET['untilDate'].'&count='.$countItem.'&totalCount='.$count.'&redirect=1';
	    echo "<h2>Please wait.</h2>";
		header('Refresh: 0; dispenses-report.php?fromDate='.$_GET['fromDate'].'&untilDate='.$_GET['untilDate'].'&count='.$countItem.'&totalCount='.$count.'&redirect=1');
		exit();
		ob_end_flush();
   		
		

	}			
  
//  ignore_user_abort ();
$f=$fileExcel;   
$file = ("excel/$f");

	    if(file_exists($file)){
?>
	 <script type="text/javascript">
	 	
	 	window.opener.location.replace("<?php echo $file ?>");
	 	setTimeout(function(){ window.close(); }, 1000);
	 	
	 </script>
<?php
		
 }

 ?>	 
	 
	 
	 
	 
	 
	 
	 
	 
	 

	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
<?php

} else {
	
/*	if ($_SESSION['userGroup'] == 1 || ($_SESSION['userGroup'] == 2 && $_SESSION['domain'] == 'amagi')) {
			
		// Query to look up sales
		 $selectSales = "SELECT saleid, saletime, userid, amount, amountpaid, quantity, realQuantity, units, adminComment, creditBefore, creditAfter, discount, direct FROM sales  WHERE 1 $timeLimit $user_limit ORDER by saletime DESC $limitVar";

	} else {
		
		// Query to look up sales
		$selectSales = "SELECT saleid, saletime, userid, amount, amountpaid, quantity, realQuantity, units, adminComment, creditBefore, creditAfter, discount, direct FROM sales WHERE DATE(saletime) = DATE(NOW()) $user_limit ORDER by saletime DESC";
		
	}
		try
		{
			$results = $pdo3->prepare("$selectSales");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}*/
	
		
     	       $rows=array();
				$page_size=1000;
				$count = $_GET['totalCount'];
				$fileExcel = "dispenses-{$_SESSION['domain']}.xlsx";
				
		    if($_GET['count'] == 0){
				// excel count query
				$excelCountQuery  =   "SELECT saleid, saletime, userid, amount, amountpaid, quantity, units, adminComment, creditBefore, creditAfter, discount, direct FROM sales WHERE 1 $timeLimit ORDER by saletime DESC";
			   //$query = "select id from shipment Limit ".$page_size." OFFSET ".$offset_var;
			   $countResults= $pdo3->prepare("$excelCountQuery");
			   $countResults->execute();

				$total_records=$countResults->rowCount();

				$count=ceil($total_records/$page_size);

				$filepath = "excel/".$fileExcel;

				if(file_exists($filepath)){
					unlink($filepath);
				}
				//$count = 3;
			}
		
	// Create month-by-month split
	$findStartDate = "SELECT saletime FROM sales ORDER BY saletime ASC LIMIT 1";
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
		$startDate = date('01-m-Y', strtotime($row['saletime']));
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

if($_GET['count'] == 0){
	  $objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('A1',$lang['global-time']);
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);  		
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('B1',$lang['global-member']);
		$objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('C1',$lang['global-category']);
		$objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('D1',$lang['global-product']);
		$objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('E1', $lang['global-quantity']);
		$objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('F1', $lang['global-quantity']." real");
		$objPHPExcel->getActiveSheet()->getStyle('F1')->getFont()->setBold(true);

		$objPHPExcel->getActiveSheet()
		->setCellValue('G1',"Full price". $currencyoperator);
		$objPHPExcel->getActiveSheet()->getStyle('G1')->getFont()->setBold(true);

		$objPHPExcel->getActiveSheet()
				->setCellValue('H1',"Discount price". $currencyoperator);
		$objPHPExcel->getActiveSheet()->getStyle('H1')->getFont()->setBold(true);

		$objPHPExcel->getActiveSheet()
				->setCellValue('I1',"Discount applied");
		$objPHPExcel->getActiveSheet()->getStyle('I1')->getFont()->setBold(true);

	
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('J1','Total g');
		$objPHPExcel->getActiveSheet()->getStyle('J1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('K1','Real g');
		$objPHPExcel->getActiveSheet()->getStyle('K1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('L1','Total u');
		$objPHPExcel->getActiveSheet()->getStyle('L1')->getFont()->setBold(true);

		$objPHPExcel->getActiveSheet()
				->setCellValue('M1','Total full price '. $currencyoperator);
		$objPHPExcel->getActiveSheet()->getStyle('M1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
				->setCellValue('N1',"Checkout discount %");
		$objPHPExcel->getActiveSheet()->getStyle('N1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
				->setCellValue('O1',"Total discounted price".$currencyoperator);
		$objPHPExcel->getActiveSheet()->getStyle('O1')->getFont()->setBold(true);

		$objPHPExcel->getActiveSheet()
		            ->setCellValue('P1',$lang['dispense-oldcredit']);
		$objPHPExcel->getActiveSheet()->getStyle('P1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('Q1',$lang['dispense-newcredit']);
		$objPHPExcel->getActiveSheet()->getStyle('Q1')->getFont()->setBold(true);
		 if ($_SESSION['creditOrDirect'] != 1) { 
		 		$objPHPExcel->getActiveSheet()
		            ->setCellValue('R1',$lang['paid-by']);
				$objPHPExcel->getActiveSheet()->getStyle('R1')->getFont()->setBold(true);
				$objPHPExcel->getActiveSheet()
		            ->setCellValue('S1',$lang['global-comment']);
				$objPHPExcel->getActiveSheet()->getStyle('S1')->getFont()->setBold(true);
		 }else{
				$objPHPExcel->getActiveSheet()
				            ->setCellValue('R1',$lang['global-comment']);
				$objPHPExcel->getActiveSheet()->getStyle('R1')->getFont()->setBold(true);
		}

	}
	   $startIndex = 2; 
	   $ex = 0; 
	   $disp = 0;
	   $countItem = $_GET['count'];
	   $discount_type_name=array('1'=>'Individual','2'=>'General Medical','3'=>'Happy Hour','4'=>'Usergroup','5'=>'5','6'=>'Volume Discounts','7'=>'Gift');
if($_GET['count'] <= $_GET['totalCount']){
		$offset_var = $countItem * $page_size;
	   	$query  =   "SELECT saleid, saletime, userid, amount, amountpaid, quantity, realQuantity, units, adminComment, creditBefore, creditAfter, discount, direct, discounteur, operatorid FROM sales WHERE 1 $timeLimit  ORDER by saletime DESC Limit ".$page_size." OFFSET ".$offset_var;
	   	//$query = "select id from shipment Limit ".$page_size." OFFSET ".$offset_var;
	   	$results= $pdo3->prepare("$query");
	   	$results->execute();

	    $startIndex = 2; 
		$ex = 0; 
	   	if(file_exists("excel/$fileExcel")){
	   		$objPHPExcel = PHPExcel_IOFactory::load("excel/$fileExcel");
	   		$startIndex = $objPHPExcel->getActiveSheet()->getHighestRow()+1;
	   	}


		while ($sale = $results->fetch()) {

		$formattedDate = date("d-m-Y H:i:s", strtotime($sale['saletime']."+$offsetSec seconds"));
		$saleid = $sale['saleid'];
		$userid = $sale['userid'];
		$quantity = $sale['quantity'];
		$realQuantity = $sale['realQuantity'];
		$units = $sale['units'];
		$credit = $sale['creditBefore'];
		$newcredit = $sale['creditAfter'];
		$discount = number_format($sale['discount'],0);
		$direct = $sale['direct'];
		$discounteur = $sale['discounteur'];
		$operatorID = $sale['operatorid'];

		if ($operatorID == 0) {
			$operator = '';
		} else {
			$operator = getOperator($operatorID);
		}
		
		if ($direct == 3) {
			$paymentMethod = $lang['global-credit'];
		} else if ($direct == 2) {
			$paymentMethod = "{$lang['card']}";
		} else if ($direct == 1) {
			$paymentMethod = "{$lang['cash']}";
		} else {
			$paymentMethod = '';
		}
		
		$amount = $sale['amount'];
		$amountpaid = $sale['amountpaid'];
		
		$userLookup = "SELECT first_name, memberno FROM users WHERE user_id = {$userid}";
		try
		{
			$result = $pdo3->prepare("$userLookup");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
	
		$row = $result->fetch();
		$first_name = $row['first_name'];
		$memberno = $row['memberno'];
		
	if ($sale['adminComment'] != '') {
		
		$commentRead = "
		                <img src='images/comments.png' id='comment$saleid' /><div id='helpBox$saleid' class='helpBox'>{$sale['adminComment']}</div>
		                <script>
		                  	$('#comment$saleid').on({
						 		'mouseover' : function() {
								 	$('#helpBox$saleid').css('display', 'inline-block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBox$saleid').css('display', 'none');
							  	}
						  	});
						</script>
		                ";
		
	} else {
		
		$commentRead = "";
		
	}

			$selectoneSale = "SELECT d.category, d.productid, d.quantity, d.realQuantity, d.amount, d.discountType, d.purchaseid FROM salesdetails d, sales s WHERE d.saleid = {$saleid} and s.saleid = d.saleid";
		try
		{
			$onesaleResult = $pdo3->prepare("$selectoneSale");
			$onesaleResult->execute();
			$totResult = $onesaleResult->fetchAll();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		
			// Make unpaid rows red:
			if ($amountpaid < $amount) {
				echo "<tr class='negative'>";
			} else {
				echo "<tr>";
			}
	   
	
			
/*echo "
  	   </td>
  	   <td class='clickableRow' href='dispense.php?saleid={$saleid}'>";*/
  	    $a = 0;
	  	   	foreach ($totResult as $onesale) {	
			if ($onesale['category'] == 1) {
				$category = $lang['global-flower'];
				

			} else if ($onesale['category'] == 2) {
				$category = $lang['global-extract'];
				
			} else {
				
				// Query to look for category
				$categoryDetails = "SELECT name FROM categories WHERE id = {$onesale['category']}";
		try
		{
			$result = $pdo3->prepare("$categoryDetails");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
					$category = $row['name'];
			}
				
			//echo $category . "<br />";
			$catIndex = $startIndex + $a;
			$catarr[$disp][$a] = $category;
			$objPHPExcel->getActiveSheet()
		                ->setCellValue('A'.$catIndex, $formattedDate);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B'.$catIndex, "#".$memberno." - ".$first_name);
			$objPHPExcel->getActiveSheet()
           				 ->setCellValue('C'.$catIndex, $category);
			$a++;
		}
		$catex = implode("\n", $catarr[$disp]);

		 
           //$objPHPExcel->getActiveSheet()->getStyle('C'.$startIndex)->getAlignment()->setWrapText(true);

		//echo "</td><td class='clickableRow' href='dispense.php?saleid={$saleid}'>";
		$b = 0;
	  	   	foreach ($totResult as $onesale) {	
			
			$productid = $onesale['productid'];
			
	// Determine product type, and assign query variables accordingly
	if ($onesale['category'] == 1) {
		$purchaseCategory = 'Flower';
		$queryVar = ', breed2';
		$prodSelect = 'flower';
		$prodJoin = 'flowerid';
	} else if ($onesale['category'] == 2) {
		$purchaseCategory = 'Extract';
		$queryVar = '';
		$prodSelect = 'extract';
		$prodJoin = 'extractid';
	} else {
		$purchaseCategory = $category;
		$queryVar = '';
		$prodSelect = 'products';
		$prodJoin = "productid";
	}
	
		$selectProduct = "SELECT name{$queryVar} FROM {$prodSelect} WHERE ({$prodJoin} = {$productid})";
		try
		{
			$result = $pdo3->prepare("$selectProduct");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		
		if ($row['breed2'] != '') {
			$name = $row['name'] . " x " . $row['breed2'];
		} else {
			$name = $row['name'];
		}


			//echo $name . "<br />";
			$namearr[$disp][$b] = $name;
			$nameIndex = $startIndex + $b;
			$objPHPExcel->getActiveSheet()
           				 ->setCellValue('D'.$nameIndex, $name);
			$b++;
		}
		$nameex = implode("\n", $namearr[$disp]);
		
           //$objPHPExcel->getActiveSheet()->getStyle('D'.$startIndex)->getAlignment()->setWrapText(true);
		//echo "</td><td class='clickableRow right' href='dispense.php?saleid={$saleid}'>";
		$c = 0;
	  	   	foreach ($totResult as $onesale) {	
			if ($onesale['category'] > 2) {
				
				// Query to look for category
				$categoryDetailsC = "SELECT name, type FROM categories WHERE id = {$onesale['category']}";
		try
		{
			$result = $pdo3->prepare("$categoryDetailsC");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$rowC = $result->fetch();
					$category = $rowC['name'];
					$type = $rowC['type'];
			}

			if ($onesale['category'] < 3 || $type == 1) {
				
				$quantarr[$disp][$c] =  number_format($onesale['quantity'],2) . " g";
				$quant =  number_format($onesale['quantity'],2) . " g";
			} else {
				
				$quant =  number_format($onesale['quantity'],2) . " u";
			}

			$quantIndex = $startIndex + $c;

			$objPHPExcel->getActiveSheet()
           				 ->setCellValue('E'.$quantIndex, $quant);

			$c++;
		}
		$quantex = implode("\n", $quantarr[$disp]);
		
          // $objPHPExcel->getActiveSheet()->getStyle('E'.$startIndex)->getAlignment()->setWrapText(true);
		//echo "</td><td class='clickableRow right' href='dispense.php?saleid={$saleid}'>";
		$d = 0;
	  	   	foreach ($totResult as $onesale) {	
			if ($onesale['category'] > 2) {
				
				// Query to look for category
				$categoryDetailsC = "SELECT name, type FROM categories WHERE id = {$onesale['category']}";
		try
		{
			$result = $pdo3->prepare("$categoryDetailsC");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$rowC = $result->fetch();
					$category = $rowC['name'];
					$type = $rowC['type'];
			}

			if ($onesale['category'] < 3 || $type == 1) {
				//echo number_format($onesale['realQuantity'],2) . " g<br />";
				$realquantarr[$disp][$d] = number_format($onesale['realQuantity'],2) . " g";
				$realquant = number_format($onesale['realQuantity'],2) . " g";
			} else {
				//echo number_format($onesale['realQuantity'],2) . " u<br />";
				$realquantarr[$disp][$d] = number_format($onesale['realQuantity'],2) . " u";
				$realquant = number_format($onesale['realQuantity'],2) . " u";
			}

			$realquaantIndex = $startIndex + $d;


			$objPHPExcel->getActiveSheet()
           				 ->setCellValue('F'.$realquaantIndex, $realquant);
			$d++;
		}
		$realquantex = implode("\n", $realquantarr[$disp]);

		$e1 = 0;
		$totalFullPrice = 0;
	  	foreach ($totResult as $onesale) {	
			$prodJoins = "SELECT salesPrice FROM purchases WHERE purchaseid = {$onesale['purchaseid']}";
			try
			{
				$salePrice_result = $pdo3->prepare("$prodJoins");
				$salePrice_result->execute();
				$salePrice_row = $salePrice_result->fetch();
			}
			catch (PDOException $e)
			{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
			}
			$totalamt = $onesale['quantity'] * $salePrice_row['salesPrice'];
			$totalFullPrice = $totalFullPrice + $totalamt;
			$amountIndex1 = $startIndex + $e1;
			$objPHPExcel->getActiveSheet()
		                ->setCellValue('G'.$amountIndex1, number_format($totalamt,2) . "{$currencyoperator}");
			$e1++;
		}
		$totalFullPrice = number_format($totalFullPrice,2);

		
           //$objPHPExcel->getActiveSheet()->getStyle('F'.$startIndex)->getAlignment()->setWrapText(true);
		//echo "</td><td class='clickableRow right' href='dispense.php?saleid={$saleid}'>";
		$e=0;
	  	   	foreach ($totResult as $onesale) {	
			
			$amountarr[$disp][$e] = number_format($onesale['amount'],2) . " {$currencyoperator}";
			$amountIndex = $startIndex + $e;
			$objPHPExcel->getActiveSheet()
           				 ->setCellValue('H'.$amountIndex, number_format($onesale['amount'],2) . " {$currencyoperator}");
			$e++;
		}
		$amountex = implode("\n", $amountarr[$disp]);
		
          // $objPHPExcel->getActiveSheet()->getStyle('G'.$startIndex)->getAlignment()->setWrapText(true);
		//echo "</td>";

		$e2 = 0;
	  	foreach ($totResult as $onesale) {	
			
			$amountIndex2 = $startIndex + $e2;
			$objPHPExcel->getActiveSheet()
		                ->setCellValue('I'.$amountIndex2, $discount_type_name[$onesale['discountType']]);
			$e2++;
		}
		
		$quantity = number_format($quantity,2);
		$realQuantity = number_format($realQuantity,2);
		$amount = number_format($amount,2);
		
	if ($_SESSION['domain'] == 'headbanger') {
		if ($_SESSION['userGroup'] < 3) {
			$deleteOrNot = "<td class='noExl' style='text-align: center;'><a href='javascript:delete_sale({$saleid})'><img src='images/delete.png' height='15' title='{$lang['dispenses-deletesale']}' /></a></td>";
		} else {
			$deleteOrNot = "<td class='noExl' style='text-align: center;'></td>";
		}
	} else if ($_SESSION['domain'] == 'drjoe') {
		
		if ($_SESSION['userGroup'] < 3) {
			$deleteOrNot = "<td class='noExl' style='text-align: center;'><a href='javascript:delete_sale({$saleid})'><img src='images/delete.png' height='15' title='{$lang['dispenses-deletesale']}' /></a></td>";
		} else {
			$deleteOrNot = "<td class='noExl' style='text-align: center;'></td>";
		}
	
	} else if ($_SESSION['domain'] == 'crtfd') {
		
		if ($_SESSION['userGroup'] == 1) {
			$deleteOrNot = "<td class='noExl' style='text-align: center;'><a href='javascript:delete_sale({$saleid})'><img src='images/delete.png' height='15' title='{$lang['dispenses-deletesale']}' /></a></td>";
		} else {
			$deleteOrNot = "<td class='noExl' style='text-align: center;'></td>";
		}
	
	} else {
		$deleteOrNot = "<td class='noExl' style='text-align: center;'><a href='javascript:delete_sale({$saleid})'><img src='images/delete.png' height='15' title='{$lang['dispenses-deletesale']}' /></a></td>";
	}

		
		
			if ($credit == NULL && $oldcredit == NULL) {
				
/*				echo "
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$quantity} g</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$realQuantity} g</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$units} u</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$amount} {$currencyoperator}</strong></td>
				<td class='clickableRow centered' href='dispense.php?saleid={$saleid}'><strong>{$discount}%</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'></td>";
				if ($_SESSION['creditOrDirect'] != 1) {
						echo "<td class='left'>$paymentMethod</td>";
					}
				echo "
				<td class='noExl centered'><span class='relativeitem'>$commentRead</span></td>
				<td class='noExl' style='text-align: center;'><a href='javascript:delete_sale({$saleid})'><img src='images/delete.png' height='15' title='{$lang['dispenses-deletesale']}' /></a></td></tr>
				";*/

				    $objPHPExcel->getActiveSheet()
		            			->setCellValue('J'.$startIndex, $quantity." g"); 
		            $objPHPExcel->getActiveSheet()->getStyle('J'.$startIndex)->getFont()->setBold(true);			
		            $objPHPExcel->getActiveSheet()
		            			->setCellValue('K'.$startIndex, $realQuantity." g");
		            $objPHPExcel->getActiveSheet()->getStyle('K'.$startIndex)->getFont()->setBold(true);  
		            $objPHPExcel->getActiveSheet()
		            			->setCellValue('L'.$startIndex, $units." u");
		            $objPHPExcel->getActiveSheet()->getStyle('L'.$startIndex)->getFont()->setBold(true); 
					$objPHPExcel->getActiveSheet()
					->setCellValue('M'.$startIndex, $totalFullPrice." ".$currencyoperator); 
					$objPHPExcel->getActiveSheet()->getStyle('M'.$startIndex)->getFont()->setBold(true);
					if($discount == '0.00' && $discounteur !='0.00'){
						$objPHPExcel->getActiveSheet()
									->setCellValue('N'.$startIndex, $discounteur." ".$currencyoperator); 
						$objPHPExcel->getActiveSheet()->getStyle('N'.$startIndex)->getFont()->setBold(true);
						}else{
						$objPHPExcel->getActiveSheet()
								->setCellValue('N'.$startIndex, $discount." %"); 
						$objPHPExcel->getActiveSheet()->getStyle('N'.$startIndex)->getFont()->setBold(true);
						}
					$objPHPExcel->getActiveSheet()
						->setCellValue('O'.$startIndex, $amount." ".$currencyoperator); 
					$objPHPExcel->getActiveSheet()->getStyle('O'.$startIndex)->getFont()->setBold(true);
		            $objPHPExcel->getActiveSheet()
		            			->setCellValue('P'.$startIndex, ""); 
		            $objPHPExcel->getActiveSheet()
		            			->setCellValue('Q'.$startIndex, ""); 
		           		if ($_SESSION['creditOrDirect'] != 1) {
		            		$objPHPExcel->getActiveSheet()
		            			->setCellValue('R'.$startIndex, $paymentMethod);
		            		$objPHPExcel->getActiveSheet()
		            			->setCellValue('S'.$startIndex, $sale['adminComment']);

		            		}else{
		            			$objPHPExcel->getActiveSheet()
		            			->setCellValue('R'.$startIndex, $sale['adminComment']);
		            		}
				
			} else {
				
/*				echo "
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$quantity} g</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$realQuantity} g</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$units} u</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$amount} {$currencyoperator}</strong></td>
				<td class='clickableRow centered' href='dispense.php?saleid={$saleid}'><strong>{$discount}%</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'>{$credit} {$currencyoperator}</td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleid}'>{$newcredit} {$currencyoperator}</td>";
if ($_SESSION['creditOrDirect'] != 1) {
				echo "<td class='left'>$paymentMethod</td>";
}
				echo "
				<td class='noExl centered'><span class='relativeitem'>$commentRead</span></td>
				$deleteOrNot</tr>
				";*/

				    $objPHPExcel->getActiveSheet()
		            			->setCellValue('J'.$startIndex, $quantity." g"); 
		            $objPHPExcel->getActiveSheet()->getStyle('J'.$startIndex)->getFont()->setBold(true);			
		            $objPHPExcel->getActiveSheet()
		            			->setCellValue('K'.$startIndex, $realQuantity." g");
		            $objPHPExcel->getActiveSheet()->getStyle('K'.$startIndex)->getFont()->setBold(true);  
		            $objPHPExcel->getActiveSheet()
		            			->setCellValue('L'.$startIndex, $units." u");
		            $objPHPExcel->getActiveSheet()->getStyle('L'.$startIndex)->getFont()->setBold(true); 
					$objPHPExcel->getActiveSheet()
					->setCellValue('M'.$startIndex, $totalFullPrice." ".$currencyoperator); 
					$objPHPExcel->getActiveSheet()->getStyle('M'.$startIndex)->getFont()->setBold(true);
					if($discount == '0.00' && $discounteur !='0.00'){
					$objPHPExcel->getActiveSheet()
								->setCellValue('N'.$startIndex, $discounteur." ".$currencyoperator); 
					$objPHPExcel->getActiveSheet()->getStyle('N'.$startIndex)->getFont()->setBold(true);
						}else{
					$objPHPExcel->getActiveSheet()
							->setCellValue('N'.$startIndex, $discount." %"); 
					$objPHPExcel->getActiveSheet()->getStyle('N'.$startIndex)->getFont()->setBold(true);
						}
					$objPHPExcel->getActiveSheet()
						->setCellValue('O'.$startIndex, $amount." ".$currencyoperator); 
					$objPHPExcel->getActiveSheet()->getStyle('O'.$startIndex)->getFont()->setBold(true);
		            $objPHPExcel->getActiveSheet()
		            			->setCellValue('P'.$startIndex, $credit." ".$currencyoperator); 
		            $objPHPExcel->getActiveSheet()
		            			->setCellValue('Q'.$startIndex, $newcredit." ".$currencyoperator); 
		           		if ($_SESSION['creditOrDirect'] != 1) {
		            		$objPHPExcel->getActiveSheet()
		            			->setCellValue('R'.$startIndex, $paymentMethod);
		            		$objPHPExcel->getActiveSheet()
		            			->setCellValue('S'.$startIndex, $sale['adminComment']);

		            		}else{
		            			$objPHPExcel->getActiveSheet()
		            			->setCellValue('R'.$startIndex, $sale['adminComment']);
		            		}
			
			}

			if($a > 1){
				$startIndex = $startIndex + $a -1;
			}

		    $startIndex++; 
		    $disp++;
		

	}

        ob_end_clean();
		$cacheMethod = PHPExcel_CachedObjectStorageFactory:: cache_to_phpTemp;
		$cacheSettings = array( ' memoryCacheSize ' => '1024MB');
		PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
       	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

		//$objWriterLoop = $objWriter.$excelIndex; 
	    //header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	   	// header('Content-type: application/vnd.ms-excel');
	   	// header('Content-Disposition: attachment;filename=dispenses.xlsx');
	    //header("Content-Type: application/download");
	    //header('Cache-Control: max-age = 0');
	    $objWriter->save("excel/$fileExcel");
	    $countItem++;
	    
	    $redirectURL = 'dispenses-report.php?fromDate='.$_GET['fromDate'].'&untilDate='.$_GET['untilDate'].'&count='.$countItem.'&totalCount='.$count.'&redirect=1';


		echo "<h2>Please wait.</h2>";
		header('Refresh: 0; dispenses-report.php?fromDate='.$_GET['fromDate'].'&untilDate='.$_GET['untilDate'].'&count='.$countItem.'&totalCount='.$count.'&redirect=1');
		exit();
		ob_end_flush();

	}			
  
			//  ignore_user_abort ();
			$f=$fileExcel;   
			$file = ("excel/$f");

				    if(file_exists($file)){
			?>
				 <script type="text/javascript">
				 	
				 	window.opener.location.replace("<?php echo $file ?>");
				 	setTimeout(function(){ window.close(); }, 1000);
				 	
				 </script>
			<?php
					
			 }

 
}

