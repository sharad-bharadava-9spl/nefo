<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	

	pageStart($lang['title-newpicture'], NULL, NULL, "ppurchase", "admin dev-align-center", $lang['title-newpicture'], $_SESSION['successMessage'], $_SESSION['errorMessage']);


?>
<div class="actionbox-np2">
	<div class='boxcontent'>
<form action='bar-change-image-upload-nocrop-2.php' method='post' enctype='multipart/form-data'>

 <input type="hidden" name="MAX_FILE_SIZE" value="20000000" />
 <table>
  <tr>
  <!--  <td><strong><?php echo $lang['submit']; ?> 1:</strong></td> -->
   <td style="padding-left: 5px;">

   <!-- 	<input type="file" name="fileToUpload" id="fileToUpload"> -->
		  	<div class="upload-btn-wrapper">
			  <button class="btn">Choose file</button>
			  <input type="file" name="fileToUpload" id="fileToUpload">
			</div>
   </td>
  </tr>
  <tr>
  <!--  <td style="padding-top: 10px;"><strong><?php echo $lang['submit']; ?> 2:</strong></td> -->
   <td style="padding-top: 10px; padding-left: 5px;"><input class='cta1' type="submit" value="Subir" name="submit"></td>
  </tr>
</table>
</form>
</div>
</div>


<?php displayFooter(); ?>
