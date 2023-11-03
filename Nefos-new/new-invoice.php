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
	  			 dateFormat: "dd-mm-yy"
	  		});
	  	$( "#date2" ).datepicker({
	  	   dateFormat: "dd-mm-yy"
	  	});	  	
	  	$( "#date3" ).datepicker({
	  			 dateFormat: "dd-mm-yy"
	  		});
	  	$( "#date4" ).datepicker({
	  	   dateFormat: "dd-mm-yy"
	  	});

  	   $.validator.addMethod('totalCheck', function(value, element, params) {
		    var credit_amount = $('input[name="' + params[0] + '"]').val(),
		        total_credit = $('input[name="' + params[1] + '"]').val(),
		        total_amount = $('input[name="' + params[2] + '"]').val();
		        

		    // return parseInt(value) === parseInt(field_1) + parseInt(field_2);
		   if(parseFloat(credit_amount) < 0){
		   		return false;
		   }
		    if (parseFloat(credit_amount) > parseFloat(total_credit) || parseFloat(credit_amount) >parseFloat(total_amount)) {
			    return false;
		    } else {
			    return true;
		    }
		}, "Enter the valid amount ");  	   

		$.validator.addMethod('totalDebitCheck', function(value, element, params) {
		    var debit_amount = $('input[name="' + params[0] + '"]').val(),
		        total_debit = $('input[name="' + params[1] + '"]').val();
		        

		    // return parseInt(value) === parseInt(field_1) + parseInt(field_2);
		   if(parseFloat(debit_amount) < 0){
		   		return false;
		   }
		    if (parseFloat(debit_amount) > parseFloat(total_debit)) {
			    return false;
		    } else {
			    return true;
		    }
		}, "Enter the valid amount ");
    	    
	  $('#registerForm').validate({
		  rules: {
			  name: {
				  required: true
			  },
			  base_amount:{
			  	number: true
			  },
			  discount:{
			  	number: true
			  },
			  vat:{
			  	number:true
			  },			 
			   shipping:{
			  	number:true
			  },			  
			  unit_price:{
			  	number:true
			  },			  
			  number_items:{
			  	number:true
			  },
			  credit_amount: {
				  totalCheck: ['credit_amount', 'total_credit', 'total_amount']
			  },			  
			  debit_amount: {
				  totalCheck: ['debit_amount', 'total_debit']
			  },
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

	  $('#registerForm2').validate({
		  rules: {
			  name: {
				  required: true
			  },
			  base_amount:{
			  	number: true
			  },
			  discount:{
			  	number: true
			  },
			  vat:{
			  	number:true
			  },			 
			   shipping:{
			  	number:true
			  },			  
			  unit_price:{
			  	number:true
			  },			  
			  number_items:{
			  	number:true
			  },
			  credit_amount2: {
				  totalCheck: ['credit_amount2', 'total_credit2', 'total_amount_order']
			  },
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

   //  query to look up issues
	$selectElements = "SELECT * FROM invoice_elements order by id ASC"; 
		try
		{
			$result_element = $pdo3->prepare("$selectElements");
			$result_element->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

		$i =0;
		 while($rowElement = $result_element->fetch()){  
		 		$element_arr[$i]['name'] = $rowElement['element_en'];
		 		$element_arr[$i]['price'] = $rowElement['element_price'];
		 		$element_arr[$i]['custom_options'] = $rowElement['custom_options'];
		 		$i++;
		 }

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



	pageStart("New Invoice", NULL, $validationScript, "pprofile", NULL, "New Invoice", $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>
<!-- <center><a href='invoice-section.php' class='cta'>Invoice Section</a></center> -->

<?php

	// current_date

	$current_timestamp = date("Y-m-d H:i");

	$invoice_date = date("d-m-Y");
	$invoice_due_date = date("d-m-Y");
	$previous_month =   date("F Y", strtotime("last day of previous month"));

	$form_type = $_REQUEST['type'];

	$hw_form_style = 'display:none;';
	$sw_form_style = '';

	if($form_type == 'sw'){
		$hw_form_style = "display:none;";
		$sw_form_style = '';
	}else if($form_type == 'hw'){
		$sw_form_style = "display:none;";
		$hw_form_style = '';
	}
?>
<center>
	<!-- <table class='profileTable' style='text-align: left; margin: 0;'>
		 <tr>
		 	<td><strong>Invoice Type</strong></td>
		 	<td>
		 		<input type="radio" name="choose_invoice_form" value="SW" checked>Software Invoice
		 		<input type="radio" name="choose_invoice_form" value="HW">Hardware Invoice
		 	</td>
		 </tr>
		 <tr>
	</table> -->
</center>

<center>
<div class="overview">
	<div id="mainbox-no-width">
		<div class='boxcontent'>
			<form>
			<table class='profileTable' style='text-align: left; margin: 0;'>
				<tr>
					<td colspan="2">
						<center><strong style='font-size: 18px;'><u><a href='invoice-section.php' class='cta'>Invoice Section</a></u></strong>
						</center>
						<br />
					</td>
				</tr>
				<tr>
					<td><strong>Invoice Type</strong></td>
					<td>
						<div class="fakeboxholder customradio">
							<label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								<input type="radio" name="choose_invoice_form" value="SW" <?php if($form_type == 'sw'){ echo "checked"; } ?>>Software Invoice
								<div class="fakebox"></div>
							</label>
						</div>
						<div class="fakeboxholder customradio">
							<label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								<input type="radio" name="choose_invoice_form" value="HW" <?php if($form_type == 'hw') { echo 'checked'; } ?>>Hardware Invoice
								<div class="fakebox"></div>
							</label>
						</div>
					</td>
				</tr>
			</table>
			</form>
		</div>
	</div>
</div>
</center>
<div class="overview" id="software_form" style="<?php echo $sw_form_style; ?>">
	<form id="registerForm" action="invoice-process.php" method="POST">
		<input type="hidden" name="choose_invoice_type" value="SW">
		<center>
			<div id="mainbox-no-width">
				<div class='boxcontent'>
					<table>
						<tr>
							<td colspan="6">
								<center><strong style='font-size: 18px;'><u>Software Invoice</u></strong>
								</center>
								<br />
							</td>
						</tr>
						<tr>
<!-- 							<td><strong>Payment Status</strong>
							</td>
							<td>
								<select name="status" class="defaultinput" style="width: 201px; height: 39px;">
									<option value="">Select Status</option>
									<option value="Credited">Credited</option>
									<option value="Paid">Paid</option>
									<option value="Write Off">Write off</option>
								</select>
							</td> -->
						<!-- </tr>
						<tr> -->
							<td><strong>Customer</strong>
							</td>
							<td>
								<input type="text" name="customer_number" id="cust_num" class="defaultinput" value="<?php //echo $nextMemberNo ?>" required/><br>
								<span id="error_message" style="color: red; font-size: 12px;"></span>
							</td>
						<!-- </tr>
						<tr> -->
							<td>	<strong>Invoice Date</strong>
							</td>
							<td>
								<input type="text" name="invoice_date" class="defaultinput" id="date1" required="" value="<?php echo $invoice_date; ?>">
							</td>
							<td><strong>Invoice Due Date</strong>
							</td>
							<td>
								<input type="text" name="invoice_due_date" class="defaultinput" id="date2" required="" value="<?php echo $invoice_due_date; ?>" />
							</td>
						</tr>
						<tr>

						<!-- </tr>
						<tr> -->
							<td><strong>Currency</strong>
							</td>
							<td>
								<select name="currency" class="defaultinput" style="width: 201px; height: 39px;">
									<option value="EUR">EUR</option>
									<option value="GBP">GBP</option>
									<option value="N/A">N/A</option>
								</select>
							</td>
						<!-- </tr>
						<tr> -->
							<td><strong>SW Base Price</strong>
							</td>
							<td>
								<input type="text" name="base_amount" class="total_calc defaultinput" id="base_calc" required="" >
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<div id="mainbox-no-width" class="club_statstics" style="display: none;">
									<div id='mainboxheader'>Club Member Statstics</div>
									<div class="boxcontent">
									     <table>
										      <tbody><tr>
										       <td>Members Dispense last month:</td>
										       <td><span id="dispense_members_no"></span></td>
										      </tr>
										      <tr>
										       <td>Number of users:</td>
										       <td><span id='users_number'></span></td>
										      </tr>
										      <tr>
										       <td>Number of log operations last month:</td>
										       <td><span id='log_oprtaions'></span></td>
										      </tr>
										     </tbody>
									 	</table>
								    </div>
								</div>
							</td>
						</tr>
						<tr>
							<td><strong>Members section</strong></td>
							<td>
								<div class="fakeboxholder customradio">
									<label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
										<input type="radio" name="member_check" value="MM">Member module (<span id="member_module_total">x</span> €)
										<div class="fakebox"></div>
									</label>
									<input type="hidden" name="member_module_val" id="member_module_val">
								</div>
								<div class="fakeboxholder customradio">
									<label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
										<input type="radio" name="member_check" value="MD">Members dispensed (<span id="member_dispense_total">y</span> €)
										<div class="fakebox"></div>
									</label>
									<input type="hidden" name="member_dispense_val" id="member_dispense_val">
								</div>
							</td>
						</tr>
						<?php foreach($element_arr as $element){ ?>
						<tr>

							<td>
								<?php 
									if($element['custom_options'] == 1){
										
								?>
									<div class="fakeboxholder customradio">
										<label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
											<input type="checkbox" name="" value="yes" class="total_unit_check defaultinput" data-id="units_<?php echo $element['name']; ?>">
											<div class="fakebox"></div>
										</label>
										<input type="text" id="units_<?php echo $element['name']; ?>" name="" class="defaultinput total_units" data-price = "<?php echo $element['price'] ?>" placeholder="Enter <?php echo $element['name'] ?> units/hours" style="display: none;" >
										<input type="hidden" name="fees[<?php echo $element['name']; ?>]" id="total_units_<?php echo $element['name']; ?>">
									</div>
								<?php }else if($element['custom_options'] == 2){  ?>
									<div class="fakeboxholder customradio">
										<label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
											<input type="checkbox" name="" value="yes" class="custom_amount_check defaultinput" data-id="custom_amount_<?php echo $element['name']; ?>">
											<div class="fakebox"></div>
										</label>
										<input type="text" id="custom_amount_<?php echo $element['name']; ?>" name="fees[<?php echo $element['name']; ?>]" class="defaultinput total_custom_amount" placeholder="Enter custom amount" style="display: none;" >
									</div>
								<?php	}
									else{ ?>
									<div class="fakeboxholder customradio">
										<label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
											<input type="checkbox" name="fees[<?php echo $element['name']; ?>]" value="<?php echo $element['price'] ?>" class="total_check defaultinput">
											<div class="fakebox"></div>
										</label>
									</div>
								<?php } ?>	
								
							</td>
							<td><strong><?php echo $element['name']; ?> <?php if($element['custom_options'] != 2){ ?> (<?php echo $element['price'] ?> €) <?php } ?></strong>
							</td>	
						</tr>
						<?php } ?>
						<tr>
							<td><strong>Discount (%)</strong>
							</td>
							<td>
								<input type="text" class="defaultinput" name="discount" id="disc" >
							</td>
						<!-- </tr>
						<tr> -->
							<td><strong>VAT (%)</strong>
							</td>
							<td>
								<input type="text" class="defaultinput" name="vat" id="vat">
							</td>							

							<td><strong>Use credit?</strong>
							</td>
							<td>
								<div class="fakeboxholder customradio">
									<label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
										<input type="radio" name="use_credit" value="Yes" class="defaultinput"> Yes
										<div class="fakebox"></div>
									</label>
								</div>								
								<div class="fakeboxholder customradio">
									<label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
										<input type="radio" name="use_credit" value="No" class="defaultinput" checked> No
										<div class="fakebox"></div>
									</label>
								</div>
								<br>
								(<span id="credit_avail">0</span> € Available)
								<input type="text" name="credit_amount" id="credit_amount" class="defaultinput" placeholder="Enter Credit amount" style="display: none;" required="">
								<input type="hidden" name="total_credit" value="0" id="total_credit">
							</td>
						</tr>
						<tr>
							<td><strong>Allocate customer debit</strong>
							</td>
							<td>
								<div class="fakeboxholder customradio">
									<label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
										<input type="radio" name="use_debit" value="Yes" class="defaultinput"> Yes
										<div class="fakebox"></div>
									</label>
								</div>								
								<div class="fakeboxholder customradio">
									<label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
										<input type="radio" name="use_debit" value="No" class="defaultinput" checked> No
										<div class="fakebox"></div>
									</label>
								</div>
								<br>
								(<span id="debit_avail">0</span> € Available)
								<input type="text" name="debit_amount" id="debit_amount" class="defaultinput" placeholder="Enter Debit amount" style="display: none;" required="">
								<input type="hidden" name="total_debit" value="0" id="total_debit">
							</td>
							<td><strong>Total Amount</strong>
							</td>
							<td>
								<input type="text" class="defaultinput" name="total_amount" id="total_amount" readonly required="">
							</td>
						<!-- </tr>
						<tr> -->
							<td style="align:top;"><strong>Description</strong>
							</td>
							<td colspan="3">
								<textarea class="defaultarea" name="description">Software: <?php echo $previous_month ?></textarea>
							</td>
						</tr>
					</table>
				</div>
			</div>
			<br />
			<button class='oneClick cta1' name='save_invoice' type="submit"><?php echo $lang['global-savechanges']; ?></button>
	</form>
</div>
<div class="overview" id="hardware_form" style="<?php echo $hw_form_style; ?>">

	<form id="registerForm2" action="invoice-process.php" method="POST">
		<input type="hidden" name="choose_invoice_type" value="HW">
		<center>
			<div id="mainbox-no-width">
				<div class='boxcontent'>
					<table>
						<tr>
							<td colspan="4">
								<center><strong style='font-size: 18px;'><u>Hardware Invoice</u></strong>
								</center>
								<br />
							</td>
						</tr>
						<tr>

						<td><strong>Customer</strong></td>
						<td>
							<input type="text" class="defaultinput" name="customer_number" id="cust_num1"  value="<?php //echo $nextMemberNo ?>"  required/>
						</td>
						</tr>
						<tr>
								<td>
								<strong>Invoice Date</strong>
								</td>
								<td> 
								<input type="text" class="defaultinput" name="invoice_date" id="date3" required="" value="<?php echo $invoice_date; ?>">
								</td>					
						<!-- </tr>
						<tr> -->
							<td><strong>Invoice Due Date</strong></td>
							<td><input type="text" class="defaultinput" name="invoice_due_date" id="date4" required="" value="<?php echo $invoice_due_date; ?>" /></td>
						</tr>
						<tr>
							<td><strong>Currency</strong></td>
							<td>
								<select name="currency" class="defaultinput">
									<option value="EUR">EUR</option>
									<option value="GBP">GBP</option>
									<option value="N/A">N/A</option>
								</select>
							</td>
						<!-- </tr>
						<tr> -->
								<td><strong>Order id (Sale id)</strong></td>
								<td>
									<input type="text" class="defaultinput" name="order_id" id="order_id" required/>
									<input type="hidden" name="total_amount_order" id="total_amount_order" value="">
								</td>
							</tr>
							<tr>
								<td>
									<strong>Use credit?</strong>
									<div class="fakeboxholder customradio">
										<label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
											<input type="radio" name="use_credit2" value="Yes" class="defaultinput"> Yes
											<div class="fakebox"></div>
										</label>
									</div>								
									<div class="fakeboxholder customradio">
										<label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
											<input type="radio" name="use_credit2" value="No" class="defaultinput" checked> No
											<div class="fakebox"></div>
										</label>
									</div>
									<br>
									(<span id="credit_avail2">0</span> € Available)
									<input type="text" name="credit_amount2" id="credit_amount2" class="defaultinput" placeholder="Enter Credit amount" style="display: none;" required="">
									<input type="hidden" name="total_credit2" value="0" id="total_credit2">
								</td>
							<td><strong>Description</strong></td>
								<td colspan="3">
									<textarea class="defaultarea" name="description"></textarea>
							</td>
						</tr>
					</table><br>
				</div>
			</div>
			<br />
			<button class='oneClick cta1' name='save_invoice' type="submit"><?php echo $lang['global-savechanges']; ?></button>
	</form>
	
</div>
<?php displayFooter(); ?>

<?php
foreach($customer_arr as $cust_key => $cust_val){
	$customer_bind_arr[] = strval($cust_key)."--".$cust_val;
}
?>

<script type="text/javascript">
	var customer_bind_arr = <?php echo json_encode($customer_bind_arr); ?>;
		$( "#cust_num" ).autocomplete({
	       	source: customer_bind_arr,
	      	minLength: 0,
	      	select: function( event, ui ) {
	      		var selected_value = ui.item.value;
	      		var split_vlaue = selected_value.split("-");
	      		var customer_num = split_vlaue[0];
	      		console.log(customer_num);
	      			$.ajax({
					      type:"post",
					      url:"getMembersDetails.php?cust_num="+customer_num,
					      datatype:"JSON",
					      success:function(data)
					      {
					      	console.log(data);
					      	
					      	if(data.error){
					      		$("#error_message").text(data.error);
					      		$(".club_statstics").hide();
					      	}else{
					      		$("#error_message").text('');
					      		$(".club_statstics").show();
					      	}
					      	if(data.member_dispensed_total){
					      		var member_dispensed_total = data.member_dispensed_total;
					      		$("#member_dispense_total").text(member_dispensed_total);
					      		$("#member_dispense_val").val(member_dispensed_total);
					      	}else{
					      		$("#member_dispense_total").text('');
					      		$("#member_dispense_val").val(0);
					      	}					      	
					      	if(data.member_module_total){
					      		var member_module_total = data.member_module_total;
					      		$("#member_module_total").text(member_module_total);
					      		$("#member_module_val").val(member_module_total);
					      	}else{
					      		$("#member_module_total").text('');
					      		$("#member_module_val").val(0);
					      	}

					      	if(data.member_dispensed){
					      		$("#dispense_members_no").text(data.member_dispensed);
					      	}else{
					      		$("#dispense_members_no").text('');
					      	}

					      	if(data.users){
					      		$("#users_number").text(data.users);
					      	}else{
					      		$("#users_number").text('');
					      	}

					      	if(data.logs){
					      		$("#log_oprtaions").text(data.logs);
					      	}else{
					      		$("#log_oprtaions").text('');
					      	}					      	
					      	if(data.vat){
					      		$("#vat").val(data.vat);
					      	}else{
					      		$("#vat").val('');
					      	}
					      	if(data.member_selection){
					      		$("input[name='member_check'][value = '"+data.member_selection+"']").prop('checked', true);
					      		$("input[name='member_check']").trigger("change");
					      	}else{
					      		$("input[name='member_check']").prop('checked', false);
					      		$("input[name='member_check']").trigger("change");
					      	}

					      	if(data.credit){
					      		$("#total_credit").val(data.credit);
					      		$("#credit_avail").text(data.credit);
					      	}else{
					      		$("#total_credit").val(0);
					      		$("#credit_avail").text('0');
					      	}					      	
					      	if(data.debit){
					      		$("#total_debit").val(data.debit);
					      		$("#debit_avail").text(data.debit);
					      	}else{
					      		$("#total_debit").val(0);
					      		$("#debit_avail").text('0');
					      	}

							//$("#select_contact").html(data);
					      }
					   });
	      	}
	    }).focus(function(){
	        if (this.value == ""){
	            $(this).autocomplete("search");
	        }
	    });	

		$( "#cust_num1" ).autocomplete({
	       	source: customer_bind_arr,
	      	minLength: 0,
	      	select: function( event, ui ) {
	      		var selected_value = ui.item.value;
	      		var split_vlaue = selected_value.split("-");
	      		var customer_num = split_vlaue[0];
	      		console.log(customer_num);
	      			$.ajax({
					      type:"post",
					      url:"getMembersDetails.php?cust_num="+customer_num,
					      datatype:"JSON",
					      success:function(data)
					      {
					      	console.log(data);
					      	if(data.credit){
					      		$("#total_credit2").val(data.credit);
					      		$("#credit_avail2").text(data.credit);
					      	}else{
					      		$("#total_credit2").val(0);
					      		$("#credit_avail2").text('0');
					      	}					      	

							//$("#select_contact").html(data);
					      }
					   });
	      	}
	    }).focus(function(){
	        if (this.value == ""){
	            $(this).autocomplete("search");
	        }
	    });	 

	 $("input[name='choose_invoice_form']").change(function(){
		var invoice_type = $(this).val();
		if(invoice_type == 'HW'){
			$("#base_calc").prop("readonly", true);
			//$("input[type='text']").val('');
			$("#hardware_form").show();
			$("#software_form").hide();
		}else{
			$("#base_calc").prop("readonly", false);
			//$("input[type='text']").val('');
			$("#software_form").show();
			$("#hardware_form").hide();
		}
	});

	$("input[name='member_check']").change(function(){
		var member_val = $("input[name='member_check']:checked").val();
		var member_value = 0;
		var total_sum = 0;
		if(member_val == 'MM'){
			member_value = $("#member_module_val").val();
		}else if(member_val == 'MD'){
			member_value = $("#member_dispense_val").val();
		}
		if(member_value > 0){
			$("#base_calc").val(member_value);
		}
		$(".total_calc").trigger("change");
	});

	$(".total_calc").on("change keyup keypress",function(){
  		//var invoice_type_val = $("input[name='choose_invoice_type']:checked").val();
		var total_sum = 0;
  		var member_value = 0;
  		var member_val= $('input[name="member_check"]:checked').val();
  		if(member_val == 'MM'){
			member_value = $("#member_module_val").val();
		}else if(member_val == 'MD'){
			member_value = $("#member_dispense_val").val();
		}
		//total_sum += parseFloat(member_value);
		$(".total_calc").each(function(){
			var this_val = $(this).val();
			var this_type = $(this).attr('type');
			if(this_val != ''){
				total_sum += parseFloat(this_val);

			}
		});

		$(".total_custom_amount").each(function(){
			var this_val = $(this).val();
			var this_id = $(this).attr('id');
			if(this_val != ''){
				total_sum += parseFloat(this_val);

			}
		});	

		if($(".total_units").is(":visible")){
			$(".total_units").each(function(){
				var this_units = $(this).val();
				var this_price = $(this).data("price");
				if(this_units != ''){
					var unit_sum = parseFloat(this_units) * parseFloat(this_price)
					total_sum += parseFloat(unit_sum) ;
				}
			});
		}

		$(".total_check:checked").each(function(){
			var this_val = $(this).val();
			if(this_val != ''){
				total_sum += parseFloat(this_val);
			}
		});
		//$("#disc").val(0);
		var vat = $("#vat").val();
			/*if(discount != ''){
				discount = (100 - discount) / 100;
				total_sum = total_sum * discount;
			}*/
			if(vat != ''){
				vat = total_sum * vat / 100
				total_sum = total_sum + vat;
			}
			var total_amount = total_sum.toFixed(2);
			$("#total_amount").val(total_amount);
			
	});

	$(".total_check").on("change",function(){
  		//var invoice_type_val = $("input[name='choose_invoice_type']:checked").val();
		var total_sum = 0;
  		var member_value = 0;
  		var member_val= $('input[name="member_check"]:checked').val();
  		if(member_val == 'MM'){
			member_value = $("#member_module_val").val();
		}else if(member_val == 'MD'){
			member_value = $("#member_dispense_val").val();
		}
		//total_sum += parseFloat(member_value);
		$(".total_calc").each(function(){
			var this_val = $(this).val();
			var this_id = $(this).attr('id');
			if(this_val != ''){
				total_sum += parseFloat(this_val);

			}
		});

		$(".total_custom_amount").each(function(){
			var this_val = $(this).val();
			var this_id = $(this).attr('id');
			if(this_val != ''){
				total_sum += parseFloat(this_val);

			}
		});	

		if($(".total_units").is(":visible")){
			$(".total_units").each(function(){
				var this_units = $(this).val();
				var this_price = $(this).data("price");
				if(this_units != ''){
					var unit_sum = parseFloat(this_units) * parseFloat(this_price)
					total_sum += parseFloat(unit_sum) ;
				}
			});
		}
		$(".total_check:checked").each(function(){
			var this_val = $(this).val();
			if(this_val != ''){
				total_sum += parseFloat(this_val);
			}
		});
		var discount = $("#disc").val();
		var vat = $("#vat").val();
		/*if(discount != ''){
			discount = (100 - discount) / 100;
			total_sum = total_sum * discount;
		}*/
		if(vat != ''){
			vat = total_sum * vat / 100
			total_sum = total_sum + vat;
		}
		var total_amount = total_sum.toFixed(2);
		$("#total_amount").val(total_amount);
	});
	$("#vat").on("change keyup keypress", function(){
		var total_sum = 0;
		var member_value = 0;
  		var member_val= $('input[name="member_check"]:checked').val();
  		if(member_val == 'MM'){
			member_value = $("#member_module_val").val();
		}else if(member_val == 'MD'){
			member_value = $("#member_dispense_val").val();
		}
		//total_sum += parseFloat(member_value);
			$(".total_calc").each(function(){
				var this_val = $(this).val();
				var this_id = $(this).attr('id');
				if(this_val != ''){
					total_sum += parseFloat(this_val);
				}
			});

		$(".total_custom_amount").each(function(){
			var this_val = $(this).val();
			var this_id = $(this).attr('id');
			if(this_val != ''){
				total_sum += parseFloat(this_val);

			}
		});	

		if($(".total_units").is(":visible")){
			$(".total_units").each(function(){
				var this_units = $(this).val();
				var this_price = $(this).data("price");
				if(this_units != ''){
					var unit_sum = parseFloat(this_units) * parseFloat(this_price)
					total_sum += parseFloat(unit_sum) ;
				}
			});
		}
			$(".total_check:checked").each(function(){
				var this_val = $(this).val();
				if(this_val != ''){
					total_sum += parseFloat(this_val);
				}
			});
			var discount = $("#disc").val();
				var vat = $("#vat").val();
				/*if(discount != ''){
					discount = (100 - discount) / 100;
					total_sum = total_sum * discount;
				}*/
				if(vat != ''){
					vat = total_sum * vat / 100
					total_sum = total_sum + vat;
				}
			var total_amount = total_sum.toFixed(2);
			$("#total_amount").val(total_amount);

	});
	$(".total_unit_check").change(function(){
		var checked_unit = $(".total_unit_check:checked").val();
		var this_id = $(this).data('id');

		if(this.checked){
			$("#"+this_id).fadeIn(500);
		}else{
			$("#"+this_id).fadeOut(500);
			$("#"+this_id).val('');
		}
		$(".total_units").trigger("change");
	});	

	$(".custom_amount_check").change(function(){
		var checked_unit = $(".custom_amount_check:checked").val();
		var this_id = $(this).data('id');

		if(this.checked){
			$("#"+this_id).fadeIn(500);
		}else{
			$("#"+this_id).fadeOut(500);
			$("#"+this_id).val('');
		}
		//$(".total_units").trigger("change");
	});
	$(".total_units").on("change keyup keypress", function(){
		var total_sum = 0;
  		var member_value = 0;
  		var member_val= $('input[name="member_check"]:checked').val();
  		if(member_val == 'MM'){
			member_value = $("#member_module_val").val();
		}else if(member_val == 'MD'){
			member_value = $("#member_dispense_val").val();
		}
		//total_sum += parseFloat(member_value);
		$(".total_calc").each(function(){
			var this_val = $(this).val();
			var this_id = $(this).attr('id');
			if(this_val != ''){
				total_sum += parseFloat(this_val);

			}
		});	
		$(".total_custom_amount").each(function(){
			var this_val = $(this).val();
			var this_id = $(this).attr('id');
			if(this_val != ''){
				total_sum += parseFloat(this_val);

			}
		});	

		if($(".total_units").is(":visible")){
			$(".total_units").each(function(){
				var this_units = $(this).val();
				var this_price = $(this).data("price");
				var this_id = $(this).attr('id');

				if(this_units != '' ){
					var unit_sum = parseFloat(this_units) * parseFloat(this_price);
					$("#total_"+this_id).val(unit_sum);
					total_sum += parseFloat(unit_sum) ;
				}
			});
		}
		
		$(".total_check:checked").each(function(){
			var this_val = $(this).val();
			if(this_val != ''){
				total_sum += parseFloat(this_val);
			}
		});
		var discount = $("#disc").val();
		var vat = $("#vat").val();
		/*		if(discount != ''){
			discount = (100 - discount) / 100;
			total_sum = total_sum * discount;
		}*/
		if(vat != ''){
			vat = total_sum * vat / 100
			total_sum = total_sum + vat;
		}
		var total_amount = total_sum.toFixed(2);
		$("#total_amount").val(total_amount);
	});

	// update base price on discount

	$("#disc").on("keyup keypress", function(){
		var discount = $(this).val();
		var base_amount  = $("#base_calc").val();
		var discounted_base_amiunt;
		if(base_amount == ''){
			discounted_base_amiunt = 0;
		}
		if(discount != ''){
			discount = (100 - discount) / 100;
			discounted_base_amiunt = base_amount * discount;
			$("#base_calc").val(discounted_base_amiunt.toFixed(2));
		}
			
			$(".total_check").trigger("change");
			$("#vat").trigger("change");
			//$(".total_calc").trigger("change");
			$(".total_units").trigger("change");
			$(".total_custom_amount").trigger("change");
	});
	$("#base_calc").on("keyup keypress", function(){
		$("#disc").val(0);
	});
	// update calulation on custom amount
	$(".total_custom_amount").on("change keyup keypress", function(){
		var total_sum = 0;
  		var member_value = 0;
  		var member_val= $('input[name="member_check"]:checked').val();
  		if(member_val == 'MM'){
			member_value = $("#member_module_val").val();
		}else if(member_val == 'MD'){
			member_value = $("#member_dispense_val").val();
		}
		//total_sum += parseFloat(member_value);
		$(".total_calc").each(function(){
			var this_val = $(this).val();
			var this_id = $(this).attr('id');
			if(this_val != ''){
				total_sum += parseFloat(this_val);

			}
		});			
		$(".total_custom_amount").each(function(){
			var this_val = $(this).val();
			var this_id = $(this).attr('id');
			if(this_val != ''){
				total_sum += parseFloat(this_val);

			}
		});	

		if($(".total_units").is(":visible")){
			$(".total_units").each(function(){
				var this_units = $(this).val();
				var this_price = $(this).data("price");
				var this_id = $(this).attr('id');

				if(this_units != '' ){
					var unit_sum = parseFloat(this_units) * parseFloat(this_price);
					$("#total_"+this_id).val(unit_sum);
					total_sum += parseFloat(unit_sum) ;
				}
			});
		}
		
		$(".total_check:checked").each(function(){
			var this_val = $(this).val();
			if(this_val != ''){
				total_sum += parseFloat(this_val);
			}
		});
		var discount = $("#disc").val();
		var vat = $("#vat").val();
		/*		if(discount != ''){
			discount = (100 - discount) / 100;
			total_sum = total_sum * discount;
		}*/
		if(vat != ''){
			vat = total_sum * vat / 100
			total_sum = total_sum + vat;
		}
		var total_amount = total_sum.toFixed(2);
		$("#total_amount").val(total_amount);
	});

	// change due date on chnage 
	$("#date1").change(function(){
		var this_date =$(this).val();
		$("#date2").val(this_date);
	});	
	$("#date3").change(function(){
		var this_date =$(this).val();
		$("#date4").val(this_date);
	});

   // cchange credit card options

   $("input[name='use_credit']").change(function(){
   		var this_val = $(this).val();
   		var totalAmt = $("#total_amount").val();
   		var totalCredit = $("#total_credit").val(); 
   		if(this_val == 'Yes'){
   			$("#credit_amount").fadeIn(500);
   			if(totalCredit > totalAmt){
   				$("#credit_amount").val(totalAmt);
   			}else{
   				$("#credit_amount").val(totalCredit);
   			}
   		}else{
   			$("#credit_amount").val('').fadeOut(500);
   		}
   });   

   $("input[name='use_debit']").change(function(){
   		var this_val = $(this).val();
   		var totalAmt = $("#total_amount").val();
   		var totalDebit = $("#total_debit").val(); 
   		if(this_val == 'Yes'){
   			$("#debit_amount").fadeIn(500);
   			if(totalDebit > 0){
   				$("#debit_amount").val(totalDebit);
   			}
   		}else{
   			$("#debit_amount").val('').fadeOut(500);
   		}
   });   

   $("input[name='use_credit2']").change(function(){
   		var this_val = $(this).val();
   		var totalAmt = $("#total_amount_order").val();
   		var totalCredit = $("#total_credit2").val(); 
   		if(this_val == 'Yes'){
   			$("#credit_amount2").fadeIn(500);
   			 if(totalCredit > totalAmt){
   				$("#credit_amount2").val(totalAmt);
   			}else{
   				$("#credit_amount2").val(totalCredit);
   			}
   		}else{
   			$("#credit_amount2").val('').fadeOut(500);
   		}
   });

	$("#order_id").on("change keyup keypress", function(){
		 var order_id = $(this).val();
	  		$.ajax({
		      type:"post",
		      url:"getOrderDetails.php?order_id="+order_id,
		      datatype:"JSON",
		      success:function(data)
		      {
		      	console.log(data);
		      
		      	if(data.amount){
		      		$("#total_amount_order").val(data.amount);
		      	}else{
		      		$("#total_amount_order").val(0);
		      	}
				//$("#select_contact").html(data);
		      }
		   });
	});
</script>