<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
		
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Did this page re-submit with a form? If so, check & store details
	if (isset($_POST['name'])) {

		$name = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['name'])));
		$description = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['description'])));
		$insertTime = date('Y-m-d H:i:s');
		$cat_icon = $_POST['cat_icon'];

		if(isset($cat_icon) && $cat_icon != ''){
			$cat_folder = "images/_$domain/bar-category";

			if (!file_exists($cat_folder)) {
	    		mkdir($cat_folder, 0777, true);
			}

			$upload_filename = "images/_$domain/bar-category/" . $cat_icon;
			$upload_from = "images/icons/".$cat_icon;

			if (!copy($upload_from, $upload_filename)) {
				$_SESSION['errorMessage'] = $lang['imgError6'];
				header("Location: bar-new-category.php");
				exit();
			}
		}
	
		// Query to add new category - 11 arguments
		  $query = sprintf("INSERT INTO b_categories (time, name, description, icon) VALUES ('%s', '%s', '%s', '%s');",
		  $insertTime, $name, $description, $cat_icon);
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
			
		// On success: redirect.
		$_SESSION['successMessage'] = $lang['bar-category-added'];
		header("Location: bar-categories.php");
		exit();
	}
	/***** FORM SUBMIT END *****/

	$validationScript = <<<EOD
    $(document).ready(function() {
	    	    
	  $('#registerForm').validate({
		  rules: {
			  name: {
				  required: true
			  }
    	}, // end rules
    	errorPlacement: function(error, element) { },
    	  submitHandler: function() {
   $(".oneClick").attr("disabled", true);
   form.submit();
	    	  }
	  }); // end validate
  }); // end ready
EOD;

	pageStart($lang['new-bar-category'], NULL, $validationScript, "pnewcategory", "", $lang['new-bar-category'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
?>
<style type="text/css">
	#pprofilenew.donations .icon_selector input[type="radio"]{
		display: inline-block;
	}
	.icon_selector img{
		padding: 10px;
    	vertical-align: middle;
	}
			/* HIDE RADIO */
	[type=radio].icon_img { 
	  position: absolute;
	  opacity: 0;
	  width: 0;
	  height: 0;
	}

	/* IMAGE STYLES */
	[type=radio].icon_img + img {
	  cursor: pointer;
	}

	/* CHECKED STYLES */
	[type=radio].icon_img:checked + img {
	  outline: 2px solid #000;
	}
</style>
<form id="registerForm" action="" method="POST">
<center>
<div id="mainbox-no-width">
 <div id="mainboxheader">
  <?php echo $lang['new-bar-category']; ?>
 </div>
 <div class='boxcontent'>
   <input type="text" name="name" placeholder="Name" class='defaultinput' style="width:60%;" /><br />
   <textarea name="description" placeholder="Description" class='defaultinput' style='height: 100px; width: 60%;'></textarea><br />
   <div class="icon_selector" style="width: 700px;">
	  		<span><strong>Select Icon: </strong></span><br />
	   <br />
   <label>
   	<input type="radio" name="cat_icon" value="" checked> <strong>No Icon (Or select from below icons)</strong>
	</label>
   <br />
   <br />
   <?php
	   $dir = 'images/icons'; 
	  $icon_images = array_diff(scandir($dir), array('.', '..')); 
	  foreach($icon_images as $icon_image){
   ?>
	   <label>
	  		<input type="radio" name="cat_icon" class="icon_img" value="<?php echo $icon_image ?>">
	  		<img src="images/icons/<?php echo $icon_image ?>" height="30"> 
	  	</label>	
  	<?php } ?>	
  </div>  
 <button class='cta4' name='oneClick' type="submit"><?php echo $lang['submit']; ?></button>
</form>
</div>
</div>
<?php displayFooter(); ?>

