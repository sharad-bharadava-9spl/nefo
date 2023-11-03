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
	
	$memberno = $_SESSION['tempNo'];
	
	
	// Check if user skipped DNI scan
	if (!isset($_GET['skipDNI'])) {
	
		// Check if a webcam DNI photo was submitted
		if (isset($_POST['mydata'])) {
			$encoded_data = $_POST['mydata'];
			$binary_data = base64_decode( $encoded_data );
			
			$imgname = 'images/ID/' . $memberno . '-back.jpg';
			// save to server (beware of permissions)
			$result = file_put_contents( $imgname, $binary_data );
			
			if (!$result) die($lang['error-imagesave']);
			
			$_SESSION['dnibackextension'] = 'jpg';
			
		// If not, it means a photo was uploaded. Let's verify it.
		} else {
			
			$image_fieldname = "fileToUpload";
			
			// Potential PHP upload errors
			$php_errors = array(1 => $lang['imgError1'],
								2 => $lang['imgError1'],
								3 => $lang['imgError2'],
								4 => $lang['imgError3']);
							
			// Check for any upload errors
			if ($_FILES[$image_fieldname]['error'] != 0) {
				$_SESSION['errorMessage'] = $php_errors[$_FILES[$image_fieldname]['error']] . " " . $lang['try-again'];
				header("Location: dni-upload-2.php");
				exit();
			}
			
			// Check if a real file was uploaded
			if (is_uploaded_file($_FILES[$image_fieldname]['tmp_name'])) {
				
			} else {
				$_SESSION['errorMessage'] = $lang['imgError4'];
				header("Location: dni-upload-2.php");
				exit();
			}
			
			// Is this actually an image?
			if (getimagesize($_FILES[$image_fieldname]['tmp_name'])) {
				
			} else {
				$_SESSION['errorMessage'] = $lang['imgError5'];
				header("Location: dni-upload-2.php");
				exit();
			}
			
			// Save the file
			$extension = pathinfo($_FILES[$image_fieldname]['name'], PATHINFO_EXTENSION);
			$upload_filename = "images/ID/" . $memberno . "-back." . $extension;
			$_SESSION['dnibackextension'] = $extension;
			
			if (move_uploaded_file($_FILES[$image_fieldname]['tmp_name'], $upload_filename)) {
				
			} else {
				$_SESSION['errorMessage'] = $lang['imgError6'];
				header("Location: dni-upload-2.php");
				exit();
			}
				
		}
		
		$_SESSION['successMessage'] = $lang['dni-2-success'];
		
	}
	

	
	pageStart($lang['title-memberpicture'], NULL, NULL, "pprofile", NULL, $lang['member-newmembercaps'] . " - " . $lang['title-memberpicture'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	if ($_SESSION['cropOrNot'] == '1') {

	echo "<center>
           <a class='cta' href='new-picture-upload.php?newmember=true'>{$lang['upload-photo']}</a>
           <a class='cta' href='new-picture-photo.php?newmember=true'>{$lang['use-webcam']}</a>
           <a class='cta red' href='new-member-3.php?skipPhoto' style='background-color: red;'>{$lang['skip']}</a>
          </center>";
          
	} else {

	echo "<center>
           <a class='cta' href='new-picture-upload-nocrop.php?newmember=true'>{$lang['upload-photo']}</a>
           <a class='cta' href='new-picture-photo.php?newmember=true'>{$lang['use-webcam']}</a>
           <a class='cta red' href='new-member-3.php?skipPhoto' style='background-color: red;'>{$lang['skip']}</a>
          </center>";
          
	}
	

displayFooter();