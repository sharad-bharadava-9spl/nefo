<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view-photo.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$domain = $_SESSION['domain'];
	
	
		$user_id = $_GET['user_id'];
		$mconsumption = $_GET['mconsumption'];
		$usageType = $_GET['usageType'];
		$_SESSION['max_crop_height'] = 1200;
		$_SESSION['max_crop_width'] = 1200;
		// Check if a webcam DNI photo was submitted
		if (isset($_GET['upload'])) {
			
			$image_fieldname = "fileToUpload";
			
			// Potential PHP upload errors
			$php_errors = array(1 => $lang['imgError1'],
								2 => $lang['imgError1'],
								3 => $lang['imgError2'],
								4 => $lang['imgError3']);
							
			// Check for any upload errors
			if ($_FILES[$image_fieldname]['error'] != 0) {
				$_SESSION['errorMessage'] = $php_errors[$_FILES[$image_fieldname]['error']] . " " . $lang['try-again'];
				header("Location: new-signature-upload.php");
				exit();
			}
			
			// Check if a real file was uploaded
			if (is_uploaded_file($_FILES[$image_fieldname]['tmp_name'])) {
				
			} else {
				$_SESSION['errorMessage'] = $lang['imgError4'];
				header("Location: new-signature-upload.php");
				exit();
			}
			
			// Is this actually an image?
			if (getimagesize($_FILES[$image_fieldname]['tmp_name'])) {
				
			} else {
				$_SESSION['errorMessage'] = $lang['imgError5'];
				header("Location: new-signature-upload.php");
				exit();
			}
			
			// Save the file
			$extension = pathinfo($_FILES[$image_fieldname]['name'], PATHINFO_EXTENSION);
			$upload_filename = "images/_$domain/sigs/" . $user_id . "." . $extension;
			
			if (move_uploaded_file($_FILES[$image_fieldname]['tmp_name'], $upload_filename)) {
				
				// Write extension to user db
				$updateUser = "UPDATE users SET sigext = '$extension' WHERE user_id = $user_id";
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


				$_SESSION['image_path'] = $upload_filename;
				$_SESSION['success_url'] = "profile.php?user_id=$user_id";
				$_SESSION['successMessage'] = "Firma subido con éxito!";
				header("Location: crop-image.php");
				exit();
				
			} else {
				$_SESSION['errorMessage'] = $lang['imgError6'];
				header("Location: new-signature-upload.php");
				exit();
			}
				
		}
		

		pageStart("Añadir firma", NULL, NULL, "ppurchase", "admin", "Añadir firma", $_SESSION['successMessage'], $_SESSION['errorMessage']);
		echo "<center>";
		echo '<div id="mainbox-no-width">';
		echo '<div class="boxcontent">';

		echo "<form action='?upload&user_id=$user_id&mconsumption=$mconsumption&usageType=$usageType' method='post' enctype='multipart/form-data'>";


	$file = "_club/_$domain/contract.php";
	
	if (file_exists($file)) {
		include $file;
	}
	

?>

		 <input type="hidden" name="MAX_FILE_SIZE" value="20000000" />
		
		  <!-- <strong><?php //echo $lang['step']; ?> 1:</strong> -->
		 <!--  <input type="file" class="defaultinput"  name="fileToUpload" id="fileToUpload"> -->
		     <div class="upload-btn-wrapper">
			  <button class="btn" >Choose file</button>
			  <input type="file" name="fileToUpload"  id="fileToUpload">
			</div>
		  <br>
		 <!--  <strong><?php //echo $lang['step']; ?> 2:</strong> -->
		  <input type="submit" value="<?php echo $lang['submit']; ?>" name="submit" class="cta1">
		  
		</form>
	</div>
</div>
</center>
