<?php 
ob_start();
// include autoloader
require_once 'vendor/autoload.php';
require_once 'cOnfig/connection.php';

ini_set("log_errors", TRUE);  
  
// setting the logging file in php.ini 
ini_set('error_log', "error.log"); 
// reference the Dompdf namespace
use Dompdf\Dompdf;
use Dompdf\Options;

// get all invoices 



// fetch columns from batch_invoices

$selectColumns = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'ccs_masterdb' AND TABLE_NAME = 'batch_invoices'";
try
{
   $column_result = $pdo->prepare("$selectColumns");
   $column_result->execute();
}
catch (PDOException $e)
{
	   $error = 'Error fetching user: ' . $e->getMessage();
	   echo $error;
	   exit();
}
while($col_row = $column_result->fetch()){
    $col_result[] = $col_row;
}

// Array of all column names
$columnArr = array_column($col_result, 'COLUMN_NAME');

foreach ($columnArr as $colVal) {
	if(is_numeric($colVal)){
		$elements_arr[] = $colVal;
	}	
}

$countItem = $_GET['count'];
$count = $_GET['totalCount'];
$page_size = 20;

	// excel count query
if($_GET['count'] == 0){
	$batchCountQuery  =   "SELECT * FROM batch_invoices WHERE uploaded = 0 order by id ASC";
   //$query = "select id from shipment Limit ".$page_size." OFFSET ".$offset_var;
   $countResults= $pdo->prepare("$batchCountQuery");
   $countResults->execute();

	$total_records=$countResults->rowCount();
	if($total_records == 0){
				$_SESSION['errorMessage'] = "No new invoice found to generate !";
				header("Location: invoice-batch.php");
				exit();
			}
	

   $count=ceil($total_records/$page_size);  
}


