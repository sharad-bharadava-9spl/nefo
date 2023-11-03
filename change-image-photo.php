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
	$purchaseid = $_SESSION['purchaseid'];

	$_SESSION['max_crop_width'] = "500";
	$_SESSION['max_crop_height'] = "500";
	

	// Exception for when a picture has been taken ("form submission" in a way):
	if (isset($_POST['newpic'])) {
		
		
		$encoded_data = $_POST['mydata'];
		$binary_data = base64_decode( $encoded_data );
		
		
		$imgname = "images/_$domain/purchases/" . $purchaseid . '.jpg';
		// save to server (beware of permissions)
		
		$result = file_put_contents( $imgname, $binary_data );
		
		if (!$result) die($lang['error-imagesave']);
		
		// Save file extension
		$updateUser = "UPDATE purchases SET photoext = 'jpg' WHERE purchaseid = $purchaseid";
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

		// On success: redirect.
		$_SESSION['image_path'] = $imgname;
		$_SESSION['success_url'] = "purchase.php?purchaseid=$purchaseid";
		$_SESSION['successMessage'] = $lang['purchase-photo-update-success'];
		header("Location: crop-image.php");
		exit();
	}
	/***** FORM SUBMIT END *****/
		
	

	pageStart($lang['title-newpicture'], NULL, NULL, "pprofile", "admin  dev-align-center", $lang['title-newpicture'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

?>
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
			<input type=button value="<?php echo $lang['member-takesnapshot']; ?>" class="cta1" onClick="preview_snapshot()">
		</div>
		<div id="post_take_buttons" style="display:none">
			<input type=button value="&lt; <?php echo $lang['member-takeanother']; ?>" class="cta1" onClick="cancel_preview()">
			<input type=button value="<?php echo $lang['member-savephoto']; ?> &gt;" class="cta1" onClick="save_photo()" style="font-weight:bold;">
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
	
<form onsubmit='oneClick.disabled = true; return true;' id='myform' method='post' action=''>

	
	<input type="hidden" name="mydata" id="mydata" value=""/>
	<input type="hidden" name="newpic" value="yes" />
	</form>

<?php

 displayFooter(); ?>
