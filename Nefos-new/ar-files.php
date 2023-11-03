<?php

	require_once 'cOnfig/connection.php';
	// require_once 'cOnfig/view.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);

	
	session_start();

	$memberScript = <<<EOD
	
	    $(document).ready(function() {
		    
		    
			$("#xllink").click(function(){

			  $("#mainTable").table2excel({
			    // exclude CSS class
			    exclude: ".noExl,.dropdown-filter-dropdown",
			    name: "OpenAR",
			    filename: "OpenAR" //do not include extension
		
			  });
		
			});
			
			$.tablesorter.addParser({
			  id: 'dates',
			  is: function(s) { return false },
			  format: function(s) {
			    var dateArray = s.split('-');
			    return dateArray[2].substring(0,4) + dateArray[1] + dateArray[0];
			  },
			  type: 'numeric'
			});
			
			
			/*$('#mainTable').tablesorter({
				usNumberFormat: true,
				headers: {
					6: {
						sorter: "dates"
					},
					8: {
						sorter: "currency"
					}
				}
			}); */
			
		}); 

	function delete_element(delete_id){
      	 if(confirm('Are you sure to delete this Invoice ?')){
      	 	 window.location = "uTil/delete-invoice.php?id="+delete_id;
      	 }
      }
EOD;


	pageStart("Open AR", NULL, $memberScript, "pmembership", NULL, "Open AR", $_SESSION['successMessage'], $_SESSION['errorMessage']);

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



	//-----------------------------------------------------//
?>

<center>
<a href="ar-files-historical.php" class="cta1">Historical</a>
<a href="ar-files-process.php" class="cta1">Save Report</a>
</center>
<br />
	  
<?php

	$output .= <<<EOD
	
	 <table class='default' id='mainTable'>
	  <thead>	
	   <tr style='cursor: pointer;'>
	   	<th style='min-width: 55px;'>LOB</th>
	   	<th style='min-width: 60px;'>Inv. #</th>
	   	<th style='min-width: 75px;'>Cust. #</th>
	   	<th style='min-width: 180px;'>Cust. name</th>
	   	<th style='min-width: 84px;'>Inv. date</th>
	   	<th style='min-width: 84px;'>Due date</th>
	   	<th style='min-width: 85px;'>Age</th>
	   	<th style='min-width: 80px;'>Status</th>
	   	<th style='min-width: 90px;'>Amount</th>
	   	<th style='min-width: 110px;'>Aged 0 - 30</th>
	   	<th style='min-width: 110px;'>Aged 31 - 60</th>
	   	<th style='min-width: 110px;'>Aged 61 - 90</th>
	   	<th style='min-width: 110px;'>Aged > 90</th>
	   	<th style='min-width: 110px;'>Aged > 180</th>
	   </tr>
	  </thead>
	  <tbody>
	
