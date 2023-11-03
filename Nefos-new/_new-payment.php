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

	$validationScript = <<<EOD
    $(document).ready(function() {

	  	$( "#date1" ).datepicker({
	  			 dateFormat: "yy-mm-dd",
	  		});
	  	$( "#date2" ).datepicker({
	  	   dateFormat: "yy-mm-dd"
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


	$settled_date = date("Y-m-d");
	$bank_lodgement_date = date("Y-m-d");

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

	$selectInvoices = "SELECT * FROM invoices2 WHERE paid='' AND payment_type IS NULL AND deleteFlag = 0 order by invoice_created DESC";

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

	?>
	<link rel="stylesheet" href="css/excel-bootstrap-table-filter-style.css">
	<center>
			<a href='invoice-payments.php' class='cta1'>Invoice Payments</a>
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
							<td><strong>Amount</strong></td>
							<td>
								<input type="number" name="amount" id="payment_amount" class="defaultinput" required="">
							</td>
						<!-- </tr>
						<tr> -->
							<td><strong>Currency</strong></td>
							<td>
								<select name="currency" class="defaultinput" style="width: 201px; height: 39px;">
									<option value="EUR">EUR</option>
									<option value="GBP">GBP</option>
									<option value="N/A">N/A</option>
								</select>
							</td>
						</tr>
						<tr>
							<td><strong>Bank ID</strong></td>
							<td>
								<select name="bank_id" required="" class="defaultinput">
									<option value="">Select Bank ID</option>
									<?php  while($bank_id_row = $bank_id_results->fetch()){ ?>
										<option value="<?php echo $bank_id_row['id'] ?>"><?php echo $bank_id_row['bank_id']; ?></option>
									<?php } ?>	
								</select>
							</td>
						<!-- </tr>	
						<tr> -->
							<td><strong>Settled Date</strong> </td>
							<td>
								<input type="text" name="settled_date" id="date1" class="defaultinput" value="<?php echo $settled_date; ?>"  required="">
								<a id="yes1" href="javascript:void(0)" style="text-decoration: underline;">Yesterday</a>
							</td>							
							
						</tr>
						<tr>

							<td><strong>Bank Lodgement Date</strong> </td>
							<td>
								<input type="text" name="bank_lodgement_date" id="date2" class="defaultinput"  value="<?php echo $bank_lodgement_date; ?>" required=""><br>
								<a id="yes2" href="javascript:void(0)" style="text-decoration: underline;">Yesterday</a>
							</td>
							<td><strong>Payment Type</strong></td>
							<td>
								<select name="payment_type" required="" class="defaultinput">
									<option value="">Select Payment Type</option>
									<?php while($payment_type_row = $payment_type_results->fetch()){ ?>
										<option value="<?php echo $payment_type_row['id'] ?>"><?php echo $payment_type_row['name']  ?> (<?php echo $payment_type_row['code']; ?>)</option>
									<?php } ?>	
								</select>
							</td><br>
						</tr>
						<!-- <tr>
							<td><strong>Invoices</strong></td>
 							<td>
								<select id="invoice" name="invoices[]" required="" multiple="">
									<?php /*  while($invoice_row = $invoices_results->fetch()){ ?>
										<option value="<?php echo $invoice_row['invno'] ?>"><?php echo $invoice_row['invno'] ?> (Base Amount <?php echo $invoice_row['base_amount'] ?>) (Total Amount <?php echo $invoice_row['amount'] ?>)</option>
									<?php }  */ ?>
								</select>
							</td>
						</tr> -->					
						
						<tr id="paid_status">
						</tr>
						<tr><td colspan="4"><br><br></td></tr>
						<tr>
							<td colspan="1"><strong> Allocate delta to client credit </strong> </td>
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
						<tr><td colspan="4"><br><br></td></tr>
						<tr>
							<td colspan="1"><strong> Delta (€) </strong></td>
							<td colspan="3">
								<span id="delta_amount"></span>
								<input type="hidden" name="delta_val" id="delta_value">
								<input type="hidden" name="delta" id="final_delta_value">
							</td>
						</tr>
						<tr><td colspan="4"><br><br></td></tr>
						<tr>
							<td colspan="1"><strong> Comment </strong></td>
							<td colspan="2"><textarea name="comment" class="defaultinput"></textarea></td>
						</tr>
						<tr><td colspan="4"><br><br></td></tr>
						<tr>
							<td><strong>Invoices</strong></td>
 							<td colspan="3">
								<table class='default' id='mainTable'>
									<thead>	
										<tr style='cursor: pointer;'>
											<th>Select</th>
											<th>Mark as paid</th>
											<th># Inv</th>
											<th width="70px">Inv Date</th>
											<th>Customer Name</th>
											<th>Customer Number</th>
											<th>Total invoice balance</th>
											<th>Number of invoices</th>
											<th>Base Amount</th>
											<th>Full Amount</th>
										</tr>
									</thead>
									<tbody>

										
										<?php 
										while ($invoice_row = $invoices_results->fetch()){
											$invNumber = $invoice_row['invno'];
											$invDate = ($invoice_row['invdate'])?date('Y-m-d',strtotime($invoice_row['invdate'])):'';
											$base_amount = $invoice_row['base_amount'];
											$amount = $invoice_row['amount'];
											$customer = $invoice_row['customer'];
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
												<td class='right'>%s</td></tr>",$invNumber,$invNumber,$invNumber,$invNumber,$invNumber,$invDate, $longName, $customer, $invoice_balance, $noOfInvoices, $base_amount,$amount);
										 } ?>
									</tbody>
								</table>
							</td>
						</tr>
					</table>
				</div>
			</div>
			<br />
					<!--   delta popup box -->
				<div id="delta_popup" title="Allocate Delta to credit" style="display:none">
					<table id="delta_table">
					</table>
					<!-- 			<button class='oneClick cta1' name='save_credit' style="display: none;" id="save_credit" type="button" onClick="add_credit();" >
									Add Credit
								</button> -->
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
		var yest_date = yyyy+"-"+mm+"-"+dd;
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
			      datatype:"JSON",
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
				      	$("#end_customer_num").val(data.customer);
				      	$("#end_invoice_id").val(data.invoice_id);
				      	for(var i=0; i<amount_arr.length; i++){
				      		
				      		rem_amount = parseFloat(rem_amount) - parseFloat(amount_arr[i]);
				      		var invo_id = in_id_arr[i];
				      		if(amount_arr.length-1 == i){
				      			var delta = rem_amount.toFixed(2);
				      		 	output += "<tr><td><span id='success_delta' style='color:green;'></span><br>Available Credit: <span id='avail_credit'>"+data.client_credit+"</span><input type='hidden' name='client_credit_val' value='"+data.client_credit+"' id='client_credit_val'></td></tr>"+
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
				      	// $("#paid_status").html(output2);
				      	$("#save_credit").show();
			      }
			  });
		}else{
			$("#delta_amount").text('');
			$("#delta_value").val('');
			$("#final_delta_value").val('');
			$("#use_delta_hidden").val('');
			$("#delta_table").html('No invoice selected !!');
			$("#paid_status").html('');
			$("#save_credit").hide();
		}
		//return deltaArray;
	}


	/*
	$('#invoice').on('select2:select', function (e) {
	    var data = e.params.data;
	   // console.log(data);
	    var invoice_id = data.id;
	    var selected_ids = $(this).val();
	    var invoice_ids = selected_ids.join(",");
	   // console.log(invoice_ids);
	   getInvoice(invoice_ids);

	});

	$('#invoice').on('select2:unselect', function (e) {
	    var data = e.params.data;
	    var invoice_id = data.id;
	    var selected_ids = $(this).val();
	    var invoice_ids = '';
	    if(selected_ids != null){
	    	var invoice_ids = selected_ids.join(",");
		}
	   
	   // console.log(invoice_ids);
	   getInvoice(invoice_ids);
	});	
	*/

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
		$("#avail_credit").text(avail_credit.toFixed(2));	
		$("#error_credit").html("");
		$("#use_delta_hidden").val(this_delta);

	});

	// save credit value

	function add_credit(){
		var use_delta = $("#use_delta").val();
		var delta_cust_num = $("#delta_cust_num").val();
		var delta_inv_id = $("#delta_inv_id").val();
		if(use_delta == '' ){
			$("#error_credit").html("Please enter a valid value to allocate !");
			return false;
		}

			$.ajax({
			      type:"post",
			      url:"allocateCredit.php",
			      data:{ 'credit': use_delta,'customer': delta_cust_num, 'invoice_id': delta_inv_id},
			      datatype:"JSON",
			      success:function(data)
			      {
			      	$("#avail_credit").text(data.client_credit);
			      	var calc_delta = $("#calc_delta").text();
			      	$("#delta_amount").text(calc_delta);
			      	$("#delta_value").val(calc_delta);
			      	$("#success_delta").html("Credit Added !");
			      }
			  });

	}
</script>
<script src="js/excel-bootstrap-table-filter-bundle.js"></script>
<script type="text/javascript">
	$('#mainTable').excelTableFilter();
</script>
