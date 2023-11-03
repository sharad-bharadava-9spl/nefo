<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view-photo.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings();
	
	// Authenticate & authorize
	authorizeUser($accessLevel);

	if (isset($_GET['productid'])) {
		$_SESSION['productid'] = $_GET['productid'];
	} else {
		echo $lang['error-noproductspecified'];
		exit();
	}
// this is for products and profile images
	$_SESSION['max_crop_width'] = "500";
	$_SESSION['max_crop_height'] = "500";

	require_once 'crop-functions.php';

	$productid = $_SESSION['productid'];

	if (isset($_POST['mydata'])) {
		
		$encoded_data = $_POST['mydata'];
		$binary_data = base64_decode( $encoded_data );
		
		$imgname = "images/_$domain/bar-products/" . $productid . ".jpg";
		
		// save to server (beware of permissions)
		$result = file_put_contents( $imgname, $binary_data );
		
		if (!$result) die($lang['error-imagesave']);
		
		$_SESSION['photoextension'] = 'jpg';
		$_SESSION['successMessage'] = $lang['picture-updatesuccess'];

		$query = "UPDATE b_products SET photoExt = '{$_SESSION['photoextension']}' WHERE productid = $productid";
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
		
		$_SESSION['image_path'] = $imgname;
		$_SESSION['success_url'] = "bar-products.php";
		header("Location: crop-image.php");
		exit();
		
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
			header("Location: ?productid=$productid");
			exit();
		}
		
		// Check if a real file was uploaded
		if (is_uploaded_file($_FILES[$image_fieldname]['tmp_name'])) {
			
		} else {
			$_SESSION['errorMessage'] = $lang['imgError4'];
			header("Location: ?productid=$productid");
			exit();
		}
		
		// Is this actually an image?
		if (getimagesize($_FILES[$image_fieldname]['tmp_name'])) {
			
		} else {
			$_SESSION['errorMessage'] = $lang['imgError5'];
			header("Location: ?productid=$productid");
			exit();
		}
		
		// Save the file and store the extension for later db entry
		$extension = pathinfo($_FILES[$image_fieldname]['name'], PATHINFO_EXTENSION);
		$upload_filename = "images/_$domain/bar-products/" . $productid . "." . $extension;
		$_SESSION['photoextension'] = $extension;
		
		if (move_uploaded_file($_FILES[$image_fieldname]['tmp_name'], $upload_filename)) {
			//resizeImageScale($upload_filename, $max_height, $max_width);
		} else {
			$_SESSION['errorMessage'] = $lang['imgError6'];
			header("Location: ?productid=$productid");
			exit();
		}

		// Save the file and store the extension for later db entry
		
		$query = "UPDATE b_products SET photoExt = '{$_SESSION['photoextension']}' WHERE productid = $productid";
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
		$_SESSION['image_path'] = $upload_filename;
		$_SESSION['success_url'] = "bar-products.php";
		$_SESSION['successMessage'] = $lang['picture-updatesuccess'];
		header("Location: crop-image.php");
		exit();
			
	}

	pageStart($lang['title-newpicture'], NULL, NULL, "pprofile", 'campage dev-align-center', $lang['picture-newpicture'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
  echo $_SESSION['errorMessage']; 
	/*if ($_SESSION['cropOrNot'] == '1') {
		
		echo "<center>
		       <a class='cta1' href='bar-change-image-upload.php'>{$lang['upload-photo']}</a>
		       <a class='cta1' href='bar-change-image-photo.php'>{$lang['use-webcam']}</a>
		      </center>";
		      
	} else {
		
		echo "<center>
		       <a class='cta1' href='bar-change-image-upload-nocrop.php'>{$lang['upload-photo']}</a>
		       <a class='cta1' href='bar-change-image-photo.php'>{$lang['use-webcam']}</a>
		      </center>";
		      
	}*/
?>

<div id="mainbox">
	  <div class="twoboxes">
			
		   <div class="boxheader">
		    Upload photo   </div>
			 <div class="boxcontent">
			 	<?php   if ($_SESSION['cropOrNot'] == '1') { ?>
					<form name="photo" enctype="multipart/form-data" action="?productid=<?php echo $productid; ?>" method="post" id="crop_form">
						 <input type="hidden" name="MAX_FILE_SIZE" value="20000000" />
 							<input type="hidden" name="photoupload" />
								<div class="upload-btn-wrapper">
								  <button class="btn">Choose file</button>
								  <input type="file" name="fileToUpload" size="30">
								</div>
								
					 	<input type="submit" value="<?php echo $lang['submit']; ?>" name="upload"  class="uploadbutton okbutton1 cta4" style="position: absolute; bottom: 5px; left: 20px;">
					 </form>
				<?php }else{ ?>	 
					 <form action='bar-change-image-upload-nocrop-2.php' method='post' enctype='multipart/form-data'>
					 <input type="hidden" name="MAX_FILE_SIZE" value="20000000" />
					<div class="upload-btn-wrapper">
						  <button class="btn">Choose file</button>
						  <input type="file" name="fileToUpload"  id="imgInp" size="30">
							
						</div>
					  <input type="submit" value="<?php echo $lang['submit']; ?>" name="submit" class="uploadbutton okbutton1 cta4" style="position: absolute; bottom: 5px; left: 20px;">
					
					</form>
				<?php } ?>	
				 </div>
			</div>

			<div class="twoboxes">
				<div class="boxheader">
					    Use webcam   </div>
					<div id="my_camera"></div>
					<script language="JavaScript">
						Webcam.set({
							height: 240,
							image_format: 'jpeg',
							jpeg_quality: 100
						});
						Webcam.attach( '#my_camera' );
					</script>
					<!-- A button for taking snaps -->
						<div id="pre_take_buttons">
							<input type=button class="cta1" value="<?php echo $lang['member-takesnapshot']; ?>" onClick="preview_snapshot()">
						</div>
						<div id="post_take_buttons" style="display:none">
							<input type=button class="cta1" value="&lt; <?php echo $lang['member-takeanother']; ?>" onClick="cancel_preview()">
							<input type=button class="cta1" value="<?php echo $lang['member-savephoto']; ?> &gt;" onClick="save_photo()" style="font-weight:bold;">
						</div>
					<!-- Code to handle taking the snapshot and displaying it locally -->
					<script language="JavaScript">
						function preview_snapshot() {
							// freeze camera so user can preview pic
							Webcam.freeze();
							
							// swap button sets
							document.getElementById('pre_take_buttons').style.display = 'none';
							document.getElementById('post_take_buttons').style.display = '';
						}
						
						function cancel_preview() {
							// cancel preview freeze and return to live camera feed
							Webcam.unfreeze();
							
							// swap buttons back
							document.getElementById('pre_take_buttons').style.display = '';
							document.getElementById('post_take_buttons').style.display = 'none';
						}
						
						function save_photo() {
							// actually snap photo (from preview freeze) and display it
							Webcam.snap( function(data_uri) {
						var raw_image_data = data_uri.replace(/^data\:image\/\w+\;base64\,/, '');
						
						document.getElementById('mydata').value = raw_image_data;
						document.getElementById('myform').submit();
								
								// swap buttons back
								document.getElementById('pre_take_buttons').style.display = '';
								document.getElementById('post_take_buttons').style.display = 'none';
							} );
						}
					</script>
					
					<form onsubmit='oneClick.disabled = true; return true;' id='myform' method='post' action='bar-change-image-photo.php'>

					
					<input type="hidden" name="mydata" id="mydata" value=""/>
					<input type="hidden" name="newpic" value="yes" />
					</form>


					<br><br>
			</div>
</div>
<center>
  <form action="bar-products.php" method="post">
   <input type="submit" name="skip" class="uploadbutton skipbutton" value="<?php echo $lang['skip']; ?>" />
  </form>
 </center> 
<?php	
 displayFooter();
