<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	$pageid = $_GET['pageid'];
		if(empty($pageid) || $pageid == ''){
			header("Location:page-manager.php");
			exit();
		}
	// Authenticate & authorize
	authorizeUser($accessLevel);

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
	
if(isset($_POST['page_id'])){

		 $page_title = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['page_title']))); 
		 $page_link = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['page_link']))); 
		 $page_category = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['page_category'])));
		 $admin_menu = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['admin_menu'])));

		 if($admin_menu == ''){
		 	$admin_menu = 0;
		 }

		 $page_id = $_POST['page_id'];

		$checkPage = "SELECT * from admin_page_details WHERE  (page_title = '".$page_title."' OR page_link = '".$page_link."') AND id !=".$page_id;

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
            header("Location: edit-page.php?pageid=".$page_id);
            die;
		}

		 $updatePage = "UPDATE admin_page_details SET page_title = '$page_title', page_link = '$page_link',category = '$page_category', admin_menu = '$admin_menu' WHERE id=$page_id";  

		try
		{
			$result = $pdo3->prepare("$updatePage")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}	

		$_SESSION['successMessage'] = "Page Details updated successfully!";
		header("Location: page-manager.php");
		exit();


}


	pageStart("Edit Page Details", NULL, $validationScript, "pprofile", NULL, "Edit Page Details", $_SESSION['successMessage'], $_SESSION['errorMessage']);


		// Query to look up videos
		$selectPages = "SELECT * FROM  admin_page_details WHERE id = $pageid";
		try
		{
			$results = $pdo3->prepare("$selectPages");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		$row = $results->fetch();
			$page_title = $row['page_title'];
			$page_link = $row['page_link'];
			$category = $row['category'];
			$admin_menu = $row['admin_menu'];
					
?>
<center>
	<a href='page-manager.php' class='cta'>Page Management</a>
</center>
<form id="registerForm" action="" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="page_id" value="<?php echo $pageid ?>">
 <div class="overview">
	<table class='profileTable'>
		 <tr>
		  <td><strong>Page Title</strong></td>
		  <td><input type="text" name="page_title" value="<?php echo $page_title ?>" required /></td>
		 </tr>		 
		 <tr>
		  <td><strong>Page Link</strong></td>
		  <td><input type="text" name="page_link" value="<?php echo $page_link; ?>" required /></td>
		 </tr>			 
		 <tr>
		  <td><strong>Select Category</strong></td>
		  <td>
		  		<select name="page_category" required="">
		  			<option value="">Select Category</option>
		  			<option value="Dispensary"  <?php if($category == 'Dispensary'){  echo 'selected';  } ?>>Dispensary</option>
		  			<option value="Bar" <?php if($category == 'Bar'){  echo 'selected';  } ?>>Bar</option>
		  			<option value="Members" <?php if($category == 'Members'){  echo 'selected';  } ?>>Members</option>
		  			<option value="Products" <?php if($category == 'Products'){  echo 'selected';  } ?>>Products</option>
		  			<option value="Administration" <?php if($category == 'Administration'){  echo 'selected';  } ?>>Administration</option>
		  			<option value="Reports" <?php if($category == 'Reports'){  echo 'selected';  } ?>>Reports</option>
		  		</select>
		  </td>
		 </tr>
		 <tr>
		  <td><strong>Show link in admin menu ?</strong></td>
		  <td>
		  	<input type="checkbox" name="admin_menu" value="1" class="specialInput" <?php if($admin_menu == 1){ echo "checked";  } ?> > Yes
		  </td>
		 </tr>	
	</table>	
	<button class='oneClick' name='save_page' type="submit"><?php echo $lang['global-savechanges']; ?></button>
</div>

<?php displayFooter(); ?>