if($_GET['count'] <= $_GET['totalCount']){
		//$offset_var = $countItem * $page_size;
		$offset_var = 0;
	   $selectInvoices = "SELECT * FROM batch_invoices WHERE uploaded = 0 order by id ASC limit ".$page_size." OFFSET ".$offset_var;

		try
		{
		   $invoice_result = $pdo->prepare("$selectInvoices");
		   $invoice_result->execute();
		}
		catch (PDOException $e)
		{
			   $error = 'Error fetching user: ' . $e->getMessage();
			   echo $error;
			   exit();
		}

		$invoiceCount = $invoice_result->rowCount();


		while($invoice_row = $invoice_result->fetch()){

			   	$invoice_type ="SW"; 
				$customer_number= $invoice_row['customer_number'];
				$batch_id = $invoice_row['id'];
				$invoice_date = date("d-m-Y");
				$invoice_dateSQL = date("Y-m-d H:i:s", strtotime($invoice_date));
				$invoice_due_date = date("d-m-Y");
				$invoice_due_dateSQL = date("Y-m-d H:i:s", strtotime($invoice_due_date));
				$base_amount = $invoice_row['base_price'];
				//$total_amount = $invoice_row['total_price'];
				// assign permanent customer number
				$is_temp_customer =  substr($customer_number,0,1);

				 if($is_temp_customer == 9){

				 		$customer_number = updatePermanentCustomer($customer_number);
				 	}
				$element_id = [];
				$fees_elements = [];
				foreach ($elements_arr as $element) {
					if($invoice_row[$element] != ''){

						// fetch elements details
						$selectElements = "SELECT * FROM invoice_elements WHERE id=".$element;

						try
						{
						   $element_result = $pdo3->prepare("$selectElements");
						   $element_result->execute();
						}
						catch (PDOException $e)
						{
							   $error = 'Error fetching user: ' . $e->getMessage();
							   echo $error;
							   exit();
						}
						while($element_row = $element_result->fetch()){
							$fees_elements[$element_row['element_en']] = $invoice_row[$element];
						}
					}
				}
				
				$fees_elements = array_filter($fees_elements);
				//$invNo = $invoice_row['id'];

				$inv_query = "SELECT * FROM invoices2";
					try
					{
						$inv_result = $pdo->prepare("$inv_query");
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



				$iban = ($invoice_type=="SW")? "ES94 0182 0981 4902 0318 3962" : "ES74 0182 0981 4002 0319 2038";
				$subtotal=$base_amount;
				$previous_month =   "Software:".date("F Y", strtotime("last day of previous month"));
				$billing_details = '';

				$billing_details .= '<tr>
								      <th>Concepto</th>
								      <th>Descuento</th>
								      <th>Total</th>
								  </tr>';
				$billing_details .= '<tr class="item-row"><td>'.$previous_month.'</td>
									  <td style="text-align: center;"></td>
									  <td style="text-align: right;"><span class="price">'.number_format($base_amount, 2).' €</span></td>
												  </tr>';
				if(!empty($fees_elements)){
					foreach($fees_elements as $fee_name=> $fee_val){
						if(is_numeric($fee_val)){
							$subtotal +=$fee_val;
						}
						if($fee_val != 0){
							$billing_details .= '<tr class="item-row"><td>'.$fee_name.'</td>
												  <td></td>
												  <td style="text-align: right;"><span class="price">'.number_format($fee_val, 2).' €</span></td>
												  </tr>';
						}
					}
				}
				//----------fetch customer details-------------
				$queryCustomerDetails = "SELECT * from customers WHERE number =".$customer_number;
				try
				{
					$customerDetails = $pdo3->prepare("$queryCustomerDetails");
					$customerDetails->execute();
					
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
				$customerDetails = $customerDetails->fetch();
				
				$vat = $customerDetails['vat'];
				$vatAmount = 0;
				if($vat > 0){
					$vatAmount = ($vat>0)? number_format($subtotal*$vat/100,2, '.', '') : 0;
				}
				$total_amount = number_format($subtotal+$vatAmount,2, '.', '');
				if($total_amount < 400){
					$invNo = 'S'.$nextInvoiceNumber;
				}else{
					$invNo = $nextInvoiceNumber;
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
					$invoice_club_path = '/var/www/html/ccsnube_com/ttt/_club/_'.$domain.'/invoices/'.$invoice.'.pdf';
					$invoice_club_dir = '/var/www/html/ccsnube_com/ttt/_club/_'.$domain.'/invoices';
				}
				$invoice_root_path = "/var/www/html/ccsnube_com/ttt/Nefos-new/invoices/".$invoice.".pdf";
				$invoice_root_dir = "/var/www/html/ccsnube_com/ttt/Nefos-new/invoices";
		

				if($invoice_date==$invoice_due_date){
							$dueDateText = "Se vence al recibo";
						}
				
				$created_date = date("Y-m-d H:i:s");
				$currency = 'EUR';
				// Query to update user - 28 arguments
				  $insertInvoice = sprintf("INSERT INTO invoices2 (invno, invdate, invduedate, invoice_generate_time, currency, base_amount, amount, fees, vat, customer, brand, description, invoice_created) VALUES ('%s', '%s', '%s', '%s', '%s', '%f', '%f', '%s', '%f', '%s', '%s', '%s', '%s')",
							$invNo,
							$invoice_dateSQL,
							$invoice_due_dateSQL,
							date('H:i:s'),
							$currency,
							$base_amount,
							$total_amount,
							serialize($fees_elements),
							$vat,
							$customer_number,
							$invoice_type,
							$previous_month,
							$created_date
							);
				try
				{
					 $pdo->prepare("$insertInvoice")->execute();
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
				body { margin:10px; border:2px solid black; padding:5px; line-height: 2; font: 14px/1.4 Calibri, Calibri;}

				table { border-collapse: collapse; border: 2px solid black; }
				table th { border: 2px solid black; padding: 5px; }
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
				#customer-title {  font: bold 21px; text-align: center;}
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
									<strong>Mykinlink SL</strong><br>
									B87843504<br>
									Calle Clara Del Rey 36, Planta 2, Puerta B<br>
									28002 Madrid,<br>
									España<br>
					        </div>
							<div style="clear:both;"></div>
					    </div>


						<div id="address2">
							<strong>'.$customerDetails['longName'].'</strong><br>
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
							<tr>
								<td>+ IVA ('.$vat.'%)</td>
								<td style="text-align:right;">'.$vatAmount.' €</td>
							</tr>
							</table>
							<br>
							<table id="calculateTable">
								<tr>
									<td style="border:none;"><strong>A pagar</strong></td>
									<td style="border:none; text-align:right;"><strong>'.number_format($total_amount, 2).' €</strong></td>
								</tr>
							</table>
						</div>
						<div style="clear:both;"></div>

						<br>
						<div id="final_details">
							<table class="lastDetails" id="meta">
								<tr>
									<td colspan="2"><span><strong><u>Detalles del pago</u></strong></span></td>
								</tr>
								<tr>
									<td colspan="2"><span><i>Transferencia bancaria</i></span></td>
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

				// update invoce status to 1

				$updateInvoice = "UPDATE batch_invoices SET uploaded = 1, created_at = '$created_date' WHERE id=".$batch_id;

				try
				{
				   $pdo->prepare("$updateInvoice")->execute();
				}
				catch (PDOException $e)
				{
					   $error = 'Error fetching user: ' . $e->getMessage();
					   echo $error;
					   exit();
				}


		}
		 $countItem++;
		 header('Refresh: 0; batch-process.php?count='.$countItem.'&totalCount='.$count.'&redirect=1');
		exit();
	}

$_SESSION['successMessage'] = "Invoice generated successfully!";
//header("Location: invoice-section.php");

echo "<script>window.location.replace('invoice-batch.php');</script>";