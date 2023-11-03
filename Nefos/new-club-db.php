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

	$Allowed_extention = array("sql");
	$allowed = array( "application/sql", "application/graphql");

	$sql_upload_dir = "cOnfig";     // The directory for the preview image to be saved in
	$sql_upload_path = $sql_upload_dir."/";      
	$sql_name = "new-club";
	$sql_location = $sql_upload_path.$sql_name.".sql"; 

	if (!empty($_FILES['sql_file']['name'])) { 
	          //Get the file information

		            $sql_name = $_FILES['sql_file']['name'];
		            $sql_tmp = $_FILES['sql_file']['tmp_name'];
		            $sql_size = $_FILES['sql_file']['size'];
		            $sql_type = $_FILES['sql_file']['type'];
		            $filename = basename($_FILES['sql_file']['name']);
		            $file_ext = strtolower(substr($filename, strrpos($filename, '.') + 1));
		            $_SESSION['extension'] = $file_ext;

		            if(!in_array($file_ext, $Allowed_extention)){
		              	 $_SESSION['errorMessage']  = "Please upload valid file types only !";
		                 header("Location: new-club-db.php");
		                 die;
		             }
		          /* echo $mimetype = mime_content_type($sql_tmp);  die;

		           	if(!in_array($mimetype, $allowed)){
		              	 $_SESSION['errorMessage']  = "Please upload valid files !";
		                 header("Location: new-club-db.php");
		                 die;
		              }*/
		              
		              if (isset($_FILES['sql_file']['name'])){
		                //this file could now has an unknown file extension (we hope it's one of the ones set above!)

			                 if(file_exists($sql_location)){ 
			                 	unlink($sql_location); 
			                 }
		              
			                move_uploaded_file($sql_tmp, $sql_location); 
			                chmod($sql_location, 0777);
		              }

		              $_SESSION['successMessage']  = "New club db updated !";
		              header("location:pending-clubs.php");
		              die;
		            

	        }
	$validationScript = <<<EOD
    $(document).ready(function() {

	  $('#registerForm').validate({
		  rules: {
			  sql_file: {
				  required: true,
				  extension: "sql"
			  }
    	}, 
		 
    	  submitHandler: function() {
   $(".oneClick").attr("disabled", true);
   form.submit();
	    	  }
	  }); // end validate


  }); // end ready
EOD;

	pageStart("Add Club Database", NULL, $validationScript, "pprofile", NULL, "Add Club Database", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	?>
<center><a href='pending-clubs.php' class='cta'>Clubs</a></center>
	<form id="registerForm" action="" method="POST" enctype="multipart/form-data">
		<div class="overview">
		    <table class="profileTable">
			     <tr>
					  <td><strong>Upload Club Launch DB (upload your new updated sql file):</strong></td>
					  <td><input type="file" name="sql_file" required=""></td>
				 </tr>			     
		    </table>
		    <br />
	<button class='oneClick' name='oneClick' type="submit"><?php echo $lang['global-savechanges']; ?></button>
	    </div>
		
	</form>
<?php  displayFooter();