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



	pageStart("New Credit Note", NULL, $validationScript, "pprofile", NULL, "New Credit Note", $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>
<!-- <center><a href='invoice-section.php' class='cta'>Invoice Section</a></center> -->

<?php

	// current_date

	$current_timestamp = date("Y-m-d H:i");

	$invoice_date = date("d-m-Y");
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
						<center><strong style='font-size: 18px;'><u><a href='invoice-section.php' class='cta'>Credit Note</a></u></strong>
						</center>
						<br />
					</td>
				</tr>
				<tr>
					<td><strong>Invoice Type</strong></td>
					<td>
						<div class="fakeboxholder customradio">
							<label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								<input type="radio" name="choose_invoice_form" value="SW" <?php if($form_type == 'sw'){ echo "checked"; } ?>>Software Credit Note
								<div class="fakebox"></div>
							</label>
						</div>
						<div class="fakeboxholder customradio">
							<label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								<input type="radio" name="choose_invoice_form" value="HW" <?php if($form_type == 'hw') { echo 'checked'; } ?>>Hardware Credit Note
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
	<form id="registerForm" action="credit-note-process.php" method="POST">
		<input type="hidden" name="choose_invoice_type" value="SW">
		<center>
			<div id="mainbox-no-width">
				<div class='boxcontent'>
					<table>
						<tr>
							<td colspan="6">
								<center><strong style='font-size: 18px;'><u>Software Credit Note</u></strong>
								</center>
								<br />
							</td>
						</tr>
						<tr>
							<td><strong>Customer</strong>
							</td>
							<td>
								<input type="text" name="customer_number" id="cust_num" class="defaultinput" value="<?php //echo $nextMemberNo ?>" required/><br>
								<span id="error_message" style="color: red; font-size: 12px;"></span>
							</td>
							<td>	<strong>Invoice Date</strong>
							</td>
							<td>
								<input type="text" name="invoice_date" class="defaultinput" id="date1" required="" value="<?php echo $invoice_date; ?>">
							</td>
						</tr>
						<tr>
							<td><strong>Currency</strong>
							</td>
							<td>
								<select name="currency" class="defaultinput" style="width: 201px; height: 39px;">
									<option value="EUR">EUR</option>
									<option value="GBP">GBP</option>
									<option value="N/A">N/A</option>
								</select>
							</td>
							<td><strong>SW Base Price</strong>
							</td>
							<td>
								<input type="text" name="base_amount" class="total_calc defaultinput" id="base_calc" required="" >
							</td>
						</tr>
						<tr>
							<td><strong>VAT (%)</strong>
							</td>
							<td>
								<input type="text" class="defaultinput" name="vat" id="vat">
							</td>	
							<td><strong>Total Amount</strong>
							</td>
							<td>
								<input type="text" class="defaultinput" name="total_amount" id="total_amount" readonly required="">
							</td>						
						</tr>
						<tr>
							<td><strong>Description</strong>
							</td>
							<td colspan="4" style="padding: 15px;">
								<textarea class="defaultarea" name="description">Software: <?php echo $previous_month ?></textarea>
							</td>
						</tr>
					</table>
				</div>
			</div>
			<br />
			<button class='oneClick cta1' name='save_credit_note' type="submit"><?php echo $lang['global-savechanges']; ?></button>
	</form>
</div>
<div class="overview" id="hardware_form" style="<?php echo $hw_form_style; ?>">

	<form id="registerForm2" action="credit-note-process.php" method="POST">
		<input type="hidden" name="choose_invoice_type" value="HW">
		<center>
			<div id="mainbox-no-width">
				<div class='boxcontent'>
					<table>
						<tr>
							<td colspan="4">
								<center><strong style='font-size: 18px;'><u>Hardware Credit Note</u></strong>
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
					</table><br>
				</div>
			</div>
			<br />
			<button class='oneClick cta1' name='save_credit_note' type="submit"><?php echo $lang['global-savechanges']; ?></button>
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
					      	
					      	/*if(data.error){
					      		$("#error_message").text(data.error);
					      		$(".club_statstics").hide();
					      	}else{
					      		$("#error_message").text('');
					      		$(".club_statstics").show();
					      	}*/
					      	
					      	if(data.vat){
					      		$("#vat").val(data.vat);
					      	}else{
					      		$("#vat").val('');
					      	}
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

	$(".total_calc").on("change keyup keypress",function(){
	  		//var invoice_type_val = $("input[name='choose_invoice_type']:checked").val();
			var total_sum = 0;
			//total_sum += parseFloat(member_value);
			$(".total_calc").each(function(){
				var this_val = $(this).val();
				var this_type = $(this).attr('type');
				if(this_val != ''){
					total_sum += parseFloat(this_val);

				}
			});
			var vat = $("#vat").val();
			if(vat != ''){
				vat = total_sum * vat / 100
				total_sum = total_sum + vat;
			}
			var total_amount = total_sum.toFixed(2);
			$("#total_amount").val(total_amount);
			
	});

	$("#vat").on("change keyup keypress", function(){
		var total_sum = 0;
		//total_sum += parseFloat(member_value);
			$(".total_calc").each(function(){
				var this_val = $(this).val();
				var this_id = $(this).attr('id');
				if(this_val != ''){
					total_sum += parseFloat(this_val);
				}
			});
				var vat = $("#vat").val();
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