EOD;

			$query = "SELECT * from invoices2 WHERE DATE(invdate) > '2019-12-31' AND paid = '' ORDER BY invdate DESC";
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
		
		while ($row = $results->fetch()) {
			
			$invno = $row['invno'];		
			$paid = $row['paid'];		
			$invdate = date("d-m-Y", strtotime($row['invdate']));
			$invdateSQL = date("Y-m-d", strtotime($row['invdate']));
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
			$delta = $row['delta'];
			$currency = $row['currency'];
			$fee_elements = array_filter(unserialize($row['fees']));
			$credit_payment_type = $row['payment_type'];
			
			$dateNow = date("Y-m-d");
			// Calculate age
			$earlier = new DateTime("$invdateSQL");
			$later = new DateTime("$dateNow");
			$age = $later->diff($earlier)->format("%a");
			
			$sumTotal = $sumTotal + $amount;
			
			$age030amt = 0;
			$age3160amt = 0;
			$age6190amt = 0;
			$ageo90amt = 0;
			$ageo180amt = 0;
					
			if ($age < 31) {
				$age030 = $age030 + $amount;
				$age030amt = $amount;
			} else if ($age < 61) {
				$age3160 = $age3160 + $amount;
				$age3160amt = $amount;
			} else if ($age < 91) {
				$age6190 = $age6190 + $amount;
				$age6190amt = $amount;
			}
			
			if ($age > 90) {
				$ageo90 = $ageo90 + $amount;
				$ageo90amt = $amount;
			}
			if ($age > 180) {
				$ageo180 = $ageo180 + $amount;
				$ageo180amt = $amount;
			}
			
			$daysSinceLastLog = 0;
			$warningText = "";
		
			$query = "SELECT warning, cutoff FROM db_access WHERE customer = '$customer'";
			try
			{
				$result = $pdo->prepare("$query");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user1: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$rowW = $result->fetch();
				$warning = $rowW['warning'];
				
			if ($warning == 1) {
				$warningText = "Soft warning";
			} else if ($warning == 2) {
				$warningText = "Final warning";
			} else if ($warning == 3) {
				$warningText = "<strong>CUT OFF</strong>";
			}
				
				
			
				// Check if customer has been sunset
				$findDomainX = "SELECT domain, db_pwd, warning, cutoff FROM db_access WHERE customer = '$customer'";
				try
				{
					$resultX = $pdo->prepare("$findDomainX");
					$resultX->execute();
					$data2X = $resultX->fetchAll();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user2: ' . $e->getMessage();
						echo $error;
						exit();
				}
		
				if ($data2X) {
		
					$row2X = $data2X[0];
					$domain = $row2X['domain'];
					$_SESSION['customerdomain'] = $domain;
					$db_pwd = $row2X['db_pwd'];
					$warning = $row2X['warning'];
					$cutoff = date("d-m-Y", strtotime($row2X['cutoff']));
					$db_name = "ccs_" . $domain;
					$db_user = $db_name . "u";
					
					try	{
				 		$pdo9 = new PDO('mysql:host='.DATABASE_HOST.';dbname='.$db_name, $db_user, $db_pwd);
				 		$pdo9->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				 		$pdo9->exec('SET NAMES "utf8"');
					}
					catch (PDOException $e)	{
				 		$warningText = '<strong>SUNSET</strong>';
					}
					
				}
				
				
				// Check last log, if more than 3 days then run queries
				$selectUsersUW = "SELECT logtime FROM log ORDER BY logtime DESC LIMIT 1";
				try
				{
					$resultW = $pdo9->prepare("$selectUsersUW");
					$resultW->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			
				$rowW = $resultW->fetch();
					$lastLog = date("Y-m-d", strtotime($rowW['logtime']));
					$lastLogFull = date("d-m-Y H:i", strtotime($rowW['logtime']));
					
				$dateNow = date("Y-m-d");
				
				$date1 = new DateTime("$lastLog");
				$date2 = new DateTime("$dateNow");
				$interval = $date1->diff($date2);
				
				$daysSinceLastLog = $interval->days;
				
				if ($daysSinceLastLog > 3 && $warningText == '') {
					$warningText = 'Stopped using SW';				
				}
				
			
			


			if($currency == ''){
				$currency = 'EUR';
			}
			$units = '';
			$unit_price = '';
			$shipping = '';
			// adding multiple lines in hardware inovices
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
						   $name_arr[$x][$i] = "<strong>".$produNum.".</strong> ".$productName;
						   $unit_arr[$x][$i] = $productQty;
						   $unitPrice_arr[$x][$i] =  $getSalesRow['amount'] / $productQty;
						   //$description .= $productName."<br>";
						   $baseAmount[$x] = $baseAmount[$x] + ($totalAmt[$x] * $discountOp);

						   $i++;

					}
				/*echo "<pre>";
				print_r($name_arr[$x]);*/

				$description = implode("<br>", $name_arr[$x]);
				$units = implode("<br>", $unit_arr[$x]);
				$unit_price = implode("<br>", $unitPrice_arr[$x]);
				$base_amount = $baseAmount[$x];
			}
			// adding fee elements in description for software invoice
			if($brand == 'SW' && !empty($fee_elements)){
				$description = "<strong>1. </strong>".$description;
				$unit_price = $base_amount;
				$subtotal=$base_amount;
				$j=0;
				foreach($fee_elements as $fee_name=> $fee_val){
						$num = $j+2;
						if(is_numeric($fee_val)){
							$subtotal +=$fee_val;
						}
						$fee_name_arr[$x][$j] = "<strong>".$num." .</strong>".$fee_name;
						$fee_val_arr[$x][$j] = $fee_val;

					$j++;
				}
				$description .= "<br>".implode("<br>", $fee_name_arr[$x]);
				$unit_price .= "<br>".implode("<br>", $fee_val_arr[$x]);
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

			$customer_link = 'customer.php?user_id='.$customerId;
			
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
			$invoice_path = "/var/www/html/ccsnubev2_com/v6/invoices/".$invoice_no;
			$inovice_pdf = '';
			if(file_exists($invoice_path)){
				$inovice_pdf = str_replace("/var/www/html/ccsnubev2_com", "https://ccsnubev2.com", $invoice_path);
			}
			if($brand == 'HW' && $inovice_pdf != ''){
				$description = "<a href='".$inovice_pdf."' target='_blank'>".$description."</a>";
			}			
			if($brand == 'SW' && !empty($fee_elements)){
				$description = "<a href='".$inovice_pdf."' target='_blank'>".$description."</a>";
			}
			$payment_no =  $payment_type = $bank_id = $settled_date = $bank_lodgement_date = $settled_amount='';
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


			if($credit_payment_type == 'CN'){
				$allocate_payment_type = "Credit Note";
			}else{
				$allocate_payment_type = $payment_details[$payment_type];
			}

			if($credit_payment_type == 'CN'){
				$base_amount = -$base_amount;
				if($creditCardFee > 0){
					$creditCardFee = -$creditCardFee;
				}
				$amount = -$amount;
				$invduedate = '';
			}
			$output .= sprintf("
			<tr>
			<td class=''>%s</td>
			<td class=''><a href='%s' target='_blank'>%s</a></td>
			<td class=''><a href='%s' target='_blank'>%s</a></td>
			<td class=''><a href='%s' target='_blank'>%s</a></td>
			<td class=''>%s</td>
			<td class=''>%s</td>
			<td class='centered'>%s</td>
			<td class=''>%s</td>
			<td class='right'>%s</td>
			<td class='right'>%s</td>
			<td class='right'>%s</td>
			<td class='right'>%s</td>
			<td class='right'>%s</td>
			<td class='right'>%s</td>

		</tr>",
			$brand,
			$inovice_pdf,$invno,
			$customer_link,
			$customer,
			$customer_link,
			$longName,
			$invdate, 
			$invduedate,
			$age,
			$warningText,
			$amount,
			$age030amt,
			$age3160amt,
			$age6190amt,
			$ageo90amt,
			$ageo180amt

		);

			$x++;
			
		}
		

		echo <<<EOD
<center>
<div id='productoverview'>
 <table>
  <tr>
   <td>Total debt:&nbsp;&nbsp;&nbsp;&nbsp;</td>
   <td class='yellow fat right'>{$expr(number_format($sumTotal,2))}</td>
  </tr>
  <tr class="smaller">
   <td>&nbsp;&nbsp;0 - 30:</td>
   <td class='yellow fat right'><a href='members.php?filter=5'>{$expr(number_format($age030,2))}</a></td>
  </tr>
  <tr class="smaller">
   <td>&nbsp;&nbsp;31 - 60:</td>
   <td class='yellow fat right'><a href='members.php?filter=5'>{$expr(number_format($age3160,2))}</a></td>
  </tr>
  <tr class="smaller">
   <td>&nbsp;&nbsp;61 - 90:</td>
   <td class='yellow fat right'><a href='members.php?filter=5'>{$expr(number_format($age6190,2))}</a></td>
  </tr>
  <tr class="smaller">
   <td>&nbsp;&nbsp;> 90:</td>
   <td class='yellow fat right'><a href='members.php?filter=5'>{$expr(number_format($ageo90,2))}</a></td>
  </tr>
  <tr class="smaller">
   <td>&nbsp;&nbsp;> 180:</td>
   <td class='yellow fat right'><a href='members.php?filter=5'>{$expr(number_format($ageo180,2))}</a></td>
  </tr>
 </table>
</div>
</center>
<br /><br />
<center>
<link rel="stylesheet" href="css/excel-bootstrap-table-filter-style.css">
 <a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});"><img src="images/excel-new.png" style='margin: 0 0 -5px 8px;'/></a>
