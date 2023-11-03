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
	  			 dateFormat: "dd-mm-yy",
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


	pageStart("Add New Write Off", NULL, $validationScript, "pprofile", NULL, "Add Write Off", $_SESSION['successMessage'], $_SESSION['errorMessage']);


	$settled_date = date("d-m-Y");


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
			<a href='invoice-section.php' class='cta1'>Invoice Section</a>
			<a href='invoice-write-offs.php' class='cta1'>Invoice Write Offs</a>
	</center>
	<center>
		<form id="registerForm" action="writeOff-process.php" method="POST">
			<div id="mainbox-no-width">
				<input type="hidden" name="id" value="<?php echo $id; ?>">
				<div id="mainboxheader"> Add Write Off </div>
				<div class='boxcontent'>
					<table>
						<tr><td colspan="4"><br><br></td></tr>
						<tr>
							<td><strong>Write Off Date</strong> </td>
							<td>
								<input type="text" name="settled_date" id="date1" class="defaultinput" value="<?php echo $settled_date; ?>"  required="" style="width: 23%;" ><br>
								<a id="yes1" href="javascript:void(0)" style="text-decoration: underline;">Yesterday</a>
							</td>

						</tr>
						<tr><td colspan="4"><br><br></td></tr>
						<tr>
							<td><strong> Comment </strong></td>
							<td><textarea name="comment" class="defaultinput"></textarea></td>
						</tr>
						<tr><td colspan="4"><br><br></td></tr>
						<tr>
							<td><strong>Invoices</strong></td>
 							<td colspan="3">
								<table class='default' id='mainTable'>
									<thead>	
										<tr style='cursor: pointer;'>
											<th>Mark as Write Off</th>
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
															<input type='checkbox' name='write_invoice[]' value='%s' id='write_%s'>
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
												<td class='right'>%s</td></tr>", $invNumber,$invNumber,$invNumber,$invDate, $longName, $customer, $invoice_balance, $noOfInvoices, $base_amount,$amount);
										 } ?>
									</tbody>
								</table>
							</td>
						</tr>
					</table>
				</div>
			</div>
			<br />
				<input type="hidden" name="end_customer_num" id="end_customer_num">
				<input type="hidden" name="end_invoice_id" id="end_invoice_id">
			<button class='oneClick cta1' name='save_writeoff' type="submit">
				<?php echo $lang['global-savechanges']; ?>
			</button>
			</div>
		</form>


	</center>	
<?php  displayFooter(); ?>

<script type="text/javascript">
	$("#yes1").click(function(){
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
		}
		
	});



</script>
<script src="js/excel-bootstrap-table-filter-bundle.js"></script>
<script type="text/javascript">
	$('#mainTable').excelTableFilter();
</script>
