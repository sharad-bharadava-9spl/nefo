<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	$accessLevel = '3';

	//ini_set("display_errors", 'on');
	// Authenticate & authorize
	authorizeUser($accessLevel);
	$video_upload_set_path = "/var/www/html/ccsnubev2_com/v6/Nefos/videos";
	//$video_upload_set_path = "videos";
	$preview_upload_set_path = "/var/www/html/ccsnubev2_com/v6/Nefos/videos/preview_images";
	//$preview_upload_set_path = "videos/preview_images";
	// maxmum upload file size
	//$max_file = 20; // video size
	//$max_file2 = 20; // preview image size

	$allowed_image_types = array('image/pjpeg'=>"jpg",'image/jpeg'=>"jpg",'image/jpg'=>"jpg",'image/pjpeg'=>"jpeg",'image/jpeg'=>"jpeg",'image/jpg'=>"jpeg",'image/png'=>"png",'image/x-png'=>"png",'image/gif'=>"gif");

	$allowed_video_types = array('video/mp4'=>'mp4');

	$video_upload_dir = $video_upload_set_path;     // The directory for the video to be saved in
	if(!is_dir($video_upload_dir)){
	  mkdir($video_upload_dir, 0777);
	}
	$video_upload_dir = $video_upload_set_path; 
	$video_upload_path = $video_upload_dir."/";      
	$video_prefix_en = "help_video_en_";      
	$video_prefix_es = "help_video_es_";      
	$video_prefix_ca = "help_video_ca_";      
	$video_prefix_fr = "help_video_fr_";      
	$video_prefix_nl = "help_video_nl_";      
	$video_prefix_it = "help_video_it_";      
	$video_name_en = $video_prefix_en.strtotime(date('Y-m-d H:i:s'));
	$video_name_es = $video_prefix_es.strtotime(date('Y-m-d H:i:s'));
	$video_name_ca = $video_prefix_ca.strtotime(date('Y-m-d H:i:s'));
	$video_name_fr = $video_prefix_fr.strtotime(date('Y-m-d H:i:s'));
	$video_name_nl = $video_prefix_nl.strtotime(date('Y-m-d H:i:s'));
	$video_name_it = $video_prefix_it.strtotime(date('Y-m-d H:i:s'));
	$video_location_en = $video_upload_path.$video_name_en.$_SESSION['video_file_ext_en']; 	
	$video_location_es = $video_upload_path.$video_name_es.$_SESSION['video_file_ext_es'];  	
	$video_location_ca = $video_upload_path.$video_name_ca.$_SESSION['video_file_ext_ca'];  	
	$video_location_fr = $video_upload_path.$video_name_fr.$_SESSION['video_file_ext_fr'];  	
	$video_location_nl = $video_upload_path.$video_name_nl.$_SESSION['video_file_ext_nl'];  	
	$video_location_it = $video_upload_path.$video_name_it.$_SESSION['video_file_ext_it'];  
	$video_fullname_en = "videos/".$video_name_en.$_SESSION['video_file_ext_en'];
	$video_fullname_es = "videos/".$video_name_es.$_SESSION['video_file_ext_es'];
	$video_fullname_ca = "videos/".$video_name_ca.$_SESSION['video_file_ext_ca'];
	$video_fullname_fr = "videos/".$video_name_fr.$_SESSION['video_file_ext_fr'];
	$video_fullname_nl = "videos/".$video_name_nl.$_SESSION['video_file_ext_nl'];
	$video_fullname_it = "videos/".$video_name_it.$_SESSION['video_file_ext_it'];	

	$preview_upload_dir = $preview_upload_set_path;     // The directory for the preview image to be saved in
	if(!is_dir($preview_upload_dir)){
	  mkdir($preview_upload_dir, 0777);
	}
	$preview_upload_dir = $preview_upload_set_path;
	$preview_upload_path = $preview_upload_dir."/";      
	$preview_prefix = "video_preview_";      
	$preview_name = $preview_prefix.strtotime(date('Y-m-d H:i:s'));
	$preview_location = $preview_upload_path.$preview_name.$_SESSION['preview_file_ext'];
	$preview_fullname =  "videos/preview_images/".$preview_name.$_SESSION['preview_file_ext']; 
	// Did this page re-submit with a form? If so, check & store details
	if (isset($_POST['video_id'])) {
		
	    $video_title_en = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['video_title_en']))); 
	    $video_title_es = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['video_title_es']))); 
	    $video_title_ca = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['video_title_ca']))); 
	    $video_title_fr = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['video_title_fr']))); 
	    $video_title_nl = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['video_title_nl']))); 
	    $video_title_it = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['video_title_it']))); 
		$tag_ids = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['tag_id'])));

		 $tag = implode(",", $_POST['tag_id']); 

		$video_duration_en = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['video_duration_en'])));
		$video_duration_es = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['video_duration_es'])));
		$video_duration_ca = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['video_duration_ca'])));
		$video_duration_fr = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['video_duration_fr'])));
		$video_duration_nl = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['video_duration_nl'])));
		$video_duration_it = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['video_duration_it'])));


		if($video_duration_en == ''){
			$video_duration_en = '0.00';
		}
		if($video_duration_es == ''){
			$video_duration_es = '0.00';
		}
		if($video_duration_ca == ''){
			$video_duration_ca = '0.00';
		}
		if($video_duration_fr == ''){
			$video_duration_fr = '0.00';
		}
		if($video_duration_nl == ''){
			$video_duration_nl = '0.00';
		}
		if($video_duration_it == ''){
			$video_duration_it = '0.00';
		}
		$video_status = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['video_status'])));
		$last_video_path_en = $_POST['last_video_path_en'];
		$last_video_path_es = $_POST['last_video_path_es'];
		$last_video_path_ca = $_POST['last_video_path_ca'];
		$last_video_path_fr = $_POST['last_video_path_fr'];
		$last_video_path_nl = $_POST['last_video_path_nl'];
		$last_video_path_it = $_POST['last_video_path_it'];
		$last_preview_path = $_POST['last_preview_path'];		
		$last_video_name_en = $_POST['last_video_name_en'];
		$last_video_name_es = $_POST['last_video_name_es'];
		$last_video_name_ca = $_POST['last_video_name_ca'];
		$last_video_name_fr = $_POST['last_video_name_fr'];
		$last_video_name_nl = $_POST['last_video_name_nl'];
		$last_video_name_it = $_POST['last_video_name_it'];
		$last_preview_name = $_POST['last_preview_name'];
		$video_id = $_POST['video_id'];

		if(empty($tag) || $tag == ''){
		    $_SESSION['errorMessage'].= "Please choose video tags from the list only !";
            header("Location: edit-video.php?videoid=".$video_id);
            die;
		}
			// english video upload
	      	if (!empty($_FILES['video_file_en']['name'])) { 
	          //Get the file information

		            $video_name_en = $_FILES['video_file_en']['name'];
		            $video_tmp_en = $_FILES['video_file_en']['tmp_name'];
		            $video_size_en = $_FILES['video_file_en']['size'];
		            $video_type_en = $_FILES['video_file_en']['type'];
		            $filename_en = basename($_FILES['video_file_en']['name']);
		            $file_ext_en = strtolower(substr($filename_en, strrpos($filename_en, '.') + 1));
		            $_SESSION['extension_en'] = $file_ext_en;
		            
		            //Only process if the file is a JPG, PNG or GIF and below the allowed limit
		            if((!empty($_FILES["video_file_en"])) && ($_FILES['video_file_en']['error'] == 0)) {
		              
		              foreach ($allowed_video_types as $mime_type => $ext) {
		                //loop through the specified image types and if they match the extension then break out
		                //everything is ok so go and check file size
		                if($file_ext_en==$ext && $video_type_en==$mime_type){
		                  $error = "";
		                  break;
		                }
		              }
		              //check if the file size is above the allowed limit
		             /* if ($video_size > ($max_file*1048576)) {
		                $_SESSION['errorMessage'].= "video must be under ".$max_file."MB in size";
		                header("Location: edit-video.php?videoid=".$video_id);
		                die;
		              }*/
		              
		            }

		            //Everything is ok, so we can upload the image.
		            if (strlen($error)==0){
		              
		              if (isset($_FILES['video_file_en']['name'])){
		                //this file could now has an unknown file extension (we hope it's one of the ones set above!)
		                if ($_SESSION['video_file_ext_en'] == "") {
		                  $video_location_en = $video_location_en.".".$file_ext_en;
		                  $video_fullname_en = $video_fullname_en.".".$file_ext_en;
		                  //$thumb_image_location = $thumb_image_location.".".$file_ext;
		                }     
		                
		                	//put the file ext in the session so we know what file to look for once its uploaded
		               		 $_SESSION['video_file_ext_en']=".".$file_ext_en;
		              
			                move_uploaded_file($video_tmp_en, $video_location_en); 
			                chmod($video_location_en, 0777);
			                unlink($last_video_path_en);
		              }
		              
		            }

	          }else{
	          	 $video_location_en = $last_video_path_en;
	          	 $video_fullname_en = $last_video_name_en;
	        }

	          // spanish video upload
	      	if (!empty($_FILES['video_file_es']['name'])) { 
	          //Get the file information

		            $video_name_es = $_FILES['video_file_es']['name'];
		            $video_tmp_es = $_FILES['video_file_es']['tmp_name'];
		            $video_size_es = $_FILES['video_file_es']['size'];
		            $video_type_es = $_FILES['video_file_es']['type'];
		            $filename_es = basename($_FILES['video_file_es']['name']);
		            $file_ext_es = strtolower(substr($filename_es, strrpos($filename_es, '.') + 1));
		            $_SESSION['extension_es'] = $file_ext_es;
		            
		            //Only process if the file is a JPG, PNG or GIF and below the allowed limit
		            if((!empty($_FILES["video_file_es"])) && ($_FILES['video_file_es']['error'] == 0)) {
		              
		              foreach ($allowed_video_types as $mime_type => $ext) {
		                //loop through the specified image types and if they match the extension then break out
		                //everything is ok so go and check file size
		                if($file_ext_es==$ext && $video_type_es==$mime_type){
		                  $error = "";
		                  break;
		                }
		              }
		              //check if the file size is above the allowed limit
		             /* if ($video_size > ($max_file*1048576)) {
		                $_SESSION['errorMessage'].= "video must be under ".$max_file."MB in size";
		                header("Location: edit-video.php?videoid=".$video_id);
		                die;
		              }*/
		              
		            }

		            //Everything is ok, so we can upload the image.
		            if (strlen($error)==0){
		              
		              if (isset($_FILES['video_file_es']['name'])){
		                //this file could now has an unknown file extension (we hope it's one of the ones set above!)
		                if ($_SESSION['video_file_ext_es'] == "") {
		                  $video_location_es = $video_location_es.".".$file_ext_es;
		                  $video_fullname_es = $video_fullname_es.".".$file_ext_es;
		                  //$thumb_image_location = $thumb_image_location.".".$file_ext;
		                }     
		                
		                	//put the file ext in the session so we know what file to look for once its uploaded
		               		 $_SESSION['video_file_ext_es']=".".$file_ext_es;
		              
			                move_uploaded_file($video_tmp_es, $video_location_es); 
			                chmod($video_location_es, 0777);
			                unlink($last_video_path_es);
		              }
		              
		            }

	          }else{
	          	 $video_location_es = $last_video_path_es;
	          	 $video_fullname_es = $last_video_name_es;
	       }
	       	   // catlan video upload
	      	if (!empty($_FILES['video_file_ca']['name'])) { 
	          //Get the file information

		            $video_name_ca = $_FILES['video_file_ca']['name'];
		            $video_tmp_ca = $_FILES['video_file_ca']['tmp_name'];
		            $video_size_ca = $_FILES['video_file_ca']['size'];
		            $video_type_ca = $_FILES['video_file_ca']['type'];
		            $filename_ca = basename($_FILES['video_file_ca']['name']);
		            $file_ext_ca = strtolower(substr($filename_ca, strrpos($filename_ca, '.') + 1));
		            $_SESSION['extension_ca'] = $file_ext_ca;
		            
		            //Only process if the file is a JPG, PNG or GIF and below the allowed limit
		            if((!empty($_FILES["video_file_ca"])) && ($_FILES['video_file_ca']['error'] == 0)) {
		              
		              foreach ($allowed_video_types as $mime_type => $ext) {
		                //loop through the specified image types and if they match the extension then break out
		                //everything is ok so go and check file size
		                if($file_ext_ca==$ext && $video_type_ca==$mime_type){
		                  $error = "";
		                  break;
		                }
		              }
		              //check if the file size is above the allowed limit
		             /* if ($video_size > ($max_file*1048576)) {
		                $_SESSION['errorMessage'].= "video must be under ".$max_file."MB in size";
		                header("Location: edit-video.php?videoid=".$video_id);
		                die;
		              }*/
		              
		            }

		            //Everything is ok, so we can upload the image.
		            if (strlen($error)==0){
		              
		              if (isset($_FILES['video_file_ca']['name'])){
		                //this file could now has an unknown file extension (we hope it's one of the ones set above!)
		                if ($_SESSION['video_file_ext_ca'] == "") {
		                  $video_location_ca = $video_location_ca.".".$file_ext_ca;
		                  $video_fullname_ca = $video_fullname_ca.".".$file_ext_ca;
		                  //$thumb_image_location = $thumb_image_location.".".$file_ext;
		                }     
		                
		                	//put the file ext in the session so we know what file to look for once its uploaded
		               		 $_SESSION['video_file_ext_ca']=".".$file_ext_ca;
		              
			                move_uploaded_file($video_tmp_ca, $video_location_ca); 
			                chmod($video_location_ca, 0777);
			                unlink($last_video_path_ca);
		              }
		              
		            }

	          }else{
	          	 $video_location_ca = $last_video_path_ca;
	          	 $video_fullname_ca = $last_video_name_ca;
	       }
	       	     // french video upload
	      	if (!empty($_FILES['video_file_fr']['name'])) { 
	          //Get the file information

		            $video_name_fr = $_FILES['video_file_fr']['name'];
		            $video_tmp_fr = $_FILES['video_file_fr']['tmp_name'];
		            $video_size_fr = $_FILES['video_file_fr']['size'];
		            $video_type_fr = $_FILES['video_file_fr']['type'];
		            $filename_fr = basename($_FILES['video_file_fr']['name']);
		            $file_ext_fr = strtolower(substr($filename_fr, strrpos($filename_fr, '.') + 1));
		            $_SESSION['extension_fr'] = $file_ext_fr;
		            
		            //Only process if the file is a JPG, PNG or GIF and below the allowed limit
		            if((!empty($_FILES["video_file_fr"])) && ($_FILES['video_file_fr']['error'] == 0)) {
		              
		              foreach ($allowed_video_types as $mime_type => $ext) {
		                //loop through the specified image types and if they match the extension then break out
		                //everything is ok so go and check file size
		                if($file_ext_fr==$ext && $video_type_fr==$mime_type){
		                  $error = "";
		                  break;
		                }
		              }
		              //check if the file size is above the allowed limit
		             /* if ($video_size > ($max_file*1048576)) {
		                $_SESSION['errorMessage'].= "video must be under ".$max_file."MB in size";
		                header("Location: edit-video.php?videoid=".$video_id);
		                die;
		              }*/
		              
		            }

		            //Everything is ok, so we can upload the image.
		            if (strlen($error)==0){
		              
		              if (isset($_FILES['video_file_fr']['name'])){
		                //this file could now has an unknown file extension (we hope it's one of the ones set above!)
		                if ($_SESSION['video_file_ext_fr'] == "") {
		                  $video_location_fr = $video_location_fr.".".$file_ext_fr;
		                  $video_fullname_fr = $video_fullname_fr.".".$file_ext_fr;
		                  //$thumb_image_location = $thumb_image_location.".".$file_ext;
		                }     
		                
		                	//put the file ext in the session so we know what file to look for once its uploaded
		               		 $_SESSION['video_file_ext_fr']=".".$file_ext_fr;
		              
			                move_uploaded_file($video_tmp_fr, $video_location_fr); 
			                chmod($video_location_fr, 0777);
			                unlink($last_video_path_fr);
		              }
		              
		            }

	          }else{
	          	 $video_location_fr = $last_video_path_fr;
	          	 $video_fullname_fr = $last_video_name_fr;
	       }
	       	   // dutch video upload
	      	if (!empty($_FILES['video_file_nl']['name'])) { 
	          //Get the file information

		            $video_name_nl = $_FILES['video_file_nl']['name'];
		            $video_tmp_nl = $_FILES['video_file_nl']['tmp_name'];
		            $video_size_nl = $_FILES['video_file_nl']['size'];
		            $video_type_nl = $_FILES['video_file_nl']['type'];
		            $filename_nl = basename($_FILES['video_file_nl']['name']);
		            $file_ext_nl = strtolower(substr($filename_nl, strrpos($filename_nl, '.') + 1));
		            $_SESSION['extension_nl'] = $file_ext_nl;
		            
		            //Only process if the file is a JPG, PNG or GIF and below the allowed limit
		            if((!empty($_FILES["video_file_nl"])) && ($_FILES['video_file_nl']['error'] == 0)) {
		              
		              foreach ($allowed_video_types as $mime_type => $ext) {
		                //loop through the specified image types and if they match the extension then break out
		                //everything is ok so go and check file size
		                if($file_ext_nl==$ext && $video_type_nl==$mime_type){
		                  $error = "";
		                  break;
		                }
		              }
		              //check if the file size is above the allowed limit
		             /* if ($video_size > ($max_file*1048576)) {
		                $_SESSION['errorMessage'].= "video must be under ".$max_file."MB in size";
		                header("Location: edit-video.php?videoid=".$video_id);
		                die;
		              }*/
		              
		            }

		            //Everything is ok, so we can upload the image.
		            if (strlen($error)==0){
		              
		              if (isset($_FILES['video_file_nl']['name'])){
		                //this file could now has an unknown file extension (we hope it's one of the ones set above!)
		                if ($_SESSION['video_file_ext_nl'] == "") {
		                  $video_location_nl = $video_location_nl.".".$file_ext_nl;
		                  $video_fullname_nl = $video_fullname_nl.".".$file_ext_nl;
		                  //$thumb_image_location = $thumb_image_location.".".$file_ext;
		                }     
		                
		                	//put the file ext in the session so we know what file to look for once its uploaded
		               		 $_SESSION['video_file_ext_nl']=".".$file_ext_nl;
		              
			                move_uploaded_file($video_tmp_nl, $video_location_nl); 
			                chmod($video_location_nl, 0777);
			                unlink($last_video_path_nl);
		              }
		              
		            }

	          }else{
	          	 $video_location_nl = $last_video_path_nl;
	          	 $video_fullname_nl = $last_video_name_nl;
	       }	       	   
	       // italian video upload
	      	if (!empty($_FILES['video_file_it']['name'])) { 
	          //Get the file information

		            $video_name_it = $_FILES['video_file_it']['name'];
		            $video_tmp_it = $_FILES['video_file_it']['tmp_name'];
		            $video_size_it = $_FILES['video_file_it']['size'];
		            $video_type_it = $_FILES['video_file_it']['type'];
		            $filename_it = basename($_FILES['video_file_it']['name']);
		            $file_ext_it = strtolower(substr($filename_it, strrpos($filename_it, '.') + 1));
		            $_SESSION['extension_it'] = $file_ext_it;
		            
		            //Only process if the file is a JPG, PNG or GIF and below the allowed limit
		            if((!empty($_FILES["video_file_it"])) && ($_FILES['video_file_it']['error'] == 0)) {
		              
		              foreach ($allowed_video_types as $mime_type => $ext) {
		                //loop through the specified image types and if they match the extension then break out
		                //everything is ok so go and check file size
		                if($file_ext_it==$ext && $video_type_it==$mime_type){
		                  $error = "";
		                  break;
		                }
		              }
		              //check if the file size is above the allowed limit
		             /* if ($video_size > ($max_file*1048576)) {
		                $_SESSION['errorMessage'].= "video must be under ".$max_file."MB in size";
		                header("Location: edit-video.php?videoid=".$video_id);
		                die;
		              }*/
		              
		            }

		            //Everything is ok, so we can upload the image.
		            if (strlen($error)==0){
		              
		              if (isset($_FILES['video_file_it']['name'])){
		                //this file could now has an unknown file extension (we hope it's one of the ones set above!)
		                if ($_SESSION['video_file_ext_it'] == "") {
		                  $video_location_it = $video_location_it.".".$file_ext_it;
		                  $video_fullname_it = $video_fullname_it.".".$file_ext_it;
		                  //$thumb_image_location = $thumb_image_location.".".$file_ext;
		                }     
		                
		                	//put the file ext in the session so we know what file to look for once its uploaded
		               		 $_SESSION['video_file_ext_it']=".".$file_ext_it;
		              
			                move_uploaded_file($video_tmp_it, $video_location_it); 
			                chmod($video_location_it, 0777);
			                unlink($last_video_path_it);
		              }
		              
		            }

	          }else{
	          	 $video_location_it = $last_video_path_it;
	          	 $video_fullname_it = $last_video_name_it;
	       }
          // upload preview image
 			if (!empty($_FILES['video_preview']['name'])) { 
	          //Get the file information

		            $preview_name = $_FILES['video_preview']['name'];
		            $preview_tmp = $_FILES['video_preview']['tmp_name'];
		            $preview_size = $_FILES['video_preview']['size'];
		            $preview_type = $_FILES['video_preview']['type'];
		            $preview_filename = basename($_FILES['video_preview']['name']);
		            $preview_file_ext = strtolower(substr($preview_filename, strrpos($preview_filename, '.') + 1));
		            //$_SESSION['extension'] = $file_ext;
		            
		            //Only process if the file is a JPG, PNG or GIF and below the allowed limit
		            if((!empty($_FILES["video_preview"])) && ($_FILES['video_preview']['error'] == 0)) {
		              
		              foreach ($allowed_image_types as $mime_type => $ext) {
		                //loop through the specified image types and if they match the extension then break out
		                //everything is ok so go and check file size
		                if($preview_file_ext==$ext && $preview_type==$mime_type){
		                  $error = "";
		                  break;
		                }
		              }
		              //check if the file size is above the allowed limit
		            /*  if ($preview_size > ($max_file2*1048576)) {
		                $_SESSION['errorMessage'].= "Video preview image must be under ".$max_file2."MB in size";
		                header("Location: edit-video.php?videoid=".$video_id);
		                die;
		              }*/
		              
		            }

		            //Everything is ok, so we can upload the image.
		            if (strlen($error)==0){
		              
		              if (isset($_FILES['video_preview']['name'])){
		                //this file could now has an unknown file extension (we hope it's one of the ones set above!)
		                if ($_SESSION['preview_file_ext'] == "") {
		                  $preview_location = $preview_location.".".$preview_file_ext;
		                  $preview_fullname = $preview_fullname.".".$preview_file_ext;
		                  //$thumb_image_location = $thumb_image_location.".".$file_ext;
		                }     
		                
		                	//put the file ext in the session so we know what file to look for once its uploaded
		               		 $_SESSION['preview_file_ext']=".".$preview_file_ext;
		              
			                move_uploaded_file($preview_tmp, $preview_location); 
			                chmod($preview_location, 0777);
			                unlink($last_preview_path);
		              }
		              
		            }

          }else{
          		$preview_location = $last_preview_path;
          		$preview_fullname = $last_preview_name;
          }


         $updateTime = date("Y-m-d H:i:s");

		 $updateVideo = "UPDATE help_videos SET video_title_en = '$video_title_en', video_title_es = '$video_title_es',video_title_ca = '$video_title_ca',video_title_fr = '$video_title_fr',video_title_nl = '$video_title_nl',video_title_it = '$video_title_it', video_status = '$video_status', tags = '$tag', video_path_en = '$video_fullname_en', video_path_es = '$video_fullname_es', video_path_ca = '$video_fullname_ca', video_path_fr = '$video_fullname_fr', video_path_nl = '$video_fullname_nl', video_path_it = '$video_fullname_it', preview_path = '$preview_fullname',video_duration_en = '$video_duration_en', video_duration_es = '$video_duration_es',video_duration_ca = '$video_duration_ca', video_duration_fr = '$video_duration_fr', video_duration_nl = '$video_duration_nl', video_duration_it = '$video_duration_it', created_at = '$updateTime' WHERE id=$video_id";  

				try
				{
					$result = $pdo3->prepare("$updateVideo")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}	

				$_SESSION['successMessage'] = "Video updated successfully!";
				header("Location: edit-video.php?videoid=".$video_id);
				exit();
	}
	
	/***** FORM SUBMIT END *****/