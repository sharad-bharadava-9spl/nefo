<?php 
    
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '5';

	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings();
	
	$validationScript = <<<EOD
    $(document).ready(function() {
	    
	  $('#registerForm').validate({
		  rules: {
			  telephone: {
				  required: true
			  },
			  email: {
				  required: true
			  },
			  name: {
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

	$query = "SELECT services, minorder, clubname, clubemail, clubphone FROM systemsettings";
	try
	{
		$result = $pdo3->prepare("$query");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$services = $row['services'];
		$email = $row['email'];
		$minorder = $row['minorder'];
		$name = $row['clubname'];
		$email = $row['clubemail'];
		$telephone = $row['clubphone'];


	pageStart("CCS", NULL, $validationScript, "pindex", "notSelected", $lang['pre-order'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	

?>
<center>
   <form id="registerForm" action="pre-order-2.php" method="POST">

 <div id='mainbox'>
  <div id='mainboxheader'>
#1 - <?php echo $lang['member-contactdetails']; ?>  </div>
  
   <div class='boxcontent'>

<br />
<input type="hidden" name="step1" value="complete" />

 <table class=''>
  <tr class="nonhover">
   <td><?php echo $lang['club-name']; ?>:</td>
   <td><input type="text" placeholder="" class='defaultinput' name="name" value="<?php echo $name; ?>" /></td>
  </tr>
  <tr class="nonhover">
   <td><?php echo $lang['club-phone']; ?>:</td>
   <td><input type="text" placeholder="" class='defaultinput' name="telephone" value="<?php echo $telephone; ?>" /></td>
  </tr>
  <tr class="nonhover">
   <td><?php echo $lang['club-email']; ?>:</td>
   <td><input type="text" placeholder="" class='defaultinput' name="email" value="<?php echo $email; ?>" /></td>
  </tr>
 </table> 
<br />

  </div>
  </div>

 <input type="submit" class='cta1' name='oneClick' type="submit" value="<?php echo $lang['global-save']; ?>">

</form>

