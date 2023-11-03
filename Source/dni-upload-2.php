<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings();
	
	$domain = $_SESSION['domain'];
	
	$memberno = $_SESSION['tempNo'];

	
	$image_fieldname = "fileToUpload";

	
	// Potential PHP upload errors
	$php_errors = array(1 => $lang['imgError1'],
						2 => $lang['imgError2'],
						3 => $lang['imgError3'],
						4 => $lang['imgError4']);
					
	// Check for any upload errors
	if ($_FILES[$image_fieldname]['error'] != 0) {
		$_SESSION['errorMessage'] = $php_errors[$_FILES[$image_fieldname]['error']] . " " . $lang['try-again'];
		header("Location: dni-upload-1.php");
		exit();
	}
	
	// Check if a real file was uploaded
	if (is_uploaded_file($_FILES[$image_fieldname]['tmp_name'])) {
		
	} else {
		$_SESSION['errorMessage'] = $lang['imgError4'];
		header("Location: dni-upload-1.php");
		exit();
	}
	
	// Is this actually an image?
	if (getimagesize($_FILES[$image_fieldname]['tmp_name'])) {
		
	} else {
		$_SESSION['errorMessage'] = $lang['imgError5'];
		header("Location: dni-upload-1.php");
		exit();
	}
	
	// Save the file and store the extension for later db entry
	$extension = pathinfo($_FILES[$image_fieldname]['name'], PATHINFO_EXTENSION);
	$upload_filename = "images/_$domain/ID/" . $memberno . "-front." . $extension;
	$_SESSION['dnifrontextension'] = $extension;
	
	if (move_uploaded_file($_FILES[$image_fieldname]['tmp_name'], $upload_filename)) {
		
	} else {
		$_SESSION['errorMessage'] = $lang['imgError6'];
		header("Location: dni-upload-1.php");
		exit();
	}
	
	$_SESSION['successMessage'] = $lang['dni-1-success'];

	
	pageStart("DNI / " . $lang['member-passport'], NULL, NULL, "pprofile", NULL, $lang['member-newmembercaps'] . " - " . $lang['dni-back'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	

?>

<form action="new-member-2.php" method="post" enctype="multipart/form-data">
 <table>
  <tr>
   <td><strong><?php echo $lang['step']; ?> 1:</strong></td>
   <td style="padding-left: 5px;"><input type="file" name="fileToUpload" id="fileToUpload" size="10"></td>
  </tr>
  <tr>
   <td style="padding-top: 10px;"><strong><?php echo $lang['step']; ?> 2:</strong></td>
   <td style="padding-top: 10px; padding-left: 5px;"><input type="submit" value="<?php echo $lang['submit']; ?>" name="submit"></td>
  </tr>
  <tr>
   <td style="padding-top: 10px;"></td>
   <td style="padding-top: 10px; padding-left: 5px;"><br /><a class="cta" href="new-member-2.php?skipDNI" style='background-color: red; margin: 0;'><?php echo $lang['skip']; ?></a></td>
  </tr>
  
  
</form>


<?php displayFooter(); ?>
