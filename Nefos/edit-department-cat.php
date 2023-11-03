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
	if (isset($_POST['id'])) {
		$id = $_POST['id'];
		$dept_name = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['department'])));
		$dept_cat = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['department_cat'])));

		// Query to update user - 28 arguments
		 $updateUser = "UPDATE department_cat SET category = '$dept_cat',department_id = '$dept_name' WHERE id = $id"; 
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
		$_SESSION['successMessage'] = "Department category updated succesfully!";
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
	pageStart("Edit Department Category", NULL, $validationScript, "pprofile", NULL, "Edit Department Category", $_SESSION['successMessage'], $_SESSION['errorMessage']);

	$id = $_GET['id'];
	// Query to look up calls
	$selectUsers = "SELECT * FROM  department_cat WHERE id = $id";
	try
	{
		$results = $pdo3->prepare("$selectUsers");
		$results->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	$row = $results->fetch();
		$department_id = $row['department_id'];
		$category = $row['category'];
		$category_id = $row['id'];
	
// get departmentcat options

		$departmentOptions = "SELECT id,category from department_cat WHERE department_id = ".$department_id;

		try
		{
			$results = $pdo3->prepare("$departmentOptions");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

		$dep_optionsRow = $results->fetchAll();
		// get departmets and categories

	 $getDepartment = "SELECT a.id AS department_id, b.id, a.name, b.category FROM departments a, department_cat b WHERE a.id = b.department_id";

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
		$departments = array_unique($department_names);
	?>

	<form id="registerForm" action="" method="POST">
		<div class="overview">
		    <input type="hidden" name="id" value="<?php echo $id; ?>" />

		    <table class="profileTable" style="text-align: left; margin: 0;">
			    <tr>
				  <td><strong>Department</strong></td>
				  <td>
				  	<select name="department" id="dept_name" required="" class="">
				  		<option value="">Select Department</option>
						 <?php  foreach ($departments as $dep_id => $dep_name) { ?>
				  			<option value="<?php echo $dep_id ?>" <?php if($department_id == $dep_id){ echo "selected"; } ?>><?php echo $dep_name; ?></option>
				  		<?php } ?>
				  	</select>
				  
				  </td>
				 </tr>
				<tr>
				  <td><strong>Department Category</strong></td>
				  <td>
				  	<input type="text" name="department_cat" id="call_issue" value="<?php echo $category; ?>" required/>
				  	<!-- <select name="department_cat" id="dept_cat" required="">
				  		<option value="">Select Category</option>
					 <?php foreach ($dep_optionsRow as $dep_row) {   ?>
			  			<option value="<?php echo $dep_row['id'] ?>" <?php if($dep_row['id'] == $category_id){ echo "selected"; } ?>><?php echo $dep_row['category'] ?></option>
			  		<?php	} ?>
				  	</select> -->
				  	
				 </td>
				</tr>
		    </table>
		    <br />

	<button class='oneClick' name='oneClick' type="submit"><?php echo $lang['global-savechanges']; ?></button>
	    </div>
		
	</form>
<!-- <script type="text/javascript">
	  // change options dynamically

  var department_arr = <?php echo json_encode($department_arr); ?>;
 
	$("#dept_name").change(function(){
		var options = "<option value=''>Select category</option>";
		var dept_id = $(this).val();
		for(var i in department_arr){
			var deptID = department_arr[i].department_id;
			if(dept_id == deptID){
				options += "<option value="+department_arr[i].cat_id+">"+department_arr[i].category+"</option>";
			}
		}
		$("#dept_cat").html(options);
	});
</script> -->
<?php  displayFooter();