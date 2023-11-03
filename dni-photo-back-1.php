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
	$user_id = $_GET['user_id'];
		
	if (isset($_GET['snapped'])) {
			
		$encoded_data = $_POST['mydata'];
		$binary_data = base64_decode( $encoded_data );
		
		$imgname = "images/_$domain/ID/" . $user_id . '-back.jpg';
		// save to server (beware of permissions)
		$result = file_put_contents( $imgname, $binary_data );
		if (!$result) die($lang['error-imagesave']);
					
		// Write extension to user db
		$updateUser = "UPDATE users SET dniext2 = 'jpg' WHERE user_id = $user_id";
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
		$_SESSION['successMessage'] = $lang['picture-updatesuccess'];
		header("Location: profile.php?user_id={$user_id}");
		exit();
		
	}
	

	pageStart($lang['update-dni'], NULL, NULL, "pprofile", 'dev-align-center', $lang['update-dni'] . " - " . $lang['dni-back'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

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
			<input class='cta1' type=button value="<?php echo $lang['member-takesnapshot']; ?>" onClick="preview_snapshot()"><br /><br />
			<a class="skipbutton" href="new-member-2.php?skipDNI" style='margin: 0; margin-left: 3px; width: 162px;'><?php echo $lang['skip']; ?></a>
		</div>
		<div id="post_take_buttons" style="display:none">
			<input  class='cta1' type=button value="&lt; <?php echo $lang['member-takeanother']; ?>" onClick="cancel_preview()">
			<input class='cta1' type=button value="<?php echo $lang['member-savephoto']; ?> &gt;" onClick="save_photo()" style="font-weight:bold;"><br /><br />
			<a class="skipbutton" href="new-member-2.php?skipDNI" style=' margin: 0; margin-left: 3px; width: 162px;'><?php echo $lang['skip']; ?></a>
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

		echo "<form onsubmit='oneClick.disabled = true; return true;' id='myform' method='post' action='?snapped&user_id=$user_id'>";
		
?>

	
	<input type="hidden" name="mydata" id="mydata" value=""/>
	<input type="hidden" name="user_id" value="<?php echo $user_id; ?>"/>
	<input type="hidden" name="newpic" value="yes" />
	</form>

<?php

 displayFooter(); ?>
