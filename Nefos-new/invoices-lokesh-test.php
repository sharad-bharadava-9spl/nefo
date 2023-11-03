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
			    name: "Invoices",
			    filename: "Invoices" //do not include extension
		
			  });
		
			});
		    
			$( "#exceldatepicker" ).datepicker({
				dateFormat: "dd-mm-yy"
		    });
			$( "#exceldatepicker2" ).datepicker({
				dateFormat: "dd-mm-yy"
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


	pageStart("Invoices", NULL, $memberScript, "pmembership", NULL, "Invoices", $_SESSION['successMessage'], $_SESSION['errorMessage']);

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
	
	<link rel="stylesheet" href="css/excel-bootstrap-table-filter-style.css">
<?php if ($_SESSION['userGroup'] == 1) { ?>
	<center><a href='invoices-orig.php' class='cta1'>Old filter types</a><a href='invoice-section.php' class='cta1'>Invoice Section</a></center>
<?php } ?>
	<center><a href="#" id="openCOnfirm"  onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});"><img src="images/excel-new.png" style='margin: 0 0 -5px 8px;'/></a></center><br>
	<style type="text/css">
		.rdiv{
			float: left !important;
		}
	</style>

	 <!-- <table class='default' id='cloneTable'>
      <tr class='nonhover'>
       <td colspan='13' style='border-bottom: 0;'>
         <a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});"><img src="images/excel.png" style='margin: 0 0 -5px 8px;'/></a>
       </td>
      </tr>
     </table> -->
<br />

	 <table class='default' id='mainTable'>
	  <thead>	
	   <tr style='cursor: pointer;'>
	   	<th style='min-width: 55px; background-color: #f2b149;'>LOB</th>
	   	<th style='min-width: 60px; background-color: #f2b149;'>Inv. #</th>
	   	<th style='min-width: 70px; background-color: #f2b149;'>Region</th>
	   	<th style='min-width: 80px; background-color: #f2b149;'>Country</th>
	   	<th style='min-width: 75px; background-color: #f2b149;'>Cust. #</th>
	   	<th style='min-width: 180px; background-color: #f2b149;'>Cust. name</th>
	   	<th style='min-width: 30px; background-color: #f2b149;'>CIF</th>
	   	<th style='min-width: 84px; background-color: #f2b149;'>Inv. date</th>
	   	<th style='min-width: 110px; background-color: #f2b149;'>Inv. due date</th>
	   	<th style='min-width: 85px; background-color: #f2b149;'>Currency</th>
	   	<th style='min-width: 80px; background-color: #f2b149;'>Shipping</th>
	   	<th style='min-width: 90px; background-color: #f2b149;'>Unit price</th>
	   	<th style='min-width: 80px; background-color: #f2b149;'># items</th>
	   	<th style='min-width: 90px; background-color: #f2b149;'>Base amt</th>
	   	<th style='min-width: 85px; background-color: #f2b149;'>Discount</th>
	   	<th style='min-width: 53px; background-color: #f2b149;'>IVA</th>
	   	<th style='min-width: 68px; background-color: #f2b149;'>CC fee</th>
	   	<th style='min-width: 90px; background-color: #f2b149;'>Total amt</th>
	   	<th style='min-width: 200px; background-color: #f2b149;'>Description</th>
	   	<th style='min-width: 75px; background-color: #f2b149;'>Status</th>
	   	<th style='min-width: 95px; background-color: #f2b149;'>Payment #</th>
	   	<th style='min-width: 115px; background-color: #f2b149;'>Payment type</th>
	   	<th style='min-width: 75px; background-color: #f2b149;'>Bank ID</th>
	   	<th style='min-width: 105px; background-color: #f2b149;'>Settled date</th>
	   	<th style='min-width: 125px; background-color: #f2b149;'>Lodgment date</th>
	   	<th style='min-width: 100px; background-color: #f2b149;'>Amt settled</th>
	   	<th style='min-width: 60px; background-color: #f2b149;'>Delta</th>
	   	<th style='min-width: 95px; background-color: #f2b149;'>Comments</th>
	   	<th style='min-width: 70px; background-color: #f2b149;'>Action</th>


	    <!-- <th class='centered'>#</th>
	    <th class='centered'>Club</th>
	    <th class='centered'>Region</th>
	    <th class='centered'>Country</th>
	    <th class='centered'>Inv #</th>
	    <th class='centered'>Inv date</th>
	    <th class='centered'>Invoice due date</th>
	    <th class='centered'>Amount</th>
	    <th class='centered'>Brand</th>
	    <th class='centered'>Status</th>
	    <th class='centered noExl'>Action</th> -->
	   </tr>
	  </thead>
	  <tbody>
	  
	  
