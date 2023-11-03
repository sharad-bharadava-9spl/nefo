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
	if (isset($_POST['department'])) {
		$dept_name = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['department'])));
		$department_cat = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['department_cat'])));
		// check duplicate department
	   $checkDepartment = "SELECT category from department_cat where category = '$department_cat'";  
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
			$_SESSION['errorMessage'] = "Department Category already exist !";
			header("Location: new-department-cat.php");
			exit();
		}
		// Query to update user - 28 arguments
		 $updateUser = "INSERT into department_cat SET category = '$department_cat', department_id= '$dept_name'"; 
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

		// On success: redirect.
		$_SESSION['successMessage'] = "Department cateory added succesfully!";
		header("Location: department-category.php");
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
	pageStart("New Department Category", NULL, $validationScript, "pprofile", NULL, "New Department Category", $_SESSION['successMessage'], $_SESSION['errorMessage']);
		// get departmets and categories

	/* $getDepartment = "SELECT a.id AS department_id, b.id, a.name, b.category FROM departments a, department_cat b WHERE a.id = b.department_id";

		try
		{
			$results = $pdo3->prepare("$getDepartment");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
        $department_arr = [];
        $i = 0;
		while($depRow = $results->fetch()){
			$department_arr[$i]['cat_id'] = $depRow['id'];
			$department_arr[$i]['name'] =  $depRow['name'];
			$department_arr[$i]['department_id'] =  $depRow['department_id'];
			$department_arr[$i]['category'] = $depRow['category'];
			$department_names[$depRow['department_id']] = $depRow['name'];
			$i++;
		}
		$departments = array_unique($department_names);*/
		$getDepartment = "SELECT name from departments";
		try
		{
			$results = $pdo3->prepare("$getDepartment");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		while($department = $results->fetch()){
			$departments[] = $department['name'];
		}
		
	?>


	<form id="registerForm" action="" method="POST">
		<div class="overview">
		    

		    <table class="profileTable" style="text-align: left; margin: 0;">
			    <tr>
				  <td><strong>Department</strong></td>
				  <td>
				  	<select name="department" id="dept_name" required="" class="">
				  		<option value="">Select Department</option>
						 <?php  foreach ($departments as $dep_id => $dep_name) { ?>
				  			<option value="<?php echo $dep_id ?>" ><?php echo $dep_name; ?></option>
				  		<?php } ?>
				  	</select>
				  
				  </td>
				 </tr>
				<tr>
				  <td><strong>Department Category</strong></td>
				  <td>
				  	<input type="text" name="department_cat" id="call_issue"  required/>
				  	
				 </td>
				</tr>
		    </table>
		    <br />

	<button class='oneClick' name='oneClick' type="submit"><?php echo $lang['global-savechanges']; ?></button>
	    </div>
		
	</form>
<?php  displayFooter();