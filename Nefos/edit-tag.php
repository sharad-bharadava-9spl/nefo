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
	    $tag_name_es = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['tag_name_es'])));
		$tag_name_ca = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['tag_name_ca'])));
		$tag_name_fr = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['tag_name_fr'])));
		$tag_name_nl = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['tag_name_nl'])));
		$tag_name_it = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['tag_name_it'])));
		$most_popular = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['most_popular'])));
		if(empty($most_popular)){
			$most_popular = 0;
		}
		// check duplicate tag
/*			  $checkTag = "SELECT tag from video_tags where tag = '$tag_name'"; 
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
		}*/
		// Query to update user - 28 arguments
		 $updateUser = "UPDATE video_tags SET tag = '$tag_name', tag_es = '$tag_name_es', tag_ca = '$tag_name_ca', tag_fr = '$tag_name_fr', tag_nl = '$tag_name_nl', tag_it = '$tag_name_it', most_popular = '$most_popular'  WHERE id = $id"; 
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
		$name_es = $row['tag_es'];
		$name_ca = $row['tag_ca'];
		$name_fr = $row['tag_fr'];
		$name_nl = $row['tag_nl'];
		$name_it = $row['tag_it'];
		$most_popular_tag = $row['most_popular'];

	


	?>
<center><a href='help-section.php' class='cta'>Help Center</a><a href='video-tags.php' class='cta'>Tags</a></center>
	<form id="registerForm" action="" method="POST">
		<div class="overview">
		    <input type="hidden" name="id" value="<?php echo $id; ?>" />

		    <table class="profileTable">
			     <tr>
					  <td><strong>Tag Name (English)</strong></td>
					  <td><input type="text" name="tag_name" value="<?php echo $name; ?>" required=""></td>
				 </tr>			     
				 <tr>
					  <td><strong>Tag Name (Spanish)</strong></td>
					  <td><input type="text" name="tag_name_es" value="<?php echo $name_es; ?>"></td>
				 </tr>			     
				 <tr>
					  <td><strong>Tag Name (Catalan)</strong></td>
					  <td><input type="text" name="tag_name_ca" value="<?php echo $name_ca; ?>"></td>
				 </tr>			     
				 <tr>
					  <td><strong>Tag Name (French)</strong></td>
					  <td><input type="text" name="tag_name_fr" value="<?php echo $name_fr; ?>"></td>
				 </tr>			     
				 <tr>
					  <td><strong>Tag Name (Dutch)</strong></td>
					  <td><input type="text" name="tag_name_nl" value="<?php echo $name_nl; ?>"></td>
				 </tr>			     
				 <tr>
					  <td><strong>Tag Name (Italian)</strong></td>
					  <td><input type="text" name="tag_name_it" value="<?php echo $name_it; ?>"></td>
				 </tr>
				 <tr>
		    		<td><strong>Most Popular ?</strong></td>
		    		<td><input type="checkbox" class="specialInput" name="most_popular" value="1"  <?php if($most_popular_tag == 1){  echo 'checked';  } ?> > Yes</td>
		    	</tr>
		    </table>
		    <br />

	<button class='oneClick' name='oneClick' type="submit"><?php echo $lang['global-savechanges']; ?></button>
	    </div>
		
	</form>
<?php  displayFooter();