<?php

		$resultLimit = 3000;
		if (isset($_GET['page'])) {
			$page = $_GET['page'] + 1;
			$offset = $resultLimit * $page;
		} else {
			$page = 0;
			$offset = 0;
		}

		if (isset($_GET['customer'])) {
			
			$query = "SELECT a.*,b.id AS p_id, b.payment_type AS p_payment_type, b.bank_id AS p_bank_id, b.settled_date AS p_settled_date, b.bank_lodgement_date AS p_bank_lodgement_date, b.amount AS p_amount  from invoices a  LEFT JOIN invoice_payments b ON a.payment = b.id WHERE DATE(a.invdate) > '2019-12-31' AND a.deleteFlag =0 AND a.customer = '{$_GET['customer']}' ORDER BY a.invdate DESC LIMIT $offset, $resultLimit";
			$queryTotal = ("SELECT * from invoices  LEFT JOIN invoice_payments ON invoices.payment = invoice_payments.id WHERE DATE(invoices.invdate) > '2019-12-31' AND invoices.deleteFlag =0  AND a.customer = '{$_GET['customer']}' ORDER BY invoices.invdate DESC");
		
			
		} else {
			$query = "SELECT a.*,b.id AS p_id, b.payment_type AS p_payment_type, b.bank_id AS p_bank_id, b.settled_date AS p_settled_date, b.bank_lodgement_date AS p_bank_lodgement_date, b.amount AS p_amount  from invoices a  LEFT JOIN invoice_payments b ON a.payment = b.id WHERE DATE(a.invdate) > '2019-12-31' AND a.deleteFlag =0 ORDER BY a.invdate DESC LIMIT $offset, $resultLimit";

			$queryTotal = ("SELECT * from invoices  LEFT JOIN invoice_payments ON invoices.payment = invoice_payments.id WHERE DATE(invoices.invdate) > '2019-12-31' AND invoices.deleteFlag =0 ORDER BY invoices.invdate DESC");
		}
		
		// $query = "SELECT * from invoices WHERE invno LIKE ('%180%') OR invno LIKE ('%M1%') ORDER BY invdate DESC";
		try
		{
			$results = $pdo->prepare("$query");
			$results->execute();

			$totalRecords = $pdo->prepare("$queryTotal");
			$totalRecords->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		//-----pagination calculate------------
		$noOfClosings = $totalRecords->rowCount();
		$resultsLeft = $noOfClosings - ($page * $resultLimit);
		$x=0;
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
			$payment_no =  $payment_type = $bank_id = $settled_date = $bank_lodgement_date = $settled_amount='';
			if($payment > 0){
				$payment_no = $row['p_id']; 
				$payment_type = $row['p_payment_type'];
				$bank_id = $row['p_bank_id'];
				$settled_date = date("d-m-Y", strtotime($row['p_settled_date']));
				$bank_lodgement_date = date("d-m-Y", strtotime($row['p_bank_lodgement_date']));
				$settled_amount = $row['p_amount'];
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
					$checkOrder = "SELECT * from sales a, salesdetails b, products c WHERE a.saleid =".$saleid."  AND a.saleid = b.saleid AND b.productid = c.productid";
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

/*				   while($orderRow = $check_result->fetch()){
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
					   }*/
				   $i =0;	
				   while($getSalesRow = $check_result->fetch()){
				   	   $shipping = $getSalesRow['shipping'];
					   $total_amount = $getSalesRow['amount'];
					   $paymentoption = $getSalesRow['paymentoption'];
					   $productid = $getSalesRow['productid'];
					   $purchaseid = $getSalesRow['purchaseid'];
					   // get name
	/*					$getName = "SELECT name from products WHERE productid =".$productid;

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
						   $nameRow = $select_result->fetch();*/
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
						
						   $productName = $getSalesRow['name'];
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
			$selectUsersU = "SELECT id,longName, state, country, cif FROM customers WHERE number = '$customer'";
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
			$cif = $rowX['cif'];
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
			if($brand == 'HW' && $inovice_pdf != ''){
				$description = "<a href='".$inovice_pdf."' target='_blank'>".$description."</a>";
			}			
			if($brand == 'SW' && !empty($fee_elements)){
				$description = "<a href='".$inovice_pdf."' target='_blank'>".$description."</a>";
			}

/*			if($payment>0){
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
			}	*/
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
			echo sprintf("
			<tr>
			<td class=''>%s</td>
			<td class=''><a href='%s' target='_blank'>%s</a></td>
			<td class=''>%s</td>
			<td class=''>%s</td>
			<td class=''><a href='%s' target='_blank'>%s</a></td>
			<td class=''><a href='%s' target='_blank'>%s</a></td>
			<td class=''>%s</td>
			<td class=''>%s</td>
			<td class=''>%s</td>
			<td class=''>%s</td>
			<td class=''>%s</td>
			<td class=''>%s</td>
			<td class=''>%s</td>
			<td class=''>%s</td>
			<td class=''>%s</td>
			<td class=''>%s</td>
			<td class=''>%s</td>
			<td class=''>%s</td>
			<td class=''>%s</td>
			<td class=''>%s</td>
			<td class=''>%s</td>
			<td class=''>%s</td>
			<td class=''>%s</td>
			<td class=''>%s</td>
			<td class=''>%s</td>
			<td class=''>%s</td>
			<td class=''>%s</td>
			<td class=''>%s</td>
			<td class=''><a href='javascript:void(0);' onClick='delete_element(\"".$invno."\")'><img src='images/delete.png' height='15' title='Delete Invoice'></a></td>
		</tr>",
			$brand,
			$inovice_pdf,$invno,
			$state, 
			$country, 
			$customer_link,
			$customer,
			$customer_link,
			$longName,
			$cif,
			$invdate, 
			$invduedate,
			$currency,
			$shipping,
			$unit_price,
			$units,
			$base_amount,
			$discount,
			$vat,
			$creditCardFee,
			$amount,
			$description,
			$paid,
			$payment_no,
			$allocate_payment_type,
			$bank_details[$bank_id],
			$settled_date,
			$bank_lodgement_date,
			$settled_amount,
			$delta,
			''
			
		);


// 			echo sprintf("
//   	  <tr>
//   	   <td class=''><a href='%s' target='_blank'>%s</a></td>
//   	   <td class=''>%s</td>
//   	   <td class=''>%s</td>
//   	   <td class=''>%s</td>
//   	   <td class=''>%s</td>
//   	   <td class=''>%s</td>
//   	   <td class=''>%s</td>
//   	   <td class='right'>%s</td>
//   	   <td class='centered'>%s</td>
//   	   <td class='centered'>%s</td>
//   	   <td class='centered noExl'>%s</td>
// </tr>",
// 	  $inovice_pdf, $customer, $longName, $state, $country, $invno, $invdate, $invduedate, $amount, $brand, $paid,  $reinvoice_link);
			$x++;
			
		}

		// Pagination display
		$output = '';
		if ($resultsLeft < $resultLimit && $offset != 0) {
			$last = $page - 2;
			$output .=  "<br /><a href='$_PHP_SELF?page=$last' style='font-size:16px;'>&laquo; Previous</a><br />&nbsp;";
		 } else if ($page > 0) {
			$last = $page - 2;
			$output .=  "<strong><a href='$_PHP_SELF?page=$last' style='font-size:16px;'>&laquo;Previous</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='$_PHP_SELF?page=$page' style='font-size:16px;'>Next&raquo;</a></strong><br />&nbsp;";
		} else if ($page == 0 && $offset == 0 && $noOfClosings>$resultLimit) {
			$output .=  "<br /><a href='$_PHP_SELF?page=$page' style='font-size:16px;'>Next &raquo;</a><br />&nbsp;";
		}
		 echo "</tbody></table>";
		 echo "<center>".$output."</center>";
		
?>
   <div  class="actionbox-npr" id = "dialog-3" title = "Invoices">
		
		<div class='boxcomtemt'>
<?php if ($_SESSION['userGroup'] == 1) { ?>
			<button class="cta1" id="fullList">Entire List</button>OR
<?php } ?>
			<p>Exportar Excel entre fechas:</p><br>
			<input type="text" id="exceldatepicker" name="fromDate" autocomplete="nope" class="sixDigit defaultinput" placeholder="Desde fecha" />
			 <input type="text" id="exceldatepicker2" name="untilDate" autocomplete="nope" class="sixDigit defaultinput" placeholder="Hasta fecha" />
				<button class='cta1' id="invoiceDateList">Ok</button>
			
		</div>
	</div> 
<a href="#" style="position: fixed; top: 200px; right: 80px; background-color: #5aa242; color: #fff; padding: 10px; border-radius: 5px;">Scroll to top</a>
<?php  displayFooter(); ?>
<!-- <script src="js/excel-bootstrap-table-filter-bundle.js"></script>
<script type="text/javascript">
	$('#mainTable').excelTableFilter();
</script> -->
<script type="text/javascript" src="https://unpkg.com/tablefilter@latest/dist/tablefilter/tablefilter.js"></script>
<script type="text/javascript">
	var filtersConfig = {
  // instruct TableFilter location to import ressources from
  base_path: 'https://unpkg.com/tablefilter@latest/dist/tablefilter/',
/*  col_0: 'select',
  col_1: 'select',
  col_2: 'select',
  col_3: 'select',
  col_4: 'select',
  col_5: 'select',
  col_6: 'select',
  col_7: 'select',
  col_8: 'select',
  col_9: 'select',
  col_10: 'select',
  col_11: 'select',
  col_12: 'select',
  col_13: 'select',
  col_14: 'select',
  col_15: 'select',
  col_16: 'select',
  col_17: 'select',
  col_18: 'select',
  col_19: 'select',
  col_20: 'select',
  col_21: 'select',
  col_22: 'select',
  col_23: 'select',
  col_24: 'select',
  col_25: 'select',
  col_26: 'select',*/
  btn_reset: true,
  loader: true,
  auto_filter: true,
  /*rows_counter: true,*/
  mark_active_columns: true,
  highlight_keywords: true,
  no_results_message: true,
  col_types: [
        'string',
        'string',
        'string',
        'string',
        'number',
        'string',
        { type: 'date', locale: 'fr' },
        { type: 'date', locale: 'fr' },
        'string',
        'number',
        'number',
        'number',
        'number',
        'number',
        'number',
        'number',
        'number',
        'string',
        'string',
        'number',
        'string',
        'string',
        { type: 'date', locale: 'fr' },
        { type: 'date', locale: 'fr' },
        'number',
        'string',
    ],
  extensions: [{
    name: 'sort',
    images_path: 'https://unpkg.com/tablefilter@latest/dist/tablefilter/style/themes/'
  }]
};

var tf = new TableFilter('mainTable', filtersConfig);
tf.init();
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
	    var url = 'invoices-report-lokesh.php?fromDate='+fromDate+'&untilDate='+untilDate+'&count=0&totalCount=0';
	    window.open(url, "Invoices Report","height=300,width=300,modal=yes,alwaysRaised=yes");
	    setTimeout(function () {
	        $("#load").hide();
	    }, 2000);    
	 });	 
	 $("#fullList").click(function(){
	    $("#load").show();
	    $( "#dialog-3" ).dialog( "close" );
	  
	    var url = 'invoices-report-lokesh.php?count=0&totalCount=0';
	    window.open(url, "Invoices Report","height=300,width=300,modal=yes,alwaysRaised=yes");
	    setTimeout(function () {
	        $("#load").hide();
	    }, 2000);    
	 });
</script>	
