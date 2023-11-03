<?php
	
	require_once 'cOnfig/connection-master.php';
	require_once 'cOnfig/view-loggedout.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	session_start();
	
    $key = $_REQUEST['key'];

    // check link for verification

    if(!isset($key) || $key == ''){
    	echo "Invalid link, please check!";
    	die();
    }
	if(isset($_REQUEST['password'])){
	    $very_param = base64_decode($key);
	    $param_arr = explode(",", $very_param);
	    $email = $param_arr[0];
	    $token = $param_arr[1];

	    $selectUser = "SELECT * from members WHERE email = '".$email."' AND token = '".$token."'";  
		$result = $pdo->prepare("$selectUser");
		$result->execute();
		$userCount = $result->rowCount(); 
		$new_password = md5($_REQUEST['password'].$email);
		$very_param = base64_encode($email.",".$token);
		if($userCount > 0){
			$updateUser = "UPDATE members SET password = '$new_password' WHERE email = '".$email."' AND token = '".$token."'";
			$pdo->prepare("$updateUser")->execute();
			
			$_SESSION['successMessage'] = "Password changed successfully, please login into the APP!";
			header("Location: reset-member-password.php?key=".$very_param);
			die();
		}else{
			$_SESSION['errorMessage'] = "Invalid link, please check!";
			header("Location: reset-member-password.php?key=".$very_param);
	    	die(); 
		}
	}

	$validationScript = <<<EOD
    $(document).ready(function() {
	    	    
	  $('#registerForm').validate({
		  rules: {
	  	    password : {
                minlength : 5
            },
            password_confirm : {
                minlength : 5,
                equalTo : "#password"
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

	pageStart("Reset App Member Password", NULL, $validationScript, "pnewcategory", "", "Reset App Member Password", $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>


<form id="registerForm" action="" method="POST">
<center>
<div id="mainbox-no-width">
 <div class='boxcontent'>
  
 <input type="password" class="defaultinput" name="password" id="password" placeholder="password" required />


 <input type="password" name="password_confirm"  class='defaultinput'  placeholder="Confirm password" required/>

 <button class='cta4' name='oneClick' type="submit"><?php echo $lang['submit']; ?></button>
</form>
</div>
</div>