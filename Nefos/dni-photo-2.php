<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Exception for when a picture has been taken ("form submission" in a way):
	
	if ((isset($_POST['newpic'])) && ($_POST['pic2'] == 'yes')) {
		
		$user_id = $_GET['user_id'];
			
		$encoded_data = $_POST['mydata'];
		$binary_data = base64_decode( $encoded_data );
		
		$imgname = 'images/ID/' . $user_id . '-back.jpg';
		// save to server (beware of permissions)
		$result = file_put_contents( $imgname, $binary_data );
		if (!$result) die($lang['error-imagesave']);
					
		// Write extension to user db
		$updateUser = "UPDATE users SET dniext1 = 'jpg', dniext2 = 'jpg' WHERE user_id = $user_id";
		
		mysql_query($updateUser)
			or handleError($lang['error-userload'],"Error loading user: " . mysql_error());

		// On success: redirect.
		$_SESSION['successMessage'] = $lang['picture-updatesuccess'];
		header("Location: profile.php?user_id={$user_id}");
		exit();
	}
	/***** FORM SUBMIT END *****/
		
	if ($_GET['newmember'] == 'true') {
		$nextMemberNo = $_SESSION['tempNo'];
	} else {
		$nextMemberNo = $_GET['user_id'];
		$user_id = $nextMemberNo;
	}
			
		if (isset($_POST['mydata'])) {
						
			$encoded_data = $_POST['mydata'];
			$binary_data = base64_decode( $encoded_data );
			
			$imgname = 'images/ID/' . $nextMemberNo . '-front.jpg';
			
			// save to server (beware of permissions)
			$result = file_put_contents( $imgname, $binary_data );
						
			if (!$result) die($lang['error-imagesave']);
			
			$_SESSION['dnifrontextension'] = 'jpg';
			
			$_SESSION['successMessage'] = $lang['dni-1-success'];

		}

	pageStart("DNI / " . $lang['member-passport'], NULL, NULL, "pprofile", NULL, "DNI / " . $lang['member-passport'] . " - " . $lang['dni-back'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

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
			<input type=button value="<?php echo $lang['member-takesnapshot']; ?>" onClick="preview_snapshot()"><br /><br />
			<a class="cta" href="new-member-2.php?skipDNI" style='background-color: red; margin: 0; margin-left: 3px; width: 162px;'><?php echo $lang['skip']; ?></a>
		</div>
		<div id="post_take_buttons" style="display:none">
			<input type=button value="&lt; <?php echo $lang['member-takeanother']; ?>" onClick="cancel_preview()">
			<input type=button value="<?php echo $lang['member-savephoto']; ?> &gt;" onClick="save_photo()" style="font-weight:bold;"><br /><br />
			<a class="cta" href="new-member-2.php?skipDNI" style='background-color: red; margin: 0; margin-left: 3px; width: 162px;'><?php echo $lang['skip']; ?></a>
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
	if ($_GET['newmember'] == 'true') {
		echo "<form onsubmit='oneClick.disabled = true; return true;' id='myform' method='post' action='new-member-2.php'>";
	} else {
		echo "<form onsubmit='oneClick.disabled = true; return true;' id='myform' method='post' action='?user_id=$user_id'>";
	}
?>

	
	<input type="hidden" name="mydata" id="mydata" value=""/>
	<input type="hidden" name="newpic" value="yes" />
	<input type="hidden" name="pic2" value="yes" />
	</form>

<?php

 displayFooter(); ?>
