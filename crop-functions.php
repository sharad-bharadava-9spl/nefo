<?php
include_once 'googleConfig.php';

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


	// function to upload the cropped image on server

function cropImage($cropedPhoto, $path, $max_height = 500, $max_width = 500){
		//session_start();

		$bucketName = "ccsnubev2";

		$binary_data = base64_decode( $cropedPhoto );
		
		$imgname =  $path;
		
		// save to server (beware of permissions)
		chmod($imgname, 0777);
		
		$result = file_put_contents( $imgname, $binary_data );
		
		if (!getimagesize($imgname)) {
			$_SESSION['errorMessage'] = $lang['imgError5'];
			header("Location:".$_SERVER['HTTP_REFERER']);
			exit();
		}
		
		if (!$result) die($lang['error-imagesave']);
		
		//$_SESSION['photoextension'] = 'jpg';
		 //$_SESSION['successMessage'] = $lang['picture-updatesuccess'];

		        $width = getWidth($imgname);
                $height = getHeight($imgname);
                //Scale the image if it is greater than the width set above
                if ($height > $max_height){

	                  $scale = $max_height/$height; 
	                 // $uploaded = makeThumbnails($large_image_location, 250, 150);
	                  $uploaded = resizeImage($imgname, $imgname, $width,$height,$scale);
                 
                }
                    $new_width = getWidth($imgname); 
               		$new_height = getHeight($imgname);


                if($new_width > $max_width){
                	 $scale = $max_width/$new_width; 
	                 // $uploaded = makeThumbnails($large_image_location, 250, 150);
	                 $uploaded = resizeImage($imgname, $imgname, $new_width,$new_height,$scale);
                }
                else{
                  $scale = 1;
                  $uploaded = resizeImage($imgname,$imgname,$width,$height,$scale);
                  
                }
                $fileContent = file_get_contents($uploaded);

                if($_SERVER['HTTP_HOST'] == '192.168.0.41' || $_SERVER['HTTP_HOST'] == 'localhost'){
			        $google_root_folder = "local_server/";
			    }else if($_SERVER['HTTP_HOST'] == 'ccsnube.com'){
			        $google_root_folder = "demo_server/";
			    }else{
			        $google_root_folder = "live_server/";
			    }

                $cloudPath = $google_root_folder.$uploaded;
                $isSucceed = uploadFile($bucketName, $fileContent, $cloudPath);
		        if ($isSucceed == true) {
		        	unlink($uploaded);
		            $response['msg'] = 'SUCCESS: to upload ' . $cloudPath . PHP_EOL;
		            // TEST: get object detail (filesize, contentType, updated [date], etc.)
		            $response['data'] = getFileInfo($bucketName, $cloudPath);
		        } else {
		            $response['code'] = "201";
		            $response['msg'] = 'FAILED: to upload ' . $cloudPath . PHP_EOL;
		        }
              return $response;
	}

	function resizeImageScale($path, $max_height = 500, $max_width = 500){

				$width = getWidth($path);
                $height = getHeight($path);
                //Scale the image if it is greater than the width set above
                if ($height > $max_height){

	                  $scale = $max_height/$height; 
	                 // $uploaded = makeThumbnails($large_image_location, 250, 150);
	                  $uploaded = resizeImage($path, $path, $width,$height,$scale);
                 
                }
                    $new_width = getWidth($path); 
               		$new_height = getHeight($path);


                if($new_width > $max_width){
                	 $scale = $max_width/$new_width; 
	                 // $uploaded = makeThumbnails($large_image_location, 250, 150);
	                 $uploaded = resizeImage($path, $path, $new_width,$new_height,$scale);
                }
                else{
                  $scale = 1;
                  $uploaded = resizeImage($path,$path,$width,$height,$scale);
                  
                }
                
                $bucketName = "ccsnubev2";
                $fileContent = file_get_contents($uploaded);

                if($_SERVER['HTTP_HOST'] == '192.168.0.41' || $_SERVER['HTTP_HOST'] == 'localhost'){
			        $google_root_folder = "local_server/";
			    }else if($_SERVER['HTTP_HOST'] == 'ccsnube.com'){
			        $google_root_folder = "demo_server/";
			    }else{
			        $google_root_folder = "live_server/";
			    }
                $cloudPath = $google_root_folder.$uploaded;
                $isSucceed = uploadFile($bucketName, $fileContent, $cloudPath);
		        if ($isSucceed == true) {
		        	unlink($uploaded);
		            $response['msg'] = 'SUCCESS: to upload ' . $cloudPath . PHP_EOL;
		            // TEST: get object detail (filesize, contentType, updated [date], etc.)
		            $response['data'] = getFileInfo($bucketName, $cloudPath);
		        } else {
		            $response['code'] = "201";
		            $response['msg'] = 'FAILED: to upload ' . $cloudPath . PHP_EOL;
		        }
              return $response;
	}