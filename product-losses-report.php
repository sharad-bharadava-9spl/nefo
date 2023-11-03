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
	// Check if new Filter value was submitted, and assign query variable accordingly
	if (isset($_GET['untilDate']) && $_GET['untilDate'] != '') {
		
		$firstLoad = 'false';
		
		$fromDate = date("Y-m-d", strtotime($_GET['fromDate']));
		$untilDate = date("Y-m-d", strtotime($_GET['untilDate']));
		
		$timeLimit = "AND DATE(saletime) BETWEEN DATE('$fromDate') AND DATE('$untilDate')";
			
	} else {
		
		$firstLoad = 'true';
		
		$nowDate = date("d-m-Y");
		
		$timeLimit = "AND DATE(saletime) = DATE(NOW())";
		
	}

	// KONSTAT CODE UPDATE BEGIN
	$selectedUsergroup = "1,2,3";
	$selectedDiscount = "1,2,3,4,6,7";
	if(isset($_GET['submitted']) && $_GET['submitted'] != ''){
			$firstSelect = 'false';
			//  code to filter the sales from usergroups
			if(isset($_GET['cashBox']) && $_GET['cashBox'] != ''){
				$selectedUserArr = $_GET['cashBox'];
				$selectedUsergroup = implode(",", $selectedUserArr);
				$getUsers = "SELECT user_id FROM users WHERE userGroup IN ($selectedUsergroup)";
				$result = $pdo3->prepare("$getUsers");
				$result->execute();
				while($user_ids = $result->fetch()){
					$userArr[] = $user_ids['user_id'];
				}
				//array_push($userArr, 999999);
				$selectedUsers = implode(',',$userArr);
				$user_limit = "AND operatorid IN ($selectedUsers)";
			}else{
				$user_limit = 'AND operatorid = 0';
			}
			//  code to filter the sales from discounts
			if(isset($_GET['discountType']) && $_GET['discountType'] != ''){
				$selectedDiscountArr = $_GET['discountType'];
				$happyIdArr[] = 0;
				$volumeIdArr[] = 0;
				$salesIdArr[] = 0;
				$checkoutIdArr[] = 0;
			    $selectedDiscount = implode(',',$selectedDiscountArr);
			   if(empty($selectedDiscount) || $selectedDiscount == ''){
					$selectedDiscount = -1;		    	
			    }
				    // happy hour discount
				    if(in_array(3, $selectedDiscountArr)){
				    	$getSales  = "SELECT p.saleid  FROM sales p, salesdetails q WHERE q.happyhourDiscount = '3' AND p.saleid = q.saleid";
				    	$getResult =  $pdo3->prepare("$getSales");
				    	$getResult->execute();
				    	while($happyRow = $getResult->fetch()){
				    		$happyIdArr[] = $happyRow['saleid']; 
				    	}
				    }
				    // Volume discount
				    if(in_array(6,  $selectedDiscountArr)){
				       $getSales  = "SELECT p.saleid  FROM sales p, salesdetails q WHERE q.volumeDiscount = '6'  AND p.saleid = q.saleid";
				    	$getResult =  $pdo3->prepare("$getSales");
				    	$getResult->execute();
				    	while($volumeRow = $getResult->fetch()){
				    		$volumeIdArr[] = $volumeRow['saleid']; 
				    	}
					}				    
					// Checkout discount
				    if(in_array(5,  $selectedDiscountArr)){
				       $getSales  = "SELECT p.saleid  FROM sales p, sales_discount q WHERE q.discountType = '5'  AND p.saleid = q.salesId";
				    	$getResult =  $pdo3->prepare("$getSales");
				    	$getResult->execute();
				    	while($checkoutRow = $getResult->fetch()){
				    		$checkoutIdArr[] = $checkoutRow['saleid']; 
				    	}
					}
						// get all sales ids from discount
						
						$getSales  = "SELECT p.saleid  FROM sales p, salesdetails q WHERE q.discountType IN ($selectedDiscount) AND p.saleid = q.saleid";
					    	$getResult =  $pdo3->prepare("$getSales");
					    	$getResult->execute();
					    	while($salesRow = $getResult->fetch()){
					    		$salesIdArr[] = $salesRow['saleid']; 
					    	}
					    
				    	$filtersales = array_merge($happyIdArr, $volumeIdArr, $salesIdArr, $checkoutIdArr);
				    	$filterSaleIds = array_unique($filtersales);
				        $selectedDiscountIds = implode(',', $filterSaleIds); 
						 if(empty($selectedDiscountIds) || $selectedDiscountIds == ''){
							$selectedDiscountIds = -1;		    	
					    }
				        $discount_limit = "AND saleid IN ($selectedDiscountIds)";
			    
			}else{

			    	$selectedDiscount = 0;
			        $getSales  = "SELECT p.saleid  FROM sales p, salesdetails q WHERE q.discountType  = '$selectedDiscount' AND p.saleid = q.saleid";
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


		  $getSales  = "SELECT p.saleid  FROM sales p, salesdetails q WHERE q.discountType IN ($selectedDiscount) AND p.saleid = q.saleid";
	    	$getResult =  $pdo3->prepare("$getSales");
	    	$getResult->execute();
	    	while($salesRow = $getResult->fetch()){
	    		$salesIdArr[] = $salesRow['saleid']; 
	    	}
	    	$filterSaleIds = array_unique($salesIdArr);
	        $selectedDiscountIds = implode(',', $filterSaleIds);
	       if(empty($selectedDiscountIds) || $selectedDiscountIds == ''){
					$selectedDiscountIds = -1;		    	
			    }
	    	$discount_limit = "AND saleid IN ($selectedDiscountIds)";
	}
   
	// KONSTANT CODE UPDATE END
	// Go through each saledetail in the period
	// Look up purchaseID's price
	// Quantity * purchase price = dispPrice vs amount
	// Add all dispPrice
	// Add all amount
	// Find difference
	
	// Gifts: quantity != 0 AND amount == 0 (to avoid blank dispenses)

	
	/*$deleteDonationScript = <<<EOD
	
	    $(document).ready(function() {
		    
	  $( function() {
	    $( "#datepicker" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });
	  });
	  $( function() {
	    $( "#datepicker2" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });
	  });

		    
		    
			$('#cloneTable').width($('#mainTable').width());
			
			$('#mainTable').tablesorter({
				usNumberFormat: true,
				headers: {
					3: {
						sorter: "dates"
					},
					7: {
						sorter: "dates"
					}
				}
			}); 
			
EOD;

	$deleteDonationScript .= <<<EOD

		
			
		});
		
var tablesToExcel = (function () {
    var uri = 'data:application/vnd.ms-excel;base64,'
    , template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets>'
    , templateend = '</x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--><meta http-equiv="content-type" content="text/plain; charset=UTF-8"/></head>'
    , body = '<body>'
    , tablevar = '<table>{table'
    , tablevarend = '}</table>'
    , bodyend = '</body></html>'
    , worksheet = '<x:ExcelWorksheet><x:Name>'
    , worksheetend = '</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet>'
    , worksheetvar = '{worksheet'
    , worksheetvarend = '}'
    , base64 = function (s) { return window.btoa(unescape(encodeURIComponent(s))) }
    , format = function (s, c) { return s.replace(/{(\w+)}/g, function (m, p) { return c[p]; }) }
    , wstemplate = ''
    , tabletemplate = '';

    return function (table, name, filename) {
        var tables = table;

        for (var i = 0; i < tables.length; ++i) {
            wstemplate += worksheet + worksheetvar + i + worksheetvarend + worksheetend;
            tabletemplate += tablevar + i + tablevarend;
        }

        var allTemplate = template + wstemplate + templateend;
        var allWorksheet = body + tabletemplate + bodyend;
        var allOfIt = allTemplate + allWorksheet;

        var ctx = {};
        for (var j = 0; j < tables.length; ++j) {
            ctx['worksheet' + j] = name[j];
        }

        for (var k = 0; k < tables.length; ++k) {
            var exceltable;
            if (!tables[k].nodeType) exceltable = document.getElementById(tables[k]);
            ctx['table' + k] = exceltable.innerHTML;
        }

        //document.getElementById("dlink").href = uri + base64(format(template, ctx));
        //document.getElementById("dlink").download = filename;
        //document.getElementById("dlink").click();

        window.location.href = uri + base64(format(allOfIt, ctx));

    }
})();
		
		$(window).resize(function() {
			$('#cloneTable').width($('#mainTable').width());
		});
		
function delete_donation(donationid,amount,userid) {
	if (confirm("{$lang['donation-deleteconfirm']}")) {
				window.location = "uTil/delete-donation.php?donationid=" + donationid + "&amount=" + amount + "&userid=" + userid + "&donscreen";
				}
}
EOD;
			
	pageStart("Regalos y descuentos", NULL, $deleteDonationScript, "pstatus", "statutes", "Regalos y descuentos", $_SESSION['successMessage'], $_SESSION['errorMessage']);	*/

	// Query to look up sales
	// $selectSales = "SELECT s.userid, s.saleid, s.saletime, d.purchaseid, d.quantity, d.amount, d.purchaseid FROM sales s, salesdetails d WHERE s.saleid = d.saleid $timeLimit ORDER by saletime DESC";
		
		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('A1',$lang['global-time']);
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);  		
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('B1', 'Usergroup');
		$objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('C1',$lang['global-member']);
		$objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('D1',$lang['global-category']);
		$objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('E1', $lang['global-product']);
		$objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('F1',$lang['global-quantity']);
		$objPHPExcel->getActiveSheet()->getStyle('F1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('G1',$lang['price']);
		$objPHPExcel->getActiveSheet()->getStyle('G1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('H1',$lang['paid']);
		$objPHPExcel->getActiveSheet()->getStyle('H1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('I1','Discount applied');
		$objPHPExcel->getActiveSheet()->getStyle('I1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('J1','Summary');
		$objPHPExcel->getActiveSheet()->getStyle('J1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('K1',$lang['total-price']);
		$objPHPExcel->getActiveSheet()->getStyle('K1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('L1',$lang['total-paid']);
		$objPHPExcel->getActiveSheet()->getStyle('L1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('M1',$lang['loss']);
		$objPHPExcel->getActiveSheet()->getStyle('M1')->getFont()->setBold(true);
     $selectSales = "SELECT userid, saleid, saletime, quantity, amount, discount FROM sales WHERE 1 $timeLimit $discount_limit $user_limit ORDER by saletime DESC"; 
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
		}
	

	// Determine all saleid's for gifts and for discounts! DO NOT FORGET CHECKOUT DISCOUNTS!!!!
		$pl =0;
		$startIndex = 2;
		while ($sale = $results->fetch()) {
		
		$saleid = $sale['saleid'];
		$discount = $sale['discount'];
		
		$selectSale = "SELECT purchaseid, quantity, amount FROM salesdetails WHERE saleid = $saleid";
		try
		{
			$resultOne = $pdo3->prepare("$selectSale");
			$resultOne->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($saleAnalysis = $resultOne->fetch()) {
			
			$quantity = $saleAnalysis['quantity'];
			$amount = $saleAnalysis['amount'];
			$purchaseid = $saleAnalysis['purchaseid'];
			
			// quantity   2,35
			// amount    10,39
			
			// AMOUNT:
			// hierba     0,00
			// kritikal  10,39
			
			// FULLPRICE:
			// hierba     0,00 * 0,37 = 0,00
			// kritikal   5,25 * 1,98 = 10,39
			
			
			
			$selectPrice = "SELECT salesPrice FROM purchases WHERE purchaseid = $purchaseid";
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
				
			$fullPrice = round(round($salesPrice,2) * round($quantity,2),2);
				
			// Construct saleid arrays
			// Normal
			if ($amount == $fullPrice && $amount != 0) {
				
			// Gifted
			} else if ($quantity > 0 && $amount == 0) {
				
				if(!in_array($saleid, $giftArray)){
					$giftArray[] = $saleid;
				}
				
			// Discounted
			} else if (($fullPrice - $amount) > 0.1) {
				
				if(!in_array($saleid, $discountArray)){
					$discountArray[] = $saleid;
				}
				
			}
			
		}
		
	}
	
	if (isset($giftArray) && isset($discountArray)) {
		
		$combinedArray = array_merge($giftArray,$discountArray);
		
	} else if (isset($giftArray)) {
		
		$combinedArray = $giftArray;
		
	} else if (isset($discountArray)) {
		
		$combinedArray = $discountArray;
		
	}
	
	rsort($combinedArray);
	
	$combinedArray = array_unique($combinedArray);
	// Konstant CODE UPDATE BEGIN
	   $current_date =  date('d-m-Y');
	   $expected_date = "01-01-2019";
	   $dispalyGroup = 0;
		if(strtotime($current_date) > strtotime($expected_date)){
			$dispalyGroup = 1;
		}
	// Konstant CODE UPDATE END	

	// Loop through array and create table value for each
	foreach($combinedArray as $value) {
		// KONSTANT CODE UPDATE BEGIN		
		$selectSales = "SELECT s.saletime, s.userid, s.operatorid, d.purchaseid, d.quantity, d.amount, d.purchaseid FROM sales s, salesdetails d WHERE s.saleid = d.saleid AND s.saleid = $value";
		// KONSTANT CODE UPDATE END
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
			$formattedDate = date("d-m-Y H:i:s", strtotime($row['saletime']."+$offsetSec seconds"));
			// KONSTANT CODE UPDATE BEGIN
			$operatorId = $row['operatorid'];
			// KONSTANT CODE UPDATE END
			$saleid = $value;
			$saleidOne = $saleid;
			$quantity = $row['quantity'];
			$amount = $row['amount'];
			$purchaseid = $row['purchaseid'];
			$userid = $row['userid'];
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
			
			
			
		
		// KONSTANT CODE UPDATE BEGIN	
		$selectoneSale = "SELECT d.category, d.productid, d.quantity, d.amount, d.purchaseid, d.discountType, d.discountPercentage, d.happyhourDiscount, d.volumeDiscount, s.discount FROM salesdetails d, sales s WHERE d.saleid = $saleid and s.saleid = d.saleid";
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
			$onesaleResult5 = $pdo3->prepare("$selectoneSale");
			$onesaleResult5->execute();
			$onesaleResult6 = $pdo3->prepare("$selectoneSale");
			$onesaleResult6->execute();
			$onesaleResult7 = $pdo3->prepare("$selectoneSale");
			$onesaleResult7->execute();
			$onesaleResult8 = $pdo3->prepare("$selectoneSale");
			$onesaleResult8->execute();
			// KONSTANT CODE UPDATE BEGIN		
			$onesaleResult10 = $pdo3->prepare("$selectoneSale");
			$onesaleResult10->execute();			
			$onesaleResult11 = $pdo3->prepare("$selectoneSale");
			$onesaleResult11->execute();
			// KONSTANT CODE UPDATE END
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
	  /* 
		$detailedLosses .= "<tr>
  	   <td class='clickableRow' href='dispense.php?saleid={$saleidOne}'>";*/
  	   
  	   		$o = 0;
  	   		while ($onesale = $onesaleResult6->fetch()) {
	  	   		
	  	   	/*	if ($o == 0) {
					$detailedLosses .= "$formattedDate<br/>";
				} else {
					$detailedLosses .= "<span class='white'>$formattedDate</span><br/>";
				}
				   
				$o++;*/
			}
			// KONSTANT CODE UPDATE BEGIN
			if($dispalyGroup == 1){
			/*	$detailedLosses .= "</td>
		  	   <td class='clickableRow' href='dispense.php?saleid={$saleidOne}'>";

					$detailedLosses .= "$operatorName<br/>";*/
			}
			// KONSTANT CODE UPDATE END
			/*$detailedLosses .= "</td>
  	   <td class='clickableRow' href='dispense.php?saleid={$saleidOne}'>";*/
  	   
  	   		$p = 0;
  	   		while ($onesale = $onesaleResult7->fetch()) {
	  	   		/*if ($p == 0) {
					$detailedLosses .= "#$memberno - $first_name<br/>";

				} else {
					$detailedLosses .= "<span class='white'>#$memberno - $first_name</span><br/>";
				}*/
				    
				$p++;
			}
/*$detailedLosses .= "
  	   </td>
  	   <td class='clickableRow' href='dispense.php?saleid={$saleidOne}'>";
*/		
  	   $A1 = 0;
  	   while ($onesale = $onesaleResult->fetch()) {
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
				
			//$detailedLosses .= $category . "<br />";
			$catarr[$pl][$a1] = $category;
			$a1++;
		}
		$catex = implode(",", $catarr[$pl]);
		//$detailedLosses .= "</td><td class='clickableRow' href='dispense.php?saleid={$saleidOne}'>";
		$b1 =0;
		while ($onesale = $onesaleResult2->fetch()) {
			
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


			//$detailedLosses .= $name . "<br />";
			$namearr[$pl][$b1] = $name;
			$b1++;
		}
		$nameex = implode(",", $namearr[$pl]);
		//$detailedLosses .= "</td><td class='clickableRow right' href='dispense.php?saleid={$saleidOne}'>";
		$c1=0;
		while ($onesale = $onesaleResult3->fetch()) {
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
				//$detailedLosses .= number_format($onesale['quantity'],2) . " g<br />";
				$qunatarr[$p1][$c1] = number_format($onesale['quantity'],2) . " g";
			} else {
				//$detailedLosses .= number_format($onesale['quantity'],2) . " u<br />";
				$qunatarr[$p1][$c1] = number_format($onesale['quantity'],2) . " u";
			}
			$c1++;
		}
		$quantex = implode(",", $qunatarr[$p1]);
		//$detailedLosses .= "</td><td class='clickableRow right' href='dispense.php?saleid={$saleidOne}'>";
		
		
		$normalPrice = 0;
		$fullNormalPrice = 0;
		$d1 = 0;
		while ($onesale = $onesaleResult8->fetch()) {
			
			$selectPrice = "SELECT salesPrice FROM purchases WHERE purchaseid = {$onesale['purchaseid']}";
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
			
			if (($normalPrice - $onesale['amount']) > 0.05) {
				
				//$detailedLosses .= number_format($normalPrice,2) . " <span class='smallerfont'>$_SESSION['currencyoperator']</span><br />";
				$fullNormalPrice = $fullNormalPrice + $normalPrice;
				$amountarr[$p1][$d1] = number_format($normalPrice,2)." ".$_SESSION['currencyoperator'];
				
			} else {
				
				//$detailedLosses .= number_format($onesale['amount'],2) . " <span class='smallerfont'>".$_SESSION['currencyoperator']/span><br />";
				$amountarr[$p1][$d1] = number_format($onesale['amount'],2)." ".$_SESSION['currencyoperator'];
				$fullNormalPrice = $fullNormalPrice + $onesale['amount'];
				
			}
			
			
			
			
		}
		$amountex = implode(",", $amountarr[$p1]);
		//$detailedLosses .= "</td><td class='clickableRow right' href='dispense.php?saleid={$saleidOne}'>";
		
		$fullPaidPrice = 0;
		$e1 =0;
		while ($onesale = $onesaleResult4->fetch()) {
			
			//$detailedLosses .= number_format($onesale['amount'],2) . " <span class='smallerfont'>$_SESSION['currencyoperator']</span><br />";
			$amountpaidarr[$pl][$e1] =  number_format($onesale['amount'],2)." ".$_SESSION['currencyoperator'];
			$fullPaidPrice = $fullPaidPrice + $onesale['amount'];
			
				$selectPrice = "SELECT salesPrice FROM purchases WHERE purchaseid = {$onesale['purchaseid']}";
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
			
			if ($onesale['amount'] == 0) {
				
			
				$giftedLost = number_format($giftedLost + $normalPrice,2);
				
			} else if (($normalPrice - $onesale['amount']) > 0.05) {
				
				$reducedLost = $reducedLost + $normalPrice - $onesale['amount'];
				
				// normal price - paid price
				
			}
			
			$e1++;
			
		}
		$amountpaidex = implode(",",$amountpaidarr[$pl]);
		//$detailedLosses .= "</td>";
		// KONSTANT CODE UPDATE BEGIN
	    //$detailedLosses .= "<td class='clickableRow' href='dispense.php?saleid={$saleidOne}'>";
	    $kk = 0;
	    while ($onesale = $onesaleResult10->fetch()) {
	    	$discountName = '';
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
	    	$happyhourDiscount = $onesale['happyhourDiscount'];
	    	$volumeDiscount = $onesale['volumeDiscount'];
	    	$checkDiscount = '';
	         $checkoutValue = $onesale['discount'];
	    	if($checkoutValue != '0.00'){
	    		if($kk == 0){
	    			//$detailedLosses .= "(Checkout Disocunt) <br>";
	    			$discounarr[$pl][$kk] = "(Checkout Disocunt)";
	    		}
	    		
	    	}
	    	if($happyhourDiscount != '0.00' && $volumeDiscount != '0.00'){
	    		if($discountType == 3){
	    			$discountName2 = "";
	    		}else{
	    			$discountName2 = " + $discountName";
	    		}
	    		//$detailedLosses .= "Happy Hour + Volume Discount$discountName2<br>";
	    		$discounarr[$pl][$kk] = "Happy Hour + Volume Discount$discountName2";
	    	}else{
	    		//$detailedLosses .= "$discountName<br>";
	    		$discounarr[$pl][$kk] = "$discountName";
	    	}
		$kk++;
		}
		$discountex = implode(",", $discounarr[$pl]);
	  //  $detailedLosses .= "</td>";
	  // 	$detailedLosses .= "<td class='clickableRow' href='dispense.php?saleid={$saleidOne}'>";
	   	$pp =0;
	    while ($onesale22 = $onesaleResult11->fetch()) {
			    $selectPrice = "SELECT salesPrice FROM purchases WHERE purchaseid = {$onesale22['purchaseid']}";
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
				$discountType = $onesale22['discountType'];
				$salesPrice = $row['salesPrice'];
			    $thisQuantity = $onesale22['quantity'];
	    		$normalPrice = round($salesPrice * $thisQuantity,2);
	    		$paid_price =  number_format($onesale22['amount'],2);
	    	    $loss_price = $normalPrice - $paid_price;
	    		$discountPer = $onesale22['discountPercentage'];
	    		$checkoutValue = $onesale22['discount'];
	    		if($happyhourDiscount != '0.00' && $volumeDiscount != '0.00'){
	    			$happyhourDiscount = $onesale22['happyhourDiscount'];
	    			$volumeDiscount = $onesale22['volumeDiscount'];
	    		    $volumeAmount = number_format(($normalPrice * $volumeDiscount) / 100,2);
	    		    $happyAmount = number_format(($normalPrice * $happyhourDiscount) / 100,2);
	    		    $volumeLoss = $volumeAmount; 
	    			$loss_price = $volumeLoss;
/*	    			if($discountPer != '0.00'){
	    				$lossAmount = number_format(($volumeAmount * $discountPer) / 100,2);
	    				$loss_price = $volumeLoss. " + ".$lossAmount;
	    			}*/
	    		}
		    	if($checkoutValue != '0.00'){
		    		if($pp == 0){
		    			//$detailedLosses .= "($checkoutValue %) <br>";
		    			$loassarr[$pl][$pp] = "($checkoutValue %)";
		    		}
		    	}
	    		//$detailedLosses .= "$loss_price $_SESSION['currencyoperator']<br>";
	    		$loassarr[$pl][$pp] =  $loss_price." ".$_SESSION['currencyoperator'];
		$pp++; }
		$losseex = implod(",", $loassarr[$p1]);
	   // $detailedLosses .= "</td>";
	     // KONSTANT CODE UPDATE END
		$amount = number_format($amount,2);
		$fullNormalPrice = number_format($fullNormalPrice,2);
		$fullPaidPrice = number_format($fullPaidPrice,2);
		$fullLoss = number_format($fullNormalPrice - $fullPaidPrice,2);

		$quantity = number_format($quantity,2);
		$fullPrice = number_format($fullPrice,2);
		$loss = number_format($fullPrice - $amount,2);
						
			/*	$detailedLosses .= "
				<td class='clickableRow right' href='dispense.php?saleid={$saleidOne}'><strong>{$fullNormalPrice} $_SESSION['currencyoperator']</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleidOne}'><strong>{$fullPaidPrice} $_SESSION['currencyoperator']</strong></td>
				<td class='clickableRow right' href='dispense.php?saleid={$saleidOne}'><strong>{$fullLoss} $_SESSION['currencyoperator']</strong></td>
				";	*/
			$objPHPExcel->getActiveSheet()
		                ->setCellValue('A'.$startIndex, $formattedDate);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B'.$startIndex, $operatorName);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('C'.$startIndex, "#".$memberno ."-". $first_name);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('D'.$startIndex, $catex); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('E'.$startIndex, $nameex); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('F'.$startIndex, $quantex); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('G'.$startIndex, $amountex); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('H'.$startIndex, $amountpaidex); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('I'.$startIndex, $discountex); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('J'.$startIndex, $losseex); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('K'.$startIndex, $fullNormalPrice." ".$_SESSION['currencyoperator']); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('L'.$startIndex, $fullPaidPrice." ".$_SESSION['currencyoperator']); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('M'.$startIndex, $fullLoss." ".$_SESSION['currencyoperator']); 
           
				$pl++;
				$startIndex++;
	}


	    ob_end_clean();
	    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	    //header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	    header('Content-type: application/vnd.ms-excel');
	    header('Content-Disposition: attachment;filename=product-losses.xlsx');
	    header("Content-Type: application/download");
	    //header('Cache-Control: max-age = 0');
	    $objWriter->save('php://output');
	    header("location:dispenses.php");  // KONSTANT CODE UPDATE END 
	    die;

?>
