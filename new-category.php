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
	
	// Did this page re-submit with a form? If so, check & store details
	if (isset($_POST['name'])) {

		$name = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['name'])));
		$type = $_POST['type'];
		$description = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['description'])));
		$insertTime = date('Y-m-d H:i:s');
		$private = $_POST['private'];
		$cat_icon = $_POST['cat_icon'];

		if(isset($cat_icon) && $cat_icon != ''){
			$cat_folder = "images/_$domain/category";
			$cat_folder_png = "images/_$domain/category/png";

			if (!file_exists($cat_folder)) {
	    		mkdir($cat_folder, 0777, true);
			}			

			if (!file_exists($cat_folder_png)) {
	    		mkdir($cat_folder_png, 0777, true);
			}

			$cat_icon_png = str_replace(".svg", ".png", $cat_icon);

			$upload_filename = "images/_$domain/category/" . $cat_icon;
			$upload_from = "images/icons/".$cat_icon;			

			$upload_filename_png = "images/_$domain/category/png/" . $cat_icon_png;
			$upload_from_png = "images/caticons_png/".$cat_icon_png;

			if (!copy($upload_from, $upload_filename)) {
				$_SESSION['errorMessage'] = $lang['imgError6'];
				header("Location: new-category.php");
				exit();
			}			

			if (!copy($upload_from_png, $upload_filename_png)) {
				$_SESSION['errorMessage'] = $lang['imgError6'];
				header("Location: new-category.php");
				exit();
			}
		}

		// Dabulance customization
		if ($_SESSION['domain'] == 'dabulance') {
			// Query to add new category - 11 arguments
			$query = sprintf("INSERT INTO categories (time, name, description, type, private, shopid, icon) VALUES ('%s', '%s', '%s', '%d', '%d', '%d', '%s');",
			 $insertTime, $name, $description, $type, $private, $_SESSION['shopid'], $cat_icon);
		} else {
			// Query to add new category - 11 arguments
			$query = sprintf("INSERT INTO categories (time, name, description, type, icon) VALUES ('%s', '%s', '%s', '%d', '%s');",
		  	 $insertTime, $name, $description, $type, $cat_icon);
		}
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
		$_SESSION['successMessage'] = $lang['category-added'];
		header("Location: categories.php");
		exit();
	}
	/***** FORM SUBMIT END *****/

	$validationScript = <<<EOD
    $(document).ready(function() {
	    	    
	  $('#registerForm').validate({
		  ignore: [],
		  rules: {
			  name: {
				  required: true
			  },
			  type: {
				  required: true
			  }
    	}, // end rules
		  errorPlacement: function(error, element) {
			  if ( element.is(":radio") || element.is(":checkbox")){
				 error.appendTo(element.parent());
			}
		},
    	  submitHandler: function() {
   $(".oneClick").attr("disabled", true);
   form.submit();
	    	  }
	  }); // end validate
  }); // end ready
EOD;

	pageStart($lang['new-category'], NULL, $validationScript, "pprofilenew", "donations", $lang['new-category'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
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
  <?php echo $lang['new-category']; ?>
 </div>
 <div class='boxcontent'>
   <input type="text" name="name" placeholder="Name" class='defaultinput' style="width:60%;" />
   <br />
    <div style='padding-left: 55px;'>
    <br />
   <span>
	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['units']; ?>
	  <input type="radio" name='type' id='type' value='0' />
	  <div class="fakebox"></div>
	 </label>
	</div><br /><br />
	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox3"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['grams']; ?>
	  <input type="radio" name='type' id='type' value='1' />
	  <div class="fakebox"></div>
	 </label>
	</div>
   </span>
   </div>
<?php if ($_SESSION['domain'] == 'dabulance') { ?>

   <br /><br />
	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox3"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Private category?
	  <input type="checkbox" name='private' id='private' value='1' />
	  <div class="fakebox"></div>
	 </label>
	</div>
<?php } ?>
<br />
   <br />
  <textarea name="description" placeholder="Description" class='defaultinput' style='height: 100px; width: 60%;'></textarea><br /><br />
   <br />
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
<?php displayFooter(); 