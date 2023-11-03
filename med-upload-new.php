<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$user_id = $_GET['user_id'];
	$domain = $_SESSION['domain'];
			
	if ($_GET['submit'] == 'true') {
		
			$image_fieldname = "fileToUpload";
			
			// Potential PHP upload errors
			$php_errors = array(1 => $lang['imgError1'],
								2 => $lang['imgError1'],
								3 => $lang['imgError2'],
								4 => $lang['imgError3']);
							
			// Check for any upload errors
			if ($_FILES[$image_fieldname]['error'] != 0) {
				$_SESSION['errorMessage'] = $php_errors[$_FILES[$image_fieldname]['error']] . " " . $lang['try-again'];
				header("Location: ?");
				exit();
			}
			
			// Check if a real file was uploaded
			if (is_uploaded_file($_FILES[$image_fieldname]['tmp_name'])) {
				
			} else {
				$_SESSION['errorMessage'] = $lang['imgError4'];
				header("Location: ?");
				exit();
			}
			
			// Is this actually an image?
			if (getimagesize($_FILES[$image_fieldname]['tmp_name'])) {
				
			} else {
				$_SESSION['errorMessage'] = $lang['imgError5'];
				header("Location: ?");
				exit();
			}
			
			// Save the file
			$extension = pathinfo($_FILES[$image_fieldname]['name'], PATHINFO_EXTENSION);
			$upload_filename = "images/_$domain/med/" . $user_id . "." . $extension;
			$_SESSION['medextension'] = $extension;
			
			if (move_uploaded_file($_FILES[$image_fieldname]['tmp_name'], $upload_filename)) {
				
			} else {
				$_SESSION['errorMessage'] = $lang['imgError6'];
				header("Location: ?");
				exit();
			}
				
			$query = "UPDATE users SET medext = '$extension'";
			try
			{
				$result = $pdo3->prepare("$query")->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
	
			$_SESSION['successMessage'] = "Certificado subido con &eacute;xito!";
			header("Location: profile.php?user_id=$user_id");
	}
	

	pageStart("Subir certificado", NULL, NULL, "pprofile", 'dev-align-center', $lang['member-newmembercaps'] . " - Subir certificado", $_SESSION['successMessage'], $_SESSION['errorMessage']);

?>
<div class="actionbox-np2">
	<div class='boxcontent'>
<form action="?user_id=<?php echo $user_id; ?>&submit=true" method="post" enctype="multipart/form-data">
 <input type="hidden" name="MAX_FILE_SIZE" value="20000000" />
 <table>
  <tr>
<!--    <td><strong><?php echo $lang['step']; ?> 1:</strong></td> -->
   <td style="padding-left: 5px;">
   	<!-- <input type="file" name="fileToUpload" id="fileToUpload"> -->
   	   		<div class="upload-btn-wrapper">
			  <button class="btn" >Choose file</button>
			  <input type="file" name="fileToUpload" id="fileToUpload">
			</div>
   </td>
  </tr>
  <tr>
   <!-- <td style="padding-top: 10px;"><strong><?php echo $lang['step']; ?> 2:</strong></td> -->
   <td style="padding-top: 10px; padding-left: 5px;"><input type="submit" value="<?php echo $lang['submit']; ?>" name="submit" class='cta1'></td>
  </tr>
</table>
</form>
</div>
</div>


<?php displayFooter(); ?>
