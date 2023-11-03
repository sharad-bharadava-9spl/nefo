<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/languages/common.php';


	$payment_id = $_REQUEST['payment_id'];
	$slectPayments = "SELECT invoices FROM invoice_payments2 WHERE id =".$payment_id;
	try
	{
		$payment_results = $pdo->prepare("$slectPayments");
		$payment_results->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	$payment_row =$payment_results->fetch();
		$invoice_arr = explode(",", $payment_row['invoices']);

	$selectInvoices = "SELECT * FROM invoices2 WHERE deleteFlag = 0 AND payment_type IS NULL order by invoice_created DESC";

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
				<th width="70px" dateformat="DD-MM-YYYY HH:mm:ss" isType="date" class="filter">Inv Date</th>
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
		$invDate = ($invoice_row['invdate'])?date('d-m-Y',strtotime($invoice_row['invdate'])):'';
		$base_amount = $invoice_row['base_amount'];
		$amount = $invoice_row['amount'];
		$customer = $invoice_row['customer'];
		$delta = $invoice_row['delta'];
		if($delta == ''){
			$delta = 0;
		}
		$pending_amount = $delta;
		if($delta == 0){
			$pending_amount = $amount;
		}
		$checked = '';
		$markChecked = '';
		$disabled = 'disabled';
		$paid_checkboxes = '<td></td><td></td>';
		if(in_array($invoice_row['invno'],$invoice_arr)){
			$checked = 'checked';
			$disabled = '';
			if($invoice_row['paid']=='Paid'){
				$markChecked = 'checked';
				$paid_checkboxes = "<td>
					<div class='fakeboxholder customradio'>
						<label class='control'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
							<input type='checkbox' name='invoices[]' value='$invNumber' id='select_$invNumber' onchange='allow_isPaid(this.value);' checked>
							<div class='fakebox'></div>
						</label>
					</div>
				</td>
				<td>
					<div class='fakeboxholder customradio'>
						<label class='control'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
							<input type='checkbox' name='paid_invoice[]' value='$invNumber' id='paid_$invNumber' checked>
							<div class='fakebox'></div>
						</label>
					</div>
				</td>";
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
		$selectUnpaidInvoices = "SELECT count(customer), sum(amount) from invoices2 WHERE customer = '$customer' AND paid = '' AND deleteFlag =0 AND payment_type IS NULL";
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

			if($invpaid == '' && $in_payment_type != 'CN'){
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
					<td class='right'>%s</td></tr>",$invNumber,$invNumber,$checked,$invNumber,$invNumber,$disabled,$markChecked,$invNumber,$invDate, $longName, $customer, $invoice_balance, $noOfInvoices, $base_amount,$amount,$pending_amount);
			}else{
				echo sprintf("
					<tr class='darkcolor'>
					$paid_checkboxes
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class='right'>%s</td>
					<td class='right'>%s</td>
					<td class='right'>%s</td></tr>",$invNumber,$invDate, $longName, $customer, $invoice_balance, $noOfInvoices, $base_amount,$amount,$pending_amount);
			 	}
	}
?>
		</tbody>
	</table>
<?php die;	