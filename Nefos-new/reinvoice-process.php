<?php 
ob_start();


// include autoloader
require_once 'vendor/autoload.php';
require_once 'cOnfig/connection.php';

//session_start();

		$invoice_type ='SW'; 

		// get the form details

		$invoice_no = $_POST['invoice_no'];
		$credit_card_fee  = $_POST['credit_card_fees']; 

		// fetch details of invoice form invoices table

		$selectInvoice = "SELECT * from invoices WHERE invno ='".$invoice_no."'";
		try
		{
			$invoice_results = $pdo->prepare("$selectInvoice");
			$invoice_results->execute();
			
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		$invoiceRow = $invoice_results->fetch();

		$status = $invoiceRow['paid'];
		$customer_number= $invoiceRow['customer'];
		$invoice_date = $invoiceRow['invdate'];
		$invoice_due_date = $invoiceRow['invduedate'];
		$base_amount = $invoiceRow['base_amount'];
		$total_amount = $invoiceRow['amount'];
		$discount = $invoiceRow['discount'];
		$member_section = $invoiceRow['member_section'];
		$vat = explode(".",$invoiceRow['vat']);
		$vat = ($vat[1]>0)?$invoiceRow['vat']:$vat[0];

		$fees_elements = array_filter(unserialize($invoiceRow['fees']));
		$description = $invoiceRow['description'];
		$invNo = $invoiceRow['invno'];
		$iban = ($invoice_type=="SW")? "ES94 0182 0981 4902 0318 3962" : "ES74 0182 0981 4002 0319 2038";
		
	   $billing_details = '';

		$subtotal=$base_amount;
		
	   if($invoice_type == 'SW'){
	   		$billing_details .= '<tr class="head_col">
							      <td><span class="bold_text">Concepto</span></td>
							      <td><span class="bold_text">Descuento</span></td>
							      <td><span class="bold_text">Total</span></td>
							  </tr>';
	   	  	$billing_details .= '<tr class="item-row"><td>'.$description.'</td>
								  <td></td>
								  <td></td>
								  <td></td>
								  <td style="text-align: right;"><span class="price">'.number_format($base_amount, 2).' €</span></td>
											  </tr>';
			if(!empty($fees_elements)){
				foreach($fees_elements as $fee_name=> $fee_val){
					if(is_numeric($fee_val)){
						$subtotal +=$fee_val;
					}
					$billing_details .= '<tr class="item-row"><td>'.$fee_name.'</td>
											  <td></td>
											  <td></td>
											  <td></td>
											  <td style="text-align: right;"><span class="price">'.number_format($fee_val, 2).' €</span></td>
											  </tr>';
				}
			}

	   }
	   //--------calculating the vat amount-------//
	   	if($invoice_type=="SW"){
			$vatAmount = ($vat>0)? number_format($subtotal*$vat/100,2) : 0;
		}
		$discountAmount = ($discount>0)? number_format(($subtotal+$vatAmount)*$discount/100,2) : 0;
		$discountText='';
		$shippingText='';
		if($discount>0){
			$discountText = '<tr>
				<td>- Discount ('.$discount.'%)</td>
				<td style="text-align:right;">'.$discountAmount.' €</td>
			</tr>';
		}
		$ccFeesText='';
		$ccFee=0;
		
		$totAmt = number_format($subtotal+$vatAmount,2);

		$ccFee = number_format($credit_card_fee,2);
		$total_amount = number_format($total_amount + $ccFee,2);
		if($ccFee > 0){
			$ccFeesText = '<tr>
				<td>+ Credit card fee</td>
				<td style="text-align:right;">'.$ccFee.' €</td>
			</tr>';
		}
		
		$total_amount = number_format($subtotal+$vatAmount+$ccFee-$discountAmount,2);
		if($invoice_type=="SW"){
			$finalText = $discountText.$ccFeesText;
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
		$invoice_root_path = "../invoices/".$invoice.".pdf";
		$invoice_root_dir = "../invoices";
		
		// update invoice in db with credit card fees

		$updateInvoice = "UPDATE invoices SET amount = '".$total_amount."', credit_card_fee = '".$ccFee."' WHERE invno ='".$invoice_no."'";
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
table td{ border: 1px solid black; padding: 5px; }
tr.head_col td{ border: 2px solid black; padding: 5px; }
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
				'.$shippingText.'
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
//echo $html; die;

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
//$dompdf->stream($invoice.".pdf");

$_SESSION['successMessage'] = "Invoice generated successfully!, please click here to <a href='".$invoice_path."' target='_blank'>Download Invoice</a>";
//header("Location: invoice-section.php");
echo "<script>window.location.replace('invoices.php');</script>";
