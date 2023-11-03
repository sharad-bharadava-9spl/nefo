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
	
	$domain = $_SESSION['domain'];
		$domain = $_SESSION['domain'];

	// Get the user ID
	if (isset($_GET['userSelect']) && $_GET['userSelect'] !='') {
		$user_id = $_GET['userSelect'];
	} else if (isset($_GET['user_id']) && $_GET['user_id'] != '') {
		$user_id = $_GET['user_id'];
	}
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
		            ->setCellValue('D1',$lang['global-quantity']);
		$objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('E1',$_SESSION['currencyoperator']);
		$objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true); 
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('F1','Tot. g');
		$objPHPExcel->getActiveSheet()->getStyle('F1')->getFont()->setBold(true); 
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('G1','Tot. g');
		$objPHPExcel->getActiveSheet()->getStyle('G1')->getFont()->setBold(true); 
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('H1','Tot. '.$_SESSION['currencyoperator']);
		$objPHPExcel->getActiveSheet()->getStyle('H1')->getFont()->setBold(true); 
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('I1',$lang['dispense-oldcredit']);
		$objPHPExcel->getActiveSheet()->getStyle('I1')->getFont()->setBold(true); 
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('J1',$lang['dispense-newcredit']);
		$objPHPExcel->getActiveSheet()->getStyle('J1')->getFont()->setBold(true); 
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('K1',$lang['global-comment']);
		$objPHPExcel->getActiveSheet()->getStyle('K1')->getFont()->setBold(true); 
		
