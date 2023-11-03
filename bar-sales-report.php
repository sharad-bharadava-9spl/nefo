<?php
	ob_start();
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
			
			if($month !='' && $year != ''){
				$timeLimit = "AND MONTH(saletime) = $month AND YEAR(saletime) = $year";
			}else{
				$timeLimit = '';
			}
			
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
			
	}else{
		$timeLimit = '';
	}
	// KONSTAT CODE UPDATE BEGIN
   $current_date =  date('d-m-Y');
   $expected_date = "01-01-2019";
   $dispalyGroup = 0;
	if(strtotime($current_date) > strtotime($expected_date)){
		$dispalyGroup = 1;
	}
	$selectedUsergroup = "1,2,3";
	$selectedDiscount = "1,2,3,4,6,7";
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
			//  code to filter the sales from discounts
			if(isset($_POST['discountType'])){
				$selectedDiscountArr = $_POST['discountType'];
				$happyIdArr[] = 0;
				$volumeIdArr[] = 0;
				$salesIdArr[] = 0;
				$checkoutIdArr[] = 0;
			    $selectedDiscount = implode(',',$selectedDiscountArr);
			   
				    // happy hour discount
				    if(in_array(3, $selectedDiscountArr)){
				    	$getSales  = "SELECT p.saleid  FROM b_sales p, b_salesdetails q WHERE q.happyhourDiscount = '3' AND p.saleid = q.saleid";
				    	$getResult =  $pdo3->prepare("$getSales");
				    	$getResult->execute();
				    	while($happyRow = $getResult->fetch()){
				    		$happyIdArr[] = $happyRow['saleid']; 
				    	}
				    }
				    // Volume discount
				    if(in_array(6,  $selectedDiscountArr)){
				       $getSales  = "SELECT p.saleid  FROM b_sales p, b_salesdetails q WHERE q.volumeDiscount = '6'  AND p.saleid = q.saleid";
				    	$getResult =  $pdo3->prepare("$getSales");
				    	$getResult->execute();
				    	while($volumeRow = $getResult->fetch()){
				    		$volumeIdArr[] = $volumeRow['saleid']; 
				    	}
					}
					// Checkout discount
				    if(in_array(5,  $selectedDiscountArr)){
				       $getSales  = "SELECT p.saleid  FROM b_sales p, b_sales_discount q WHERE q.discountType = '5'  AND p.saleid = q.salesId";
				    	$getResult =  $pdo3->prepare("$getSales");
				    	$getResult->execute();
				    	while($checkoutRow = $getResult->fetch()){
				    		$checkoutIdArr[] = $checkoutRow['saleid']; 
				    	}
					}
						// get all sales ids from discount
						if(empty($selectedDiscount) || $selectedDiscount == ''){
							$selectedDiscount = -1;
						}
						$getSales  = "SELECT p.saleid  FROM b_sales p, b_salesdetails q WHERE q.discountType IN ($selectedDiscount) AND p.saleid = q.saleid";
					    	$getResult =  $pdo3->prepare("$getSales");
					    	$getResult->execute();
					    	while($salesRow = $getResult->fetch()){
					    		$salesIdArr[] = $salesRow['saleid']; 
					    	}
					    
				    	$filtersales = array_merge($happyIdArr, $volumeIdArr, $salesIdArr ,$checkoutIdArr);
				    	$filterSaleIds = array_unique($filtersales);
				        $selectedDiscountIds = implode(',', $filterSaleIds); 
				        if(empty($selectedDiscountIds) || $selectedDiscountIds == ''){
				        	$selectedDiscountIds = -1;
				        }
				        $discount_limit = "AND saleid IN ($selectedDiscountIds)";
			    
			}else{
			    	$selectedDiscount = 0;
			        $getSales  = "SELECT p.saleid  FROM b_sales p, b_salesdetails q WHERE q.discountType  = '$selectedDiscount' AND p.saleid = q.saleid";
					    	$getResult =  $pdo3->prepare("$getSales");
					    	$getResult->execute();
					    	while($salesRow = $getResult->fetch()){
					    		$salesIdArr[] = $salesRow['saleid']; 
					    	}
					    $selectedDiscountIds = implode(',', $salesIdArr); 
						if(empty($selectedDiscountIds) || $selectedDiscountIds == ''){
				        	$selectedDiscountIds = -1;
				        }
					    $discount_limit = "AND saleid IN ($selectedDiscountIds)";
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
		   $getSales  = "SELECT p.saleid  FROM b_sales p, b_salesdetails q WHERE q.discountType IN ($selectedDiscount) AND p.saleid = q.saleid";
	    	$getResult =  $pdo3->prepare("$getSales");
	    	$getResult->execute();
	    	while($salesRow = $getResult->fetch()){
	    		$salesIdArr[] = $salesRow['saleid']; 
	    	}
	    	$filterSaleIds = array_unique($salesIdArr);
	    	$selectedDiscountIds = implode(',', $filterSaleIds);
	    	if($selectedDiscountIds == '' || empty($selectedDiscountIds)){
	    		$selectedDiscountIds = -1;
	    	}
	    	$discount_limit = "AND saleid IN ($selectedDiscountIds)";
	}
   
	// KONSTANT CODE UPDATE END
	// Query to look up individual sales
