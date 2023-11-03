<?php
	
	require_once '../cOnfig/connection-master.php';
	require_once '../cOnfig/view-newclub.php';
	require_once '../cOnfig/languages/common.php';
  global $siteroot;

	session_start();

  if(!isset($_SESSION['step2']) || !isset($_SESSION['step3'])){
     $_SESSION['errorMessage'] = "Please complete the previous steps first !";
     if(!isset($_SESSION['step2'])){
        header("location:new-club-2.php");
      }else{
        header("location:new-club-3.php");
      }
     die;
  }
//ini_set("display_errrs", "on");
  $validationScript = <<<EOD
    $(document).ready(function() {
      $("#step4").validate({
           rules: {
              club_logo: {
                extension: "jpg|jpeg|png|svg",
                filesize: 2097152
              }
           },
          messages:{
              club_logo: {
                  extension: 'Please upload valid image file',
                  filesize: 'File size must be less than 2 MB'
              }
          },
            errorPlacement: function(error, element) {
                error.insertAfter(element);
            },

        });
      tinymce.init({
        selector: '#contacttext',
        height :'400',
        plugins: "code",
    });
    $('#file-upload').change(function() {
      var file = $('#file-upload')[0].files[0].name;
      $('#filename').text(file);
    });
  }); // end ready
EOD;
//only assign a new timestamp if the session variable is empty
if (!isset($_SESSION['random_key']) || strlen($_SESSION['random_key'])==0){
    $_SESSION['random_key'] = strtotime(date('Y-m-d H:i:s')); //assign the timestamp to the session variable
  $_SESSION['user_file_ext']= "";
}
$update_id = $_SESSION['temp_id'];
$max_file = "8"; 

$allowed_image_types = array('image/pjpeg'=>"jpg",'image/jpeg'=>"jpg",'image/jpg'=>"jpg",'image/pjpeg'=>"jpeg",'image/jpeg'=>"jpeg",'image/jpg'=>"jpeg",'image/png'=>"png",'image/x-png'=>"png",'image/gif'=>"gif");


$upload_dir = "../images/_club";     // The directory for the images to be saved in
if(!is_dir($upload_dir)){
  mkdir($upload_dir, 0777);
}
$upload_dir = "../images/_club";
$upload_path = $upload_dir."/";       // The path to where the image will be saved
$thumb_upload_path = $upload_dir."/thumb/";
$thumb_image_prefix = "resize_";      // The prefix name to large image
$original_image_prefix = "original_";         // The prefix name to the thumb image
$thumb_image_name = $thumb_image_prefix.strtotime(date('Y-m-d H:i:s'));     // New name of the large image (append the timestamp to the filename)
$original_image_name = $original_image_prefix.strtotime(date('Y-m-d H:i:s')); 
$max_file = "20";              // Maximum file size in MB
$max_height = "100";             // Max width allowed for the large image
$max_width = "180";             // Max width allowed for the large image
$thumb_width = "150";           // Width of thumbnail image
$thumb_height = "100";            // Height of thumbnail image

//Image Locations

function makeThumbnails($source, $width, $height)
{
    $thumbnail_width = $width;
    $thumbnail_height = $height;
    $thumb_beforeword = "thumb";
    $arr_image_details = getimagesize($source); // pass id to thumb name
    $original_width = $arr_image_details[0];
    $original_height = $arr_image_details[1];
    if ($original_width > $original_height) {
        $new_width = $thumbnail_width;
        $new_height = intval($original_height * $new_width / $original_width);
    } else {
        $new_height = $thumbnail_height;
        $new_width = intval($original_width * $new_height / $original_height);
    }
    $dest_x = intval(($thumbnail_width - $new_width) / 2);
    $dest_y = intval(($thumbnail_height - $new_height) / 2);
    if ($arr_image_details[2] == IMAGETYPE_GIF) {
        $imgt = "ImageGIF";
        $imgcreatefrom = "ImageCreateFromGIF";
    }
    if ($arr_image_details[2] == IMAGETYPE_JPEG) {
        $imgt = "ImageJPEG";
        $imgcreatefrom = "ImageCreateFromJPEG";
    }
    if ($arr_image_details[2] == IMAGETYPE_PNG) {
        $imgt = "ImagePNG";
        $imgcreatefrom = "ImageCreateFromPNG";
    }
    if ($source) {
        $old_image = $imgcreatefrom($source);
        $new_image = imagecreatetruecolor($thumbnail_width, $thumbnail_height);
        imagecopyresized($new_image, $old_image, $dest_x, $dest_y, 0, 0, $new_width, $new_height, $original_width, $original_height);
        $imgt($new_image, $source);
    }
      chmod($source, 0777);
  return $source;
}

