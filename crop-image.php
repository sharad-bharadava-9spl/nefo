<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view-photo.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings();
	
	// Authenticate & authorize
	authorizeUser($accessLevel);

	if (isset($_SESSION['image_path'])) {
		$image_path = $_SESSION['image_path'];
	} else {
		echo $lang['error-noproductspecified'];
		exit();
	}
// this is for products and profile images

	$max_height = $_SESSION['max_crop_height'];
	$max_width = $_SESSION['max_crop_width'];

	require_once 'crop-functions.php';

 if (isset($_POST['cropedPhoto'])) {


		$cropedPhoto = $_POST['cropedPhoto'];
		if($cropedPhoto == '')
		 {
			$_SESSION['errorMessage'] = $lang['imgError4'];
			header("Location:".$_SERVER['HTTP_REFERER']);
			exit();
		}
		
		
		cropImage($cropedPhoto, $image_path, $max_height, $max_width);

		$redirect_success = $_SESSION['success_url'];
		
		unset($_SESSION['image_path']);
		unset($_SESSION['max_crop_width']);
		unset($_SESSION['max_crop_height']);
		header("Location:".$redirect_success);
		exit();
			
	}


	if(isset($_POST['skip'])){
		resizeImageScale($image_path, $max_height, $max_width);

		$redirect_success = $_SESSION['success_url'];
		
		unset($_SESSION['image_path']);
		unset($_SESSION['max_crop_width']);
		unset($_SESSION['max_crop_height']);
		header("Location:".$redirect_success);
		exit();
	}

	pageStart("Resize & Crop Image", NULL, NULL, "pprofile", 'campage dev-align-center', "Resize & Crop Image", $_SESSION['successMessage'], $_SESSION['errorMessage']);
  echo $_SESSION['errorMessage']; 

?>
<link  href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.7/cropper.min.css" rel="stylesheet">
<style type="text/css">
	.col{
    padding-left: 20px;
    padding-right: 20px; 
    box-sizing: border-box;
    float: left;   
}
.col-3 {
    width: 25%;
}
.col-9 {
    width: 75%;
}
#pprofile.campage .btn{
	width: 200px;
}
</style>

<center>

 </center> 
<!-- cropper image -->
<center>
<div class="boxcontent" id="crop_image_div">
		<div class="col col-9">
			<img id="imageCrop" src="<?php echo $image_path; ?>?=<?php echo filemtime($image_path)?>" style="display: block; max-width: 100%">
			   <div id="cropped_img" style="display: none;"></div>  <br><br>
			   <div id="pngHolder"></div> 
		</div>
		<div class="col col-3">
			  <form action="" method="post">
			   		<input type="submit" name="skip" class="uploadbutton skipbutton" value="<?php echo $lang['skip']; ?>"/>
			  </form>
			<form method="POST" action="" id="crop_form">
				<div class="pre_take_buttons" id="imagecrop_buttons">
					<button type="button" class="btn btn-primary crop_btn" id="crop">
					           Crop
					  </button>

					 <button type="button" id="zoom-in" class="btn btn-primary crop_btn" >Zoom In
					          </button> 

					 <button type="button" class="btn btn-primary crop_btn" id="zoom-out">
					           Zoom Out
					   </button>  

					<button type="button" class="btn btn-primary crop_btn" id="rotate-left">
						Rotate left (90)
					   </button>
					   <button type="button" class="btn btn-primary crop_btn" id="rotate-right">
						Rotate Right (90)
					   </button>
					<button type="button" class=" btn-success crop_btn cta4" id="get-canvas" style="width:200px; font-size:15px;">
					              Get Cropped Image
					 </button>
					 <input type="hidden" name="cropedPhoto" id="cropedPhoto">
					 <button type="button" id="crop_sub" value="" name="upload"  class="uploadbutton cta1" style="display: none; width: 200px;"><?php echo $lang['submit']; ?></button>
				</div>
			</form>
		 
		</div>	
</div>
</center>
 <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" crossorigin="anonymous"></script> 
 <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.7/cropper.min.js"></script>
<script src="js/jquery-cropper.js"></script> 
<script src="js/custom-crop.js"></script> 
<?php	
 displayFooter();
