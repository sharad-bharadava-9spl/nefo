<?php 
ob_start();


// include autoloader
require_once 'vendor/autoload.php';
require_once 'cOnfig/connection.php';

	$saleid = str_replace("'","\'",str_replace('%', '&#37;', trim($_GET['saleid']))); 
	$invoice_date = trim($_GET['invoiceDate']); 
	$invoice_date =  date("Y-m-d", strtotime($invoice_date));
	$invoice_due_date =  trim($_GET['dueDate']); 
	$invoice_due_date =  date("Y-m-d", strtotime($invoice_due_date));
	$credit_amount =  trim($_GET['credit_amount']); 
	$without_invoice_header =  $_GET['without_header']; 
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
	   $customer = $orderRow['customer'];
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
		   $order_arr[$i]['name'] = $productName;
		   $order_arr[$i]['quantity'] = $productQty;
		   $order_arr[$i]['amount'] = $getSalesRow['amount'] * $discountOp;
		   $order_arr[$i]['price'] = $getSalesRow['amount'] / $productQty;
		   $order_arr[$i]['discount'] = $discountTxt;

		   $i++;

	}

   //--=====customer details================--
   $query = "SELECT customer FROM db_access WHERE domain = '$customer'";
		try
		{
			$result = $pdo->prepare("$query");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$custnumber = $row['customer'];
		
	$query = "SELECT shortName, street, streetnumber, flat, postcode, city, email, phone, shipping, number, vat FROM customers WHERE number = '$custnumber'";
	try
	{
		$result = $pdo2->prepare("$query");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	$row = $result->fetch();
   //--====customer details==================--

//-------------------------------------------------------------------------------------

		$invoice_type ="HW"; 
		$customer_number= $row['number'];
		$customer_name = $row['shortName'];
		$vat = explode(".",$row['vat']);
		$vat = ($vat[1]>0)?$row['vat']:$vat[0];

		$order_arr=$order_arr;
		$iban = ($invoice_type=="SW")? "ES94 0182 0981 4902 0318 3962" : "ES74 0182 0981 4002 0319 2038";

		$billing_details = '';
		$subtotal=$base_amount;
	 
		foreach($order_arr as $order_val){
			$subtotal +=$order_val['amount'];
			$billing_details .= '<tr class="item-row"><td>'.$order_val['name'].'</td>
								<td style="text-align: center;">'.$order_val['quantity'].'</td>
								<td style="text-align: right;">'.number_format($order_val['price'], 2) .' €</td>
								<td style="text-align: center;">'.$order_val['discount'].'</td>
								<td style="text-align: right;"><span class="price">'.number_format($order_val['amount'], 2).' €</span></td>
								</tr>';

			}
	   //--------calculating the vat amount-------//
	   	if($invoice_type=="SW"){
			$vatAmount = ($vat>0)? number_format($subtotal*$vat/100,2, '.', '') : 0;
		}else{
			$vatAmount = ($vat>0)? number_format(($subtotal+$shipping)*$vat/100,2, '.', '') : 0;
		}

		$shippingText='';
		$ccFeesText='';
		$creditText='';
		$ccFee=0;
		if ($paymentoption == 8 || $paymentoption == 9) {	
			$totAmt = number_format($subtotal+$shipping+$vatAmount, 2, '.', '');

			$ccFee = number_format($totAmt * 0.015,2, '.', '');
			$total_amount = number_format($total_amount + $ccFee,2, '.', '');

			$ccFeesText = '<tr>
				<td>+ Credit card fee (1.5%)</td>
				<td style="text-align:right;">'.$ccFee.' €</td>
			</tr>';
		}
		$creditText='';
		if($credit_amount>0){
			$creditText = '<tr>
				<td>- Credit</td>
				<td style="text-align:right;">'.number_format($credit_amount, 2).' €</td>
			</tr>';
		}
		if($shipping>0){
			$shippingText = '<tr>
				<td>+ Shipping</td>
				<td style="text-align:right;">'.$shipping.' €</td>
			</tr>';
		}
		$total_amount = number_format($subtotal+$shipping+$vatAmount+$ccFee-$credit_amount,2, '.', '');


		//-----invoice generated--------------------

			$query = "select * from invoices2";
			try
			{
				$inv_result = $pdo->prepare("$query");
				$inv_result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			while($inv_row = $inv_result->fetch()){
				$inv_number = $inv_row['invno'];

				if(strpos($inv_number, 'S') !== false){
					$inv_arr[] = str_replace("S", "", $inv_number);
				}else if(strpos($inv_number, 'M') !== false){
					$inv_arr[] = str_replace("M", "", $inv_number);
				}else if(strpos($inv_number, 'CN') !== false){
					$inv_arr[] = str_replace("CN", "", $inv_number);
				}
				else{
					$inv_arr[] = $inv_number;
				}
			}

			$maxInvoiceNumber = max($inv_arr);  

			if(strpos($maxInvoiceNumber, 'S') !== false){
				$invoiceNumber = str_replace("S", "", $maxInvoiceNumber);
			}else{
				$invoiceNumber = $maxInvoiceNumber;
			}
			$nextInvoiceNumber = $invoiceNumber + 1;

			if($total_amount < 400){
				$invNo = 'S'.$nextInvoiceNumber;
			}else{
				$invNo = $nextInvoiceNumber;
			}

			$status = "";

			if (abs(($total_amount-$credit_amount)/$credit_amount) < 0.00001) {
				$status = 'Paid';
			}
			$currency = 'EUR';
			// Query to update user - 28 arguments
			$insertInvoice = sprintf("INSERT INTO invoices2 (invno, paid, invdate, invduedate,invoice_generate_time, currency, base_amount, amount, vat, order_id, customer, brand, client_credit, credit_card_fee) VALUES ('%s', '%s', '%s','%s','%s', '%s', '%f', '%f', '%f', '%d', '%s', '%s', '%f', '%f')",
			$invNo,
			$status,
			$invoice_date,
			$invoice_due_date,
			date('H:i:s'),
			$currency,
			$subtotal,
			$total_amount,
			$vat,
			$saleid,
			$customer_number,
			$invoice_type,
			$credit_amount,
			$ccFee
			);
			try
			{
				$result = $pdo->prepare("$insertInvoice")->execute();
			}
			catch (PDOException $e)
			{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
			}
		//-----invoice generated--------------------

		if($credit_amount > 0){	

				// check last credit amount of customer

				$checkCredit = "SELECT credit FROM customers WHERE number =".$customer_number;

				try
				{
					$credit_result = $pdo3->prepare("$checkCredit");
					$credit_result->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
				$fetch_credit = $credit_result->fetch();
						$client_credit = $fetch_credit['credit'] - $credit_amount;
					

				// update in credit movements for credits
				$movement_at = date("Y-m-d h:i:s");
				$insertMovement = "INSERT INTO credit_movements SET invoice_id = '$invNo', customer = '$customer_number', credit_status = 'Used', amount = '$credit_amount', movement_at = '$movement_at', credit_reason ='0'";

				try
				{
					$insert_movement = $pdo3->prepare("$insertMovement");
					$insert_movement->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}

				

				// update credit in customers table

				$updateCredit = "UPDATE customers SET credit = '$client_credit' WHERE number =".$customer_number;

				try
				{
					$update_credit = $pdo3->prepare("$updateCredit");
					$update_credit->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}

				// insert into credit

				// Query to update user - 28 arguments

				$delta_credit  = -$credit_amount;
				$insertCredit = "INSERT into credits SET invoice_no = '$invNo', customer = '$customer_number', amount = '$delta_credit', credit_balance = '$client_credit', credit_type = 2, created_at = '$movement_at'";  
				try
				{
					$result = $pdo3->prepare("$insertCredit")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}

		}
		

		//----------fetch customer details-------------
		$queryCustomerDetails = "SELECT * from customers WHERE number =".$customer_number;
		try
		{
			$customerDetails = $pdo2->prepare("$queryCustomerDetails");
			$customerDetails->execute();
			
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		$customerDetails = $customerDetails->fetch();

		$dueDateText='';
		if($invoice_date==$invoice_due_date){
			$dueDateText = "Se vence al recibo";
		}else{
			$dueDateText = date('d-m-Y',strtotime($invoice_due_date));
		}
		// get the customer domain name
		$getDomain = "SELECT domain from db_access WHERE customer =".$customer_number;
		try
		{
			$domainDetails = $pdo->prepare("$getDomain");
			$domainDetails->execute();
			
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		$invoice = $customer_number."-".$invNo."-".$invoice_type;
		$domainCount = $domainDetails->rowCount();
		$invoice_club_path = '';
		$invoice_club_dir = '';
		if($domainCount > 0){
			$domainRow = $domainDetails->fetch();
			$domain = $domainRow['domain'];
			$invoice_club_path = '../_club/_'.$domain.'/invoices/'.$invoice.'.pdf';
			$invoice_club_dir = '../_club/_'.$domain.'/invoices';
		}
		
		$invoice_root_path = "../invoices/".$invoice.".pdf";
		$invoice_root_dir = "../invoices";
		
		// update invoice in db with credit card fees

		$updateInvoice = "UPDATE invoices2 SET credit_card_fee = '".$ccFee."', amount = '$total_amount' WHERE invno ='".$invoice_val."'";
		try
		{
			$invoice_update = $pdo->prepare("$updateInvoice");
			$invoice_update->execute();
			
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}


		$header_section = '';
	if($without_invoice_header == 'Yes'){
		$header_section = '<div id="address">
								<span class="bold_text">Mykinlink SL</span><br>
								B87843504<br>
								Calle Clara Del Rey 36, Planta 2, Puerta B<br>
								28002 Madrid,<br>
								España<br>
				        </div>';
	}else{
		$header_section = '<div id="identity">
				            <div id="logo">
							  <img src="data:image/png;base64,' . base64_encode(file_get_contents('invoice-logo.png')).'" alt="logo" >
							</div>
						</div>
						<div style="clear:both"></div>
						<div id="address"><span>www.cannabisclub.systems - info@cannabisclub.systems</span>
							<br><br>
								<span class="bold_text">Mykinlink SL</span><br>
								B87843504<br>
								Calle Clara Del Rey 36, Planta 2, Puerta B<br>
								28002 Madrid,<br>
								España<br>
				        </div>';
	}		



// reference the Dompdf namespace
use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->set('defaultFont', 'Calibri');
$options->set('isRemoteEnabled', TRUE);
$options->set('debugKeepTemp', TRUE);
$options->set('isHtml5ParserEnabled', TRUE);

//$options->set('chroot', '');
// $dompdf = new Dompdf($options);
new Dompdf($options,array('enable_remote' => true));
$invoice = $customer_number."-".$invNo."-".$invoice_type;
$image = $siteroot."invoice-logo.png";
$html = '<html><head>
<style type="text/css" media="all">
@font-face {
  font-family: "Calibri";
  font-style: normal;
  font-weight: normal;
  src: url(fonts/Calibri.ttf) format("truetype");
}
@font-face {
  font-family: "Calibri-Bold";
  font-style: normal;
  font-weight: 700;
  src: url(fonts/calibrib.ttf) format("truetype");
}
body { margin:10px; border:2px solid black; padding:5px; line-height: 2; font: 14px/1.4 Calibri, Calibri;}
.bold_text{ font-family: "Calibri-Bold"; }
table { border-collapse: collapse; border: 2px solid black; }
table th { border: 2px solid black; padding: 5px; }
tr.head_col td{ border: 2px solid black; padding: 5px; }
table td{ border: 1px solid black; padding: 5px; }
table#meta td{ border: none !important; }

#address { width: 100%; height: auto; float: left; margin-bottom:20px; padding:5px; line-height:1.5}
#address2 { width: 45%; height: auto; float: left; margin-bottom:100px; padding:5px; line-height:1.5;}

#logo { text-align: right; float: left; position: relative; margin-top: 0px; border: 2px solid #fff; max-width: 540px; max-height: 110px; overflow: hidden; }

#logoctr { display: none; }
#logo:hover #logoctr, #logo.edit #logoctr { display: block; text-align: right; line-height: 25px; background: #eee; padding: 0 5px; }
#logohelp { text-align: left; display: none; font-style: italic; padding: 10px 5px;}
#logohelp input { margin-bottom: 5px; }
.edit #logohelp { display: block; }
.edit #save-logo, .edit #cancel-logo { display: inline; }
.edit #image, #save-logo, #cancel-logo, .edit #change-logo, .edit #delete-logo { display: none; }
#customer-title {  font-family: "Calibri-Bold"; font-size:21px; text-align: center;}
#customer{ width: 45%; float: right; }
.lastDetails {
    font: 12px/1.0 Calibri, Calibri;
}

#meta { margin-top: 18px;  border: none !important; }
#meta td.meta-head { text-align: left; }

#items { clear: both; width: 100%; margin: 30px 0 0 0; border: 2px solid black; }

#items tr.item-row td { vertical-align: top; }
#items td.description { width: 300px; }
#items td.item-name { width: 175px; }
#items td.total-line { border-right: 0; text-align: right; }
#items td.total-value { border-left: 0; padding: 5px; }
#items td.balance { background: #eee; }
#items td.blank { border: 0; }
#subtotal{  float: right;  width:40%; position:relative; left:415px;  }
#calculateTable { width:100%; }
.sub_detail{display: block; border-bottom: 1px solid black; width: auto; padding: 8px; }
#terms { text-align: center; margin: 20px 0 0 0; }
#terms h5 { text-transform: uppercase; font: 13px Calibri, Calibri; letter-spacing: 10px; border-bottom: 1px solid black; padding: 0 0 8px 0; margin: 0 0 8px 0; }
#final_details{width: 400px; border: 2px solid black; padding: 5px;}

</style>
</head>
<body>
	<div id="page-wrap">
		<div>
			'.$header_section.'
			<div style="clear:both;"></div>
	    </div>
		<div id="address2">
			<div style="clear:both;"></div>
			<span class="bold_text">'.$customerDetails['longName'].'</span><br>
			'.$customerDetails['cif'].'<br>
			'.$customerDetails['street'].' '.$customerDetails['streetnumber'].'<br>
			'.$customerDetails['flat'].'
			'.$customerDetails['city'].' '.$customerDetails['postcode'].'
			'.$customerDetails['state'].'
			'.$customerDetails['country'].'
		</div>
		<div>
			<div id="customer-title">FACTURA</div>
			<br>
		</div>
		
		<div id="customer" style="border:2px solid black; ">
            <table id="meta">
                <tr>
                    <td class="meta-head">Numero cliente</td>
                    <td>'.$customer_number.'</td>
                </tr>
                <tr>

                    <td class="meta-head">Numero factura</td>
                    <td>'.$invNo.'</td>
                </tr>
                <tr>
                    <td class="meta-head">Fecha facturación</td>
                    <td><div class="due">'.date('d-m-Y',strtotime($invoice_date)).'</div></td>
                </tr>
                <tr>
                    <td class="meta-head">Se vence al recibo</td>
                    <td><div class="due">'.$dueDateText.'</div></td>
                </tr>
            </table>
		</div>

		<div id="details">
			<table id="items">
				<tr class="head_col">
			      <td><span class="bold_text">Concepto</span></td>
			      <td><span class="bold_text">Cantidad</span></td>
			      <td><span class="bold_text">Precio</span></td>
			      <td><span class="bold_text">Descuento</span></td>
			      <td><span class="bold_text">Total</span></td>
			  	</tr>
			  '.$billing_details.'
			</table>
			<div style="clear:both;"></div>
		</div>

	
			<div id="subtotal">
				<br>
				<table id="calculateTable">
					<tr>
						<td>Subtotal</td>
						<td style="text-align:right;">'.number_format($subtotal, 2).' €</td>
					</tr>
					'.$creditText.$shippingText.'
					<tr>
						<td>+ IVA ('.$vat.'%)</td>
						<td style="text-align:right;">'.$vatAmount.' €</td>
					</tr>
					'.$ccFeesText.'
				</table>
				<br>
				<table id="calculateTable">
					<tr>
						<td style="border:none;"><span class="bold_text">A pagar</span></td>
						<td style="border:none; text-align:right;"><span class="bold_text">'.$total_amount.' €</span></td>
					</tr>
				</table>
			</div>
			<div style="clear:both;"></div>
		
		<br>
		<div id="final_details">
			<table class="lastDetails" id="meta">
				<tr>
					<td colspan="2"><span class="bold_text"><u>Detalles del pago</u></span></td>
				</tr>
				<tr>
					<td colspan="2">Transferencia bancaria</td>
				</tr>
				<tr>
					<td>Titular:</td>
					<td>Mykinlink SL</td>
				</tr>
				<tr>
					<td>CIF:</td>
					<td>B87843504</td>
				</tr>
				<tr>
					<td>IBAN:</td>
					<td>'.$iban.'</td>
				</tr>
				<tr>
					<td>SWIFT:</td>
					<td>BBVAESMM</td>
				</tr>
				<tr>
					<td>Concepto:</td>
					<td>'.$invoice.'</td>
				</tr>
			</table>
		</div>
	</div>
</body>
</html>';

ob_get_clean();

	if(!is_dir($invoice_club_dir)){
    	mkdir($invoice_club_dir, 0777, true);
	} 	
	if(!is_dir($invoice_root_dir)){
    	mkdir($invoice_root_dir, 0777, true);
	} 
$dompdf = new DOMPDF();
$dompdf->loadHtml($html);
$dompdf->render();
$output = $dompdf->output();
if($invoice_club_path != ''){
	$invoice_path = $invoice_club_path;
	file_put_contents($invoice_club_path, $output);
}else{
	$invoice_path = $invoice_root_path;
}
file_put_contents($invoice_root_path, $output);
$_SESSION['successMessage'] = "Invoice generated successfully!, please click here to <a href='".$invoice_path."' target='_blank'>Download Invoice</a>";
header("Location: dispense.php?saleid=".$saleid);
//$dompdf->stream($invoice.".pdf");
