<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';

	// Authenticate & authorize
	authorizeUser($accessLevel);

	if(isset($_POST['save_page'])){


		 $page_title = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['page_title']))); 
		 $page_link = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['page_link']))); 
		 $page_category = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['page_category']))); 
		 $admin_menu = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['admin_menu']))); 

		 if($admin_menu == ''){
		 	$admin_menu == 0;
		 }


		$checkPage = "SELECT * from admin_page_details WHERE page_title = '".$page_title."' OR page_link = '".$page_link."'";

		try
		{
			$result = $pdo3->prepare("$checkPage");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		$page_count = $result->rowCount();

		if($page_count > 0){
			$_SESSION['errorMessage'].= "Page already exist !";
            header("Location: new-page.php");
            die;
		}

		 $insertPage = sprintf("INSERT INTO admin_page_details (page_title, page_link, category, admin_menu) VALUES ('%s', '%s', '%s', '%d')",
		
					$page_title,
					$page_link,
					$page_category,
					$admin_menu,
					);   
		try
		{
			$result = $pdo3->prepare("$insertPage")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}	

		$_SESSION['successMessage'] = "Page added successfully!";
		header("Location: page-manager.php");
		exit();


	}

	$validationScript = <<<EOD
    $(document).ready(function() {
    	    
	  $('#registerForm').validate({
		  rules: {
			 
    	}, // end rules
		  errorPlacement: function(error, element) {
			 if ( element.is(":radio") || element.is(":checkbox")){
				 error.appendTo(element.parent());
			} else {
				return true;
			}
		}
		 
    	 
	  }); // end validate


  }); // end ready
EOD;
	

	pageStart("Add New Page", NULL, $validationScript, "pprofile", NULL, "Add New Page", $_SESSION['successMessage'], $_SESSION['errorMessage']);


?>
<center>
	<a href='page-manager.php' class='cta'>Page Management</a>
</center>
<form id="registerForm" action="" method="POST" enctype="multipart/form-data">
    
 <div class="overview">
	<table class='profileTable'>
		 <tr>
		  <td><strong>Page Title</strong></td>
		  <td><input type="text" name="page_title" required /></td>
		 </tr>		 
		 <tr>
		  <td><strong>Page Link</strong></td>
		  <td><input type="text" name="page_link" required /></td>
		 </tr>			 
		 <tr>
		  <td><strong>Select Category</strong></td>
		  <td>
		  		<select name="page_category" required="">
		  			<option value="">Select Category</option>
		  			<option value="Dispensary">Dispensary</option>
		  			<option value="Bar">Bar</option>
		  			<option value="Members">Members</option>
		  			<option value="Products">Products</option>
		  			<option value="Administration">Administration</option>
		  			<option value="Reports">Reports</option>
		  		</select>
		  </td>
		 </tr>			 
		 <tr>
		  <td><strong>Show link in admin menu ?</strong></td>
		  <td>
		  	<input type="checkbox" name="admin_menu" value="1" class="specialInput"> Yes
		  </td>
		 </tr>			 
		</table>
		
	<button class='oneClick' name='save_page' type="submit"><?php echo $lang['global-savechanges']; ?></button>
</div>

<?php displayFooter(); ?>