<?php
	// Created by konstant for Task-14971249 on 10-11-2021
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);

	include "wabr.php";
	$serverUrl = "";
	$authorization = "yuhghhlls5678kmkii89d5566njj67h";
	$reader = new WABarcodeReader($serverUrl, $authorization);
	//$image = "https://wabr.inliteresearch.com/SampleImages/drvlic.ca.jpg";
	$types = ""; 
	$directions=""; 
	$tbr_code = 0;
	
	if (isset($_POST['mydata'])) {
		
			$encoded_data = $_POST['mydata'];
			//$binary_data = base64_decode( $encoded_data );
			
			try
		    {
			    $barcodes = $reader->Read($encoded_data, $types, $directions, $tbr_code);
			   // Process barcode reading results
		    }
		  	catch (Exception $ex)
		    {
		    	//echo "EXCEPTION: " . $ex->getMessage();
		      	$_SESSION['errorMessage'] = "EXCEPTION: " . $ex->getMessage();
	      		header("Location: new-member-barcode.php");
				exit();
		    }
		    if(empty($barcodes)){
		    	$_SESSION['errorMessage'] = "Please enter valid ID!";
		    	header("Location: new-member-barcode.php");
				exit();
		    }
	    	foreach ($barcodes as $barcode)
		  	{
		     echo "Barcode Type:" . $barcode->Type . "  File:" . $barcode->File . "<br>"; 
		      echo $barcode->Text . "<br>";
		      foreach ($barcode->Values as $key => $value){ 
		          echo $key  . " : " . $value . "<br>";
		      }
		  	}
			

		// If not, it means a photo was uploaded. Let's verify it.
		} else if (isset($_POST['photoupload'])) {
		
			$image_fieldname = "fileToUpload";

			
			// Potential PHP upload errors
			$php_errors = array(1 => $lang['imgError1'],
								2 => $lang['imgError2'],
								3 => $lang['imgError3'],
								4 => $lang['imgError4']);
							
			// Check for any upload errors
			if ($_FILES[$image_fieldname]['error'] != 0) {
				$_SESSION['errorMessage'] = $php_errors[$_FILES[$image_fieldname]['error']] . " " . $lang['try-again'];
				header("Location: new-member-barcode.php");
				exit();
			}
			
			// Check if a real file was uploaded
			if (is_uploaded_file($_FILES[$image_fieldname]['tmp_name'])) {
				
			} else {
				$_SESSION['errorMessage'] = $lang['imgError4'];
				header("Location: new-member-barcode.php");
				exit();
			}
			
			// Is this actually an image?
			if (getimagesize($_FILES[$image_fieldname]['tmp_name'])) {
				
			} else {
				$_SESSION['errorMessage'] = $lang['imgError5'];
				header("Location: new-member-barcode.php");
				exit();
			}
			
			$extension = pathinfo($_FILES[$image_fieldname]['name'], PATHINFO_EXTENSION);
			$file_path = $_FILES[$image_fieldname]['tmp_name'];

			try
		    {
			    $barcodes = $reader->Read($file_path, $types, $directions, $tbr_code);
			   // Process barcode reading results
		    }
		  	catch (Exception $ex)
		    {
		      	$_SESSION['errorMessage'] = "EXCEPTION: " . $ex->getMessage();
	      		header("Location: new-member-barcode.php");
				exit();
		    }
		    if(empty($barcodes)){
		    	$_SESSION['errorMessage'] = "Please enter valid ID!";
		    	header("Location: new-member-barcode.php");
				exit();
		    }
		    foreach ($barcodes as $barcode)
			  	{
			/*     echo "Barcode Type:" . $barcode->Type . "  File:" . $barcode->File . "<br>"; 
			      echo $barcode->Text . "<br>";*/
			      foreach ($barcode->Values as $key => $value){ 
			          echo $key  . " : " . $value . "<br>";
			      }
			  	}
				
			
	}

	//$_SESSION['successMessage'] = $lang['dni-1-success'];

 displayFooter();
?>
