<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '1';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	
	$validationScript = <<<EOD
    $(document).ready(function() {
	    	    
	  $( function() {
	    $( "#datepicker" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });
	  });
	  
	  $('#registerForm').validate({
	  	 ignore:'',
		  rules: {
			  userPass: {
				  required: true
			  },
			  untilDate: {
				  required: true
			  },		  
			  selectType: {
				  required: true
			  }
    	}, // end rules
		  errorPlacement: function(error, element) {
			  if ( element.is(":radio") || element.is(":checkbox")){
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


	pageStart($lang['give-baja'], NULL, $validationScript, "pexpenses", NULL, $lang['give-baja'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
		

?>

  <script>
  </script>	
<center>
	<div id="mainbox-no-width">
		<div id="mainboxheader"><?php echo $lang['give-baja-since']; ?>:</div>
		<div class="boxcontent">
			<form id="registerForm" action="give-baja-2.php" method="POST">

			<center>
			
			 <input type="text" id="datepicker" name="untilDate" autocomplete="nope" class="sixDigit defaultinput" placeholder="<?php echo $lang['ddmmyy']; ?>" /><br /><br />
			 <div class="fakeboxholder">	
			 <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			  Make inactive
			  <input type="radio" name="selectType" value="1" required="">
			  <div class="fakebox"></div>
			 </label>
			</div>	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;		 
			<div class="fakeboxholder">	
			 <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			  Delete members
			  <input type="radio" name="selectType" value="2">
			  <div class="fakebox"></div>
			 </label>
			</div>
			<br><br>
			 <button class='oneClick cta4' name='oneClick' type="submit"><?php echo $lang['submit']; ?></button>

			</center>
			</form>
		</div>
	</div>
</center>	