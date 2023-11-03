<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$domain = $_SESSION['domain'];
	
	if ($domain == 'choko') {
		$ftcolor = 'fff';
	} else {
		$ftcolor = '444';
	}
	
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


 
	
	// If no closing ID is set, we display the list of closing dates
	// Check if new Filter value was submitted, and assign query variable accordingly
	
	// Check if 'entre fechas' was utilised
	if (isset($_GET['untilDate']) && $_GET['untilDate'] != '') {
		
		$limitVar = "";
		
		$openingTime = date("Y-m-d", strtotime($_GET['fromDate']));
		$closingTime = date("Y-m-d", strtotime($_GET['untilDate']));
		
		$openingTimeView = date("d-m-Y", strtotime($_GET['fromDate']));
		$closingTimeView = date("d-m-Y", strtotime($_GET['untilDate']));
		
		$monthDisp = $openingTimeView . " - " . $closingTimeView;
			
	} else {
		
		$openingTime = date("Y-m-1", strtotime(date("Y-m-d")));
		$closingTime = date("Y-m-t", strtotime(date("Y-m-d")));
		
		$monthDisp = date("F Y", strtotime($closingTime));

		
	}
	
		// Look up todays donations
		$selectDonations = "SELECT SUM(amount) from donations WHERE donatedTo <> 3 AND donationTime BETWEEN '$openingTime' AND '$closingTime'";
		try
		{
			$result = $pdo3->prepare("$selectDonations");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$donations = $row['SUM(amount)'];
				
		$selectDonations = "SELECT SUM(amount) from donations WHERE (donatedTo = 2) AND donationTime BETWEEN '$openingTime' AND '$closingTime'";
		try
		{
			$result = $pdo3->prepare("$selectDonations");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$donationsbank = $row['SUM(amount)'];
			
		$selectDonations = "SELECT SUM(amount) from donations WHERE (donatedTo < 2 OR donatedTo = 4) AND donationTime BETWEEN '$openingTime' AND '$closingTime'";
		try
		{
			$result = $pdo3->prepare("$selectDonations");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$donationscash = $row['SUM(amount)'];
			
		// Look up todays membership fees
		$selectDonations = "SELECT SUM(amountPaid) from memberpayments WHERE paymentdate BETWEEN '$openingTime' AND '$closingTime'";
		try
		{
			$result = $pdo3->prepare("$selectDonations");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$membershipFees = $row['SUM(amountPaid)'];
			
		// Look up todays membership fees
		$selectDonations = "SELECT SUM(amountPaid) from memberpayments WHERE (paidTo = 2) AND paymentdate BETWEEN '$openingTime' AND '$closingTime'";
		try
		{
			$result = $pdo3->prepare("$selectDonations");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$membershipFeesbank = $row['SUM(amountPaid)'];
			
		// Look up todays membership fees
		$selectDonations = "SELECT SUM(amountPaid) from memberpayments WHERE (paidTo < 2 OR paidTo = 4) AND paymentdate BETWEEN '$openingTime' AND '$closingTime'";
		try
		{
			$result = $pdo3->prepare("$selectDonations");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$membershipFeescash = $row['SUM(amountPaid)'];
			
		// Look up todays dispenses
		$selectSalesFlower = "SELECT SUM(d.amount), SUM(d.realQuantity), SUM(d.quantity), SUM(s.units) from sales s, salesdetails d WHERE s.saleid = d.saleid AND s.saletime BETWEEN '$openingTime' AND '$closingTime'";
		try
		{
			$result = $pdo3->prepare("$selectSalesFlower");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$sales = $row['SUM(d.amount)'];
			$realQuantity = $row['SUM(d.realQuantity)'];
			$quantity = $row['SUM(d.quantity)'];
					
		// Look up todays unit dispenses
		$selectSalesFlower = "SELECT SUM(units) from sales WHERE saletime BETWEEN '$openingTime' AND '$closingTime'";
		try
		{
			$result = $pdo3->prepare("$selectSalesFlower");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$units = $row['SUM(units)'];
			$quantity = $quantity - $units;
			$realQuantityTot = $realQuantity - $units;
			
		// Look up todays dispensary sales
		$selectSalesFlower = "SELECT SUM(d.amount) from sales s, salesdetails d WHERE s.saleid = d.saleid AND s.saletime BETWEEN '$openingTime' AND '$closingTime' AND direct = 2";
		try
		{
			$result = $pdo3->prepare("$selectSalesFlower");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$salesbank = $row['SUM(d.amount)'];
					
		// Look up todays dispensary sales
		$selectSalesFlower = "SELECT SUM(d.amount) from sales s, salesdetails d WHERE s.saleid = d.saleid AND s.saletime BETWEEN '$openingTime' AND '$closingTime' AND direct < 2";
		try
		{
			$result = $pdo3->prepare("$selectSalesFlower");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$salescash = $row['SUM(d.amount)'];
			
		// Look up todays bar sales
		$selectSalesFlower = "SELECT SUM(d.amount) from b_sales s, b_salesdetails d WHERE s.saleid = d.saleid AND s.saletime BETWEEN '$openingTime' AND '$closingTime' AND direct = 2";
		try
		{
			$result = $pdo3->prepare("$selectSalesFlower");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$barsalesbank = $row['SUM(d.amount)'];
					
		// Look up todays bar sales
		$selectSalesFlower = "SELECT SUM(d.amount) from b_sales s, b_salesdetails d WHERE s.saleid = d.saleid AND s.saletime BETWEEN '$openingTime' AND '$closingTime' AND direct < 2";
		try
		{
			$result = $pdo3->prepare("$selectSalesFlower");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$barsalescash = $row['SUM(d.amount)'];
			
		
		// Look up closing data from last closing of month
		$selectExpenses = "SELECT closingid FROM closing WHERE closingtime BETWEEN '$openingTime' AND '$closingTime' ORDER BY closingtime DESC LIMIT 1";
		try
		{
			$result = $pdo3->prepare("$selectExpenses");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$closingid = $row['closingid'];
			
		// Look up expenses till
		$selectExpenses = "SELECT SUM(amount) FROM expenses WHERE moneysource = 1 AND DATE(registertime) BETWEEN DATE('$openingTime') AND DATE('$closingTime')";
		try
		{
			$result = $pdo3->prepare("$selectExpenses");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$expensesTill = $row['SUM(amount)'];
			

		
		// Look up expenses bank
		$selectExpenses = "SELECT SUM(amount) FROM expenses WHERE moneysource = 2 AND DATE(registertime) BETWEEN DATE('$openingTime') AND DATE('$closingTime')";
		try
		{
			$result = $pdo3->prepare("$selectExpenses");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$expensesBank = $row['SUM(amount)'];
		
			
		// Look up closing data from last closing of month
		$selectExpenses = "SELECT totCredit FROM closing WHERE closingid = '$closingid'";
		try
		{
			$result = $pdo3->prepare("$selectExpenses");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$totCredit = $row['totCredit'];
			
		if ($totCredit != 0) {
			$totCredit = number_format($row['totCredit'],2) . " ".$_SESSION['currencyoperator'];
		} else {
			$totCredit = '';
		}
		
		// Look up dispensary purchases
		$selectPurchases = "SELECT purchaseid, category, productid, purchaseDate, purchasePrice, purchaseQuantity, realQuantity, salesPrice FROM purchases WHERE DATE(purchaseDate) BETWEEN DATE('$openingTime') AND DATE('$closingTime')";
		try
		{
			$pur_disp_results = $pdo3->prepare("$selectPurchases");
			$pur_disp_results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	

		
					
		// Look up dispensary reloads
		$selectReloads = "SELECT SUM(price) FROM productmovements WHERE movementTypeid = 1 AND DATE(movementtime) BETWEEN DATE('$openingTime') AND DATE('$closingTime')";
		try
		{
			$disp_reload_results = $pdo3->prepare("$selectReloads");
			$disp_reload_results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $disp_reload_results->fetch();
			$reloadsDispensary = $row['SUM(price)'];
			
		$selectReloads = "SELECT movementtime, purchaseid, quantity, price, paid FROM productmovements WHERE movementTypeid = 1 AND DATE(movementtime) BETWEEN DATE('$openingTime') AND DATE('$closingTime')";
		try
		{
			$disp_reload_results2 = $pdo3->prepare("$selectReloads");
			$disp_reload_results2->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	

		
		// Look up bar reloads
		$selectReloads = "SELECT SUM(price) FROM b_productmovements WHERE movementTypeid = 1 AND DATE(movementtime) BETWEEN DATE('$openingTime') AND DATE('$closingTime')";
		try
		{
			$result = $pdo3->prepare("$selectReloads");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$reloadsBar = $row['SUM(price)'];
		
		$selectReloads = "SELECT movementtime, purchaseid, quantity, price, paid FROM b_productmovements WHERE movementTypeid = 1 AND DATE(movementtime) BETWEEN DATE('$openingTime') AND DATE('$closingTime')";
		try
		{
			$bar_reload_results = $pdo3->prepare("$selectReloads");
			$bar_reload_results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		
		
		// Look up bar purchases
		$selectPurchases = "SELECT purchaseid, category, productid, purchaseDate, purchasePrice, purchaseQuantity, salesPrice FROM b_purchases WHERE DATE(purchaseDate) BETWEEN DATE('$openingTime') AND DATE('$closingTime')";
		try
		{
			$bar_pur_results = $pdo3->prepare("$selectPurchases");
			$bar_pur_results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
// 		while ($pur = $bar_pur_results->fetch()) {
			
// 			$purchaseid = $pur['purchaseid'];
// 			$category = $pur['category'];
// 			$productid = $pur['productid'];
// 			$purchaseDate = $pur['purchaseDate'];
// 			$purchasePrice = $pur['purchasePrice'];
// 			$purchaseQuantity = $pur['purchaseQuantity'];
// 			$salesPrice = $pur['salesPrice'];
			
// 			$barPurchased = $barPurchased + $purchasePrice * $purchaseQuantity;
			
// 			$totalPrice = number_format($purchasePrice * $purchaseQuantity,2);
			

				
// 				// Query to look for category
// 				$categoryDetailsCN = "SELECT name FROM b_categories WHERE id = '$category'";
// 		try
// 		{
// 			$result = $pdo3->prepare("$categoryDetailsCN");
// 			$result->execute();
// 		}
// 		catch (PDOException $e)
// 		{
// 				$error = 'Error fetching user: ' . $e->getMessage();
// 				echo $error;
// 				exit();
// 		}
	
// 		$rowCN = $result->fetch();
// 					$catName = $rowCN['name'];
					
// 				// Look up product
// 				$selectProduct = "SELECT name FROM b_products WHERE productid = '$productid'";
// 		try
// 		{
// 			$result = $pdo3->prepare("$selectProduct");
// 			$result->execute();
// 		}
// 		catch (PDOException $e)
// 		{
// 				$error = 'Error fetching user: ' . $e->getMessage();
// 				echo $error;
// 				exit();
// 		}
	
// 		$row = $result->fetch();
// 					$name = $row['name'];
							
				
// // 				$barTable .= <<<EOD
// //  <tr>
// //   <td class='left'>$purchaseDate</td>
// //   <td class='left'>$catName</td>
// //   <td class='left'>$name</td>
// //   <td>$totalPrice $_SESSION['currencyoperator']</td>
// //   <td></td>
// //   <td>$purchaseQuantity u.</td>
// //  </tr>
			
// // EOD;

			
		
			
// 		}
		
		$expTot = $expensesTill + $expensesBank + $dispPurchased + $reloadsDispensary + $reloadsBar + $barPurchased;
		

			
		// Look up stash data
		$selectExpenses = "SELECT intStash, extStash FROM closing WHERE DATE(closingtime) = DATE('$closingTime')";
		try
		{
			$result = $pdo3->prepare("$selectExpenses");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$intStash = $row['intStash'];
			$extStash = $row['extStash'];
			$totStash = $intStash + $extStash;

					$objPHPExcel->setActiveSheetIndex(0);
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('C1',$monthDisp);
					$objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true); 					
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('C2',$lang['revenue']);
					$objPHPExcel->getActiveSheet()->getStyle('C2')->getFont()->setBold(true);  


					$objPHPExcel->getActiveSheet()
					            ->setCellValue('A3','');
					$objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);  
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('B3',$lang['cash']);
					$objPHPExcel->getActiveSheet()->getStyle('B3')->getFont()->setBold(true);  		
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('C3',$lang['card']);
					$objPHPExcel->getActiveSheet()->getStyle('C3')->getFont()->setBold(true);  
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('D3',$lang['card']);
					$objPHPExcel->getActiveSheet()->getStyle('D3')->getFont()->setBold(true);  					
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('A4',$lang['global-donations']);
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('B4',number_format($donationscash,2)." ".$_SESSION['currencyoperator']);
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('C4',number_format($donationsbank,2)." ".$_SESSION['currencyoperator']);
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('D4',number_format($donations,2)." ".$_SESSION['currencyoperator']);					
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('A4',$lang['memberfees']);
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('B4',number_format($membershipFeescash,2)." ".$_SESSION['currencyoperator']);
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('C4',number_format($membershipFeesbank,2)." ".$_SESSION['currencyoperator']);
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('D4',number_format($membershipFees,2)." ".$_SESSION['currencyoperator']);					
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('A5',$lang['direct-dispenses']);
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('B5',number_format($salescash,2)." ".$_SESSION['currencyoperator']);
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('C5',number_format($salesbank,2)." ".$_SESSION['currencyoperator']);
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('D5',number_format($barsalescash + $barsalesbank,2)." ".$_SESSION['currencyoperator']);					

					$objPHPExcel->getActiveSheet()
					            ->setCellValue('A6',$lang['revenue']);
					$objPHPExcel->getActiveSheet()->getStyle('A6')->getFont()->setBold(true);             
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('B6',number_format($donationscash + $membershipFeescash + $salescash + $barsalescash,2)." ".$_SESSION['currencyoperator']);
					$objPHPExcel->getActiveSheet()->getStyle('B6')->getFont()->setBold(true); 
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('C6',number_format($donationsbank + $membershipFeesbank + $salesbank + $barsalesbank,2)." ".$_SESSION['currencyoperator']);
					$objPHPExcel->getActiveSheet()->getStyle('C6')->getFont()->setBold(true); 
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('D6',number_format($donations + $membershipFees + $salescash + $salesbank + $barsalescash + $barsalesbank,2)." ".$_SESSION['currencyoperator']);
					$objPHPExcel->getActiveSheet()->getStyle('D6')->getFont()->setBold(true); 
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('C7',$lang['global-expenses']);
					$objPHPExcel->getActiveSheet()->getStyle('C7')->getFont()->setBold(true); 
						$objPHPExcel->getActiveSheet()
					            ->setCellValue('A8','');
					            $objPHPExcel->getActiveSheet()->getStyle('A8')->getFont()->setBold(true);
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('B8',$lang['cash']);
					            $objPHPExcel->getActiveSheet()->getStyle('B8')->getFont()->setBold(true);
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('C8',$lang['card']);
					            $objPHPExcel->getActiveSheet()->getStyle('C8')->getFont()->setBold(true);
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('D8',$lang['global-total']);	
					            $objPHPExcel->getActiveSheet()->getStyle('D8')->getFont()->setBold(true);
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('A9',$lang['global-expenses']);
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('B9',number_format($expensesTill,2)." ".$_SESSION['currencyoperator']);
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('C9',number_format($expensesBank,2)." ".$_SESSION['currencyoperator']);
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('D9',number_format($expensesTill + $expensesBank,2)." ".$_SESSION['currencyoperator']);		
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('A10',$lang['product-purchases-dispensary']);
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('B10'," ");
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('C10'," ");
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('D10',number_format($dispPurchased,2)." ".$_SESSION['currencyoperator']);						
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('A11',$lang['reloads-dispensary']);
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('B11'," ");
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('C11'," ");
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('D11',number_format($reloadsDispensary,2)." ".$_SESSION['currencyoperator']);					
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('A12',$lang['product-purchases-bar']);
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('B12'," ");
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('C12'," ");
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('D12',number_format($barPurchased,2)." ".$_SESSION['currencyoperator']);						
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('A13',$lang['reloads-bar']);
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('B13'," ");
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('C13'," ");
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('D14',number_format($reloadsBar,2)." ".$_SESSION['currencyoperator']);					
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('A14',$lang['total-expenses']);
					            $objPHPExcel->getActiveSheet()->getStyle('A14')->getFont()->setBold(true);   
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('B14'," ");
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('C14'," ");
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('D14',number_format($expTot,2)." ".$_SESSION['currencyoperator']);
					            $objPHPExcel->getActiveSheet()->getStyle('D14')->getFont()->setBold(true);
					 $objPHPExcel->getActiveSheet()
					            ->setCellValue('C15',$lang['profit']);
					            $objPHPExcel->getActiveSheet()->getStyle('C15')->getFont()->setBold(true);   
					 $objPHPExcel->getActiveSheet()
					            ->setCellValue('D15',number_format($donations + $membershipFees + $salescash + $barsalescash + $salesbank + $barsalesbank - $expTot,2)." ".$_SESSION['currencyoperator']);
					            $objPHPExcel->getActiveSheet()->getStyle('D15')->getFont()->setBold(true);  
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('C16',$lang['purchase-details-dispensary']);
					            $objPHPExcel->getActiveSheet()->getStyle('C16')->getFont()->setBold(true); 

					$objPHPExcel->getActiveSheet()
					            ->setCellValue('A17',$lang['pur-date']);
					$objPHPExcel->getActiveSheet()->getStyle('A17')->getFont()->setBold(true);  
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('B17',$lang['global-category']);
					$objPHPExcel->getActiveSheet()->getStyle('B17')->getFont()->setBold(true);  		
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('C17',$lang['global-product']);
					$objPHPExcel->getActiveSheet()->getStyle('C17')->getFont()->setBold(true);  
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('D17','Euro');
					$objPHPExcel->getActiveSheet()->getStyle('D17')->getFont()->setBold(true); 
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('E17',$lang['grams']);
					$objPHPExcel->getActiveSheet()->getStyle('E17')->getFont()->setBold(true); 
					$objPHPExcel->getActiveSheet()
					            ->setCellValue('F17',$lang['units']);
					$objPHPExcel->getActiveSheet()->getStyle('F17')->getFont()->setBold(true);   
					$index1 = 18;

							while ($pur = $pur_disp_results->fetch()) {
			
										$purchaseid = $pur['purchaseid'];
										$category = $pur['category'];
										$productid = $pur['productid'];
										$purchaseDate = date("d-m-Y", strtotime($pur['purchaseDate']));
										$purchasePrice = $pur['purchasePrice'];
										$purchaseQuantity = $pur['purchaseQuantity'];
										$realQuantity = $pur['realQuantity'];
										$salesPrice = $pur['salesPrice'];
										
												
										$dispPurchased = $dispPurchased + ($purchasePrice * $purchaseQuantity);
										
										$totalPrice = number_format($purchasePrice * $purchaseQuantity,2);
										
										if ($category == 1) {
											
											$catName = $lang['global-flowers'];
											
											// Look up flower
											$selectProduct = "SELECT name, breed2 FROM flower WHERE flowerid = '$productid'";
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
												$breed2 = $row['breed2'];
											
											if ($breed2 != '') {
												$name = $name . " x " . $breed2;
											}
											
										} else if ($category == 2) {
											
											$catName = $lang['global-extracts'];
											
											// Look up extract
											$selectProduct = "SELECT name FROM extract WHERE extractid = '$productid'";
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
								
										} else {
											
											// Query to look for category
											$categoryDetailsCN = "SELECT name, type FROM categories WHERE id = '$category'";
									try
									{
										$result = $pdo3->prepare("$categoryDetailsCN");
										$result->execute();
									}
									catch (PDOException $e)
									{
											$error = 'Error fetching user: ' . $e->getMessage();
											echo $error;
											exit();
									}
								
									$rowCN = $result->fetch();
												$catName = $rowCN['name'];
												$type = $rowCN['type'];
												
											// Look up product
											$selectProduct = "SELECT name FROM products WHERE productid = '$productid'";
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
											
										}
										
					if ($category < 3 || $type == 1) {
											
					
						$objPHPExcel->getActiveSheet()
					                ->setCellValue('A'.$index1, $purchaseDate);
					    $objPHPExcel->getActiveSheet()
					                ->setCellValue('B'.$index1, $catName);
					    $objPHPExcel->getActiveSheet()
					                ->setCellValue('C'.$index1, $name);
					    $objPHPExcel->getActiveSheet()
					                ->setCellValue('D'.$index1, $totalPrice." ".$_SESSION['currencyoperator']); 
			            $objPHPExcel->getActiveSheet()
			            			->setCellValue('E'.$index1, $purchaseQuantity." g."); 
			           

										} else {
											
					
						$objPHPExcel->getActiveSheet()
					                ->setCellValue('A'.$index1, $purchaseDate);
					    $objPHPExcel->getActiveSheet()
					                ->setCellValue('B'.$index1, $catName);
					    $objPHPExcel->getActiveSheet()
					                ->setCellValue('C'.$index1, $name);
					    $objPHPExcel->getActiveSheet()
					                ->setCellValue('D'.$index1, $totalPrice." ".$_SESSION['currencyoperator']); 
			            $objPHPExcel->getActiveSheet()
			            			->setCellValue('E'.$index1, $purchaseQuantity." u."); 

										}
			
			$index1++;
		}

					// reload dispensery
					$objWorkSheet = $objPHPExcel->createSheet(1);
					//$objPHPExcel->setActiveSheetIndex(1);
					$objWorkSheet->setCellValue('C1',$lang['reloads-dispensary']);
					            $objWorkSheet->getStyle('C1')->getFont()->setBold(true); 

					$objWorkSheet->setCellValue('A2',$lang['pur-date']);
					$objWorkSheet->getStyle('A2')->getFont()->setBold(true);  
					$objWorkSheet->setCellValue('B2',$lang['global-category']);
					$objWorkSheet->getStyle('B2')->getFont()->setBold(true);  		
					$objWorkSheet->setCellValue('C2',$lang['global-product']);
					$objWorkSheet->getStyle('C2')->getFont()->setBold(true);  
					$objWorkSheet->setCellValue('D2','Euro');
					$objWorkSheet->getStyle('D2')->getFont()->setBold(true); 
					$objWorkSheet->setCellValue('E2',$lang['paid']);
					$objWorkSheet->getStyle('E2')->getFont()->setBold(true); 
					$objWorkSheet->setCellValue('F2',$lang['grams']);
					$objWorkSheet->getStyle('F2')->getFont()->setBold(true);  
					$objWorkSheet->setCellValue('G2',$lang['units']);
					$objWorkSheet->getStyle('G2')->getFont()->setBold(true);  
					$index2 = 3;

					while ($pur2 = $disp_reload_results2->fetch()) {
			
							$purchaseDate = date("d-m-Y", strtotime($pur2['movementtime']));
							$purchaseid = $pur2['purchaseid'];
							$purchaseQuantity = $pur2['quantity'];
							$totalPrice = $pur2['price'];
							$paid = $pur2['paid'];
							
							$selectProdName = "SELECT category, productid FROM purchases WHERE purchaseid = $purchaseid";
							try
							{
								$result = $pdo3->prepare("$selectProdName");
								$result->execute();
							}
							catch (PDOException $e)
							{
									$error = 'Error fetching user: ' . $e->getMessage();
									echo $error;
									exit();
							}
						
							$rowp = $result->fetch();
								$category = $rowp['category'];
								$productid = $rowp['productid'];
								
							if ($category == 1) {
								
								$catName = $lang['global-flowers'];
								
								// Look up flower
								$selectProduct = "SELECT name, breed2 FROM flower WHERE flowerid = '$productid'";
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
									$breed2 = $row['breed2'];
								
								if ($breed2 != '') {
									$name = $name . " x " . $breed2;
								}
								
							} else if ($category == 2) {
								
								$catName = $lang['global-extracts'];
								
								// Look up extract
								$selectProduct = "SELECT name FROM extract WHERE extractid = '$productid'";
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
					
							} else {
								
								// Query to look for category
								$categoryDetailsCN = "SELECT name, type FROM categories WHERE id = '$category'";
						try
						{
							$result = $pdo3->prepare("$categoryDetailsCN");
							$result->execute();
						}
						catch (PDOException $e)
						{
								$error = 'Error fetching user: ' . $e->getMessage();
								echo $error;
								exit();
						}
					
						$rowCN = $result->fetch();
									$catName = $rowCN['name'];
									$type = $rowCN['type'];
									
								// Look up product
								$selectProduct = "SELECT name FROM products WHERE productid = '$productid'";
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
								
							}
							
							if ($category < 3 || $type == 1) {
							
						$objWorkSheet->setCellValue('A'.$index2, $purchaseDate);
					    $objWorkSheet->setCellValue('B'.$index2, $catName);
					    $objWorkSheet->setCellValue('C'.$index2, $name);
					    $objWorkSheet->setCellValue('D'.$index2, $totalPrice." ".$_SESSION['currencyoperator']); 
			            $objWorkSheet->setCellValue('E'.$index2, $paid." ".$_SESSION['currencyoperator']);  
			            $objWorkSheet->setCellValue('F'.$index2, $purchaseQuantity." g.");
			           	$objWorkSheet->setCellValue('G'.$index2, ""); 

							} else {
								
						
								$objWorkSheet->setCellValue('A'.$index2, $purchaseDate);
							    $objWorkSheet->setCellValue('B'.$index2, $catName);
							    $objWorkSheet->setCellValue('C'.$index2, $name);
							    $objWorkSheet->setCellValue('D'.$index2, $totalPrice." ".$_SESSION['currencyoperator']); 
					            $objWorkSheet->setCellValue('E'.$index2, $paid." ".$_SESSION['currencyoperator']);  
					            $objWorkSheet->setCellValue('F'.$index2, " ");
					           	$objWorkSheet->setCellValue('G'.$index2, $purchaseQuantity." u."); 

							}
							$index2++;
							
						}
					// bar purchases
					$objWorkSheet2 = $objPHPExcel->createSheet(2);
					//$objPHPExcel->setActiveSheetIndex(1);
					$objWorkSheet2->setCellValue('C1',$lang['purchase-details-bar']);
					$objWorkSheet2->getStyle('C1')->getFont()->setBold(true); 

					$objWorkSheet2->setCellValue('A2',$lang['pur-date']);
					$objWorkSheet2->getStyle('A2')->getFont()->setBold(true);  
					$objWorkSheet2->setCellValue('B2',$lang['global-category']);
					$objWorkSheet2->getStyle('B2')->getFont()->setBold(true);  		
					$objWorkSheet2->setCellValue('C2',$lang['global-product']);
					$objWorkSheet2->getStyle('C2')->getFont()->setBold(true);  
					$objWorkSheet2->setCellValue('D2','Euro');
					$objWorkSheet2->getStyle('D2')->getFont()->setBold(true); 
					$objWorkSheet2->setCellValue('E2',$lang['paid']);
					$objWorkSheet2->getStyle('E2')->getFont()->setBold(true); 
					$objWorkSheet2->setCellValue('F2',$lang['units']);
					$objWorkSheet2->getStyle('F2')->getFont()->setBold(true);  
					
					$index3 = 3;			
		while ($pur_bar = $bar_pur_results->fetch()) {
			
			$purchaseid = $pur_bar['purchaseid'];
			$category = $pur_bar['category'];
			$productid = $pur_bar['productid'];
			$purchaseDate = $pur_bar['purchaseDate'];
			$purchasePrice = $pur_bar['purchasePrice'];
			$purchaseQuantity = $pur_bar['purchaseQuantity'];
			$salesPrice = $pur_bar['salesPrice'];
			
			$barPurchased = $barPurchased + $purchasePrice * $purchaseQuantity;
			
			$totalPrice = number_format($purchasePrice * $purchaseQuantity,2);
			

				
				// Query to look for category
				$categoryDetailsCN = "SELECT name FROM b_categories WHERE id = '$category'";
		try
		{
			$result = $pdo3->prepare("$categoryDetailsCN");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$rowCN = $result->fetch();
					$catName = $rowCN['name'];
					
				// Look up product
				$selectProduct = "SELECT name FROM b_products WHERE productid = '$productid'";
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
							
				
// 				$barTable .= <<<EOD
//  <tr>
//   <td class='left'>$purchaseDate</td>
//   <td class='left'>$catName</td>
//   <td class='left'>$name</td>
//   <td>$totalPrice ".$_SESSION['currencyoperator']/td>
//   <td></td>
//   <td>$purchaseQuantity u.</td>
//  </tr>
			
// EOD;
					$objWorkSheet2->setCellValue('A'.$index3,$purchaseDate);
					$objWorkSheet2->setCellValue('B'.$index3,$catName);
					$objWorkSheet2->setCellValue('C'.$index3,$name);
					$objWorkSheet2->setCellValue('D'.$index3,$totalPrice." ".$_SESSION['currencyoperator']);
					$objWorkSheet2->setCellValue('E'.$index3,'');
					$objWorkSheet2->setCellValue('F'.$index3,$purchaseQuantity." u.");
					
					$index3++;
		
			
		}

		//bar reloads
					$objWorkSheet3 = $objPHPExcel->createSheet(3);
					//$objPHPExcel->setActiveSheetIndex(1);
					$objWorkSheet3->setCellValue('C1',$lang['reloads-bar']);
					$objWorkSheet3->getStyle('C1')->getFont()->setBold(true); 

					$objWorkSheet3->setCellValue('A2',$lang['pur-date']);
					$objWorkSheet3->getStyle('A2')->getFont()->setBold(true);  
					$objWorkSheet3->setCellValue('B2',$lang['global-category']);
					$objWorkSheet3->getStyle('B2')->getFont()->setBold(true);  		
					$objWorkSheet3->setCellValue('C2',$lang['global-product']);
					$objWorkSheet3->getStyle('C2')->getFont()->setBold(true);  
					$objWorkSheet3->setCellValue('D2','Euro');
					$objWorkSheet3->getStyle('D2')->getFont()->setBold(true); 
					$objWorkSheet3->setCellValue('E2',$lang['paid']);
					$objWorkSheet3->getStyle('E2')->getFont()->setBold(true); 
					$objWorkSheet3->setCellValue('F2',$lang['units']);
					$objWorkSheet3->getStyle('F2')->getFont()->setBold(true);  
					
					$index4 = 3;	
		while ($bar_reload_pur = $bar_reload_results->fetch()) {
			
			$purchaseDate = date("d-m-Y", strtotime($bar_reload_pur['movementtime']));
			$purchaseid = $bar_reload_pur['purchaseid'];
			$purchaseQuantity = $bar_reload_pur['quantity'];
			$totalPrice = $bar_reload_pur['price'];
			$paid = $bar_reload_pur['paid'];
			
			$selectProdName = "SELECT category, productid FROM b_purchases WHERE purchaseid = $purchaseid";
		try
		{
			$result = $pdo3->prepare("$selectProdName");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$rowp = $result->fetch();
				$category = $rowp['category'];
				$productid = $rowp['productid'];
				

				
				// Query to look for category
				$categoryDetailsCN = "SELECT name FROM b_categories WHERE id = '$category'";
		try
		{
			$result = $pdo3->prepare("$categoryDetailsCN");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$rowCN = $result->fetch();
					$catName = $rowCN['name'];
					
				// Look up product
				$selectProduct = "SELECT name FROM b_products WHERE productid = '$productid'";
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
				
				
// 				$barReloads .= <<<EOD
//  <tr>
//   <td class='left'>$purchaseDate</td>
//   <td class='left'>$catName</td>
//   <td class='left'>$name</td>
//   <td>$totalPrice ".$_SESSION['currencyoperator']/td>
//   <td>$paid ".$_SESSION['currencyoperator']/td>
//   <td>$purchaseQuantity u.</td>
//  </tr>
			
// EOD;
					$objWorkSheet3->setCellValue('A'.$index4,$purchaseDate);
					$objWorkSheet3->setCellValue('B'.$index4,$catName);
					$objWorkSheet3->setCellValue('C'.$index4,$name);
					$objWorkSheet3->setCellValue('D'.$index4,$totalPrice." ".$_SESSION['currencyoperator']);
					$objWorkSheet3->setCellValue('E'.$index4,'');
					$objWorkSheet3->setCellValue('F'.$index4,$purchaseQuantity." u.");
					
					$index4++;
			
			
		}
					$objWorkSheet4 = $objPHPExcel->createSheet(4);
					$objWorkSheet4->setCellValue('A1',$lang['product-dispensed']);
					$objWorkSheet4->getStyle('A1')->getFont()->setBold(true); 
					//$objPHPExcel->setActiveSheetIndex(1);
					$objWorkSheet4->setCellValue('A2',$lang['closeday-dispensed']." ".$_SESSION['currencyoperator']);
					$objWorkSheet4->setCellValue('B2',number_format($sales,2)." ".$_SESSION['currencyoperator']);
					$objWorkSheet4->setCellValue('A3',$lang['closeday-dispensed']." g.");
					$objWorkSheet4->setCellValue('B3',number_format($quantity,2)." g.");	
					$objWorkSheet4->setCellValue('A4',$lang['closeday-dispensed']." g. real");
					$objWorkSheet4->setCellValue('B4',number_format($realQuantityTot,2)." g.");
					$objWorkSheet4->setCellValue('A5',$lang['closeday-dispensed']." u");
					$objWorkSheet4->setCellValue('B5',number_format($units,2)." u");
					

							
					



	// Look up dispensed last month
	$selectSalesFlower = "SELECT DISTINCT(d.purchaseid), d.productid, d.category FROM salesdetails d, sales s WHERE s.saleid = d.saleid AND s.saletime BETWEEN '$openingTime' AND '$closingTime' ORDER BY d.category ASC";
		try
		{
			$results = $pdo3->prepare("$selectSalesFlower");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	

// 	echo <<<EOD
// <div class="historybox">	
// <table class='default historytable' id='t7'>
//  <thead>
//  <tr style='cursor: pointer;'>
//   <th style='text-align: left;'>{$lang['global-category']}</th>
//   <th style='text-align: left;'>{$lang['global-product']}</th>
//   <th style='text-align: left;'>Euro</th>
//   <th style='text-align: left;'>{$lang['grams']}</th>
//   <th style='text-align: left;'>{$lang['grams']} real</th>
//   <th style='text-align: left;'>{$lang['units']}</th>
//  </tr>
//  </thead>
//  <tbody>
// EOD;
					

					
					$objWorkSheet4->setCellValue('A6',$lang['global-category']);
					$objWorkSheet4->getStyle('A6')->getFont()->setBold(true);  		
					$objWorkSheet4->setCellValue('B6',$lang['global-product']);
					$objWorkSheet4->getStyle('B6')->getFont()->setBold(true);  
					$objWorkSheet4->setCellValue('C6','Euro');
					$objWorkSheet4->getStyle('C6')->getFont()->setBold(true); 
					$objWorkSheet4->setCellValue('D6',$lang['grams']);
					$objWorkSheet4->getStyle('D6')->getFont()->setBold(true); 
					$objWorkSheet4->setCellValue('E6',$lang['grams']." real");
					$objWorkSheet4->getStyle('E6')->getFont()->setBold(true); 
					$objWorkSheet4->setCellValue('F6',$lang['units']);
					$objWorkSheet4->getStyle('F6')->getFont()->setBold(true);  
					$index5 = 7;
		while ($pur = $results->fetch()) {
		
		
		$purchaseid = $pur['purchaseid'];
		$productid = $pur['productid'];
		
		$selectSalesPurchase = "SELECT SUM(d.amount), SUM(d.quantity), SUM(d.realQuantity), MAX(d.category) from sales s, salesdetails d WHERE s.saleid = d.saleid AND s.saletime BETWEEN '$openingTime' AND '$closingTime' AND d.purchaseid = $purchaseid";
		try
		{
			$result = $pdo3->prepare("$selectSalesPurchase");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$category = $row['MAX(d.category)'];
			$salesTot = $row['SUM(d.amount)'];
			$quantitySoldTot = $row['SUM(d.quantity)'];
			$realquantitySoldTot = $row['SUM(d.realQuantity)'];
						
		if ($category == 1) {
			
			$selectProdName = "SELECT name, breed2 FROM flower WHERE flowerid = $productid";
		try
		{
			$result = $pdo3->prepare("$selectProdName");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$prod = $result->fetch();
				$prodName = $prod['name'] . " " . $prod['breed2'];
				
			$selectProdName = "SELECT growType FROM purchases WHERE purchaseid = $purchaseid";
		try
		{
			$result = $pdo3->prepare("$selectProdName");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$prod = $result->fetch();
				$growtype = $prod['growType'];
				
			if ($growtype > 0) {
				
			$selectProdName = "SELECT growtype FROM growtypes WHERE growtypeid = $growtype";
		try
		{
			$result = $pdo3->prepare("$selectProdName");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$prod = $result->fetch();
				$growtype = $prod['growtype'];
				
				$prodName = "$prodName <span class='smallerfont'>($growtype)</span>";
				
			} else {
				
				$prodName = $prodName;
				
			}
				
// 			$output1 .= <<<EOD
			
//  <tr>
//   <td style='text-align: left;'>{$lang['global-flower']}</td>
//   <td style='text-align: left;'>$prodName</td>
//   <td style='text-align: right;'>$salesTot</td>
//   <td style='text-align: right;'>$quantitySoldTot</td>
//   <td style='text-align: right;'>$realquantitySoldTot</td>
//   <td style='text-align: right;'></td>
//  </tr>

			
// EOD;
					$objWorkSheet4->setCellValue('A'.$index5,$lang['global-flower']);
					$objWorkSheet4->setCellValue('B'.$index5,$prodName);
					$objWorkSheet4->setCellValue('C'.$index5,$salesTot);
					$objWorkSheet4->setCellValue('D'.$index5,$quantitySoldTot);
					$objWorkSheet4->setCellValue('E'.$index5,$realquantitySoldTot);
					$objWorkSheet4->setCellValue('F'.$index5," ");
		} else if ($category == 2) {
			
			$selectProdName = "SELECT name FROM extract WHERE extractid = $productid";
		try
		{
			$result = $pdo3->prepare("$selectProdName");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$prod = $result->fetch();
				$prodName = $prod['name'];
				
// 			$output2 .=  <<<EOD
			
//  <tr>
//   <td style='text-align: left;'>{$lang['global-extract']}</td>
//   <td style='text-align: left;'>$prodName</td>
//   <td style='text-align: right;'>$salesTot</td>
//   <td style='text-align: right;'>$quantitySoldTot</td>
//   <td style='text-align: right;'>$realquantitySoldTot</td>
//   <td style='text-align: right;'></td>
//  </tr>

			
// EOD;

					$objWorkSheet4->setCellValue('A'.$index5,$lang['global-extract']);
					$objWorkSheet4->setCellValue('B'.$index5,$prodName);
					$objWorkSheet4->setCellValue('C'.$index5,$salesTot);
					$objWorkSheet4->setCellValue('D'.$index5,$quantitySoldTot);
					$objWorkSheet4->setCellValue('E'.$index5,$realquantitySoldTot);
					$objWorkSheet4->setCellValue('F'.$index5," ");
		} else {
			
			$selectCAT = "SELECT name, type FROM categories WHERE id = $category";
		try
		{
			$result = $pdo3->prepare("$selectCAT");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$catR = $result->fetch();
				$catName = $catR['name'];
				$type = $catR['type'];
			
			$selectProdName = "SELECT name FROM products WHERE productid = $productid";
		try
		{
			$result = $pdo3->prepare("$selectProdName");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$prod = $result->fetch();
				$prodName = $prod['name'];
				
				
			if ($type == 1) {
				
// 				//gramcat
// 				$output3 .= <<<EOD
			
//  <tr>
//   <td style='text-align: left;'>{$catName}</td>
//   <td style='text-align: left;'>$prodName</td>
//   <td style='text-align: right;'>$salesTot</td>
//   <td style='text-align: right;'>$quantitySoldTot</td>
//   <td style='text-align: right;'>$realquantitySoldTot</td>
//   <td style='text-align: right;'></td>
//  </tr>

			
// EOD;
					$objWorkSheet4->setCellValue('A'.$index5,$catName);
					$objWorkSheet4->setCellValue('B'.$index5,$prodName);
					$objWorkSheet4->setCellValue('C'.$index5,$salesTot);
					$objWorkSheet4->setCellValue('D'.$index5,$quantitySoldTot);
					$objWorkSheet4->setCellValue('E'.$index5,$realquantitySoldTot);
					$objWorkSheet4->setCellValue('F'.$index5," ");
				
			} else {
				
// 				$output4 .=  <<<EOD
			
//  <tr>
//   <td style='text-align: left;'>{$catName}</td>
//   <td style='text-align: left;'>$prodName</td>
//   <td style='text-align: right;'>$salesTot</td>
//   <td style='text-align: right;'></td>
//   <td style='text-align: right;'></td>
//   <td style='text-align: right;'>$realquantitySoldTot</td>
//  </tr>

			
// EOD;
					$objWorkSheet4->setCellValue('A'.$index5,$catName);
					$objWorkSheet4->setCellValue('B'.$index5,$prodName);
					$objWorkSheet4->setCellValue('C'.$index5,$salesTot);
					$objWorkSheet4->setCellValue('D'.$index5,"");
					$objWorkSheet4->setCellValue('E'.$index5,"");
					$objWorkSheet4->setCellValue('F'.$index5,$realquantitySoldTot);
				
			}
		}
		$index5++;
	}
	    ob_end_clean();
	    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	    //header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	    header('Content-type: application/vnd.ms-excel');
	    header('Content-Disposition: attachment;filename=month-report.xlsx');
	    header("Content-Type: application/download");
	    //header('Cache-Control: max-age = 0');
	    $objWriter->save('php://output');
	    
	    die;
 ?>
