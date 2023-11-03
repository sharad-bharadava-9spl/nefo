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
		$tag_name = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['tag_name'])));
		// check duplicate tag
	  $checkTag = "SELECT tag from video_tags where tag = '$tag_name'";  
		try
		{
			$chkResult = $pdo3->prepare("$checkTag");
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
			$_SESSION['errorMessage'] = "Tag already exist !";
			header("Location: edit-tag.php?id=".$id);
			exit();
		}
		// Query to update user - 28 arguments
		 $updateUser = "UPDATE video_tags SET tag = '$tag_name'  WHERE id = $id"; 
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
		$_SESSION['successMessage'] = "Tag updated succesfully!";
		header("Location: video-tags.php");
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
	pageStart("Edit Tag", NULL, $validationScript, "pprofile", NULL, "Edit Tag", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	$id = $_GET['id'];
	// Query to look up calls
	$selectUsers = "SELECT * FROM  video_tags WHERE id = $id";
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
		
		$name = $row['tag'];

	


	?>
<center><a href='help-section.php' class='cta'>Help Center</a><a href='video-tags.php' class='cta'>Tags</a></center>
	<form id="registerForm" action="" method="POST">
		<div class="overview">
		    <input type="hidden" name="id" value="<?php echo $id; ?>" />

		    <table class="profileTable">
			     <tr>
					  <td><strong>Tag Name</strong></td>
					  <td><input type="text" name="tag_name" value="<?php echo $name; ?>" required=""></td>
				 </tr>
		    </table>
		    <br />

	<button class='oneClick' name='oneClick' type="submit"><?php echo $lang['global-savechanges']; ?></button>
	    </div>
		
	</form>
<?php  displayFooter();