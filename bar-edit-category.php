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
		$category = $_POST['category'];
		$description = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['description'])));
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
					header("Location: bar-edit-category.php?categoryid=".$category);
					exit();
				}
		}
	
		// Query to update category
		$updateCat = sprintf("UPDATE b_categories SET name = '%s', description = '%s', icon = '%s' WHERE id = '%d';",
			$name,
			$description,
			$cat_icon,
			$category
		);
		try
		{
			$result = $pdo3->prepare("$updateCat")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		// On success: redirect.
		$_SESSION['successMessage'] = $lang['bar-category-updated'];
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

	$categoryid = $_GET['categoryid'];

	// Query to look for category
	$categoryDetails = "SELECT name, description, icon FROM b_categories WHERE id = $categoryid";
		try
		{
			$result = $pdo3->prepare("$categoryDetails");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$name = $row['name'];
		$description = $row['description'];
		$icon = $row['icon'];


	pageStart($lang['edit-category'], NULL, $validationScript, "pnewcategory", "", $lang['edit-category'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
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
  <?php echo $lang['edit-category']; ?>
 </div>
 <div class='boxcontent'>
 <input type="hidden" name="category" value="<?php echo $categoryid; ?>" />
   <input type="text" name="name" value="<?php echo $name; ?>" class='defaultinput' style="width: 60%;"/><br />
   <textarea name="description" placeholder="Description" class='defaultinput' style='height: 100px; width: 60%;'><?php echo $description; ?></textarea>
<br />
<br />
<br />
  <div class="icon_selector" style="width: 700px;">
  		<span><strong>Select Icon: </strong></span><br />
   <br />
	   <label>
	   	<input type="radio" name="cat_icon" value="" <?php if(trim($icon) == ""){ echo "checked";  } ?>> <strong>No Icon (Or select from below icons)</strong>
		</label>
	   <br />
	   <br />
   <?php
	   $dir = 'images/icons'; 
	  $icon_images = array_diff(scandir($dir), array('.', '..')); 
	  foreach($icon_images as $icon_image){
   ?>
	   <label>
	  		<input type="radio" name="cat_icon" class="icon_img" value="<?php echo $icon_image ?>" <?php if($icon == $icon_image){ echo "checked"; } ?>> 
	  		<img src="images/icons/<?php echo $icon_image ?>" height="30">
	  	</label>	
  	<?php } ?>	
  </div> 
 <button class='cta4' name='oneClick' type="submit"><?php echo $lang['submit']; ?></button>
</form>
</div>
</div>

<?php displayFooter(); ?>