?>

	  
	  <?php
	  
	  
	// Pagination
	if (isset($_GET['pageno']) && $_GET['pageno'] != '') {
    	$pageno = $_GET['pageno'];
    } else {
    	$pageno = 1;
    }
    	$no_of_records_per_page = 20;
	
    $offset = ($pageno-1) * $no_of_records_per_page; 

    $total_pages_sql = "SELECT COUNT(saleid) FROM sales WHERE userid = $user_id";
	$rowCount1 = $pdo3->query("$total_pages_sql")->fetchColumn();
    $total_pages_sql = "SELECT COUNT(saleid) FROM b_sales WHERE userid = $user_id";
	$rowCount2 = $pdo3->query("$total_pages_sql")->fetchColumn();
    $total_pages_sql = "SELECT COUNT(donationid) FROM donations WHERE userid = $user_id";
	$rowCount3 = $pdo3->query("$total_pages_sql")->fetchColumn();
    $total_pages_sql = "SELECT COUNT(paymentid) FROM memberpayments WHERE userid = $user_id";
	$rowCount4 = $pdo3->query("$total_pages_sql")->fetchColumn();
	
	$rowCount = $rowCount1 + $rowCount2 + $rowCount3 + $rowCount4;
    
    $total_pages = ceil($rowCount / $no_of_records_per_page);

	// Query to look up individual sales
	$selectSales = "SELECT saleid, saletime, userid, amount, amountpaid, quantity, units, adminComment, creditBefore, creditAfter, 'sale' as Type, '' AS donatedTo FROM sales WHERE userid = $user_id UNION ALL SELECT saleid, saletime, userid, amount, '' AS amountpaid, '' AS quantity, unitsTot AS units, adminComment, creditBefore, creditAfter, 'bar' as Type, '' AS donatedTo FROM b_sales WHERE userid = $user_id UNION ALL SELECT donationid, donationTime as saletime, userid, amount, '' AS amountpaid, '' AS quantity, '' AS units, comment AS adminComment, creditBefore, creditAfter, 'donation' as Type, donatedTo AS donatedTo FROM donations WHERE userid = $user_id UNION ALL SELECT paymentid, paymentdate as saletime, userid, amountPaid AS amount, '' AS amountpaid, '' AS quantity, '' AS units, comment AS adminComment, creditBefore, creditAfter, 'memberpayment' as Type, paidTo AS donatedTo FROM memberpayments WHERE userid = $user_id ORDER by saletime DESC LIMIT $offset, $no_of_records_per_page";
	
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
	$startIndex = 2;
	$pr = 0;
	while ($sale = $results->fetch()) {
	
		$formattedDate = date("d M H:i", strtotime($sale['saletime'] . "+$offsetSec seconds"));
		$saleid = $sale['saleid'];
		$userid = $sale['userid'];
		$quantity = $sale['quantity'];
		$units = $sale['units'];
		$credit = $sale['creditBefore'];
		$newcredit = $sale['creditAfter'];
		$type = $sale['Type'];
		$donatedTo = $sale['donatedTo'];
		$amount = $sale['amount'];
		$amountpaid = $sale['amountpaid'];
		$adminComment = $sale['adminComment'];
		
	if ($adminComment != '') {
		
		$commentRead = "
		                <img src='images/comments.png' id='comment$saleid' /><div id='helpBox$saleid' class='helpBox'>$adminComment</div>
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
		
		
		
		// Make unpaid rows red and donation rows green:
		// Make unpaid rows red:
		
		
// Separate methodologies and row displays (linkage) for donations vs sales. Change Credit first:
		if ($type == 'donation' && $donatedTo == 3) {
			/*echo "
  	   <td class='clickableRow' href='donation-management.php?userid={$userid}'>{$formattedDate}</td>
  	   <td class='clickableRow' href='donation-management.php?userid={$userid}' colspan='6'>{$lang['changed-credit']}</td>
		<td class='clickableRow right' href='donation-management.php?userid={$userid}'><strong>{$amount} <span class='smallerfont'>$_SESSION['currencyoperator']</span></strong></td>
		<td class='clickableRow right' href='donation-management.php?userid={$userid}'>{$credit} $_SESSION['currencyoperator']</td>
		<td class='clickableRow right' href='donation-management.php?userid={$userid}'>{$newcredit} $_SESSION['currencyoperator']</td>
		<td class='clickableRow right' href='donation-management.php?userid={$userid}'><span class='relativeitem'>{$commentRead}</span></td>
		";*/

			$objPHPExcel->getActiveSheet()
		                ->setCellValue('A'.$startIndex, $formattedDate);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B'.$startIndex, $lang['changed-credit']);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('C'.$startIndex, $amount." ".$_SESSION['currencyoperator']);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('D'.$startIndex, $credit." ".$_SESSION['currencyoperator']); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('E'.$startIndex, $newcredit." ".$_SESSION['currencyoperator']); 
            $objPHPExcel->getActiveSheet()
           				 ->setCellValue('F'.$startIndex, $adminComment);  
           	
		
// Separate methodologies and row displays (linkage) for donations vs sales. Gift credit first
		} else if ($type == 'donation' && $donatedTo == 5) {
			/*echo "
  	   <td class='clickableRow' href='donation-management.php?userid={$userid}'>{$formattedDate}</td>
  	   <td class='clickableRow' href='donation-management.php?userid={$userid}' colspan='6'>{$lang['gift-credit']}</td>
		<td class='clickableRow right' href='donation-management.php?userid={$userid}'><strong>{$amount} <span class='smallerfont'>$_SESSION['currencyoperator']</span></strong></td>
		<td class='clickableRow right' href='donation-management.php?userid={$userid}'>{$credit} $_SESSION['currencyoperator']</td>
		<td class='clickableRow right' href='donation-management.php?userid={$userid}'>{$newcredit} $_SESSION['currencyoperator']</td>
		<td class='clickableRow right' href='donation-management.php?userid={$userid}'><span class='relativeitem'>{$commentRead}</span></td>

		";*/
					$objPHPExcel->getActiveSheet()
		                ->setCellValue('A'.$startIndex, $formattedDate);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B'.$startIndex, $lang['gift-credit']);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('C'.$startIndex, $amount." ".$_SESSION['currencyoperator']);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('D'.$startIndex, $credit." ".$_SESSION['currencyoperator']); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('E'.$startIndex, $newcredit." ".$_SESSION['currencyoperator']); 
            $objPHPExcel->getActiveSheet()
           				 ->setCellValue('F'.$startIndex, $adminComment);  
				
// Separate methodologies and row displays (linkage) for donations vs sales. Donations next:
		} else if ($type == 'donation') {
			/*echo "
  	   <td class='clickableRow' href='donation-management.php?userid={$userid}'>{$formattedDate}</td>
  	   <td class='clickableRow' href='donation-management.php?userid={$userid}' colspan='6'>{$lang['donation-donation']}</td>
		<td class='clickableRow right' href='donation-management.php?userid={$userid}'><strong>{$amount} <span class='smallerfont'>$_SESSION['currencyoperator']</span></strong></td>
		<td class='clickableRow right' href='donation-management.php?userid={$userid}'>{$credit} $_SESSION['currencyoperator']</td>
		<td class='clickableRow right' href='donation-management.php?userid={$userid}'>{$newcredit} $_SESSION['currencyoperator']</td>
		<td class='clickableRow right' href='donation-management.php?userid={$userid}'><span class='relativeitem'>{$commentRead}</span></td>
		";
*/
			$objPHPExcel->getActiveSheet()
		                ->setCellValue('A'.$startIndex, $formattedDate);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B'.$startIndex, $lang['donation-donation']);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('C'.$startIndex, $amount." ".$_SESSION['currencyoperator']);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('D'.$startIndex, $credit." ".$_SESSION['currencyoperator']); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('E'.$startIndex, $newcredit." ".$_SESSION['currencyoperator']); 
            $objPHPExcel->getActiveSheet()
           				 ->setCellValue('F'.$startIndex, $adminComment);  
				
		} else if ($type == 'memberpayment') {
				
			if ($donatedTo == '2') {
				$paidTo = $lang['card'];
			} else if ($donatedTo == '3') {
				$paidTo = $lang['global-credit'];
			} else if ($donatedTo == '4') {
				$paidTo = "CashDro";
			} else if ($donatedTo == '5') {
				$paidTo = $lang['changed-expiry'];
			} else {
				$paidTo = $lang['cash'];
			}
		
			/*echo "
  	   <td class='clickableRow' href='pay-membership.php?user_id=$user_id'>{$formattedDate}</td>
  	   <td class='clickableRow' colspan='6' href='pay-membership.php?user_id=$user_id'>{$lang['membership-payments']}: $paidTo</td>
		<td class='clickableRow right' href='pay-membership.php?user_id=$user_id'><strong>{$amount} <span class='smallerfont'>$_SESSION['currencyoperator']</span></strong></td>
		<td class='clickableRow right' href='pay-membership.php?user_id=$user_id'>$credit</td>
		<td class='clickableRow right' href='pay-membership.php?user_id=$user_id'>$newcredit</td>
		<td class='clickableRow right' href='pay-membership.php?user_id=$user_id'><span class='relativeitem'>{$commentRead}</span></td>";*/
				$objPHPExcel->getActiveSheet()
		                ->setCellValue('A'.$startIndex, $formattedDate);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B'.$startIndex, $lang['membership-payments'].":". $paidTo);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('C'.$startIndex, $amount." ".$_SESSION['currencyoperator']);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('D'.$startIndex, $credit." ".$_SESSION['currencyoperator']); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('E'.$startIndex, $newcredit." ".$_SESSION['currencyoperator']); 
            $objPHPExcel->getActiveSheet()
           				 ->setCellValue('F'.$startIndex, $adminComment);  
// Separate methodologies and row displays (linkage) for donations vs sales. Sales next:
			} else if ($type == 'sale') {
				
		$selectoneSale = "SELECT d.category, d.productid, d.quantity, d.amount FROM salesdetails d, sales s WHERE d.saleid = {$saleid} and s.saleid = d.saleid";
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
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
				   
		/*echo "
  	   <td class='clickableRow' href='dispense.php?saleid={$saleid}'>{$formattedDate}</td>
  	   <td class='clickableRow' href='dispense.php?saleid={$saleid}'>";*/
      $a = 0;
		while ($onesale = $onesaleResult->fetch()) {
			if ($onesale['category'] == 1) {
				$category = 'Flower';
			} else if ($onesale['category'] == 2) {
				$category = 'Extract';
			} else {
				
				// Query to look for category
				$categoryDetails = "SELECT name, type FROM categories WHERE id = {$onesale['category']}";
				
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
					$catType = $row['type'];
			}
				
			//echo $category . "<br />";
			$catarr[$pr][$a] = $category;
			$a++;
		}
		$catex = implode(',', $catarr[$pr]);
		//echo "</td><td class='clickableRow' href='dispense.php?saleid={$saleid}'>";
		$b=0;
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
	} else if ($onesale['category'] > 2) {
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
			$namarr[$pr][$b] = $name;
			$b++;
		}
		$nameex = implode(",", $namarr[$pr]);
		//echo "</td><td class='clickableRow right' href='dispense.php?saleid={$saleid}'>";
		$c =0;
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
			
				$row = $result->fetch();
					$category = $row['name'];
					$type = $row['type'];
			}

			if ($onesale['category'] < 3 || $type == 1) {
				echo number_format($onesale['quantity'],2) . " g<br />";
				$quantarr[$pr][$c] = number_format($onesale['quantity'],2) . " g";
			} else {
				echo number_format($onesale['quantity'],2) . " u<br />";
				$quantarr[$pr][$c] = number_format($onesale['quantity'],2) . " u";
			}		
			$c++;
		}
		$quantex = implode(",", $quantarr[$pr]);
		//echo "</td><td class='clickableRow right' href='dispense.php?saleid={$saleid}'>";
		$d=0;
		while ($onesale = $onesaleResult4->fetch()) {
			echo number_format($onesale['amount'],2) . " <span class='smallerfont'>".$_SESSION['currencyoperator']."</span><br />";
			$amountarr[$pr][$d] = number_format($onesale['amount'],2)." ".$_SESSION['currencyoperator'];
			$d++;
		}
		$amountex = implode(",", $amountarr[$pr]);
		//echo "</td>";
		
		$quantity = number_format($quantity,2);
		$amount = number_format($amount,2);
		$units = number_format($units,1);
		/*echo "
		<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$quantity} g</strong></td>
		<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$units} u</strong></td>
		<td class='clickableRow right' href='dispense.php?saleid={$saleid}'><strong>{$amount} <span class='smallerfont'>$_SESSION['currencyoperator']</span></strong></td>
		<td class='clickableRow right' href='dispense.php?saleid={$saleid}'>{$credit} $_SESSION['currencyoperator']</td>
		<td class='clickableRow right' href='dispense.php?saleid={$saleid}'>{$newcredit} $_SESSION['currencyoperator']</td>
		<td class='clickableRow right' href='dispense.php?userid={$saleid}'><span class='relativeitem'>{$commentRead}</span></td>
		";*/
			$objPHPExcel->getActiveSheet()
		                ->setCellValue('A'.$startIndex, $formattedDate);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B'.$startIndex, $catex);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('C'.$startIndex, $nameex);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('D'.$startIndex, $quantex); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('E'.$startIndex, $amountex); 
            $objPHPExcel->getActiveSheet()
           				 ->setCellValue('F'.$startIndex, $quantity." g");  
           	$objPHPExcel->getActiveSheet()
           				 ->setCellValue('G'.$startIndex, $units." u");  
           	$objPHPExcel->getActiveSheet()
           				 ->setCellValue('H'.$startIndex, $amount." ".$_SESSION['currencyoperator']);  
           $objPHPExcel->getActiveSheet()
           				 ->setCellValue('I'.$startIndex, $credit." ".$_SESSION['currencyoperator']); 
           $objPHPExcel->getActiveSheet()
           				 ->setCellValue('J'.$startIndex, $newcredit." ".$_SESSION['currencyoperator']);  
           $objPHPExcel->getActiveSheet()
           				 ->setCellValue('K'.$startIndex, $adminComment); 
		
		// And finally, bar
	} else {
		
		$selectoneSale = "SELECT d.category, d.productid, d.quantity, d.amount FROM b_salesdetails d, b_sales s WHERE d.saleid = {$saleid} and s.saleid = d.saleid";
		try
		{
			$onesaleResult6 = $pdo3->prepare("$selectoneSale");
			$onesaleResult6->execute();
			$onesaleResult7 = $pdo3->prepare("$selectoneSale");
			$onesaleResult7->execute();
			$onesaleResult8 = $pdo3->prepare("$selectoneSale");
			$onesaleResult8->execute();
			$onesaleResult9 = $pdo3->prepare("$selectoneSale");
			$onesaleResult9->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	  /* 
		echo "
  	   <td class='clickableRow' href='bar-sale.php?saleid={$saleid}'>{$formattedDate}</td>
  	   <td class='clickableRow' href='bar-sale.php?saleid={$saleid}'>";*/
  	   $a1 = 0;
		while ($onesale = $onesaleResult6->fetch()) {
			
			// Look up bar category
			$selectBarCat = "SELECT name FROM b_categories WHERE id = {$onesale['category']}";
		
			try
			{
				$result = $pdo3->prepare("$selectBarCat");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$barRow = $result->fetch();
		   		$category = $barRow['name'];
			
			//echo $category . "<br />";
			$catarr1[$pr][$a1] = $category;
			$a1++;
		}
		$catex1 = implode(",", $catarr1[$pr]);
		//echo "</td><td class='clickableRow' href='bar-sale.php?saleid={$saleid}'>";
		$b1 = 0;
		while ($onesale = $onesaleResult7->fetch()) {
			
			$productid = $onesale['productid'];
			
		$selectProduct = "SELECT name FROM b_products WHERE productid = $productid";
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
			$name = $row['name'];


			//echo $name . "<br />";
			$namearr1[$pr][$b1] =  $name; 
			$b1++;
		}
		$nameex1 = implode(",", $namearr1[$pr]);
		//echo "</td><td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'>";
		$c1 = 0;
		while ($onesale = $onesaleResult8->fetch()) {
			//echo number_format($onesale['quantity'],0) . "<br />";
			$quantarr1[$pr][$c1] =    number_format($onesale['quantity'],0);
			$c1++;
		}
		$quantex1 = implode(",", $quantarr1[$pr][$c1]);
		//echo "</td><td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'>";
		$d1 = 0;
		while ($onesale = $onesaleResult9->fetch()) {
			//echo number_format($onesale['amount'],2) . " <span class='smallerfont'>$_SESSION['currencyoperator']</span><br />";
			$amountarr1[$pr][$d1] = number_format($onesale['amount'],2)." ".$_SESSION['currencyoperator'];
			$d1++;
		}
		$amountex1 = implode(",", $amountarr[$pr]);
		//echo "</td>";
		
		$quantity = number_format($quantity,2);
		$amount = number_format($amount,2);
		$units = number_format($units,1);
		/*echo "
		<td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'></td>
		<td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'><strong>{$units} u</strong></td>
		<td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'><strong>{$amount} <span class='smallerfont'>$_SESSION['currencyoperator']</span></strong></td>
		<td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'>{$credit} $_SESSION['currencyoperator']</td>
		<td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'>{$newcredit} $_SESSION['currencyoperator']</td>
		<td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'><span class='relativeitem'>{$commentRead}</span></td>
		";*/
		$objPHPExcel->getActiveSheet()
		                ->setCellValue('A'.$startIndex, $formattedDate);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('B'.$startIndex, $catex1);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('C'.$startIndex, $nameex1);
		    $objPHPExcel->getActiveSheet()
		                ->setCellValue('D'.$startIndex, $quantex1); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('E'.$startIndex, $amountex1); 
            $objPHPExcel->getActiveSheet()
           				 ->setCellValue('F'.$startIndex, "");  
           	$objPHPExcel->getActiveSheet()
           				 ->setCellValue('G'.$startIndex, $units." u");  
           	$objPHPExcel->getActiveSheet()
           				 ->setCellValue('H'.$startIndex, $amount." ".$_SESSION['currencyoperator']);  
           $objPHPExcel->getActiveSheet()
           				 ->setCellValue('I'.$startIndex, $credit." ".$_SESSION['currencyoperator']); 
           $objPHPExcel->getActiveSheet()
           				 ->setCellValue('J'.$startIndex, $newcredit." ".$_SESSION['currencyoperator']);    
           $objPHPExcel->getActiveSheet()
           				 ->setCellValue('K'.$startIndex, $adminComment); 
		
	}

		
	$startIndex++;
	$pr++;
}

	ob_end_clean();
			    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			    //header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			    header('Content-type: application/vnd.ms-excel');
			    header('Content-Disposition: attachment;filename=profile.xlsx');
			    header("Content-Type: application/download");
			    //header('Cache-Control: max-age = 0');
			    $objWriter->save('php://output');
    			die;
?>
