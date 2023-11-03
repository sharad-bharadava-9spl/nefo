<?php 
ob_start();


// include autoloader
require_once 'vendor/autoload.php';
require_once 'cOnfig/connection.php';
// reference the Dompdf namespace
use Dompdf\Dompdf;
use Dompdf\Options;
//session_start();
		$invoices = $_SESSION['invoices'];
		$old_invoices = $_SESSION['old_invoices']; 
		$old_payment_type = $_SESSION['old_payment_type']; 
		$payment_type = $_SESSION['payment_type']; 
		$invoice_path_arr = [];

		$removed_invoices  = array_diff($old_invoices, $invoices);

if(!empty($removed_invoices) && $old_payment_type == 3){

	foreach($removed_invoices as $removed_invoice_val){

			// fetch invoice details

				$selectRemovedInvoice = "SELECT * FROM invoices2 WHERE invno = '".$removed_invoice_val."'";

				try
			   {
				   $remove_invoice_result = $pdo->prepare("$selectRemovedInvoice");
				   $remove_invoice_result->execute();
			   }
			   catch (PDOException $e)
			   {
					   $error = 'Error fetching user: ' . $e->getMessage();
					   echo $error;
					   exit();
			   }
			   $remove_invoice_row = $remove_invoice_result->fetch();

			   		$remove_invoice_type =$remove_invoice_row['brand']; 
					$remove_status = $remove_invoice_row['paid'];
					$remove_customer_number= $remove_invoice_row['customer'];
					$remove_invoice_date = $remove_invoice_row['invdate'];
					$remove_invoice_due_date = $remove_invoice_row['invduedate'];
					$remove_base_amount = $remove_invoice_row['base_amount'];
					$remove_total_amount = $remove_invoice_row['amount'];
					$remove_discount = $remove_invoice_row['discount'];
					$remove_member_value = $remove_invoice_row['member_section'];
					$remove_vat = explode(".",$remove_invoice_row['vat']);
					$remove_vat = ($remove_vat[1]>0)?$remove_invoice_row['vat']:$remove_vat[0];

					$remove_fees_elements =  array_filter(unserialize($remove_invoice_row['fees']));
					$remove_description = $remove_invoice_row['description'];
					$remove_invNo = $remove_invoice_row['invno'];
					$remove_customer_name = $remove_invoice_row['customer_name'];
					$remove_credit_amount = $remove_invoice_row['client_credit'];
					$remove_debit_amount = $remove_invoice_row['client_debit'];
					$remove_saleid = $remove_invoice_row['order_id'];

					// check order id
					$removecheckOrder = "SELECT * from sales WHERE saleid =".$remove_saleid;
						try
					   {
						   $remove_check_result = $pdo2->prepare("$removecheckOrder");
						   $remove_check_result->execute();
					   }
					   catch (PDOException $e)
					   {
							   $error = 'Error fetching user: ' . $e->getMessage();
							   echo $error;
							   exit();
					   }
				   $remove_num_order = $remove_check_result->rowCount();

				   while($remove_orderRow = $remove_check_result->fetch()){
					   $remove_shipping = $remove_orderRow['shipping'];
					   $remove_total_amount_order = $remove_orderRow['amount'];
					   $remove_paymentoption = $remove_orderRow['paymentoption'];
				   }

				      // get product details

				   $remove_selectProduct = "SELECT * from salesdetails WHERE saleid =".$remove_saleid;

					   try
					   {
						   $remove_get_result = $pdo2->prepare("$remove_selectProduct");
						   $remove_get_result->execute();
					   }
					   catch (PDOException $e)
					   {
							   $error = 'Error fetching user: ' . $e->getMessage();
							   echo $error;
							   exit();
					   }
				   $i =0;	
				   while($remove_getSalesRow = $remove_get_result->fetch()){
					   $remove_productid = $remove_getSalesRow['productid'];
					   $remove_purchaseid = $remove_getSalesRow['purchaseid'];
					   // get name
						$remove_getName = "SELECT name from products WHERE productid =".$remove_productid;

						   try
						   {
							   $remove_select_result = $pdo2->prepare("$remove_getName");
							   $remove_select_result->execute();
						   }
						   catch (PDOException $e)
						   {
								   $error = 'Error fetching user: ' . $e->getMessage();
								   echo $error;
								   exit();
						   }
						   $remove_nameRow = $remove_select_result->fetch();
						   $remove_quantity = $remove_getSalesRow['quantity'];
						   //---------discount-----------------
						   if ($remove_purchaseid == 12 || $remove_purchaseid == 13 || $remove_purchaseid == 14 || $remove_purchaseid == 15 || $remove_purchaseid == 16) {
								if ($remove_quantity > 99) {
									$remove_discountTxt = '20%';
									$remove_discountOp = 0.8;
								} else if ($remove_quantity > 49) {
									$remove_discountTxt = '10%';
									$remove_discountOp = 0.9;
								} else if ($remove_quantity > 9) {
									$remove_discountTxt = '5%';
									$remove_discountOp = 0.95;
								} else {					
									$remove_discountTxt = '';
									$remove_discountOp = 1;
								}				
							} 
							else if ($remove_purchaseid == 25 || $remove_purchaseid == 26 || $remove_purchaseid == 27) {
								
								if ($remove_quantity > 999) {
									
									$remove_discountTxt = '5%';
									$remove_discountOp = 0.95;
									
								} else {
									
									$remove_discountTxt = '';
									$remove_discountOp = 1;
									
								}

							} 
							else {
								$remove_discountTxt = '';
								$remove_discountOp = 1;
							}
						   //---------discount-----------------
						
							$remove_qty = explode(".",$remove_getSalesRow['quantity']);
							$remove_productQty = ($remove_qty[1]>0)?$remove_getSalesRow['quantity']:$remove_qty[0];
						
						   $remove_productName = $remove_nameRow['name'];
						   $remove_order_arr[$i]['name'] = $remove_productName;
						   $remove_order_arr[$i]['quantity'] = $remove_productQty;
						   $remove_order_arr[$i]['amount'] = $remove_getSalesRow['amount'] * $remove_discountOp;
						   $remove_order_arr[$i]['price'] = $remove_getSalesRow['amount'] / $remove_productQty;
						   $remove_order_arr[$i]['discount'] = $remove_discountTxt;

						   $i++;

					}

					if($remove_credit_amount == ''){
						 $remove_credit_amount = 0;
					}
					$remove_iban = ($remove_invoice_type=="SW")? "ES94 0182 0981 4902 0318 3962" : "ES74 0182 0981 4002 0319 2038";

					$remove_previous_month =   date("F Y", strtotime("last day of previous month"));
						   $remove_billing_details = '';
				   $remove_discountText = '';
				   if($remove_invoice_type == 'SW'){
				   		if($remove_discount > 0){
				   			$remove_discountPer = (100 - $remove_discount) / 100;
				   			$remove_base_amount = $remove_base_amount;
				   			$remove_discountText =  $remove_discount.'%';
				   			
				   		}
				   		$remove_subtotal=$remove_base_amount;
					}else if($remove_invoice_type == 'HW'){
						$remove_subtotal=$remove_base_amount;
					}
				   if($remove_invoice_type == 'SW'){
				   		$remove_billing_details .=  '<tr class="head_col">
							      <td><span class="bold_text">Concepto</span></td>
							      <td><span class="bold_text">Descuento</span></td>
							      <td><span class="bold_text">Total</span></td>
							  </tr>';
				   	  	$remove_billing_details .= '<tr class="item-row"><td>'.$remove_description.'</td>
											  <td style="text-align: center;">'.$remove_discountText.'</td>
											  <td style="text-align: right;"><span class="price">'.number_format($remove_base_amount, 2).' €</span></td>
														  </tr>';
						if(!empty($remove_fees_elements)){
							foreach($remove_fees_elements as $remove_fee_name=> $remove_fee_val){
								if(is_numeric($remove_fee_val)){
									$remove_subtotal +=$remove_fee_val;
								}
								$remove_billing_details .= '<tr class="item-row"><td>'.$remove_fee_name.'</td>
														  <td></td>
														  <td style="text-align: right;"><span class="price">'.number_format($remove_fee_val, 2).' €</span></td>
														  </tr>';
							}
						}

				   }else{
				   		$remove_billing_details .= '<tr class="head_col">
							      <td><span class="bold_text">Concepto</span></td>
							      <td><span class="bold_text">Cantidad</span></td>
							      <td><span class="bold_text">Precio</span></td>
							      <td><span class="bold_text">Descuento</span></td>
							      <td><span class="bold_text">Total</span></td>
							  </tr>';
				   		foreach($remove_order_arr as $remove_order_val){
				   			if(is_numeric($remove_order_val['amount'])){
								$remove_subtotal += $remove_order_val['amount'];
							}
							// $subtotal +=$total_amount;
				   			$remove_billing_details .= '<tr class="item-row"><td>'.$remove_order_val['name'].'</td>
							   <td style="text-align: center;">'.$remove_order_val['quantity'].'</td>
							   <td style="text-align: right;">'.number_format($remove_order_val['price'], 2) .' €</td>
							   <td style="text-align: center;">'.$remove_order_val['discount'].'</td>
							   <td style="text-align: right;"><span class="price">'.number_format($remove_order_val['amount'], 2).' €</span></td>
							   </tr>';

						   }
						   
				   }
				   //--------calculating the vat amount-------//
				   	if($remove_invoice_type=="SW"){
						$remove_vatAmount = ($remove_vat>0)? number_format($remove_subtotal*$remove_vat/100,2, '.', '') : 0;
					}else{
						$remove_vatAmount = ($remove_vat>0)? number_format(($remove_subtotal+$remove_shipping)*$remove_vat/100,2, '.', '') : 0;
					}
					//$discountAmount = ($discount>0)? number_format(($subtotal+$shipping+$vatAmount)*$discount/100,2) : 0;
					$remove_discountText='';
					$remove_shippingText='';
					$remove_creditText='';
					$remove_debitText='';
					if($remove_credit_amount>0){
						$remove_creditText = '<tr>
							<td>- Credit</td>
							<td style="text-align:right;">'.number_format($remove_credit_amount, 2).' €</td>
						</tr>';
					}					
					if($remove_debit_amount>0){
						$remove_debitText = '<tr>
							<td>+ Missing Payment</td>
							<td style="text-align:right;">'.number_format($remove_debit_amount, 2).' €</td>
						</tr>';
					}
					if($remove_shipping>0){
						$remove_shippingText = '<tr>
							<td>+ Shipping</td>
							<td style="text-align:right;">'.$remove_shipping.' €</td>
						</tr>';
					}

					$remove_ccFeesText='';
					$remove_ccFee= $remove_invoice_row['credit_card_fee'];
					
					$remove_totAmt = number_format($remove_subtotal+$remove_shipping+$remove_vatAmount,2, '.', '');

					//$ccFee = number_format($totAmt * 0.015,2);
					$remove_total_amount = number_format($remove_total_amount - $remove_ccFee,2, '.', '');

					
					
					$remove_total_amount = number_format($remove_subtotal+$remove_shipping+$remove_vatAmount-$remove_ccFee+$remove_debit_amount-$remove_credit_amount,2, '.', '');
					
					$remove_finalText = $remove_ccFeesText;
					

					//----------fetch customer details-------------
					$remove_queryCustomerDetails = "SELECT * from customers WHERE number =".$remove_customer_number;
					try
					{
						$remove_customerDetails = $pdo2->prepare("$remove_queryCustomerDetails");
						$remove_customerDetails->execute();
						
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
					$remove_customerDetails = $remove_customerDetails->fetch();

					$remove_dueDateText='';
					if($remove_invoice_date==$remove_invoice_due_date){
						$remove_dueDateText = "Se vence al recibo";
					}else{
						//$dueDateText = "Fecha vencimiento";
						$remove_dueDateText = date('d-m-Y',strtotime($remove_invoice_due_date));
					}

					// get the customer domain name
					$remove_getDomain = "SELECT domain from db_access WHERE customer =".$remove_customer_number;
					try
					{
						$remove_domainDetails = $pdo->prepare("$remove_getDomain");
						$remove_domainDetails->execute();
						
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
					$remove_invoice = $remove_customer_number."-".$remove_invNo."-".$remove_invoice_type;
					$remove_domainCount = $remove_domainDetails->rowCount();
					$remove_invoice_club_path = '';
					$remove_invoice_club_dir = '';
					if($remove_domainCount > 0){
						$remove_domainRow = $remove_domainDetails->fetch();
						$remove_domain = $remove_domainRow['domain'];
						$remove_invoice_club_path = '../_club/_'.$remove_domain.'/invoices/'.$remove_invoice.'.pdf';
						$remove_invoice_club_dir = '../_club/_'.$remove_domain.'/invoices';
					}
					$remove_invoice_root_path = "invoices/".$remove_invoice.".pdf";
					$remove_invoice_root_dir = "invoices";
					
				// update invoice in db with credit card fees

				$remove_updateInvoice = "UPDATE invoices2 SET credit_card_fee = '0' WHERE invno ='".$remove_invoice_val."'";
				try
				{
					$remove_invoice_update = $pdo->prepare("$remove_updateInvoice");
					$remove_invoice_update->execute();
					
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
					




			$remove_options = new Options();
			$remove_options->set('defaultFont', 'Calibri');
			$remove_options->set('isRemoteEnabled', TRUE);
			$remove_options->set('debugKeepTemp', TRUE);
			$remove_options->set('isHtml5ParserEnabled', TRUE);

			//$options->set('chroot', '');
			// $dompdf = new Dompdf($options);
			new Dompdf($remove_options,array('enable_remote' => true));

			$remove_image = $siteroot."invoice-logo.png";
			$remove_html = '<html><head>
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
			#customer-title { font-family: "Calibri-Bold"; font-size:21px; text-align: center;}
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
			#items td.total-value { border-left: 0; padding: 10px; }
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
						<div id="identity">
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
				        </div>
						<div style="clear:both;"></div>
				    </div>


					<div id="address2">
						<span class="bold_text">'.$remove_customerDetails['longName'].'</span><br>
						'.$remove_customerDetails['cif'].'<br>
						'.$remove_customerDetails['street'].' '.$remove_customerDetails['streetnumber'].'<br>
						'.$remove_customerDetails['flat'].'
						'.$remove_customerDetails['city'].' '.$remove_customerDetails['postcode'].'
						'.$remove_customerDetails['state'].'
						'.$remove_customerDetails['country'].'
					</div>
					<div>
						<div id="customer-title">FACTURA</div>
						<br>
					</div>
					
					<div id="customer" style="border:2px solid black; ">
			            <table id="meta">
			                <tr>
			                    <td class="meta-head">Numero cliente</td>
			                    <td>'.$remove_customer_number.'</td>
			                </tr>
			                <tr>

			                    <td class="meta-head">Numero factura</td>
			                    <td>'.$remove_invNo.'</td>
			                </tr>
			                <tr>
			                    <td class="meta-head">Fecha facturación</td>
			                    <td><div class="due">'.date('d-m-Y',strtotime($remove_invoice_date)).'</div></td>
			                </tr>                
			                <tr>
			                    <td class="meta-head">Se vence al recibo</td>
			                    <td><div class="due">'.$remove_dueDateText.'</div></td>
			                </tr>
			            </table>
					</div>

					<div id="details">
						<table id="items">
						  '.$remove_billing_details.'
						</table>
						<div style="clear:both;"></div>
					</div>

					<div id="subtotal">
						<br><br>
						<table id="calculateTable">
							<tr>
								<td>Subtotal</td>
								<td style="text-align:right;">'.number_format($remove_subtotal, 2).' €</td>
							</tr>
							'.$remove_creditText.$remove_debitText.$remove_shippingText.'
							<tr>
								<td>+ IVA ('.$remove_vat.'%)</td>
								<td style="text-align:right;">'.$remove_vatAmount.' €</td>
							</tr>
							'.$remove_finalText.'
						</table>
						<br>
						<table id="calculateTable">
							<tr>
								<td style="border:none;"><span class="bold_text">A pagar</span></td>
								<td style="border:none; text-align:right;"><span class="bold_text">'.$remove_total_amount.' €</span></td>
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
								<td colspan="2"><span>Transferencia bancaria</span></td>
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
								<td>'.$remove_iban.'</td>
							</tr>
							<tr>
								<td>SWIFT:</td>
								<td>BBVAESMM</td>
							</tr>
							<tr>
								<td>Concepto:</td>
								<td>'.$remove_invoice.'</td>
							</tr>
						</table>
					</div>
				</div>
			</body>
			</html>';


			// instantiate and use the dompdf class
			/*$dompdf = new Dompdf();

			$dompdf->loadHtml($html);

			// (Optional) Setup the paper size and orientation
			$dompdf->setPaper('A4', 'landscape');

			// Render the HTML as PDF
			$dompdf->render();

			$output = $dompdf->output();
			file_put_contents("file.pdf", $output);*/
			//echo $html;

			//ob_get_clean();

				if(!is_dir($remove_invoice_club_dir)){
			    	mkdir($remove_invoice_club_dir, 0777, true);
				} 	
				if(!is_dir($remove_invoice_root_dir)){
			    	mkdir($remove_invoice_root_dir, 0777, true);
				} 

			$remove_dompdf = new DOMPDF();
			$remove_dompdf->loadHtml($remove_html);
			$remove_dompdf->render();
			$remove_output = $remove_dompdf->output();
			if($remove_invoice_club_path != ''){
				$remove_invoice_path = $remove_invoice_club_path;
				file_put_contents($remove_invoice_club_path, $remove_output);
			}else{
				$remove_invoice_path = $remove_invoice_root_path;
			}
			$remove_invoice_path_arr[] = $remove_invoice_path;
			file_put_contents($remove_invoice_root_path, $remove_output);

	}

}



foreach($invoices as $invoice_val){

		// fetch invoice details

		$selectInvoice = "SELECT * FROM invoices2 WHERE invno = '".$invoice_val."'";

		try
	   {
		   $invoice_result = $pdo->prepare("$selectInvoice");
		   $invoice_result->execute();
	   }
	   catch (PDOException $e)
	   {
			   $error = 'Error fetching user: ' . $e->getMessage();
			   echo $error;
			   exit();
	   }
	   $invoice_row = $invoice_result->fetch();

	   		$invoice_type =$invoice_row['brand']; 
			$status = $invoice_row['paid'];
			$customer_number= $invoice_row['customer'];
			$invoice_date = $invoice_row['invdate'];
			$invoice_due_date = $invoice_row['invduedate'];
			$base_amount = $invoice_row['base_amount'];
			$total_amount = $invoice_row['amount'];
			$discount = $invoice_row['discount'];
			$member_value = $invoice_row['member_section'];
			$vat = explode(".",$invoice_row['vat']);
			$vat = ($vat[1]>0)?$invoice_row['vat']:$vat[0];

			$fees_elements =  array_filter(unserialize($invoice_row['fees']));
			$description = $invoice_row['description'];
			$invNo = $invoice_row['invno'];
			$customer_name = $invoice_row['customer_name'];
			$credit_amount = $invoice_row['client_credit'];
			$debit_amount = $invoice_row['client_debit'];
			$saleid = $invoice_row['order_id'];

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
			   $total_amount_order = $orderRow['amount'];
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

			if($credit_amount == ''){
				 $credit_amount = 0;
			}
			$iban = ($invoice_type=="SW")? "ES94 0182 0981 4902 0318 3962" : "ES74 0182 0981 4002 0319 2038";

			$previous_month =   date("F Y", strtotime("last day of previous month"));
				   $billing_details = '';
		   $discountText = '';
		   if($invoice_type == 'SW'){
		   		if($discount > 0){
		   			$discountPer = (100 - $discount) / 100;
		   			$base_amount = $base_amount;
		   			$discountText =  $discount.'%';
		   			
		   		}
		   		$subtotal=$base_amount;
			}else if($invoice_type == 'HW'){
				$subtotal=$base_amount;
			}
		   if($invoice_type == 'SW'){
		   		$billing_details .= '<tr class="head_col">
							      <td><span class="bold_text">Concepto</span></td>
							      <td><span class="bold_text">Descuento</span></td>
							      <td><span class="bold_text">Total</span></td>
							  </tr>';
		   	  	$billing_details .= '<tr class="item-row"><td>'.$description.'</td>
									  <td style="text-align: center;">'.$discountText.'</td>
									  <td style="text-align: right;"><span class="price">'.number_format($base_amount, 2).' €</span></td>
												  </tr>';
				if(!empty($fees_elements)){
					foreach($fees_elements as $fee_name=> $fee_val){
						if(is_numeric($fee_val)){
							$subtotal +=$fee_val;
						}
						$billing_details .= '<tr class="item-row"><td>'.$fee_name.'</td>
												  <td></td>
												  <td style="text-align: right;"><span class="price">'.number_format($fee_val, 2).' €</span></td>
												  </tr>';
					}
				}

		   }else{
		   		$billing_details .= '<tr class="head_col">
			      <td><span class="bold_text">Concepto</span></td>
			      <td><span class="bold_text">Cantidad</span></td>
			      <td><span class="bold_text">Precio</span></td>
			      <td><span class="bold_text">Descuento</span></td>
			      <td><span class="bold_text">Total</span></td>
			  </tr>';
		   		foreach($order_arr as $order_val){
		   			if(is_numeric($order_val['amount'])){
						$subtotal += $order_val['amount'];
					}
					// $subtotal +=$total_amount;
		   			$billing_details .= '<tr class="item-row"><td>'.$order_val['name'].'</td>
					   <td style="text-align: center;">'.$order_val['quantity'].'</td>
					   <td style="text-align: right;">'.number_format($order_val['price'], 2) .' €</td>
					   <td style="text-align: center;">'.$order_val['discount'].'</td>
					   <td style="text-align: right;"><span class="price">'.number_format($order_val['amount'], 2).' €</span></td>
					   </tr>';

				   }
				   
		   }
		   //--------calculating the vat amount-------//
		   	if($invoice_type=="SW"){
				$vatAmount = ($vat>0)? number_format($subtotal*$vat/100,2, '.', '') : 0;
			}else{
				$vatAmount = ($vat>0)? number_format(($subtotal+$shipping)*$vat/100,2, '.', '') : 0;
			}
			//$discountAmount = ($discount>0)? number_format(($subtotal+$shipping+$vatAmount)*$discount/100,2) : 0;
			$discountText='';
			$shippingText='';
			$creditText='';
			$debitText='';
			if($credit_amount>0){
				$creditText = '<tr>
					<td>- Credit</td>
					<td style="text-align:right;">'.number_format($credit_amount, 2).' €</td>
				</tr>';
			}			
			if($debit_amount>0){
				$debitText = '<tr>
					<td>+ Missing Payment</td>
					<td style="text-align:right;">'.number_format($debit_amount, 2).' €</td>
				</tr>';
			}
			if($shipping>0){
				$shippingText = '<tr>
					<td>+ Shipping</td>
					<td style="text-align:right;">'.$shipping.' €</td>
				</tr>';
			}

			$ccFeesText='';
			$ccFee = 0;
			if($payment_type == 3){
				$ccFee= $invoice_row['credit_card_fee'];
				if($ccFee == 0){	
					$totAmt = number_format($subtotal+$shipping+$vatAmount,2, '.', '');

					$ccFee = number_format($totAmt * 0.015,2, '.', '');
					$total_amount = number_format($total_amount + $ccFee,2, '.', '');

					$ccFeesText = '<tr>
						<td>+ Credit card fee (1.5%)</td>
						<td style="text-align:right;">'.$ccFee.' €</td>
					</tr>';
				}else{
					$totAmt = number_format($subtotal+$shipping+$vatAmount,2, '.', '');

					//$ccFee = number_format($totAmt * 0.015,2);
					$total_amount = number_format($total_amount + $ccFee,2, '.', '');

					$ccFeesText = '<tr>
						<td>+ Credit card fee (1.5%)</td>
						<td style="text-align:right;">'.$ccFee.' €</td>
					</tr>';
				}
			}
			$total_amount = number_format($subtotal+$shipping+$vatAmount+$ccFee+$debit_amount-$credit_amount,2, '.', '');
			
			$finalText = $ccFeesText;
			

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
				//$dueDateText = "Fecha vencimiento";
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
			$invoice_root_path = "invoices/".$invoice.".pdf";
			$invoice_root_dir = "invoices";
			
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
	

		$options = new Options();
		$options->set('defaultFont', 'Calibri');
		$options->set('isRemoteEnabled', TRUE);
		$options->set('debugKeepTemp', TRUE);
		$options->set('isHtml5ParserEnabled', TRUE);

		//$options->set('chroot', '');
		// $dompdf = new Dompdf($options);
		new Dompdf($options,array('enable_remote' => true));

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
		#items td.total-value { border-left: 0; padding: 10px; }
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
					<div id="identity">
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
			        </div>
					<div style="clear:both;"></div>
			    </div>


				<div id="address2">
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
					  '.$billing_details.'
					</table>
					<div style="clear:both;"></div>
				</div>

				<div id="subtotal">
					<br><br>
					<table id="calculateTable">
						<tr>
							<td>Subtotal</td>
							<td style="text-align:right;">'.number_format($subtotal, 2).' €</td>
						</tr>
						'.$creditText.$debitText.$shippingText.'
						<tr>
							<td>+ IVA ('.$vat.'%)</td>
							<td style="text-align:right;">'.$vatAmount.' €</td>
						</tr>
						'.$finalText.'
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
							<td colspan="2"><span>Transferencia bancaria</span></td>
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


		// instantiate and use the dompdf class
		/*$dompdf = new Dompdf();

		$dompdf->loadHtml($html);

		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper('A4', 'landscape');

		// Render the HTML as PDF
		$dompdf->render();

		$output = $dompdf->output();
		file_put_contents("file.pdf", $output);*/
		//echo $html;

		//ob_get_clean();

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
		$invoice_path_arr[] = $invoice_path;
		file_put_contents($invoice_root_path, $output);

}


	$download_link = '';
	$k=0;
	foreach($invoice_path_arr as $inv_path){
		
		$download_link .= "<a href='".$inv_path."' target='_blank'>Download Invoice ".$invoices[$k]."</a>, ";

		$k++;
	}




	//$dompdf->stream($invoice.".pdf");

	$_SESSION['successMessage'] = "Invoice generated successfully!, please click here to ".$download_link;

//header("Location: invoice-section.php");
echo "<script>window.location.replace('invoice-payments.php');</script>";