function resizeImage($image, $thumb_image, $width,$height,$scale) {
  list($imagewidth, $imageheight, $imageType) = getimagesize($image);
  $imageType = image_type_to_mime_type($imageType);
  $newImageWidth = ceil($width * $scale);
  $newImageHeight = ceil($height * $scale);
  $newImage = imagecreatetruecolor($newImageWidth,$newImageHeight);
  switch($imageType) {
    case "image/gif":
      $source=imagecreatefromgif($image); 
      break;
      case "image/pjpeg":
    case "image/jpeg":
    case "image/jpg":
      $source=imagecreatefromjpeg($image); 
      break;
      case "image/png":
    case "image/x-png":
      $source=imagecreatefrompng($image); 
     // $source = $newImage;

      break;
    }
    // start changes
   switch ($imageType) {

    case 'image/gif':
    case "image/png":
    case "image/x-png":
        // integer representation of the color black (rgb: 0,0,0)
        $background = imagecolorallocate($newImage , 0, 0, 0);
        // removing the black from the placeholder
        imagecolortransparent($newImage, $background);

        // turning off alpha blending (to ensure alpha channel information
        // is preserved, rather than removed (blending with the rest of the
        // image in the form of black))
        imagealphablending($newImage, false);

        // turning on alpha channel information saving (to ensure the full range
        // of transparency is preserved)
        imagesavealpha($newImage, true);
        break;

    default:
        break;
}
  imagecopyresampled($newImage,$source,0,0,0,0,$newImageWidth,$newImageHeight,$width,$height);

   chmod($thumb_image, 0777);
  switch($imageType) {
    case "image/gif":
        imagegif($newImage,$thumb_image); 
      break;
        case "image/pjpeg":
    case "image/jpeg":
    case "image/jpg":
        imagejpeg($newImage,$thumb_image,100); 
      break;
    case "image/png":
    case "image/x-png":
      imagepng($newImage,$thumb_image);  
      break;
    }
  
  chmod($thumb_image, 0777);
  return $thumb_image;
}

//You do not need to alter these functions
function getHeight($image) {
  $size = getimagesize($image);
  $height = $size[1];
  return $height;
}
//You do not need to alter these functions
function getWidth($image) {
  $size = getimagesize($image);
  $width = $size[0];
  return $width;
}

 $thumb_image_location = $thumb_upload_path.$thumb_image_name.$_SESSION['user_file_ext']; 
 $original_image_location = $upload_path.$original_image_name.$_SESSION['user_file_ext']; 
	pageStart("CCS", NULL, $validationScript, "pprofile", "club-launch", "CCS", $_SESSION['successMessage'], $_SESSION['errorMessage']);

  $updatee_id = $_SESSION['temp_id'];
