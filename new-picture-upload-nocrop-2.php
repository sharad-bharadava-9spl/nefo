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
	
	
	$memberno = $_GET['memberno'];
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
		header("Location: new-picture-upload-nocrop.php?memberno=$memberno");
		exit();
	}
	
	// Check if a real file was uploaded
	if (is_uploaded_file($_FILES[$image_fieldname]['tmp_name'])) {
		
	} else {
		$_SESSION['errorMessage'] = $lang['imgError5'];
		header("Location: new-picture-upload-nocrop.php?memberno=$memberno");
		exit();
	}
	
	// Is this actually an image?
	if (getimagesize($_FILES[$image_fieldname]['tmp_name'])) {
		
	} else {
		$_SESSION['errorMessage'] = $lang['imgError6'];
		header("Location: new-picture-upload-nocrop.php?memberno=$memberno");
		exit();
	}
	
	// Save the file and store the extension in db
	$extension = pathinfo($_FILES[$image_fieldname]['name'], PATHINFO_EXTENSION);
	$upload_filename = "images/_$domain/members/" . $user_id . "." . $extension;
	

	
	if (move_uploaded_file($_FILES[$image_fieldname]['tmp_name'], $upload_filename)) {
		
	} else {
		$_SESSION['errorMessage'] = $lang['imgError7'];
		header("Location: new-picture-upload-nocrop.php?memberno=$memberno");
		exit();
	}
	
	// Query to update profile
	
	$query = "UPDATE users SET photoext = '$extension' WHERE user_id = $user_id";
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

	$_SESSION['successMessage'] = $lang['member-photo-update-success'];
	header("Location: profile.php?user_id=$user_id");
