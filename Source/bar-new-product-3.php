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
		
		header("Location: bar-products.php");
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
			header("Location: ?productid=<?php echo $productid; ?>");
			exit();
		}
		
		// Check if a real file was uploaded
		if (is_uploaded_file($_FILES[$image_fieldname]['tmp_name'])) {
			
		} else {
			$_SESSION['errorMessage'] = $lang['imgError4'];
			header("Location: ?productid=<?php echo $productid; ?>");
			exit();
		}
		
		// Is this actually an image?
		if (getimagesize($_FILES[$image_fieldname]['tmp_name'])) {
			
		} else {
			$_SESSION['errorMessage'] = $lang['imgError5'];
			header("Location: ?productid=<?php echo $productid; ?>");
			exit();
		}
		
		// Save the file and store the extension for later db entry
		$extension = pathinfo($_FILES[$image_fieldname]['name'], PATHINFO_EXTENSION);
		$upload_filename = "images/_$domain/bar-products/" . $productid . "." . $extension;
		$_SESSION['photoextension'] = $extension;
		
		if (move_uploaded_file($_FILES[$image_fieldname]['tmp_name'], $upload_filename)) {
			
		} else {
			$_SESSION['errorMessage'] = $lang['imgError6'];
			header("Location: ?productid=<?php echo $productid; ?>");
			exit();
		}
		
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
		
		$_SESSION['successMessage'] = $lang['picture-updatesuccess'];
		header("Location: bar-products.php?productid=$productid");
		exit();
			
	}

	pageStart($lang['title-newpicture'], NULL, NULL, "pprofile", "campage", $lang['picture-newpicture'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

?>

 <div id='mainbox'>
  <div id='mainboxheader'>
  <?php echo $lang['product-photo']; ?>
  </div>
  <div class='twoboxes'>
   <div class='boxheader'>
    <?php echo $lang['upload-photo']; ?>
   </div>
   <div class='boxcontent'>
 <form action="?productid=<?php echo $productid; ?>" method="post" enctype="multipart/form-data">
 <input type="hidden" name="MAX_FILE_SIZE" value="20000000" />
 <input type="hidden" name="photoupload" />
<div class="upload-btn-wrapper">
  <button class="btn"><?php echo $lang['choose-file']; ?></button>
  <input type="file" name="fileToUpload" id="fileToUpload">
</div>


	</div>
 <input type="submit" value="Confirmar" name="submit" class="okbutton1" style='position: absolute; bottom: 5px; left: 20px;'>
  </form>
  </div>

  <div class='twoboxes'>
   <div class='boxheader'>
    <?php echo $lang['use-webcam']; ?>
   </div>
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
			<input type=button value="<?php echo $lang['member-takesnapshot']; ?>" onClick="preview_snapshot()" class="okbutton1"><br /><br />
		</div>
		<div id="post_take_buttons" style="display:none">
			<input type=button value="<?php echo $lang['member-takeanother']; ?>" onClick="cancel_preview()" class="otherbutton">
			<input type=button value="<?php echo $lang['member-savephoto']; ?>" onClick="save_photo()" style="font-weight:bold;" class="okbutton1"><br /><br />
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
	
<?php 
		echo "<form onsubmit='oneClick.disabled = true; return true;' id='myform' method='post' action=''>";
?>

	
	<input type="hidden" name="mydata" id="mydata" value=""/>
	<input type="hidden" name="user_id" value="<?php echo $user_id; ?>"/>
	<input type="hidden" name="newpic" value="yes" />
	</form>   
  </div>


<br /><br />
</div><br /><br />
<center>

<a class="skipbutton" href="products.php"><?php echo $lang['skip']; ?></a>
   </form>
</center>
<?php
 displayFooter();
?>
