<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);

	$validationScript = <<<EOD
    $(document).ready(function() {

	  	$( "#date1" ).datepicker({
	  			 dateFormat: "dd-mm-yy",
	  		});
	  	$( "#date2" ).datepicker({
	  	   dateFormat: "dd-mm-yy"
	  	});	 

	  $('#registerForm').validate({
		  rules: {
			  name: {
				  required: true
			  }
    	}, // end rules
		  errorPlacement: function(error, element) {
			if (element.is("#savesig")){
				 error.appendTo("#errorBox1");
			} else if (element.is("#accept2")){
				 error.appendTo("#errorBox2");
			} else if (element.is("#accept3")){
				 error.appendTo("#errorBox3");
			} else if ( element.is(":radio") || element.is(":checkbox")){
				 error.appendTo(element.parent());
			} else {
				return true;
			}
		},
		 
    	  submitHandler: function() {
   $(".oneClick").attr("disabled", true);
   form.submit();
	    	  }
	  }); // end validate


  }); // end ready
EOD;


	pageStart("Add New Payment", NULL, $validationScript, "pprofile", NULL, "Add New Payment", $_SESSION['successMessage'], $_SESSION['errorMessage']);


	$settled_date = date("d-m-Y");
	$bank_lodgement_date = date("d-m-Y");

	// fetch bank ids 

	$selectBankID = "SELECT * FROM payment_bank_id";

	try
	{
		$bank_id_results = $pdo->prepare("$selectBankID");
		$bank_id_results->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	// fetch payment types

	$selectPaymentType = "SELECT * FROM payment_types";

	try
	{
		$payment_type_results = $pdo->prepare("$selectPaymentType");
		$payment_type_results->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

 // fetch invoices

	$selectInvoices = "SELECT * FROM invoices WHERE deleteFlag = 0 AND paid = '' AND payment_type IS NULL ORDER BY invdate DESC";
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
/*	while($invoice_row = $invoices_results->fetch()){
		$invoice_arr[] = $invoice_row['invno'];
	}*/

	// fetch unpaid invopices
	$selectUnpaidInv = "SELECT * FROM invoices WHERE deleteFlag =0 AND paid = '' AND payment_type IS NULL ORDER BY customer ASC, invdate ASC";

	try
	{
		$inv_results = $pdo->prepare("$selectUnpaidInv");
		$inv_results->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	$amount_results = [];
	$club_arr = [];
	$i =0;
	while($inv_row = $inv_results->fetch()){

		$invNumber = $inv_row['invno'];
		$inv_amount = $inv_row['amount'];
		$inv_customer = $inv_row['customer'];

			// Look up customer details: name and domain
		$selectUsersUInv = "SELECT id,longName, state, country FROM customers WHERE number = '$inv_customer'";
		try
		{
			$user_result1 = $pdo2->prepare("$selectUsersUInv");
			$user_result1->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		$rowXY = $user_result1->fetch();
		$inv_longName = $rowXY['longName'];
		// fetch invoice balance and invoice
		$selectUnpaidInvoices2 = "SELECT count(customer), sum(amount) from invoices WHERE customer = '$inv_customer' AND paid = '' AND deleteFlag =0 AND payment_type IS NULL ORDER BY invdate DESC";
		try
		{
			$unpaid_inv_result = $pdo->prepare("$selectUnpaidInvoices2");
			$unpaid_inv_result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		$rowYY = $unpaid_inv_result->fetch();
		$un_invoice_balance = $rowYY['sum(amount)'];
		$amount_results[$i]['value'] = $un_invoice_balance."-".$inv_amount;
		$amount_results[$i]['total_value'] = $inv_amount;
		$amount_results[$i]['_desc'] = $inv_customer." - ".$inv_longName."- Invoice ".$invNumber." - ".$inv_amount."- Total - ".$un_invoice_balance;
		$amount_results[$i]['customer'] = $inv_customer;
		$amount_results[$i]['inv_number'] = $invNumber;
		$amount_results[$i]['club_desc'] = $inv_customer." - ".$inv_longName."- Total - ".$un_invoice_balance;
		$amount_results[$i]['club_total'] = $un_invoice_balance;
		$amount_results[$i]['inv_number'] = $invNumber;
		$i++;
	}

	for($j=0; $j< count($amount_results); $j++){
		$next_value = $amount_results[$j+1]['customer'];
		$curr_value = $amount_results[$j]['customer'];
		if($next_value != $curr_value){
			$club_arr[$j]['customer'] =  $curr_value;
			$club_arr[$j]['value'] = $amount_results[$j]['club_total'];
			$club_arr[$j]['total_value'] = $amount_results[$j]['club_total'];
			$club_arr[$j]['_desc'] = $amount_results[$j]['club_desc'];
		}
	}
	$result_club_arr= array_merge($club_arr, $amount_results);

	$customer_sort = array_column($result_club_arr, 'customer');

	array_multisort($customer_sort, SORT_ASC, $result_club_arr);


	// Query to look up users
	 $selectUsers = "SELECT number,longName,shortName,alias FROM customers order by id ASC"; 
		try
		{
			$results = $pdo3->prepare("$selectUsers");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	while($row = $results->fetch()){
		$customer_arr[$row['number']] = $row['longName']." - ".$row['shortName']." - ".$row['alias'];
	}
	$j= 0;
	foreach($customer_arr as $cust_key => $cust_val){
		$customer_bind_arr[$j]['value'] = strval($cust_key)."--".$cust_val;
		$customer_bind_arr[$j]['_desc'] = strval($cust_key)."--".$cust_val;
		$customer_bind_arr[$j]['customer_number'] = strval($cust_key);
		$j++;
	}

	?>
	<link rel="stylesheet" href="css/excel-bootstrap-table-filter-style.css">
	<style type="text/css">
		#mainTable tr.darkcolor td{
			background-color: #e1e1e1 !important; 
			border-bottom: 1px solid #fafbfb;
		}
	</style>
	<center>
			<a href='invoice-payments.php' class='cta1'>&laquo; Payments &laquo;</a>
			<a href='bank-ids.php' class='cta1'>Bank IDs</a>
			<a href='payment-types.php' class='cta1'>Payment Types</a>
	</center>
	<center>
		<form id="registerForm" action="payment-process.php" method="POST">
			<div id="mainbox-no-width">
				<input type="hidden" name="id" value="<?php echo $id; ?>">
				<div id="mainboxheader"> Add Payment </div>
				<div class='boxcontent'>
				<table>
				 <tr>
				  <td><input type="number" name="amount" id="payment_amount" class="defaultinput eightDigit" required="" placeholder="Amount" ></td>
				  <td>
				   <select name="currency" class="defaultinput" style="width: 80px; height: 39px;">
									<option value="EUR">EUR</option>
									<option value="GBP">GBP</option>
									<option value="N/A">N/A</option>
								</select>
								<br />&nbsp;
				  </td>
				 </tr>
				 <tr>
				  <td>
				   <span style='margin-left: 12px;'>Settled Date:</span><br />
				   <input type="text" name="settled_date" id="date1" class="defaultinput eightDigit" value="<?php echo $settled_date; ?>"  required="" style='margin-top: 2px; margin-bottom: 0;'><br>
								<a id="yes1" href="javascript:void(0)" style="margin-left: 14px; font-size: 12px;">Yesterday</a>
				  </td>
				  <td>
				   <span style='margin-left: 12px;'>Bank Lodgement Date:</span><br />
				   <input type="text" name="bank_lodgement_date" id="date2" class="defaultinput eightDigit"  value="<?php echo $bank_lodgement_date; ?>" required=""style='margin-top: 2px; margin-bottom: 0;'><br>
								<a id="yes2" href="javascript:void(0)" style="margin-left: 14px; font-size: 12px;">Yesterday</a><br />&nbsp;
				  </td>
				 </tr>
				 <tr>
				  <td>
								<select name="bank_id" required="" class="defaultinput" style='width: 226px;'>
									<option value="">Bank ID</option>
									<?php  while($bank_id_row = $bank_id_results->fetch()){ ?>
										<option value="<?php echo $bank_id_row['id'] ?>"><?php echo $bank_id_row['bank_id']; ?></option>
									<?php } ?>	
								</select>
				  </td>
				  <td>
								<select name="payment_type" required="" class="defaultinput" style='width: 226px;'>
									<option value="">Payment Type</option>
									<?php while($payment_type_row = $payment_type_results->fetch()){ ?>
										<option value="<?php echo $payment_type_row['id'] ?>"><?php echo $payment_type_row['name']  ?> (<?php echo $payment_type_row['code']; ?>)</option>
									<?php } ?>	
								</select>
				  
				  </td>
				 </tr>

				</table>
				<br />
				<label>Filter By customer:</label>
				<input type="text" name="filter_customer" id="cust_num_val" class="defaultinput eightDigit ui-autocomplete-input">
				<br />
				<button id='clear_list' class="cta4">Clear list</button>
				<div id="loader_img" style="display: none;"><img src="images/rolling.gif" style="height: 50px;"></div>
				<br />
							<div id="sort_invoices">
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
											$selectUnpaidInvoices = "SELECT count(customer), sum(amount) from invoices WHERE customer = '$customer' AND paid = '' AND deleteFlag =0 AND payment_type IS NULL";
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
													<td class='right'>%s</td></tr>",$invNumber,$invNumber,$invNumber,$invNumber,$invNumber,$invDate, $longName, $customer, $invoice_balance, $noOfInvoices, $base_amount,$amount, $pending_amount);


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
										?>
									</tbody>
								</table>
							</div>	
								<br /><br />
					<table>
					 <tbody>
						
						<tr>
							<td colspan="1"><strong> Delta (€) </strong><br />&nbsp;</td>
							<td colspan="3">
								<span id="delta_amount"></span>
								<input type="hidden" name="delta_val" id="delta_value">
								<input type="hidden" name="delta" id="final_delta_value">
							</td>
						</tr>
						<tr>
							<td colspan="1"><strong> Allocate delta to client credit/debit </strong><br />&nbsp;</td>
							<td colspan="3"> 
								<div class="fakeboxholder customradio">
									<label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
										<input type="radio" name="allocate_payment" value="yes"> Yes
										<div class="fakebox"></div>
									</label>
								</div>								
								<div class="fakeboxholder customradio">
									<label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
										<input type="radio" name="allocate_payment" value="no" checked> No
										<div class="fakebox"></div>
									</label>
								</div>
							</td>
						</tr>						
						<tr>
							<td colspan="1" style='vertical-align: top;'><strong> Comment </strong></td>
							<td colspan="2"><textarea name="comment" class="defaultinput-no-margin" style='margin-left: 10px; height: 80px; width: 250px;'></textarea></td>
						</tr>
					</tbody>
					</table>
				</div>
			</div>
			<br />
					<!--   delta popup box -->
				<div id="delta_popup" title="Allocate Delta to credit/debit" style="display:none">
					<table id="delta_table">
					</table>
				</div>
				<input type="hidden" name="use_delta_hidden" id="use_delta_hidden">
				<input type="hidden" name="end_customer_num" id="end_customer_num">
				<input type="hidden" name="end_invoice_id" id="end_invoice_id">
			<button class='oneClick cta1' name='save_payment' type="submit">
				<?php echo $lang['global-savechanges']; ?>
			</button>
			</div>
		</form>


	</center>	
<?php  displayFooter(); ?>

<script type="text/javascript">

	function allow_isPaid(id){
		if(document.getElementById("select_"+id).checked){
			document.getElementById("paid_"+id).checked = true;
			document.getElementById("paid_"+id).disabled = false;
		}else{
			document.getElementById("paid_"+id).checked = false;
			document.getElementById("paid_"+id).disabled = true;
		}
		//------this code for getInvoice calculation--------//
		var checkboxes = document.getElementsByName('invoices[]');
		var invoice_ids = "";
		for (var i=0, n=checkboxes.length;i<n;i++) 
		{
			if (checkboxes[i].checked) 
			{
				invoice_ids += ","+checkboxes[i].value;
			}
		}
		invoice_ids = invoice_ids.substring(1);
		getInvoice(invoice_ids);
		//-------this code for getInvoice calculation--------//

	}

	$("#yes1,#yes2").click(function(){
		const today = new Date()
		const yesterday = new Date(today)

		yesterday.setDate(yesterday.getDate() - 1);

		var dd = yesterday.getDate();

		var mm = yesterday.getMonth()+1; 
		var yyyy = yesterday.getFullYear();
		if(dd<10) 
		{
		    dd='0'+dd;
		} 

		if(mm<10) 
		{
		    mm='0'+mm;
		} 
		var yest_date = dd+"-"+mm+"-"+yyyy;
		var this_id = $(this).attr('id');
		if(this_id == 'yes1'){
			$("#date1").val(yest_date);
		}else{
			$("#date2").val(yest_date);
		}
		
	});

	/*
	// Invoice multiple select option
	$('#invoice').select2({  
		width: '100%',  
		allowClear: false, 
		placeholder: 'Select Invoices'

	});
	*/
	
	// function for get the invoice amounts

	function getInvoice(invoice_ids){
		var deltaArray = new Array();
		
		if(invoice_ids != ''){
		
			$.ajax({
			      type:"post",
			      url:"getInvoiceDetails.php",
			      data:{ 'ids': invoice_ids},
			      dataType:"json",
			      success:function(data)
			      {
				      	var output = "";
				      	var output2 = "";
				      	var amount_arr = data.amount.split(',');
				      	var payment_amount = $("#payment_amount").val();
				      	if(payment_amount == ''){
				      		payment_amount = 0;
				      	}
				      	var rem_amount = payment_amount;
				        var in_id_arr = invoice_ids.split(",");
				        var delta_arr = data.inv_delta.split(',');
				      	$("#end_customer_num").val(data.customer);
				      	$("#end_invoice_id").val(data.invoice_id);
				      	for(var i=0; i<amount_arr.length; i++){
				      		if(delta_arr[i] != 0){
				      			rem_amount = parseFloat(rem_amount) - parseFloat(delta_arr[i]);
				      		}else{
				      			rem_amount = parseFloat(rem_amount) - parseFloat(amount_arr[i]);
				      		}
				      		//rem_amount = parseFloat(rem_amount) - parseFloat(amount_arr[i]);
				      		var invo_id = in_id_arr[i];
				      		if(amount_arr.length-1 == i){
				      			var delta = rem_amount.toFixed(2);
				      		 	output += "<tr><td><span id='success_delta' style='color:green;'></span><br>Current Credit: <span id='avail_credit'>"+data.client_credit+"</span><input type='hidden' name='client_credit_val' value='"+data.client_credit+"' id='client_credit_val'></td></tr><tr><td>Current Debit: <span id='avail_debit'>"+data.client_debit+"</span><input type='hidden' name='client_debit_val' value='"+data.client_debit+"' id='client_debit_val'></td></tr>"+
				      		 		"<tr>"+
				      		 		"<td><strong>invoice# "+in_id_arr[i]+" :</strong><input type='hidden' id='delta_inv_id' value='"+in_id_arr[i]+"'></td>"
				      		 		+"<td><input type='number' id='use_delta' value='"+delta+"' class='defaultinput' name='use_delta' required><br><span id='error_credit' style='color:red;'></span></td>"
				      		 		+"</tr>"+
				      		 		"<tr><td>Calculated delta: <span id='calc_delta'></span><input type='hidden' value='"+data.customer+"' id='delta_cust_num'> </td></tr>"
				      		 		;
				      		 		
									
				      		}

				      			if(i==0){
				      				output2 += "<tr><td><strong>Select Invoices to mark paid: </strong></td></tr>";
				      			}
				      			output2 +="<tr><td><div class='fakeboxholder customradio'>"+
										"<label class='control'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+
											"<input type='checkbox' name='paid_invoice[]' value='"+in_id_arr[i]+"'> "+in_id_arr[i]+ 
											"<div class='fakebox'></div>"+
											"</label></div></td></tr>";
				      		
				      	}
				      	
				      	$("#delta_amount").text(rem_amount.toFixed(2));
				      	$("#delta_value").val(rem_amount.toFixed(2));
				      	$("#final_delta_value").val(rem_amount.toFixed(2));
				      	$("#use_delta_hidden").val(rem_amount.toFixed(2));
				      	$("#delta_table").html(output);
			      },
				    error: function (xhr, error) {
				      console.debug(xhr); 
				      console.debug(error);
				    }
			  });
		}else{
			$("#delta_amount").text('');
			$("#delta_value").val('');
			$("#final_delta_value").val('');
			$("#use_delta_hidden").val('');
			$("#delta_table").html('No invoice selected !!');
		}
		//return deltaArray;
	}

	$("#payment_amount").on("change keyup keypress", function(){
		// var this_amount = $(this).val();
		// var selected_ids = $('#invoice').val();
	    // var invoice_ids = '';
	    // if(selected_ids != null){
	    // 	var invoice_ids = selected_ids.join(",");
		// }
		// getInvoice(invoice_ids);

		//------this code for getInvoice calculation--------//
		var checkboxes = document.getElementsByName('invoices[]');
		var invoice_ids = "";
		for (var i=0, n=checkboxes.length;i<n;i++) 
		{
			if (checkboxes[i].checked) 
			{
				invoice_ids += ","+checkboxes[i].value;
			}
		}
		invoice_ids = invoice_ids.substring(1);
		getInvoice(invoice_ids);
		//-------this code for getInvoice calculation--------//

	});

	$("input[name='allocate_payment']").change(function(){
		var this_val =$(this).val();
		// var selected_ids = $('#invoice').val();
	    // var invoice_ids = '';
	    // if(selected_ids != null){
	    // 	var invoice_ids = selected_ids.join(",");
		// }
		//  getInvoice(invoice_ids);
		//------this code for getInvoice calculation--------//
		var checkboxes = document.getElementsByName('invoices[]');
		var invoice_ids = "";
		for (var i=0, n=checkboxes.length;i<n;i++) 
		{
			if (checkboxes[i].checked) 
			{
				invoice_ids += ","+checkboxes[i].value;
			}
		}
		invoice_ids = invoice_ids.substring(1);
		getInvoice(invoice_ids);
		//-------this code for getInvoice calculation--------//

		 $("#success_delta").html("");
		if(this_val == 'yes'){
			$("#delta_popup").dialog({
				autoOpen: false,
				autoResize: true,
				width: 'auto',
				height: 'auto'
			});
			$('#delta_popup').dialog('open').effect( "highlight", "slow" );
		}else{
			$('#delta_popup').dialog('close').effect( "highlight", "slow" );
		}
	});

	// use delta input box

	$(document).on("keyup keypress change","#use_delta", function(){
		var this_delta = $(this).val();
		if(this_delta == ''){
			this_delta = 0;
		}
		var delta_value = $("#delta_value").val();
		if(delta_value == ''){
			delta_value = 0;
		}
		var final_delta = parseFloat(delta_value) - parseFloat(this_delta);
		var client_credit_val = $("#client_credit_val").val();
		var avail_credit = parseFloat(client_credit_val) + parseFloat(this_delta);
		$("#calc_delta").text(final_delta.toFixed(2));	
		$("#final_delta_value").val(final_delta.toFixed(2));	
		//$("#avail_credit").text(avail_credit.toFixed(2));	
		$("#error_credit").html("");
		$("#use_delta_hidden").val(this_delta);

	});

var amount_results = <?php echo json_encode($result_club_arr); ?>;
// auto complete suggestion for amounts	
  $( function() {
	 
	    $( "#payment_amount" ).autocomplete({
	      minLength: 0,
	      source: amount_results,
	      focus: function( event, ui ) {
	       // $( "#payment_amount" ).val( ui.item.value );
	        return false;
	      },
	      select: function( event, ui ) {
	        $( "#payment_amount" ).val( ui.item.total_value );
	        var customer_num = ui.item.customer;
    	    $.ajax({
			      type:"post",
			      url:"customerInvoices.php?cust_num="+customer_num,
			      success:function(data)
			      {
			      	//console.log(data);
					$("#sort_invoices").html(data);
					$("#cust_num_val").val('');
					$('#mainTable').excelTableFilter();
					if(ui.item.inv_number != null){
			      		$("#select_" + ui.item.inv_number).prop('checked', true).trigger("change");
			      	}
			      }
			});
	        return false;
	      }
	    }).focus(function(){
	            $(this).autocomplete("search");
	    }).autocomplete( "instance" )._renderItem = function( ul, item ) {
		      return $( "<li>" )
		        .append( "<div>" + item._desc + "</div>" )
		        .appendTo( ul );
		    };
	    
	} );

  	var customer_bind_arr = <?php echo json_encode($customer_bind_arr); ?>;
		$( "#cust_num_val" ).autocomplete({
	       	source: customer_bind_arr,
	      	minLength: 0,
	      	focus: function( event, ui ) {
	       // $( "#payment_amount" ).val( ui.item.value );
	        return false;
	      },
	      	select: function( event, ui ) {
	      		var customer_num = ui.item.customer_number;
	      		$("#cust_num_val").val(customer_num);
	      		/*var split_vlaue = selected_value.split("-");
	      		var customer_num = split_vlaue[0];*/
	      			$.ajax({
					      type:"post",
					      url:"customerInvoices.php?cust_num="+customer_num,
					      success:function(results)
					      {
							$("#sort_invoices").html(results);
							$('#mainTable').excelTableFilter();
					      }
					   });
	      		return false;
	      	},	      	
	    }).focus(function(){
	        if (this.value == ""){
	            $(this).autocomplete("search");
	        }
	    }).autocomplete( "instance" )._renderItem = function( ul, item ) {
		      return $( "<li>" )
		        .append( "<div>" + item._desc + "</div>" )
		        .appendTo( ul );
		    };


	$("#cust_num_val").on("keyup keypress", function(){
		var this_num = $(this).val();
  		$.ajax({
	      type:"post",
	      url:"customerInvoices.php?cust_num="+this_num,
	      success:function(results)
	      {
			$("#sort_invoices").html(results);
			$('#mainTable').excelTableFilter();
	      }
	   });

	});	

	$("#clear_list").on("click", function(e){
		e.preventDefault();
		var pay_amt = $("#payment_amount").val();
		var cust_amt= $("#cust_num_val").val();
		if(pay_amt != '' || cust_amt != ''){
			$('#loader_img').show();
  			$(this).prop("disabled", true);
			$("#payment_amount").val('');
			$("#cust_num_val").val('');
			var this_num = 0;
	  		$.ajax({
		      type:"post",
		      url:"customerInvoices.php?cust_num="+this_num,
		      success:function(results)
		      {
		      	$('#loader_img').hide();
				$("#sort_invoices").html(results);
				$('#mainTable').excelTableFilter();
				$("#clear_list").prop("disabled", false);
				$("#payment_amount").trigger("change");
		      }
		   });
  		}

	});

  </script>
</script>
<script src="js/excel-bootstrap-table-filter-bundle.js"></script>
<script src="js/moment.js"></script>
<script type="text/javascript">
	$(function(){
		$('#mainTable').excelTableFilter();
	} );
</script>
