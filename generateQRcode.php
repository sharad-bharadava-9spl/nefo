<?php
	// created by konstant for Task-14980311 on 03-12-2021
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';


	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	


	$validationScript = <<<EOD
    $(document).ready(function() {
	    	    
	  $('#registerForm').validate({
		  rules: {
			  qr_link: {
				  required: true
			  }
    	}, // end rules
    	errorPlacement: function(error, element) { },
    	  submitHandler: function() {
   $(".oneClick").attr("disabled", true);
   form.submit();
	    	  }
	  }); // end validate
  }); // end ready
EOD;

	pageStart("Generate QR Code", NULL, $validationScript, "newpurchase", "admin", "Generate QR Code", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
?>
<center>
<div id="mainbox-no-width">
	<div class='boxcontent'>

		<form id="registerForm" action="qrSubmit.php" method="POST">
		   <input type="text" name="qr_link" placeholder="Insert Link" class='defaultinput' /><br />
		   <strong>CCS Logo</strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<div class="fakeboxholder customradio">
				<label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<input type="radio" name="use_logo" value="Yes" class="defaultinput" checked> Yes
					<div class="fakebox"></div>
				</label>
			</div>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;			
			<div class="fakeboxholder customradio">
				<label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<input type="radio" name="use_logo" value="No" class="defaultinput"> No
					<div class="fakebox"></div>
				</label>
			</div>
								
		 	<br />
		 	<br />
		 	<button class='cta1' name='oneClick' type="submit">Submit</button>
		</form>
	</div>
</div>
<?php displayFooter(); ?>

