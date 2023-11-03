<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	

pageStart($lang['title-newpicture'], NULL, NULL, "ppurchase", "admin", $lang['title-newpicture'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>
 	<center>
<div id="mainbox-no-width">	
 <div class='boxcontent'>

<?php

	if (isset($_GET['memberno'])) {
		$memberno = $_GET['memberno'];
		$user_id = $_GET['user_id'];
		echo "<form action='new-picture-upload-nocrop-2.php?memberno=$memberno&user_id=$user_id' method='post' enctype='multipart/form-data'>";
	} else {
		echo "<form action='new-member-3.php' method='post' enctype='multipart/form-data'>";
	}


?>


 <input type="hidden" name="MAX_FILE_SIZE" value="20000000" />
 <input type="hidden" name="purchaseid" value="<?php echo $purchaseid; ?>" />
<!--  <strong><?php //echo $lang['step']; ?> 1:</strong><br> -->
  <!-- <input type="file" name="fileToUpload" id="fileToUpload" class="defaultinput"> -->
  	<div class="upload-btn-wrapper">
	  <button class="btn" >Choose file</button>
	  <input type="file" name="fileToUpload" id="fileToUpload" class="defaultinput" >
	</div>
  <br>
  <!-- <strong><?php //echo $lang['step']; ?> 2:</strong><br> -->
  <input type="submit" value="<?php echo $lang['submit']; ?>" class="cta1" name="submit">

</form>
</div>
</div>
</center>
<?php displayFooter(); ?>
