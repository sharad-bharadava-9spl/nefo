<?php
	ob_start();
	require_once 'cOnfig/connection.php';
	// require_once 'cOnfig/view.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';

	
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);

		require_once  'PHPExcel/Classes/PHPExcel.php';

		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getProperties()->setCreator("Lokesh Nayak")
			                             ->setLastModifiedBy("Lokesh Nayak")
			                             ->setTitle("Test Document")
			                             ->setSubject("Test Document")
			                             ->setDescription("Test document for PHPExcel")
			                             ->setKeywords("office")
			                             ->setCategory("Test result file");

			// Check if 'entre fechas' was utilised
			if (!empty($_GET['untilDate']) && $_GET['untilDate'] != '') {
				
				$limitVar = "";
				
				$fromDate = date("Y-m-d", strtotime($_GET['fromDate']));
				$untilDate = date("Y-m-d", strtotime($_GET['untilDate']));
				
				$timeLimit = "AND DATE(invdate) BETWEEN DATE('$fromDate') AND DATE('$untilDate')";
				$limitVar = "";
					
			}else{
				$timeLimit = '';
			}


 	       	$rows=array();
			$page_size=250;
			$count = $_GET['totalCount'];
			$fileExcel = "invoices.xlsx";
				
		  if($_GET['count'] == 0){
				// excel count query
				$excelCountQuery  =   "SELECT * from invoices WHERE DATE(invdate) > '2017-12-31' $timeLimit ORDER BY invdate DESC";
			   //$query = "select id from shipment Limit ".$page_size." OFFSET ".$offset_var;
			   $countResults= $pdo->prepare("$excelCountQuery");
			   $countResults->execute();

				$total_records=$countResults->rowCount();

                $count=ceil($total_records/$page_size);

				$filepath = "excel/".$fileExcel;

				if(file_exists($filepath)){
					unlink($filepath);
				}
				//$count =4;
			}		                             

	if($_GET['count'] == 0){
        $objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('A1',"Business Type");
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);  		
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('B1','Invoice Number');
		$objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('C1','Region');
		$objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);  
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('D1','Country');
		$objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('E1', 'Customer Number');
		$objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('F1', 'Customer');
		$objPHPExcel->getActiveSheet()->getStyle('F1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('G1','Invoice Date');
		$objPHPExcel->getActiveSheet()->getStyle('G1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('H1','Invoice Due Date');
		$objPHPExcel->getActiveSheet()->getStyle('H1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('I1','Currency');
		$objPHPExcel->getActiveSheet()->getStyle('I1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('J1','Shipping');
		$objPHPExcel->getActiveSheet()->getStyle('J1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('K1','Unit Price');
		$objPHPExcel->getActiveSheet()->getStyle('K1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('L1','# of items');
		$objPHPExcel->getActiveSheet()->getStyle('L1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('M1','Base Amount');
		$objPHPExcel->getActiveSheet()->getStyle('M1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('N1','Discount');
		$objPHPExcel->getActiveSheet()->getStyle('N1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('O1','IVA');
		$objPHPExcel->getActiveSheet()->getStyle('O1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('P1','Credit card fee');
		$objPHPExcel->getActiveSheet()->getStyle('P1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('Q1','Total Amount');
		$objPHPExcel->getActiveSheet()->getStyle('Q1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('R1','Description');
		$objPHPExcel->getActiveSheet()->getStyle('R1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('S1','Status');
		$objPHPExcel->getActiveSheet()->getStyle('S1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('T1','Payment No.');
		$objPHPExcel->getActiveSheet()->getStyle('T1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('U1','Payment Type');
		$objPHPExcel->getActiveSheet()->getStyle('U1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('V1','Bank Id');
		$objPHPExcel->getActiveSheet()->getStyle('V1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('W1','Settled date');
		$objPHPExcel->getActiveSheet()->getStyle('W1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('X1','Bank lodgment date');
		$objPHPExcel->getActiveSheet()->getStyle('X1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('Y1','Amount settled');
		$objPHPExcel->getActiveSheet()->getStyle('Y1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('Z1','Delta');
		$objPHPExcel->getActiveSheet()->getStyle('Z1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()
		            ->setCellValue('AA1','Comments');
		$objPHPExcel->getActiveSheet()->getStyle('AA1')->getFont()->setBold(true);

	}	                             
	

	//-----------------------------------------------------//
	$query_bank = "SELECT * from payment_bank_id";
	$query_ptype = "SELECT * from payment_types";
	try
	{
		$bank_result = $pdo->prepare("$query_bank");
		$bank_result->execute();
		
		$ptype_result = $pdo->prepare("$query_ptype");
		$ptype_result->execute();
	}
	catch (PDOException $e)
	{
		$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	//-----fetch bank name array------//
	$bank_details = array();
	while ($row = $bank_result->fetch()) {
		$bank_details[$row['id']] = $row['bank_id'];
	}
	//-----fetch payment type array------//
	$payment_details = array();
	while ($row2 = $ptype_result->fetch()) {
		$payment_details[$row2['id']] = $row2['name'];
	}


$countItem = $_GET['count'];
if($_GET['count'] <= $_GET['totalCount']){
   	$offset_var = $countItem * $page_size;

		$query = "SELECT * from invoices WHERE DATE(invdate) > '2017-12-31' $timeLimit ORDER BY invdate DESC Limit ".$page_size." OFFSET ".$offset_var;
		
		try
		{
			$results = $pdo->prepare("$query");
			$results->execute();
			
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		$x=0;
		$startIndex = 2; 
		if(file_exists("excel/$fileExcel")){
	   		$objPHPExcel = PHPExcel_IOFactory::load("excel/$fileExcel");
	   		$startIndex = $objPHPExcel->getActiveSheet()->getHighestRow()+1;
	   	}
		while ($row = $results->fetch()) {
			
			$invno = $row['invno'];		
			$paid = $row['paid'];		
			$invdate = date("d-m-Y", strtotime($row['invdate']));
			$invduedate = date("d-m-Y", strtotime($row['invduedate']));
			$base_amount = $row['base_amount'];
			$amount = $row['amount'];
			$discount = $row['discount'];		
			$vat = $row['vat'];
			$creditCardFee = $row['credit_card_fee'];
			$customer = $row['customer'];		
			$brand = $row['brand'];
			$description = $row['description'];
			$payment = $row['payment'];
			$writeOff = $row['writeOff'];
			$delta = $row['delta'];
			$currency = $row['currency'];
			$fee_elements = array_filter(unserialize($row['fees']));
			$credit_payment_type = $row['payment_type'];
			
			if($currency == ''){
				$currency = 'EUR';
			}
			$units = '';
			$unit_price = '';
			$shipping = '';
			$baseAmount[$x] = 0;
			if($brand == 'HW'){
				$saleid = $row['order_id'];
					// check order id
					$checkOrder = "SELECT * from sales WHERE saleid =".$saleid;
						try
					   {
						   $check_result = $pdo2->prepare("$checkOrder");
						   $check_result->execute();
					   }
					   catch (PDOException $e)
					   {
							   $error = 'Error fetching user: ' . $e->getMessage();
							   echo $error;
							   exit();
					   }
				   $num_order = $check_result->rowCount();

				   while($orderRow = $check_result->fetch()){
					   $shipping = $orderRow['shipping'];
					   $total_amount = $orderRow['amount'];
					  // $customer = $orderRow['customer'];
					   $paymentoption = $orderRow['paymentoption'];
				   }

				    // get product details

				   $selectProduct = "SELECT * from salesdetails WHERE saleid =".$saleid;

					   try
					   {
						   $get_result = $pdo2->prepare("$selectProduct");
						   $get_result->execute();
					   }
					   catch (PDOException $e)
					   {
							   $error = 'Error fetching user: ' . $e->getMessage();
							   echo $error;
							   exit();
					   }
				   $i =0;	
				   while($getSalesRow = $get_result->fetch()){
					   $productid = $getSalesRow['productid'];
					   $purchaseid = $getSalesRow['purchaseid'];
					   // get name
						$getName = "SELECT name from products WHERE productid =".$productid;

						   try
						   {
							   $select_result = $pdo2->prepare("$getName");
							   $select_result->execute();
						   }
						   catch (PDOException $e)
						   {
								   $error = 'Error fetching user: ' . $e->getMessage();
								   echo $error;
								   exit();
						   }
						   $nameRow = $select_result->fetch();
						   $quantity = $getSalesRow['quantity'];
						   //---------discount-----------------
						   if ($purchaseid == 12 || $purchaseid == 13 || $purchaseid == 14 || $purchaseid == 15 || $purchaseid == 16) {
								if ($quantity > 99) {
									$discountTxt = '20%';
									$discountOp = 0.8;
								} else if ($quantity > 49) {
									$discountTxt = '10%';
									$discountOp = 0.9;
								} else if ($quantity > 9) {
									$discountTxt = '5%';
									$discountOp = 0.95;
								} else {					
									$discountTxt = '';
									$discountOp = 1;
								}				
							} 
							else if ($purchaseid == 25 || $purchaseid == 26 || $purchaseid == 27) {
								
								if ($quantity > 999) {
									
									$discountTxt = '5%';
									$discountOp = 0.95;
									
								} else {
									
									$discountTxt = '';
									$discountOp = 1;
									
								}

							} 
							else {
								$discountTxt = '';
								$discountOp = 1;
							}
						   //---------discount-----------------
						
							$qty = explode(".",$getSalesRow['quantity']);
							$productQty = ($qty[1]>0)?$getSalesRow['quantity']:$qty[0];
						
						   $productName = $nameRow['name'];
						   $oneAmount = $getSalesRow['amount'] / $productQty;
						   $totalAmt[$x] = $oneAmount * $productQty;
						   $produNum = $i+1;
						   $name_arr[$x][$i] = $produNum.". ".$productName;
						   $unit_arr[$x][$i] = $productQty;
						   $unitPrice_arr[$x][$i] =  $getSalesRow['amount'] / $productQty;
						   //$description .= $productName."<br>";
						   $baseAmount[$x] = $baseAmount[$x] + ($totalAmt[$x] * $discountOp);

						   $i++;

					}
				/*echo "<pre>";
				print_r($name_arr[$x]);*/

				$description = implode("\n", $name_arr[$x]);
				$units = implode("\n", $unit_arr[$x]);
				$unit_price = implode("\n", $unitPrice_arr[$x]);
				$base_amount = $baseAmount[$x];
			}
			// adding fee elements in description for software invoice
			if($brand == 'SW' && !empty($fee_elements)){
				$description = "1. ".$description;
				$unit_price = $base_amount;
				$subtotal=$base_amount;
				$j=0;
				foreach($fee_elements as $fee_name=> $fee_val){
						$num = $j+2;
						if(is_numeric($fee_val)){
							$subtotal +=$fee_val;
						}
						$fee_name_arr[$x][$j] = $num." .".$fee_name;
						$fee_val_arr[$x][$j] = $fee_val;

					$j++;
				}
				$description .= "\n".implode("\n", $fee_name_arr[$x]);
				$unit_price .= "\n".implode("\n", $fee_val_arr[$x]);
				$base_amount = $subtotal;
			}
			// Look up customer details: name and domain
			$selectUsersU = "SELECT id,longName, state, country FROM customers WHERE number = '$customer'";
			try
			{
				$result = $pdo2->prepare("$selectUsersU");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
			
			$rowX = $result->fetch();
			$longName = $rowX['longName'];
			$state = $rowX['state'];
			$country = $rowX['country'];
			$customerId = $rowX['id'];

			$customer_link = '../Nefos-new/customer.php?user_id='.$customerId;
			
			$query = "SELECT domain from db_access WHERE customer = '$customer'";
			try
			{
				$resultsY = $pdo->prepare("$query");
				$resultsY->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$rowY = $resultsY->fetch();
				$domain = $rowY['domain'];
				
			// If first letter is S and invoice date less than 03-04-2020, remove S from filename
			if (substr($invno, 0, 1) == 'S' && strtotime($invdate) < strtotime('2020-04-03')) {
				
				$invno = substr($invno, 1);
				$brandShort = substr($brand, 0, 2);
				$invfile = "../../ccsnubev2_com/_club/_$domain/invoices/$customer-$invno-$brandShort.pdf";
				$invfileFull = "https://ccsnubev2.com/_club/_$domain/invoices/$customer-$invno-$brandShort.pdf";
				$invfile2 = "../../ccsnubev2_com/v6/_club/_$domain/invoices/$customer-$invno-$brandShort.pdf";
				$invfile2Full = "https://ccsnubev2.com/v6/_club/_$domain/invoices/$customer-$invno-$brandShort.pdf";
				
			// if first letter is 2, else
			} else if (substr($invno, 0, 1) == 'M') {
				
				$invfile = "../../ccsnubev2_com/_club/_$domain/invoices/$invno.pdf";
				$invfileFull = "https://ccsnubev2.com/_club/_$domain/invoices/$invno.pdf";
				$invfile2 = "../../ccsnubev2_com/v6/_club/_$domain/invoices/$invno.pdf";
				$invfile2Full = "https://ccsnubev2.com/v6/_club/_$domain/invoices/$invno.pdf";
//				echo "M: $customer - $invno<br />";
//				echo "invfileFull: $invfileFull<br />";
//				echo "invfileFull2: $invfileFull2<br /><br />";
				
			} else if (substr($invno, 0, 1) == '1') {
				
				$invfile = "../../ccsnubev2_com/_club/_$domain/invoices/$customer-$invno.pdf";
				$invfileFull = "https://ccsnubev2.com/_club/_$domain/invoices/$customer-$invno.pdf";
				$invfile2 = "../../ccsnubev2_com/v6/_club/_$domain/invoices/$customer-$invno.pdf";
				$invfile2Full = "https://ccsnubev2.com/v6/_club/_$domain/invoices/$customer-$invno.pdf";
//				echo "1: $customer - $invno<br />";
//				echo "invfileFull: $invfileFull<br />";
//				echo "invfileFull2: $invfileFull2<br /><br />";
				
			} else {
				
				$brandShort = substr($brand, 0, 2);
				$invfile = "../../ccsnubev2_com/_club/_$domain/invoices/$customer-$invno-$brandShort.pdf";
				$invfileFull = "https://ccsnubev2.com/_club/_$domain/invoices/$customer-$invno-$brandShort.pdf";
				$invfile2 = "../../ccsnubev2_com/v6/_club/_$domain/invoices/$customer-$invno-$brandShort.pdf";
				$invfile2Full = "https://ccsnubev2.com/v6/_club/_$domain/invoices/$customer-$invno-$brandShort.pdf";
//				echo "2: $customer - $invno<br />";
//				echo "invfileFull: $invfileFull<br />";
//				echo "invfile2Full: $invfile2Full<br /><br />";
			
			}
										
			if (file_exists($invfile)) {
				
				$invlink = "<a href='$invfileFull'><img src='images/pdf.png' /><span style='display:none'>1</span></a>";
				
			} else if (file_exists($invfile2)) {
				
				$invlink = "<a href='$invfile2Full'><img src='images/pdf.png' /><span style='display:none'>1</span></a>";
				
			} else {
				
				$invlink = "";
				
			}

			$reinvoice_link = '';
			if($brand == 'SW'){
				$reinvoice_link = "<a href='invoice-action.php?invoice_no=".$invno."'>Re Invoice</a>";
			}

			
			$invoice_no = $customer."-".$invno."-".$brand.".pdf";
			$invoice_path = "../invoices/".$invoice_no;
			$inovice_pdf = '';
			if(file_exists($invoice_path)){
				$inovice_pdf = $invoice_path;
			}
			
			$payment_no = $payment_type = $bank_id = $settled_date = $bank_lodgement_date = $settled_amount='';
			if($payment>0){
				$query_payment = "SELECT * from invoice_payments WHERE id = '$payment'";
				try
				{
					$payment_result = $pdo->prepare("$query_payment");
					$payment_result->execute();
				}
				catch (PDOException $e)
				{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
				}
				$payment_row = $payment_result->fetch();

				$payment_no = $payment_row['id'];
				$payment_type = $payment_row['payment_type'];
				$bank_id = $payment_row['bank_id'];
				$settled_date = date("d-m-Y", strtotime($payment_row['settled_date']));
				$bank_lodgement_date = date("d-m-Y", strtotime($payment_row['bank_lodgement_date']));
				$settled_amount = $payment_row['amount'];
			}
			$write_payment_type = '';
			if($writeOff>0){
					 $query_writeoff = "SELECT * from invoice_writeoffs WHERE id = '$writeOff'";
					try
					{
						$write_result = $pdo->prepare("$query_writeoff");
						$write_result->execute();
					}
					catch (PDOException $e)
					{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
					}
					$write_row = $write_result->fetch();

					$payment_no = $write_row['id'];
					$settled_date = date("d-m-Y", strtotime($write_row['settled_date']));
					$write_payment_type = "Write Off";
					
			}

			if($credit_payment_type == 'CN'){
				$allocate_payment_type = "Credit Note";
			}
			else{
				$allocate_payment_type = $payment_details[$payment_type];
			}
			
			if($write_payment_type != ''){
				$allocate_payment_type = 'Write Off';
			}

			if($credit_payment_type == 'CN'){
				$base_amount = -$base_amount;
				if($creditCardFee > 0){
					$creditCardFee = -$creditCardFee;
				}
				$amount = -$amount;
				$invduedate = '';
			}
			$objPHPExcel->getActiveSheet()
            			->setCellValue('A'.$startIndex, $brand); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('B'.$startIndex, $invno);
            $objPHPExcel->getActiveSheet()
            			->setCellValue('C'.$startIndex, $state); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('D'.$startIndex, $country); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('E'.$startIndex, $customer); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('F'.$startIndex, $longName); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('G'.$startIndex, $invdate); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('H'.$startIndex, $invduedate); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('I'.$startIndex, $currency); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('J'.$startIndex, $shipping); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('K'.$startIndex, $unit_price);
            $objPHPExcel->getActiveSheet()->getStyle('K'.$startIndex)->getAlignment()->setWrapText(true);			 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('L'.$startIndex, $units); 
            $objPHPExcel->getActiveSheet()->getStyle('L'.$startIndex)->getAlignment()->setWrapText(true);			
            $objPHPExcel->getActiveSheet()
            			->setCellValue('M'.$startIndex, $base_amount); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('N'.$startIndex, $discount); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('O'.$startIndex, $vat); 
           	$objPHPExcel->getActiveSheet()
            			->setCellValue('P'.$startIndex, $creditCardFee); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('Q'.$startIndex, $amount); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('R'.$startIndex, $description);
            $objPHPExcel->getActiveSheet()->getStyle('R'.$startIndex)->getAlignment()->setWrapText(true);			 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('S'.$startIndex, $paid); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('T'.$startIndex, $payment_no); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('U'.$startIndex, $allocate_payment_type); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('V'.$startIndex, $bank_details[$bank_id]); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('W'.$startIndex, $settled_date); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('X'.$startIndex, $bank_lodgement_date); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('Y'.$startIndex, $settled_amount); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('Z'.$startIndex, $delta); 
            $objPHPExcel->getActiveSheet()
            			->setCellValue('AA'.$startIndex, '');
		   $startIndex++; 
		    $x++;
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
	    $redirectURL = 'invoices-report.php?fromDate='.$_GET['fromDate'].'&untilDate='.$_GET['untilDate'].'&count='.$countItem.'&totalCount='.$count.'&redirect=1';
	
		header('Refresh: 0; invoices-report.php?fromDate='.$_GET['fromDate'].'&untilDate='.$_GET['untilDate'].'&count='.$countItem.'&totalCount='.$count.'&redirect=1');
		exit();
		ob_end_flush();
}
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

