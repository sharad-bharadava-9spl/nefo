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
	
	if (isset($_POST['newpic'])) {
		
	$user_id = $_POST['user_id'];
	
	// Lookup the memerno for the user in querstion
	$userDetails = "SELECT memberno FROM users WHERE user_id = '{$user_id}'";
	
	$result = mysql_query($userDetails)
		or handleError($lang['error-userload'],"Error loading user: " . mysql_error());

	$row = mysql_fetch_array($result);
		$memberno = $row['memberno'];
	
	$encoded_data = $_POST['mydata'];
	$binary_data = base64_decode( $encoded_data );
	
	
	$imgname = 'images/members/' . $user_id . '.jpg';
	// save to server (beware of permissions)
	
	$result = file_put_contents( $imgname, $binary_data );
	if (!$result) die($lang['error-imagesave']);
	
	// Save file extension
	$updateUser = "UPDATE users SET photoext = 'jpg' WHERE user_id = $user_id";
	
	mysql_query($updateUser)
		or handleError($lang['error-userload'],"Error loading user: " . mysql_error());

		// On success: redirect.
		$_SESSION['successMessage'] = $lang['picture-updatesuccess'];
		header("Location: profile.php?user_id={$user_id}");
		exit();
	}
	/***** FORM SUBMIT END *****/
		
	if ($_GET['newmember'] != 'true') {
			
		if (isset($_GET['user_id'])) {
			$user_id = $_GET['user_id'];
		} else {
			handleError($lang['error-nomember'],"");
		}
		
	}
	
	

	pageStart($lang['title-newpicture'], NULL, NULL, "pprofile", NULL, $lang['picture-newpicture'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

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
			<a class="cta" href="new-member-3.php" style='background-color: red; margin: 0; margin-left: 3px; width: 162px;'><?php echo $lang['skip']; ?></a>
		</div>
		<div id="post_take_buttons" style="display:none">
			<input type=button value="&lt; <?php echo $lang['member-takeanother']; ?>" onClick="cancel_preview()">
			<input type=button value="<?php echo $lang['member-savephoto']; ?> &gt;" onClick="save_photo()" style="font-weight:bold;"><br /><br />
			<a class="cta" href="new-member-3.php" style='background-color: red; margin: 0; margin-left: 3px; width: 162px;'><?php echo $lang['skip']; ?></a>
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
		echo "<form onsubmit='oneClick.disabled = true; return true;' id='myform' method='post' action='new-member-3.php'>";
	} else {
		echo "<form onsubmit='oneClick.disabled = true; return true;' id='myform' method='post' action=''>";
	}
?>

	
	<input type="hidden" name="mydata" id="mydata" value=""/>
	<input type="hidden" name="user_id" value="<?php echo $user_id; ?>"/>
	<input type="hidden" name="newpic" value="yes" />
	</form>

<?php

 displayFooter(); ?>
