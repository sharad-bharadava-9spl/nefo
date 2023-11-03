<?php

	require_once 'cOnfig/connection-master.php';
	require_once 'cOnfig/viewpwd.php';
	require_once 'cOnfig/languages/common.php';
	
	// Remember to also save the new password in the club's db: lookup domain, assemble PDO connection, update password there too.
	
	if (isset($_POST['password']) && isset($_POST['password2'])) {
		
		$source = $_POST['sv'];
		$password = $_POST['password'];
		$email = $_POST['email'];
		$user_id = $_POST['dvi'];
		$newpw = crypt($password, $email);
		
		// Edit user here. Save their new password. Their usergoup has already been updated
		$updateUser = sprintf("UPDATE users SET userPass = '%s', resetHash = '' WHERE user_id = '%d';",
mysql_real_escape_string($newpw),
mysql_real_escape_string($user_id)
);

		mysql_query($updateUser)
			or handleError($lang['error-savedata'],"Error inserting user: " . mysql_error());
			
		// On success: redirect.
		$_SESSION['successMessage'] = "Password updated succesfully!";
		
		if ($source == 'p') {
			header("Location: Parents/index.php");
		} else {
			header("Location: SyS/index.php");
		}
		
		exit();

		
	}
	
	if ((isset($_GET['ad'])) && (isset($_GET['mh'])) && (isset($_GET['pwr'])) && (isset($_GET['vd'])) && (isset($_GET['s']))) {
		
		$user_id = $_GET['ad'];
		$resetHash = $_GET['mh'];
		$source = $_GET['s'];
		
		$query = "SELECT user_id, resetHash, email FROM users WHERE user_id = $user_id AND resetHash = '$resetHash'";
		
		$results = mysql_query($query)
			or handleError($lang['error-crederror'],"Error loading user credentials from db: " . mysql_error());

		if (mysql_num_rows($results) == 0) {
			
			pageStart($lang['title-login'], NULL, $validationScript, "pindex", "loggedOut", "", $_SESSION['successMessage'], "Invalid password reset request. Please try again or <a href='mailto:cashless@micibiza.com' class='yellow'>contact us for help</a> (error 4x00p).");
			exit();
			
		} else {
			
			$row = mysql_fetch_array($results);				
				$email = $row['email'];
			
			
			$validationScript = <<<EOD
    $(document).ready(function() {
	    	    
	  $('#registerForm').validate({
		  rules: {
			  password: {
				  required: true
			  },
			  password2: {
				  required: true,
				  equalTo: '#password'
			  }
			  
    	},
    	  messages: {
        	password2: "Passwords don't match!"
    	},
    	  submitHandler: function() {
   $(".oneClick").attr("disabled", true);
   form.submit();
	    	  }
	  }); // end validate

  }); // end ready
EOD;

	
	// User not logged in - possibly submitted invalid credentials. (Re-)create the index page.
	require_once 'Parents/cOnfig/languages/common.php';
	
	pageStart($lang['title-login'], NULL, $validationScript, "pindex", "loggedOut", "", $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>

<br />
<center>
   <div id='sectionText'>
    <p>
     Please choose a new password below.
    </p>
   </div>
<br /><br />
 <form id="registerForm" action="" method="POST">
 <input type="hidden" name="email" value="<?php echo $email; ?>" />
 <input type="hidden" name="dvi" value="<?php echo $user_id; ?>" />
 <input type="hidden" name="sv" value="<?php echo $source; ?>" />
 <table>
 <tr>
  <td>Password:</td>
  <td><input type="password" name="password" id="password" /></td>
 </tr>
 <tr>
  <td>Confirm password:</td>
  <td><input type="password" name="password2" /></td>
 </tr>
 </table>
 <br />
 <button name='oneClick' class="visible" type="submit" tabindex="4" id='hidecta' >Submit</button>
<br />
<img src='Parents/images/spinner.gif' id='showspinner' style='display: none; margin-top: -10px;' width='60' />
<script>
		$('#hidecta').click(function () {
		$('#showspinner').css('display', 'inline-block');
		$('#hidecta').css('display', 'none');
		});	
</script>

 </form>
</center>

<?php			
		}

	} else {
		
		pageStart($lang['title-login'], NULL, $validationScript, "pindex", "loggedOut", "", $_SESSION['successMessage'], "Invalid password reset request. Please try again or <a href='mailto:cashless@micibiza.com' class='yellow'>contact us for help</a> (error B55x6).");
		
	}
