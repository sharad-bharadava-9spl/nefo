<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);

	$domain = $_SESSION['domain'];
	
	if (isset($_GET['user_id'])) {
		$user_id = $_GET['user_id'];
	} else {
		handleError($lang['member-not-specified'],"");
	}
	
	$image_fieldname = "fileToUpload";
	
	// Potential PHP upload errors
	$php_errors = array(1 => $lang['imgError1'],
						2 => $lang['imgError2'],
						3 => $lang['imgError3'],
						4 => $lang['imgError4']);
					
	// Check for any upload errors
	if ($_FILES[$image_fieldname]['error'] != 0) {
		$_SESSION['errorMessage'] = $php_errors[$_FILES[$image_fieldname]['error']] . " " . $lang['try-again'];
		header("Location: dni-update-1.php?user_id=$user_id");
		exit();
	}
	
	// Check if a real file was uploaded
	if (is_uploaded_file($_FILES[$image_fieldname]['tmp_name'])) {
		
	} else {
		$_SESSION['errorMessage'] = $lang['imgError5'];
		header("Location: dni-update-1.php?user_id=$user_id");
		exit();
	}
	
	// Is this actually an image?
	if (getimagesize($_FILES[$image_fieldname]['tmp_name'])) {
		
	} else {
		$_SESSION['errorMessage'] = $lang['imgError6'];
		header("Location: dni-update-1.php?user_id=$user_id");
		exit();
	}
	
	// Save the file and store the extension for later db entry
	$extension = pathinfo($_FILES[$image_fieldname]['name'], PATHINFO_EXTENSION);
	$upload_filename = "images/_$domain/ID/" . $user_id . "-front." . $extension;
	$_SESSION['dnifrontextension'] = $extension;
	
	if (move_uploaded_file($_FILES[$image_fieldname]['tmp_name'], $upload_filename)) {
		
	} else {
		$_SESSION['errorMessage'] = $lang['imgError7'];
		header("Location: dni-update-1.php?user_id=$user_id");
		exit();
	}
	
	// Write extension to user db
	$updateUser = "UPDATE users SET dniext1 = '$extension' WHERE user_id = $user_id";
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
	
	$_SESSION['successMessage'] = $lang['dni-1-success'];

	
	pageStart($lang['update-dni'], NULL, NULL, "pprofile", NULL, $lang['update-dni'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
	echo "<form action='uTil/update-dni.php?user_id=$user_id' method='post' enctype='multipart/form-data'>";

?>
 <input type="hidden" name="MAX_FILE_SIZE" value="20000000" />

 <table>
  <tr>
   <td><strong><?php echo $lang['step']; ?> 1:</strong></td>
   <td style="padding-left: 5px;"><input type="file" name="fileToUpload" id="fileToUpload" size="10"></td>
  </tr>
  <tr>
   <td style="padding-top: 10px;"><strong><?php echo $lang['step']; ?> 2:</strong></td>
   <td style="padding-top: 10px; padding-left: 5px;"><input type="submit" value="Subir" name="submit"></td>
  </tr>
  <tr>
   <td style="padding-top: 10px;"></strong></td>
   <td style="padding-top: 10px; padding-left: 5px;"><a class='cta red' href='profile.php?user_id=<?php echo $user_id; ?>' style='background-color: red;'><?php echo $lang['skip']; ?></a></td>
  </tr>
  
  
</form>


<?php displayFooter(); ?>
