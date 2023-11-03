<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/languages/common.php';

	$cust_num = $_REQUEST['cust_num'];

	$sort_param = "ORDER BY FIELD(customer, ".$cust_num.") DESC";
	$selectInvoices = "SELECT * FROM invoices2 WHERE deleteFlag = 0 AND (paid = '' OR (paid <> '' AND date(invdate) > DATE('2019-12-31'))) $sort_param";
	try
	{
		$invoices_results = $pdo->prepare("$selectInvoices");
		$invoices_results->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
?>
	<table class='default' id='mainTable'>
		<thead>	
			<tr style='cursor: pointer;'>
				<th>Select</th>
				<th>Mark as paid</th>
				<th># Inv</th>
				<th width="70px">Inv Date</th>
				<th>Customer Name</th>
				<th>Customer Number</th>
				<th>Customer balance</th>
				<th>Invoices</th>
				<th>Base Amount</th>
				<th>Full Amount</th>
				<th>Pending Amount</th>
			</tr>
		</thead>
		<tbody>
<?php
	while ($invoice_row = $invoices_results->fetch()){
		$invNumber = $invoice_row['invno'];
		$invpaid = $invoice_row['paid'];
		$in_payment_type = $invoice_row['payment_type'];
		$invDate = ($invoice_row['invdate'])?date('Y-m-d',strtotime($invoice_row['invdate'])):'';
		$base_amount = $invoice_row['base_amount'];
		$amount = $invoice_row['amount'];
		$customer = $invoice_row['customer'];
		$delta = $invoice_row['delta'];
		if($delta == ''){
			$delta = 0;
		}
		if($_REQUEST['edit'] == 1){
			$checked = '';
			$markChecked = '';
			$disabled = 'disabled';
			if(in_array($invoice_row['invno'],$invoice_arr)){
				$checked = 'checked';
				$disabled = '';
				if($invoice_row['paid']=='Paid'){
					$markChecked = 'checked';
				}
			}
		}
		// Look up customer details: name and domain
		$selectUsersU = "SELECT id,longName, state, country FROM customers WHERE number = '$customer'";
		try
		{
			$user_result = $pdo2->prepare("$selectUsersU");
			$user_result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		$rowX = $user_result->fetch();
		$longName = $rowX['longName'];
		// fetch invoice balance and invoice
		$selectUnpaidInvoices = "SELECT count(customer), sum(amount) from invoices2 WHERE customer = '$customer' AND paid = ''";
		try
		{
			$unpaid_result = $pdo->prepare("$selectUnpaidInvoices");
			$unpaid_result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		$rowY = $unpaid_result->fetch();
		$invoice_balance = $rowY['sum(amount)'];
		$noOfInvoices = $rowY['count(customer)'];
		if($_REQUEST['edit'] == 1){
			echo sprintf("
				<tr>
				<td>
					<div class='fakeboxholder customradio'>
						<label class='control'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
							<input type='checkbox' name='invoices[]' value='%s' id='select_%s' onchange='allow_isPaid(this.value);' %s>
							<div class='fakebox'></div>
						</label>
					</div>
				</td>
				<td>
					<div class='fakeboxholder customradio'>
						<label class='control'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
							<input type='checkbox' name='paid_invoice[]' value='%s' id='paid_%s' %s %s>
							<div class='fakebox'></div>
						</label>
					</div>
				</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td class='right'>%s</td>
				<td class='right'>%s</td>
				<td class='right'>%s</td></tr>",$invNumber,$invNumber,$checked,$invNumber,$invNumber,$disabled,$markChecked,$invNumber,$invDate, $longName, $customer, $invoice_balance, $noOfInvoices, $base_amount,$amount,$delta);
		}else{

			if($invpaid == '' && $in_payment_type != 'CN'){
				echo sprintf("
						<tr>
						<td>
							<div class='fakeboxholder customradio'>
								<label class='control'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
									<input type='checkbox' name='invoices[]' value='%s' id='select_%s' onchange='allow_isPaid(this.value);'>
									<div class='fakebox'></div>
								</label>
							</div>
						</td>
						<td>
							<div class='fakeboxholder customradio'>
								<label class='control'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
									<input type='checkbox' name='paid_invoice[]' value='%s' id='paid_%s' disabled>
									<div class='fakebox'></div>
								</label>
							</div>
						</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td class='right'>%s</td>
						<td class='right'>%s</td>
						<td class='right'></td></tr>",$invNumber,$invNumber,$invNumber,$invNumber,$invNumber,$invDate, $longName, $customer, $invoice_balance, $noOfInvoices, $base_amount,$amount);
			}else{
				echo sprintf("
					<tr class='darkcolor'>
					<td></td>
					<td></td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class='right'>%s</td>
					<td class='right'>%s</td>
					<td class='right'>%s</td></tr>", $invNumber,$invDate, $longName, $customer, $invoice_balance, $noOfInvoices, $base_amount,$amount, $delta);
			 	}
		}
		
	 }
	?>
		</tbody>
	</table>
<?php		  
die;										