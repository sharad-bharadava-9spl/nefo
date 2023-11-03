<?php

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';

	session_start();
	$accessLevel = '5';
	
	// Is the user already logged in?
	if (!isset($_SESSION['user_id'])) {
		
		// Did the user submit a form with username for login?
		if (isset($_POST['email'])) {
			
			$_SESSION['lang'] = $_POST['siteLanguage'];
			
			require_once 'cOnfig/languages/common.php';
		
			// Try to log the user in
			$email = mysql_real_escape_string(trim($_POST['email']));
			$password = mysql_real_escape_string(trim($_POST['password']));
			
			// Look up the provided credentials
			$query = sprintf("SELECT first_name, user_id, memberno, email, userGroup, domain FROM users WHERE email = '%s' AND userPass = '%s';",
			$email, crypt($password, $email));
			 
			$results = mysql_query($query)
				or handleError($lang['error-crederror'],"Error loading user credentials from db: " . mysql_error());

			if (mysql_num_rows($results) == 1) {
				
				$result = mysql_fetch_array($results);
					$_SESSION['user_id'] = $result['user_id'];
					$_SESSION['username'] = $result['email'];
					$_SESSION['memberno'] = $result['memberno'];
					$_SESSION['first_name'] = $result['first_name'];
					$_SESSION['userGroup'] = $result['userGroup'];
					$_SESSION['domain'] = $result['domain'];
					
					
				$_SESSION['successMessage'] = $lang['index-loggedin'];
				header("Location: index.php");
				exit();
				
			} else {
				$_SESSION['errorMessage'] = $lang['error-crederror'];
			}
		}
		
	$validationScript = <<<EOD
    $(document).ready(function() {
	    	    
	  $('#registerForm').validate({
		  rules: {
			  email: {
				  required: true
			  },
			  password: {
				  required: true
			  },
			  siteLanguage: {
				  required: true
			  }
    	}, // end rules
		  errorPlacement: function(error, element) {
			  if ( element.is(":radio") || element.is(":checkbox")){
				 error.appendTo(element.parent());
			}
		},
    	  submitHandler: function() {
   $(".oneClick").attr("disabled", true);
   form.submit();
	    	  }
	  }); // end validate
  }); // end ready
EOD;

	
	// User not logged in - possibly submitted unvalid credentials. (Re-)create the index page.
	require_once 'cOnfig/languages/common.php';
	
	pageStart("CCS", NULL, $validationScript, "pindex", "loggedOut", NULL, $_SESSION['successMessage'], $_SESSION['errorMessage']);

?>

   <div class="clearFloat"></div>
   <center><form id="registerForm" action="" method="POST">
     <input type="hidden" name='action' value='submit'>
     <input type="email" name="email" value="<?php if (isset($email)) echo $email; ?>" placeholder="E-mail" /><br />
     <input type="password" name="password" placeholder="Password / Contraseña" /><br /><br />
	 &nbsp;&nbsp;<input type='radio' class='specialInput clickbox' name='siteLanguage' value='en' /> English &nbsp;&nbsp;<input type='radio' class='specialInput clickbox' name='siteLanguage' value='es' /> Espa&ntilde;ol</span><br /><br />
      <button name='oneClick' class="visible" type="submit">Enviar</button>
      <!--<a href="#" style="margin-left: 36px"><u>Forgot password?</u></a>-->
   </form></center>
<?php displayFooter();

	// User is already logged in - and shouldn't see any login form
	} else {
		
	authorizeUser($accessLevel);
		
	require_once 'cOnfig/languages/common.php';
		
	// Get the card ID
	if (isset($_POST['cardid'])) {
		$cardid = $_POST['cardid'];
		
	// Query to look up user
	$userDetails = "SELECT user_id FROM users WHERE cardid = '{$cardid}'";
	
	// Does user ID exist?
	$userCheck = mysql_query($userDetails);
	if(mysql_num_rows($userCheck) == 0) {
   		handleError($lang['error-keyfob'],"");
	}
	
	$result = mysql_query($userDetails)
		or handleError($lang['error-userload'],"Error loading user: " . mysql_error());
	
	if ($result) {
	$row = mysql_fetch_array($result);
	$user_id = $row['user_id'];
}
		// On success: redirect.
		header("Location: mini-profile.php?user_id={$user_id}");
		exit();


	}
	
	$testinput = <<<EOD
	function testInput(){
		
		var r = confirm("{$lang['index-newmemberpopup']}");
		
		if (r == true) {
    		return true;
		} else {
    		return false;
		} 

	}
EOD;


			
	pageStart("CCS | Index", NULL, $testinput, "pindex", "loggedIn", "", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	

	// $superdate = date('Y-m-d H:i:s', strtotime("+365 day", strtotime(date('Y-m-d H:i:s'))));

?>
<script>
$(document).ready(function() {
    $("#focus").focus().bind('blur', function() {
        $(this).focus();
    }); 

    $("html").click(function() {
        $("#focus").val($("#focus").val()).focus();
    });

    //disable the tab key
    $(document).keydown(function(objEvent) {
        if (objEvent.keyCode == 9) {  //tab pressed
            objEvent.preventDefault(); // stops its action
       }
    })      
});
</script>

<center>
<span class="ctalinks">

 <a href="new-dispense.php" class="first"><span id="dispenseCTA"></span><br /><?php echo $lang['index-dispense']; ?></a>
 <a href="menu.php"><span id="productsCTA"></span><br /><?php echo $lang['index-menu']; ?></a>
 <a href="bar-new-sale.php"><span id="barCTA"></span><br />BAR</a><br />
 <a href="members.php"><span id="membersCTA"></span><br /><?php echo $lang['index-membersC']; ?></a>
 <a href="new-member.php"><span id="newmemberCTA"></span><br /><?php echo $lang['index-newmember']; ?></a>
</span>
</center>

<form onsubmit='oneClick.disabled = true; return true;' id="registerForm" action="" autocomplete="off" method="POST">
 <input type="text" name="cardid" id="focus" maxlength="10" autofocus value="" /><br />
<button name='oneClick' type="submit"><?php echo $lang['form-accept']; ?></button>
</form>


<?php
 displayFooter();
	}


?>