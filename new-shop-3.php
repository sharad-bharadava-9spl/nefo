<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$shopid = $_SESSION['shopid'];
	
	$image_fieldname = "fileToUpload";
	
	// Potential PHP upload errors
	$php_errors = array(1 => $lang['imgError1'],
						2 => $lang['imgError2'],
						3 => $lang['imgError3'],
						4 => $lang['imgError4']);
					
	// Check for any upload errors
	if ($_FILES[$image_fieldname]['error'] != 0) {
		$_SESSION['errorMessage'] = $php_errors[$_FILES[$image_fieldname]['error']] . " " . $lang['try-again'];
		header("Location: new-shop-2.php");
		exit();
	}
	
	// Check if a real file was uploaded
	if (is_uploaded_file($_FILES[$image_fieldname]['tmp_name'])) {
		
	} else {
		$_SESSION['errorMessage'] = $lang['imgError5'];
		header("Location: new-shop-2.php");
		exit();
	}
	
	// Is this actually an image?
	if (getimagesize($_FILES[$image_fieldname]['tmp_name'])) {
		
	} else {
		$_SESSION['errorMessage'] = $lang['imgError6'];
		header("Location: new-shop-2.php");
		exit();
	}
	
	// Save the file and store the extension in db
	$extension = pathinfo($_FILES[$image_fieldname]['name'], PATHINFO_EXTENSION);
	$upload_filename = "images/_dabulance/shops/" . $shopid . "." . $extension;
	

	
	if (move_uploaded_file($_FILES[$image_fieldname]['tmp_name'], $upload_filename)) {
		
	} else {
		$_SESSION['errorMessage'] = $lang['imgError7'];
		header("Location: new-shop-2.php");
		exit();
	}
	
	// Query to update profile
	
	$query = "UPDATE shops SET logoExt = '$extension' WHERE id = $shopid";
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

	$_SESSION['successMessage'] = "Logo uploaded succesfully!";
	header("Location: my-shop.php");
