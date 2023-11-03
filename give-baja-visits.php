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
		  rules: {
			  userPass: {
				  required: true
			  },
			  untilDate: {
				  required: true
			  },		  
			  "toDelete[]": {
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


	pageStart($lang['give-baja'], NULL, $validationScript, "pexpenses", 'dev-align-center', $lang['give-baja'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
		

?>

  <script>
  </script>
<div class="actionbox-np2" >
<div class="boxcontent">
<form id="registerForm" action="give-baja-visits-2.php" method="POST">

<br />
<center>
<strong>Dar de baja los socios que no se han visitado desde:</strong><br /><br />
 <input type="text" id="datepicker" name="untilDate" autocomplete="nope" class="sixDigit defaultinput" placeholder="<?php echo $lang['ddmmyy']; ?>" /><br /><br />
 <button class='oneClick cta4' name='oneClick' type="submit"><?php echo $lang['submit']; ?></button>

</center>
</form>
</div>
</div>