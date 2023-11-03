<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	

	$shopid = $_SESSION['shopid'];

	pageStart("Add your logo", NULL, NULL, "pprofile", NULL, "Add your logo", $_SESSION['successMessage'], $_SESSION['errorMessage']);


?>
<center>
<div class="actionbox-np2">
 <div class='mainboxheader'>
 ADD LOGO
 </div>
 <div class='boxcontent'>
<?php if ($_SESSION['userGroup'] == 1) { ?>
<form action='shops.php' method='post' enctype='multipart/form-data'>
<?php } else { ?>
<form action='new-shop-3.php' method='post' enctype='multipart/form-data'>
<?php } ?>

 <input type="hidden" name="MAX_FILE_SIZE" value="20000000" />
 <table>
  <tr>
   <td><strong><?php echo $lang['step']; ?> 1:</strong></td>
   <td style="padding-left: 5px;"><input type="file" name="fileToUpload" id="fileToUpload"></td>
  </tr>
  <tr>
   <td style="padding-top: 10px;"><strong><?php echo $lang['step']; ?> 2:</strong></td>
   <td style="padding-top: 10px;"><input type="submit" class='cta1' value="<?php echo $lang['submit']; ?>" name="submit"></td>
  </tr>
 </table>
</form>
 </div>
 </div>
<br />
<a href="my-shop.php" class='cta1' style='background-color: red;'>SKIP</a></center>


<?php displayFooter(); ?>