$extended_url = '';
  if($_GET['edit'] == 'preview'){
      $extended_url =  "?edit=preview";
  }
  // submit step 4
  if(isset($_POST['step4_sub'])){
    
    $member_contract = str_replace("'","\'",str_replace('%', '&#37;', ($_POST['member_contract'])));

      if (!empty($_FILES['club_logo']['name'])) { 
          //Get the file information

            $clubfile_name = $_FILES['club_logo']['name'];
            $clubfile_tmp = $_FILES['club_logo']['tmp_name'];
            $clubfile_size = $_FILES['club_logo']['size'];
            $clubfile_type = $_FILES['club_logo']['type'];
            $filename = basename($_FILES['club_logo']['name']);
            $file_ext = strtolower(substr($filename, strrpos($filename, '.') + 1));
            $_SESSION['extension'] = $file_ext;
             
              $allowedTypes = array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF);
              $detectedType = exif_imagetype($_FILES['club_logo']['tmp_name']);
               if(!in_array($detectedType, $allowedTypes)){
                  $error = 1;
                  $_SESSION['errorMessage'].= "Please upload valid image file.";
                  header("Location: new-club-4.php$extended_url");
                  die;
               }
             
            //Only process if the file is a JPG, PNG or GIF and below the allowed limit
            if((!empty($_FILES["club_logo"])) && ($_FILES['club_logo']['error'] == 0)) {
              
              foreach ($allowed_image_types as $mime_type => $ext) {
                //loop through the specified image types and if they match the extension then break out
                //everything is ok so go and check file size
                if($file_ext==$ext && $clubfile_type==$mime_type){
                  $error = "";
                  break;
                }
              }
              //check if the file size is above the allowed limit
              if ($clubfile_size > ($max_file*1048576)) {
                $_SESSION['errorMessage'].= "Images must be under ".$max_file."MB in size";
                header("Location: new-club-4.php$extended_url");
                die;
              }
              
            }
          
            //Everything is ok, so we can upload the image.
            if (strlen($error)==0){
              
              if (isset($_FILES['club_logo']['name'])){
                //this file could now has an unknown file extension (we hope it's one of the ones set above!)
                if ($_SESSION['user_file_ext'] == "") {
                  $thumb_image_location = $thumb_image_location.".".$file_ext;
                  $original_image_location = $original_image_location.".".$file_ext;
                  //$thumb_image_location = $thumb_image_location.".".$file_ext;
                }  

                move_uploaded_file($clubfile_tmp, $original_image_location);
                chmod($original_image_location, 0777);
                //put the file ext in the session so we know what file to look for once its uploaded
                $_SESSION['user_file_ext']=".".$file_ext;
              
               // move_uploaded_file($clubfile_tmp, $thumb_image_location); 
                chmod($thumb_image_location, 0777);
                //$uploaded = resizeImage($large_image_location, 150, 100, 1);
                $width = getWidth($original_image_location);
                $height = getHeight($original_image_location);
                //Scale the image if it is greater than the width set above
                if ($height > $max_height){
                  $scale = $max_height/$height; 
                 // $uploaded = makeThumbnails($large_image_location, 250, 150);
                  $uploaded = resizeImage($original_image_location, $thumb_image_location, $width,$height,$scale);
                }else{
                  $scale = 1;
                  $uploaded = resizeImage($original_image_location,$thumb_image_location,$width,$height,$scale);
                }
              
              }
              
            }

          }else{
            $thumb_image_location = '';
            $original_image_location = '';
          }
       
         

          $logo_path = str_replace("../", '', $thumb_image_location);
          $original_logo_path = str_replace("../", '', $original_image_location);
          if($_GET['edit'] == 'preview' && $thumb_image_location == ''){ 
              $updateTempCustomer = "UPDATE  temp_customers SET  member_contract = '$member_contract'  WHERE id='$updatee_id'";
          }else{
             $updateTempCustomer = "UPDATE  temp_customers SET logo_path = '$logo_path', original_path = '$original_logo_path', member_contract = '$member_contract'  WHERE id='$updatee_id'";
          }
           
          try
          {
            $update_result = $pdo2->prepare("$updateTempCustomer")->execute();
          }
          catch (PDOException $e)
          {
              $error = 'Error fetching user: ' . $e->getMessage();
              echo $error;
              exit();
          }
        
           
    $_SESSION['successMessage']  = "Step 4 updated successfully !";  
    header("location:new-club-5.php");
    die;
  }

      // edit preview 
  if(isset($_GET['edit']) && $_GET['edit'] == 'preview'){
    
      $selectDetails = "SELECT * from temp_customers WHERE id=".$update_id; 
          try
          {
            $offc_result = $pdo2->prepare("$selectDetails");
            $offc_result->execute();
          }
          catch (PDOException $e)
          {
              $error = 'Error fetching user: ' . $e->getMessage();
              echo $error;
              exit();
          }
           while($clubRow = $offc_result->fetch()){
             $logo_path = $clubRow['logo_path'];
            $member_contract = $clubRow['member_contract'];
           }
  }
?>

<div id='progress'>
 <div id='progressinside4'>
 </div>
</div>
<br />
<div id='progresstext1'>
 1. Personal
</div>
<div id='progresstext2'>
 2. Club details
</div>
<div id='progresstext3'>
 3. Contact details
</div>
<div id='progresstext4'>
 4. Logo & Contract
</div>
<form id="step4" action="" method="POST" enctype="multipart/form-data">
 <div id='mainbox-new-club'>
  <div id='mainboxheader'>
   <center>
    Club logo & contract (optional)
   </center>
  </div>
  <div class='boxcontent'>
   <center>
     <table>
       <tr>
         <td>Upload club logo:</td>
         <td><input type="file" name="club_logo" id="file-upload" class="defaultinput"  accept="image/*"></td>
         <?php  if(!empty($logo_path)){ ?>
            <img src="../<?php echo $logo_path ?>">
         <?php } ?>
       </tr>
       <tr>
          <td><?php echo $lang['club-member-contract']; ?>:</td>
          <td class='txt10pd'><textarea id="contacttext" name="member_contract" placeholder="<?php echo $lang['club-member-contract'];  ?>" class="defaultinput" rows="10"><?php echo $member_contract; ?></textarea></td>
       </tr>
     </table>
   </center>
  </div>
 </div>
</div>
<center><button type="submit" name="step4_sub" class='cta1'>Continue</button></center>
</form>
<script type="text/javascript" src="<?php echo $siteroot; ?>/scripts/tinymce.min.js"></script>
<?php
 displayFooter();