/* 	 $selectSales = "SELECT saleid, operatorid, saletime, amount, unitsTot, userid, creditBefore, creditAfter, direct, adminComment FROM b_sales  WHERE 1 $timeLimit $user_limit ORDER by saletime DESC $limitVar"; 
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
				$domain = $_SESSION['domain'];
				$fileExcel = $domain."-bar-sales.xlsx";
				
		    if($_GET['count'] == 0){
				// excel count query
				$excelCountQuery  =   "SELECT saleid, operatorid, saletime, amount, unitsTot, userid, creditBefore, creditAfter, direct, adminComment FROM b_sales  WHERE 1 $timeLimit  ORDER by saletime DESC";
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
	$findStartDate = "SELECT saletime FROM b_sales ORDER BY saletime ASC LIMIT 1";
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
		            ->setCellValue('B1','Usergroup');
		$objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('C1',$lang['global-member']);
		$objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('D1',$lang['global-category']);
		$objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('E1',$lang['global-product']);
		$objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('F1',$lang['global-quantity']);
		$objPHPExcel->getActiveSheet()->getStyle('F1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('G1',$currencyoperator);
		$objPHPExcel->getActiveSheet()->getStyle('G1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('H1','Discount applied');
		$objPHPExcel->getActiveSheet()->getStyle('H1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('I1','Summary');
		$objPHPExcel->getActiveSheet()->getStyle('I1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('J1','Total u');
		$objPHPExcel->getActiveSheet()->getStyle('J1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('K1','Total '.$currencyoperator);
		$objPHPExcel->getActiveSheet()->getStyle('K1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('L1',$lang['dispense-oldcredit']);
		$objPHPExcel->getActiveSheet()->getStyle('L1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('M1',$lang['dispense-newcredit']);
		$objPHPExcel->getActiveSheet()->getStyle('M1')->getFont()->setBold(true); 
	    if ($_SESSION['creditOrDirect'] != 1) {
			$objPHPExcel->getActiveSheet()
			            ->setCellValue('N1',$lang['paid-by']);
			$objPHPExcel->getActiveSheet()->getStyle('N1')->getFont()->setBold(true);
			$objPHPExcel->getActiveSheet()
			            ->setCellValue('O1',$lang['global-comment']);
			$objPHPExcel->getActiveSheet()->getStyle('O1')->getFont()->setBold(true);
		}else{
			$objPHPExcel->getActiveSheet()
			            ->setCellValue('N1',$lang['global-comment']);
			$objPHPExcel->getActiveSheet()->getStyle('N1')->getFont()->setBold(true);
		}
	}  
	?>
	<!-- KONSTANT CODE UPDATE END -->
<?php
$countItem = $_GET['count'];
if($_GET['count'] <= $_GET['totalCount']){
   	$offset_var = $countItem * $page_size;
   	$query  =   "SELECT saleid, operatorid, saletime, amount, unitsTot, userid, creditBefore, creditAfter, direct, adminComment FROM b_sales  WHERE 1 $timeLimit  ORDER by saletime DESC Limit ".$page_size." OFFSET ".$offset_var;
   	//$query = "select id from shipment Limit ".$page_size." OFFSET ".$offset_var;
   	$results= $pdo3->prepare("$query");
   	$results->execute();

	  $startIndex = 2;
	  $p = 0;

   	if(file_exists("excel/$fileExcel")){
   		$objPHPExcel = PHPExcel_IOFactory::load("excel/$fileExcel");
   		$startIndex = $objPHPExcel->getActiveSheet()->getHighestRow()+1;
   	}
		while ($sale = $results->fetch()) {
	
		$formattedDate = date("d-m-Y H:i", strtotime($sale['saletime'] . "+$offsetSec seconds"));
		// KONSTANT CODE UPDATE BEGIN
		$operatorId = $sale['operatorid'];
		// KONSTANT CODE UPDATE END
		$saleid = $sale['saleid'];
		$userid = $sale['userid'];
		$units = $sale['unitsTot'];
		$credit = $sale['creditBefore'];
		$newcredit = $sale['creditAfter'];
		$direct = $sale['direct'];
		$direct = $sale['direct'];
		
		if ($direct == 3) {
			$paymentMethod = $lang['global-credit'];
		} else if ($direct == 2) {
			$paymentMethod = $lang['card'];
		} else if ($direct == 1) {
			$paymentMethod = $lang['cash'];
		} else {
			$paymentMethod = '';
		}
		
		$amount = $sale['amount'];
		// KONSTANT CODE UPDATE BEGIN
			if($operatorId > 0 && $operatorId != 999999){
				$operatorLookup = "SELECT a.userGroup, b.groupName FROM users a, usergroups b WHERE a.userGroup = b.userGroup AND a.user_id = {$operatorId}";
				try
				{
					$result = $pdo3->prepare("$operatorLookup");
					$result->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			
				$row = $result->fetch();
					$operatorGroup = $row['userGroup'];
				    $operatorName = $row['groupName']; 
			}else{
				if($operatorId == 999999){
					$operatorGroup = 1;
					$operatorName = 'Administrador';
				}else{
					$operatorGroup = 0;
					$operatorName = 'None';
				}
			}
		// KONSTANT CODE UPDATE END
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
								 	$('#helpBox$saleid').css('display', 'block');
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
		
		// KONSTANT CODE UPDATE BEGIN
		$selectoneSale = "SELECT d.category, d.productid, d.quantity, d.amount, d.purchaseid, d.discountType, d.discountPercentage, d.happyhourDiscount, d.volumeDiscount, s.discount FROM b_salesdetails d, b_sales s WHERE d.saleid = {$saleid} and s.saleid = d.saleid";
		// KONSTANT CODE UPDATE END
		try
		{
			$onesaleResult = $pdo3->prepare("$selectoneSale");
			$onesaleResult->execute();
			$onesaleResult2 = $pdo3->prepare("$selectoneSale");
			$onesaleResult2->execute();
			$onesaleResult3 = $pdo3->prepare("$selectoneSale");
			$onesaleResult3->execute();
			$onesaleResult4 = $pdo3->prepare("$selectoneSale");
			$onesaleResult4->execute();
			// KONSTANT CODE UPDATE BEGIN		
			$onesaleResult5 = $pdo3->prepare("$selectoneSale");
			$onesaleResult5->execute();			
			$onesaleResult6 = $pdo3->prepare("$selectoneSale");
			$onesaleResult6->execute();
			// KONSTANT CODE UPDATE END
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		
	   
		/*echo "
  	   <td class='clickableRow' href='bar-sale.php?saleid={$saleid}'>{$formattedDate}</td>";*/

  	   

  	  // echo "<td class='clickableRow' href='bar-sale.php?saleid={$saleid}'>";
  	   $a = 0;
		while ($onesale = $onesaleResult->fetch()) {
			
			$selectCatName = "SELECT name from b_categories where id = {$onesale['category']}";
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
	
		$row = $result->fetch();
				$catName = $row['name'] ;
				//$catarr[$p][$a]= $catName; 
				$catIndex = $startIndex + $a;

				$objPHPExcel->getActiveSheet()
				                ->setCellValue('A'.$catIndex, $formattedDate);
		  	     	 
					if($dispalyGroup == 1){

			  	   		$objPHPExcel->getActiveSheet()
					                ->setCellValue('B'.$catIndex, $operatorName);
			  		}
		  		
		  	    $objPHPExcel->getActiveSheet()
				                ->setCellValue('C'.$catIndex, "#{$memberno} - {$first_name}");

				$objPHPExcel->getActiveSheet()
		                ->setCellValue('D'.$catIndex, $catName);
				//echo $catName . "</br>";
				$a++;
		}
		//$catex = implode(',', $catarr[$p]);
		//echo "</td><td class='clickableRow' href='bar-sale.php?saleid={$saleid}'>";
		$b = 0;
		while ($onesale = $onesaleResult2->fetch()) {
			
			// Look up service name
			$selectServName = "SELECT name from b_products where productid = {$onesale['productid']}";
		try
		{
			$result = $pdo3->prepare("$selectServName");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$servName = $row['name'] ;
				//$servarr[$p][$b] .= $servName;

				$prodIndex = $startIndex + $b;

				$objPHPExcel->getActiveSheet()
		                ->setCellValue('E'.$prodIndex, $servName);

				//echo $servName . "<br>";
				$b++;
		}
		//$servex = implode(",", $servarr[$p]);
		//echo "</td><td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'>";
		$c = 0;
		while ($onesale = $onesaleResult3->fetch()) {
				//echo number_format($onesale['quantity'],0) . " u<br />";

				$quant= number_format($onesale['quantity'],0) . " u";

				$quantIndex = $startIndex + $c;

				$objPHPExcel->getActiveSheet()
		                ->setCellValue('F'.$quantIndex, $quant);

				$c++;
		}
		//$quantex = implode(",", $quantarr[$p]);
		//echo "</td><td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'>";
		$d = 0;
		while ($onesale = $onesaleResult4->fetch()) {
			
			$amount =   number_format($onesale['amount'],2) . $currencyoperator;

			$amountIndex = $startIndex + $d;

			$objPHPExcel->getActiveSheet()
		                ->setCellValue('G'.$amountIndex, $amount);
			$d++;
		}
		//$amountex = implode(",", $amountarr[$p]);
		//echo "</td>";
			//	echo "<td  class='clickableRow' href='bar-sale.php?saleid={$saleid}'>";
		$kk =0;
		 while ($onesale = $onesaleResult5->fetch()) {
	    	 $discountType = $onesale['discountType'];
	    	if($discountType == 1){
	    		$discountName = "Individual discount";
	    	}else if($discountType == 2){
	    		$discountName = "Medical discount";
	    	}
	    	else if($discountType == 3){
	    		$discountName = "Happy Hour discount";
	    	}
	    	else if($discountType == 4){
	    		$discountName = "Usergroup discount";
	    	}
	    	else if($discountType == 5){
	    		$discountName = "Checkout discount";
	    	}
	    	else if($discountType == 6){
	    		$discountName = "Volume discount";
	    	}
	    	else if($discountType == 7){
	    		$discountName = "Gift";
	    	}
	    	$checkoutValue = $onesale['discount'];
	    	$happyhourDiscount = $onesale['happyhourDiscount'];
	    	$volumeDiscount = $onesale['volumeDiscount'];
	    	$chexckdiscount = ''; 
	    	if($checkoutValue != '0.00'){
	    		if($kk == 0){
	    			//echo "(Checkout Disocunt) <br>";
	    			$discountarr[$p][$kk] = "(Checkout Disocunt)";
	    			$chexckdiscount = "(Checkout Disocunt)\n";
	    		}
	    	}
	    	$otherdiscount = ''; 
	    	if($happyhourDiscount != '0.00' && $volumeDiscount != '0.00'){
	    		if($discountType == 3){
	    			$discountName2 = "";
	    		}else{
	    			$discountName2 = " + $discountName";
	    		}
	    		$otherdiscount = "Happy Hour + Volume Discount$discountName2\n";
	    		$discountarr[$p][$kk] = "Happy Hour + Volume Discount$discountName2";
	    	}else{
	    		$otherdiscount =  "$discountName\n";
	    		$discountarr[$p][$kk]  = "$discountName";
	    	}
	    	$discountIndex = $startIndex + $kk;

	    	$objPHPExcel->getActiveSheet()
		                ->setCellValue('H'.$discountIndex, $chexckdiscount.$otherdiscount);
		$kk++; 
	}
	   //$discountex = $discountarr[$p];
		//echo "</td>";
		//echo "<td  class='clickableRow' href='bar-sale.php?saleid={$saleid}'>";
		$e = 0;
		 while ($onesale = $onesaleResult6->fetch()) {
		 	   $selectPrice = "SELECT salesPrice FROM b_purchases WHERE purchaseid = {$onesale['purchaseid']}";
				try
				{
					$result = $pdo3->prepare("$selectPrice");
					$result->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
				$row = $result->fetch();
				$salesPrice = $row['salesPrice'];
			    $thisQuantity = $onesale['quantity'];
			    $normalPrice = round($salesPrice * $thisQuantity,2);
	    		$paid_price =  number_format($onesale['amount'],2);
	    	    $loss_price = $normalPrice - $paid_price;
	    		$discountPer = $onesale['discountPercentage'];
	    		//echo  "$loss_price {$currencyoperator}<br>";
	    		$losspricearr[$p][$e] =  $loss_price.$currencyoperator; 

	    		$lossIndex = $startIndex + $e;

	    		$objPHPExcel->getActiveSheet()
		                ->setCellValue('I'.$lossIndex, $loss_price.$currencyoperator);

	    		$e++;
		}
		//echo "</td>";
		$losspriceex = implode(",", $losspricearr[$p]);
		$quantity = number_format($quantity,2);
		$amount = number_format($amount,2);
		$units = number_format($units,0);
		
			
			if ($credit == NULL && $oldcredit == NULL) {
/*				echo "
				<td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'><strong>{$units} u</strong></td>
				<td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'><strong>{$amount} {$currencyoperator}</strong></td>
				<td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'></td>
				<td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'></td>";
				if ($_SESSION['creditOrDirect'] != 1) {
					echo "<td class='left'>$paymentMethod</td>";
				}
				echo "<td class='centered'><span class='relativeitem'>$commentRead</span></td>";
				echo "<td class='noExl' style='text-align: center;'><a href='javascript:delete_sale({$saleid})'><img src='images/delete.png' height='15' title='{$lang['dispenses-deletesale']}' /></a></td></tr>
				";*/

				$objPHPExcel->getActiveSheet()
	            			->setCellValue('J'.$startIndex, $units." u");
	           	$objPHPExcel->getActiveSheet()->getStyle('J'.$startIndex)->getFont()->setBold(true);
	            $objPHPExcel->getActiveSheet()
	            			->setCellValue('K'.$startIndex, $amount." ".$currencyoperator); 
	            $objPHPExcel->getActiveSheet()->getStyle('K'.$startIndex)->getFont()->setBold(true);			
	            $objPHPExcel->getActiveSheet()
	            			->setCellValue('L'.$startIndex, ''); 
	            $objPHPExcel->getActiveSheet()
	            			->setCellValue('M'.$startIndex, '');
		            if ($_SESSION['creditOrDirect'] != 1) {	

		           			$objPHPExcel->getActiveSheet()
		            			->setCellValue('N'.$startIndex, $paymentMethod);	
		            		$objPHPExcel->getActiveSheet()
		            			->setCellValue('O'.$startIndex, $sale['adminComment']);	
		            	}else{

		            		$objPHPExcel->getActiveSheet()
		            			->setCellValue('N'.$startIndex, $sale['adminComment']);
		            	}
				
			} else {
				
/*				echo "
				<td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'><strong>{$units} u</strong></td>
				<td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'><strong>{$amount} {$currencyoperator}</strong></td>
				<td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'>{$credit} {$currencyoperator}</td>
				<td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'>{$newcredit} {$currencyoperator}</td>";
					if ($_SESSION['creditOrDirect'] != 1) {
									echo "<td class='left'>$paymentMethod</td>";
					}
				echo "<td class='centered'><span class='relativeitem'>$commentRead</span></td>";
				echo "<td class='noExl' style='text-align: center;'><a href='javascript:delete_sale({$saleid})'><img src='images/delete.png' height='15' title='{$lang['dispenses-deletesale']}' /></a></td></tr>
				";*/


				$objPHPExcel->getActiveSheet()
	            			->setCellValue('J'.$startIndex, $units." u");
	            $objPHPExcel->getActiveSheet()->getStyle('J'.$startIndex)->getFont()->setBold(true);			
	            $objPHPExcel->getActiveSheet()
	            			->setCellValue('K'.$startIndex, $amount." ".$currencyoperator); 
	            $objPHPExcel->getActiveSheet()->getStyle('K'.$startIndex)->getFont()->setBold(true);			
	            $objPHPExcel->getActiveSheet()
	            			->setCellValue('L'.$startIndex, $credit." ".$currencyoperator); 
	            $objPHPExcel->getActiveSheet()
	            			->setCellValue('M'.$startIndex, $newcredit." ".$currencyoperator);
		            if ($_SESSION['creditOrDirect'] != 1) {	

		           			$objPHPExcel->getActiveSheet()
		            			->setCellValue('N'.$startIndex, $paymentMethod);	
		            		$objPHPExcel->getActiveSheet()
		            			->setCellValue('O'.$startIndex, $sale['adminComment']);	
		            	}else{
		            		
		            		$objPHPExcel->getActiveSheet()
		            			->setCellValue('N'.$startIndex, $sale['adminComment']);
		            	}
			
			}

			if($a > 1){
				$startIndex = $startIndex + $a -1;
			}
           	    
		    $startIndex++; 
		    $p++;
	}


        ob_end_clean();
		$cacheMethod = PHPExcel_CachedObjectStorageFactory:: cache_to_phpTemp;
		$cacheSettings = array( ' memoryCacheSize ' => '1024MB');
		PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
       	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');


	    $objWriter->save("excel/$fileExcel");
	    $countItem++;
	   
		//header('Location:bar-sales-report.php?fromDate='.$_GET['fromDate'].'&untilDate='.$_GET['untilDate'].'&count='.$countItem.'&totalCount='.$count.'&redirect=1');

		echo "<h2>".$lang['report-generate']."</h2>";
		header('Refresh: 0; bar-sales-report.php?fromDate='.$_GET['fromDate'].'&untilDate='.$_GET['untilDate'].'&count='.$countItem.'&totalCount='.$count.'&redirect=1');
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