</center>

<br />	  

EOD;

		echo $output;


		 echo "</tbody></table>";
		
?>
<?php  displayFooter(); ?>
<script src="js/excel-bootstrap-table-filter-bundle.js"></script>
<script src="js/moment.js"></script>
<script type="text/javascript">
	$('#mainTable').excelTableFilter();
</script>
<script type="text/javascript">
	$( "#dialog-3" ).dialog({
	    autoOpen: false, 
	    hide: "puff",
	    show : "slide",
	     position: {
	       my: "top top",
	       at: "top top"
	    }      
	 });
	 $( "#openCOnfirm" ).click(function() {
	    $( "#dialog-3" ).dialog( "open" );
	 });

	 $("#invoiceDateList").click(function(){
	    $("#load").show();
	    $( "#dialog-3" ).dialog( "close" );

	    var fromDate = $("#exceldatepicker").val();
	    var untilDate = $("#exceldatepicker2").val();
	    var url = 'invoices-report.php?fromDate='+fromDate+'&untilDate='+untilDate+'&count=0&totalCount=0';
	    window.open(url, "Invoices Report","height=300,width=300,modal=yes,alwaysRaised=yes");
	    setTimeout(function () {
	        $("#load").hide();
	    }, 2000);    
	 });	 
	 $("#fullList").click(function(){
	    $("#load").show();
	    $( "#dialog-3" ).dialog( "close" );
	  
	    var url = 'invoices-report.php?count=0&totalCount=0';
	    window.open(url, "Invoices Report","height=300,width=300,modal=yes,alwaysRaised=yes");
	    setTimeout(function () {
	        $("#load").hide();
	    }, 2000);    
	 });
</script>	
