<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Retrieve System settings
	getSettings();
	
	// <input type='hidden' name='confirmReject' value='confirm' />
	
	if ($_POST['addcomment'] == 'yes') {
		
		$saleid = $_POST['saleid'];
		$comment = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['intText'])));
		
		// Update order number to confirm
		$updateOrder = "UPDATE sales SET intcomment = '$comment' WHERE saleid = $saleid";
		try
		{
			$result = $pdo3->prepare("$updateOrder")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

		$_SESSION['successMessage'] = "Comment added succesfully!";
		header("Location: dispense.php?saleid=$saleid");
		exit();
		
	}
	
	if ($_POST['confirmReject'] == 'confirm') {
		
		$saleid = $_POST['saleid'];
		$comment = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['confText'])));
		
		// Update order number to confirm
		$updateOrder = "UPDATE sales SET fulfilled = 1, commentsforclient = '$comment' WHERE saleid = $saleid";
		try
		{
			$result = $pdo3->prepare("$updateOrder")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

		$_SESSION['successMessage'] = "Order fulfilled!";
		header("Location: orders.php");
		exit();
		
	}
	
	// Get the sale ID
	if (isset($_GET['saleid'])) {
		$saleid = $_GET['saleid'];
	} else {
		handleError($lang['error-nosaleid'],"");
	}

	
	// Query to look up sale
	$selectSale = "SELECT saleid, saletime, userid, amount, amountpaid, quantity, realQuantity, units, adminComment, creditBefore, creditAfter, userConfirmed, fulfilled, delivered, drivernumber, customer, comments, intcomment, shipping, paymentoption FROM sales WHERE saleid = $saleid";
		try
		{
			$results = $pdo3->prepare("$selectSale");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
	
	pageStart("Order", NULL, NULL, "psales", "Sale", "ORDER", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
	$sale = $results->fetch();
		$formattedDate = date("Y M d H:i", strtotime($sale['saletime']."+$offsetSec seconds"));
		$saleid = $sale['saleid'];
		$userid = $sale['userid'];
		$credit = $sale['creditBefore'];
		$newcredit = $sale['creditAfter'];
		$quantity = $sale['quantity'];
		$realQuantity = $sale['realQuantity'];
		$units = $sale['units'];
		$clubConfirmed = $sale['clubConfirmed'];
		$delivered = $sale['delivered'];
		$drivernumber = $sale['drivernumber'];
		$fulfilled = $sale['fulfilled'];
		$customer = $sale['customer'];
		$adminComment = $sale['adminComment'];
		$comments = $sale['comments'];
		$intcomment = $sale['intcomment'];
		$shipping = $sale['shipping'];
		$paymentoption = $sale['paymentoption'];
	
	if ($fulfilled == 0) {
		
		$clubApproved = "<img src='images/awaiting.png' width='25' title='Awaiting action' style='margin-bottom: -3px;' />";
		
	} else if ($fulfilled == 1) {
		
		$clubApproved = "<img src='images/complete.png' width='25' title='Approved' style='margin-bottom: -3px;' />";
		
	} else {
		
		$clubApproved = "<img src='images/delete.png' width='25' title='Rejected!' style='margin-bottom: -3px;' />";
				
	}
	

	
	if ($delivered == 0) {
		
		$deliveredImg = "<img src='images/awaiting.png' width='16' alt='Awaiting action' />";
		
	} else if ($delivered == 1) {
		
		$deliveredImg = "<img src='images/complete.png' width='16' alt='Approved' />";
				
	} else {
		
		$deliveredImg = "<img src='images/delete.png' width='16' alt='Rejected!' />";
		
	}
		
		
		$amount = $sale['amount'];
		$amountpaid = $sale['amountpaid'];
		$userLookup = "SELECT first_name, last_name, memberno, telephone, email, street, streetnumber, flat, postcode, city FROM users WHERE user_id = {$userid}";
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
			$last_name = $row['last_name'];
			$memberno = $row['memberno'];
			$telephone = $row['telephone'];
			$email = $row['email'];
			$address = $row['address'];
			$city = $row['city'];
			$state = $row['state'];
			$zip = $row['zip'];
				
		$selectoneSale = "SELECT d.category, d.productid, d.quantity, d.realQuantity, d.amount FROM salesdetails d, sales s WHERE d.saleid = {$saleid} and s.saleid = d.saleid";
		try
		{
			$onesaleResult = $pdo3->prepare("$selectoneSale");
			$onesaleResult->execute();
			$totResult = $onesaleResult->fetchAll();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			   
		// Lookup club info
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
		
	$query = "SELECT shortName, street, streetnumber, flat, postcode, city, email, phone, shipping, number, vat, credit FROM customers WHERE number = '$custnumber'";
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
		$shortName = $row['shortName'];
		$street = $row['street'];
		$streetnumber = $row['streetnumber'];
		$flat = $row['flat'];
		$postcode = $row['postcode'];
		$city = $row['city'];
		$email = $row['email'];
		$phone = $row['phone'];
		$shipregion = $row['shipping'];
		$cNumber = $row['number'];
		$vat = $row['vat'];
		$credit = $row['credit'];
		
		
		if ($vat == 0) {
			$vatOp = 1; 
		} else {
			$vatOp = ($vat / 100) + 1; 
		}
		
		if ($comments != '') {
			$comments = "<strong>Comment from client:</strong><br />$comments<br /><br />";
		}
			   
		echo "
<center>
<div id='mainbox-no-width' style='margin-top: 0; margin-right: 20px; max-width: 400px;'>
 <div id='mainboxheader'>
  $formattedDate &nbsp; $clubApproved
 </div>
 <div class='boxcontent'>

  <span style='font-size: 20px; font-weight: 600;'>$shortName ($cNumber)</span><br />
  $street $streetnumber $flat<br />
  $postcode $city<br /><br />
  <strong>Contact person:</strong> $adminComment<br /><br />
  $comments
  <strong>Internal comment:</strong><br />

   <form id='registerForm' action='' method='POST'>
  <input type='hidden' name='addcomment' value='yes' />
  <input type='hidden' name='saleid' value='$saleid' />
  <textarea class='defaultinput' name='intText' style='height: 70px;'>$intcomment</textarea><br />
  <button type='submit' class='cta1'>Save comment</button>
 </form>";
 
 if ($fulfilled == 0) {

	 echo "
 <form id='registerForm' action='' method='POST'>
  <input type='hidden' name='confirmReject' value='confirm' />
  <input type='hidden' name='saleid' value='$saleid' />
  <textarea class='defaultinput' name='confText' placeholder='Comments for client?' style='height: 70px;'></textarea><br />

  <button type='submit' class='cta1'>Complete order</button>
 </form>";
 
}
 echo "

</div>
</div>
";

		$selectoneSale = "SELECT d.category, d.productid, d.quantity, d.realQuantity, d.amount, d.purchaseid FROM salesdetails d, sales s WHERE d.saleid = {$saleid} and s.saleid = d.saleid";
		try
		{
			$onesaleResult = $pdo2->prepare("$selectoneSale");
			$onesaleResult->execute();
			$totResult = $onesaleResult->fetchAll();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			   
$myOrder = <<<EOD
		
<div style='display: inline-block; vertical-align: top; border: 2px solid #c3c8c1; border-radius: 3px; background-color: #fff;'>
	 <table id='detailedsale' class='default' >
	  <thead>
	   <tr>
	    <th colspan='2'>Product</th>
	    <th>Units</th>
	    <th>Price</th>
	    <th>Discount</th>
	    <th>Total &euro;</th>
	   </tr>
	  </thead>
	  <tbody>
EOD;



	  	   	foreach ($totResult as $onesale) {
			
			$imgid = $onesale['purchaseid'];
			$purchaseid = $onesale['purchaseid'];
			$productid = $onesale['productid'];
			$quantity = $onesale['quantity'];
			
			$amount = $onesale['amount'] / $quantity;
			$totalAmt = $amount * $quantity;
			
			$totalAmtAll = $totalAmtAll + $totalAmt;
			
			$selectProduct = "SELECT name FROM products WHERE productid = $productid";
			try
			{
				$result = $pdo2->prepare("$selectProduct");
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
				
			/* Discounts
			
				12 blue
				13 black
				14 red
				15 green
				16 yellow
				27 RFID cards
			
			
			*/
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
			
		} else if ($purchaseid == 25 || $purchaseid == 26 || $purchaseid == 27) {
			
			if ($quantity > 999) {
				
				$discountTxt = '5%';
				$discountOp = 0.95;
				
			} else {
				
				$discountTxt = '';
				$discountOp = 1;
				
			}

		} else {
			$discountTxt = '';
			$discountOp = 1;
		}

			
$myOrder .= <<<EOD
<tr>
 <td><img src='https://ccsnubev2.com/CCS/images/purchases/$imgid.jpg' width='70' /></td>
 <td>$name</td>
 <td class='centered'>{$expr(sprintf("%0.0f", $quantity))}</td>
 <td class='right'>{$expr(sprintf("%0.2f", $amount))} &euro;</td>
 <td class='centered'>$discountTxt</td>
 <td class='right'>{$expr(sprintf("%0.2f", $totalAmt * $discountOp))} &euro;</td> 
</tr> 
EOD;

		$baseAmount = $baseAmount + ($totalAmt * $discountOp);
 
		}
		
		echo $myOrder;
		
		
		$vatAmt = $baseAmount * $vatOp;
		
		$totAmt = ($baseAmount + $shipping) * $vatOp;
		
		if ($paymentoption == 8 || $paymentoption == 9) {
			
			$ccFee = $totAmt * 0.015;
			$totAmt = $totAmt + $ccFee;
			
		}

		
		
echo "
      </tbody>
     </table>
<br /><br />
	 <table id='detailedsale' class='default' style='vertical-align: top;'>
	  <thead>
	   <tr>
	    <th>Shipping</th>
	    <th>Base amount</th>
	    <th>VAT</th>
	    <th>CC fee</th>
	    <th style='text-align: center;'>Total</th>
	   </tr>
	  </thead>
	  <tbody>
       <tr>
        <td class='centered'>{$expr(sprintf("%0.2f", $shipping))}&euro;</td>
        <td class='centered'>{$expr(sprintf("%0.2f", $baseAmount))}&euro;</td>
        <td class='centered'>{$expr(sprintf("%0.0f", $vat))}%</td>
        <td class='centered'>{$expr(sprintf("%0.2f", $ccFee))} &euro;</td>
        <td class='centered'><strong>{$expr(sprintf("%0.2f", $totAmt))} &euro;</strong></td>
	   </tr>
	   
      </tbody>
	 </table>
	 <form>
	 <table>
		<tr>
			<td class='centered'>Invoice Date : </td>
			<td class='centered'><input type='text' name='invoice_date' class='defaultinput' id='date1' value='".date('d-m-Y')."' autocomplete='off' style='width: 50%;'></td>
			<td class='centered'>Invoice Due Date : </td>
			<td class='centered'><input type='text' name='invoice_due_date' class='defaultinput' id='date2' value='".date('d-m-Y')."' autocomplete='off' style='width: 50%';></td>

		</tr>
		<tr>
			<td class='centered'>Use credit ?</td>
			<td class='centered'>
				<div class='fakeboxholder customradio'>
					<label class='control'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						<input type='radio' name='use_credit' value='Yes' class='defaultinput'> Yes
						<div class='fakebox'></div>
					</label>
				</div>								
				<div class='fakeboxholder customradio'>
					<label class='control'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						<input type='radio' name='use_credit' value='No' checked class='defaultinput'> No
						<div class='fakebox'></div>
					</label>
				</div>
				<br>
				(<span id='credit_avail'>{$expr(sprintf("%0.2f", $credit))}</span> € Available)
				<input type='number' name='credit_amount' id='credit_amount' class='defaultinput' placeholder='Enter Credit amount' style='display: none;' required=''>
				<input type='hidden' name='total_credit' value='".$credit."' id='total_credit'>
				<input type='hidden' name='total_amount_order' value='{$expr(sprintf("%0.2f", $totAmt))}' id='total_amount_order'><br><span id='error_msg' style='color:red;'></span>
				<br>
			 </td>
		</tr>
		<tr>
			<td class='centered' style='padding:10px;'>Generate invoice without header?</td>
			<td class='centered'>
			 	<div class='fakeboxholder customradio'>
					<label class='control'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						<input type='checkbox' name='without_header' value='Yes' class='defaultinput'> 
						<div class='fakebox'></div>
					</label>
				</div>
			 </td>
		</tr>
	  </table>
	  </form>
<center><br />
<span onClick='printMe2($saleid, $custnumber)' class='cta1'>Print FRAGILE</span>

<script>

$( '#date1' ).datepicker({
	dateFormat: 'dd-mm-yy'
});
$( '#date2' ).datepicker({
dateFormat: 'dd-mm-yy'
});	

// change due date on chnage 
$('#date1').change(function(){
	var this_date =$(this).val();
	$('#date2').val(this_date);
});	

function printMe2(sid, cid){
    var win = window.open('print-label-fragile.php?tr=yes5&saleid='+sid+'&cid='+cid, '1366002941508',  'width=500,height=200,left=375,top=330');
    return false;
}
</script>

<span onClick='printMe($saleid, $custnumber)' class='cta1'>Print label</span>
<a href='javascript:void(0);' onclick='generateInvoicePdf();'><span class='cta1'>Invoice Generate</span></a>

<script>
function printMe(sid, cid){
    var win = window.open('print-label.php?tr=yes5&saleid='+sid+'&cid='+cid, '1366002941508',  'width=500,height=200,left=375,top=330');
    return false;
}

function generateInvoicePdf(){
	var invoiceDate = $('#date1').val();
	var dueDate = $('#date2').val();
	var credit = $('#credit_amount').val();
	var total_credit = $('#total_credit').val();
	var total_amount_order = $('#total_amount_order').val();
	var checked_radio = $('input[name=\"use_credit\"]:checked').val();
	var without_header_val = $('input[name=\"without_header\"]:checked').val();
	if(credit == ''){
		credit = 0;
	}
	if(total_credit == ''){
		total_credit = 0;
	}
	if(checked_radio == 'Yes'){
		console.log(parseFloat(credit), parseFloat(total_credit), parseFloat(total_amount_order));
		if(credit < 0){
			$('#error_msg').text('Please enter valid credit amount !');
			  return false;
		}
		if(credit == 0){
			$('#error_msg').text('Please enter credit amount !');
			    return false;
		}
	 	if (parseFloat(credit) > parseFloat(total_credit) || parseFloat(credit) > parseFloat(total_amount_order)) {
	 			$('#error_msg').text('Please enter valid amount !');
			    return false;
		    }
		}
	
	window.location.href = 'dispense-invoice.php?saleid='+ $saleid+'&invoiceDate='+invoiceDate+'&dueDate='+dueDate+'&credit_amount='+credit+'&without_header='+without_header_val;
}

</script>
</div>
";


   displayFooter();
   ?>
<script type="text/javascript">

	$("input[name='use_credit']").change(function(){
   		var this_val = $(this).val();
   		if(this_val == 'Yes'){
   			$("#credit_amount").fadeIn(500);
   		}else{
   			$("#credit_amount").val('').fadeOut(500);
   		}
   }); 
	$("#credit_amount").on("keyup keypress", function(){
		$("#error_msg").text('');
	})
</script>