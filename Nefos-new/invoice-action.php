<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	session_start();
	$accessLevel = '3';
	
	 if(isset($_SESSION['lang'])){
	 	$current_lang = $_SESSION['lang'];
	 }else{
	 	$current_lang = 'en';
	 }

	 // get the invocie no

	if (isset($_GET['invoice_no'])) {
		$invoice_id = $_GET['invoice_no'];
	} else {
		handleError("No invoice number specified.","");
	}
	
	$memberScript = <<<EOD
	
	    $(document).ready(function() {
		    
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


		});
		
		$(window).resize(function() {
			$('#cloneTable').width($('#mainTable').width());
		});

		
EOD;
// delete videos
	pageStart("Invoices", NULL, $memberScript, "pmembership", NULL, "Invoices", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
?>

<center>
	<a href='invoice-section.php' class='cta1'>Invoice Section</a>
   
</center>

	<center>
		<form id="registerForm" action="reinvoice-process.php" method="POST">
			<div id="mainbox-no-width">
				<input type="hidden" name="invoice_no" value="<?php echo $invoice_id; ?>">
				<div id="mainboxheader">Re Invoice </div>
				<div class='boxcontent'>
					<table>
						<tr>
							<td><strong>Credit Card Fee</strong></td>
							<td>
								<input type="text" name="credit_card_fees" class="defaultinput" required="">
							</td>
						</tr>						
					</table>
				</div>
			</div>
			<br />
			<button class='oneClick cta1' name='oneClick' type="submit">
				<?php echo $lang['global-savechanges']; ?>
			</button>
			</div>
		</form>
	</center>	
        <!--  <a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});"><img src="images/excel-new.png" style='margin: 0 0 -5px 8px;'/></a> -->
<br />
<br />

<?php  displayFooter();