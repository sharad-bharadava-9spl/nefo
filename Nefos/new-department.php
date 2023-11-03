<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
// Did this page re-submit with a form? If so, check & store details
	if (isset($_POST['dept_name'])) {
		$dept_name = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['dept_name'])));
		// check duplicate department
	  $checkDepartment = "SELECT name from departments where name = '$dept_name'"; 
		try
		{
			$chkResult = $pdo3->prepare("$checkDepartment");
			$chkResult->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	    $chkcount = $chkResult->rowCount(); 
		if($chkcount>0){
			$_SESSION['errorMessage'] = "Department already exist !";
			header("Location: new-department.php");
			exit();
		}
		// Query to update user - 28 arguments
		 $updateUser = "INSERT into departments SET name = '$dept_name'"; 
		try
		{
			$result = $pdo3->prepare("$updateUser")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		$department_id = $pdo3->lastInsertId();	
		// Update department id in department cat table
		$updateDepartment = "UPDATE department_cat SET department_id = '$department_id' WHERE category = '$dept_name'"; 
		try
		{
			$upresult = $pdo3->prepare("$updateDepartment")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		// On success: redirect.
		$_SESSION['successMessage'] = "Department added succesfully!";
		header("Location: departments.php");
		exit();
	}


	$validationScript = <<<EOD
    $(document).ready(function() {

	  $( "#datepicker" ).datepicker({
	  	   dateFormat: "yy-mm-dd"
	  	});	  
	  	$( "#deadline" ).datepicker({
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
	pageStart("New Department", NULL, $validationScript, "pprofile", NULL, "New Department", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	?>

	<form id="registerForm" action="" method="POST">
		<div class="overview">
		    <table class="profileTable" style="text-align: left; margin: 0;">
			     <tr>
					  <td><strong>Department Name</strong></td>
					  <td><input type="text" name="dept_name" required=""></td>
				 </tr>
		    </table>
		    <br />
	<button class='oneClick' name='oneClick' type="submit"><?php echo $lang['global-savechanges']; ?></button>
	    </div>
		
	</form>
<?php  displayFooter();