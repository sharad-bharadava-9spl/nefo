<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	

	pageStart($lang['picture-newpicture'], NULL, NULL, "ppurchase", "admin", $lang['picture-newpicture'], $_SESSION['successMessage'], $_SESSION['errorMessage']);


?>
<center>
	<div id="mainbox-no-width">	

	 <div class='boxcontent'>
		<form action='change-image-upload-nocrop-2.php' method='post' enctype='multipart/form-data'>


		 <input type="hidden" name="MAX_FILE_SIZE" value="20000000" />
		<!-- <strong>Step 1:</strong><input type="file" name="fileToUpload" id="fileToUpload"> -->
			<div class="upload-btn-wrapper">
			  <button class="btn">Choose file</button>
			  <input type="file" name="fileToUpload" id="fileToUpload">
			</div>
		<br>
		<!-- <strong>Step 2:</strong> -->
		   <input type="submit" value="<?php echo $lang['submit']; ?>" class="cta1" name="submit">
		</form>

	</div>
</div>
</center>
<?php displayFooter(); ?>
