<?php
	
	require_once '../cOnfig/connection.php';
	require_once '../cOnfig/authenticate.php';
	require_once '../cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);

	$user_id = $_GET['user_id'];
			
	$image_fieldname = "fileToUpload";
	
	// Potential PHP upload errors
	$php_errors = array(1 => $lang['imgError1'],
						2 => $lang['imgError2'],
						3 => $lang['imgError3'],
						4 => $lang['imgError4']);
					
	// Check for any upload errors
	if ($_FILES[$image_fieldname]['error'] != 0) {
		$_SESSION['errorMessage'] = $php_errors[$_FILES[$image_fieldname]['error']] . " " . $lang['try-again'];
		header("Location: ../dni-update-2.php?user_id=$user_id");
		exit();
	}
	
	// Check if a real file was uploaded
	if (is_uploaded_file($_FILES[$image_fieldname]['tmp_name'])) {
		
	} else {
		$_SESSION['errorMessage'] = $lang['imgError5'];
		header("Location: ../dni-update-2.php?user_id=$user_id");
		exit();
	}
	
	// Is this actually an image?
	if (getimagesize($_FILES[$image_fieldname]['tmp_name'])) {
		
	} else {
		$_SESSION['errorMessage'] = $lang['imgError6'];
		header("Location: ../dni-update-2.php?user_id=$user_id");
		exit();
	}
	
	// Save the file
	$extension = pathinfo($_FILES[$image_fieldname]['name'], PATHINFO_EXTENSION);
	$upload_filename = "../images/ID/" . $user_id . "-back." . $extension;
	$_SESSION['dnibackextension'] = $extension;
	
	if (move_uploaded_file($_FILES[$image_fieldname]['tmp_name'], $upload_filename)) {
		
	} else {
		$_SESSION['errorMessage'] = $lang['imgError7'];
		header("Location: ../dni-update-2.php?user_id=$user_id");
		exit();
	}
	
	// Write extension to user db
	try
	{
		$result = $pdo3->prepare("UPDATE users SET dniext2 = '{$_SESSION['dnibackextension']}' WHERE user_id = $user_id")->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	
	$_SESSION['successMessage'] = $lang['dni-images-saved'];
	header("Location: ../profile.php?user_id=$user_id");