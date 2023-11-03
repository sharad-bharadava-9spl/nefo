<?php
// created by konstant for notification panel on 28-06-2022
require_once 'cOnfig/connection.php';
require_once 'cOnfig/view.php';
require_once 'cOnfig/authenticate.php';
require_once 'cOnfig/languages/common.php';

session_start();
$accessLevel = '3';
// Authenticate & authorize
authorizeUser($accessLevel);

$created_date = date("Y-m-d H:i:s");
$domain = $_SESSION['domain'];
$max_file = "20"; 
$title = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['title'])));
$content = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['content'])));
$note_type = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['note_type'])));
$upload_dir = "images/_$domain/notifications";     // The directory for the images to be saved in
if(!is_dir($upload_dir)){
  mkdir($upload_dir, 0777);
}
$upload_dir = "images/_$domain/notifications";
$upload_path = $upload_dir."/"; 
$notification_image_name = "notification_".strtotime(date('Y-m-d H:i:s')); 
$image_location = $upload_path.$notification_image_name.$_SESSION['user_file_ext'];
if (!empty($_FILES['note_image']['name'])) { 
  //Get the file information

    $image_name = $_FILES['note_image']['name'];
    $image_tmp = $_FILES['note_image']['tmp_name'];
    $image_size = $_FILES['note_image']['size'];
    $image_type = $_FILES['note_image']['type'];
    $filename = basename($_FILES['note_image']['name']);
    $file_ext = strtolower(substr($filename, strrpos($filename, '.') + 1));
    $_SESSION['extension'] = $file_ext;
     
      $allowedTypes = array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF);
      $detectedType = exif_imagetype($_FILES['note_image']['tmp_name']);
       if(!in_array($detectedType, $allowedTypes)){
          $error = 1;
          $_SESSION['errorMessage'].= "Please upload valid image file.";
          header("Location: notification-send.php");
          die;
       }
     
    //Only process if the file is a JPG, PNG or GIF and below the allowed limit
    if((!empty($_FILES["note_image"])) && ($_FILES['note_image']['error'] == 0)) {
      
      foreach ($allowed_image_types as $mime_type => $ext) {
        //loop through the specified image types and if they match the extension then break out
        //everything is ok so go and check file size
        if($file_ext==$ext && $image_type==$mime_type){
          $error = "";
          break;
        }
      }
      //check if the file size is above the allowed limit
      if ($image_size > ($max_file*1048576)) {
        $_SESSION['errorMessage'].= "Images must be under ".$max_file."MB in size";
        header("Location: notification-send.php");
        die;
      }
      
    }
  
    //Everything is ok, so we can upload the image.
    if (strlen($error)==0){
      
      if (isset($_FILES['note_image']['name'])){
        //this file could now has an unknown file extension (we hope it's one of the ones set above!)
        if ($_SESSION['user_file_ext'] == "") {
          
          $image_location = $image_location.".".$file_ext;
          //$thumb_image_location = $thumb_image_location.".".$file_ext;
        }  

        move_uploaded_file($image_tmp, $image_location);
        chmod($image_location, 0777);
        //put the file ext in the session so we know what file to look for once its uploaded
        $_SESSION['user_file_ext'] = ".".$file_ext;
      
      }
      
    }

  }else{
  	$image_location = "";
  }

	$checkAppStatus = "SELECT b.id, a.fcm_key, a.id  AS member_id FROM members a, app_requests b WHERE a.id = b.member_id AND  b.club_name = '".$domain."' AND b.allow_request = 1";

		try
		{
			$app_results = $pdo->prepare("$checkAppStatus");
			$app_results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		$allTokens = array();
		$member_count = $app_results->rowCount();
    // $member_row = $app_results->fetch();
    // echo "<pre>";
    // print_r($member_row);
    // die();
		while($member_row = $app_results->fetch()){
      $member_id = $member_row['member_id'];
      // check for active users only
      /*$activeUsers = "SELECT currentActive_app, app_member FROM users WHERE currentActive_app = 1 AND app_member = ".$member_id;
      $active_results = $pdo3->prepare("$activeUsers");
      $active_results->execute();
      $active_row = $active_results->fetch();
      $appActive = $active_row['currentActive_app'];
      if($appActive == 1){*/
			   $allTokens[] = $member_row['fcm_key'];
      //}
		}
    
    // API access key from Google API's Console
    define('API_ACCESS_KEY','AAAAeJy7Bm0:APA91bEF1NNNbKfZA9p0vBl0r9S1IT4nZTFodgbdcdv5CQL7PcR_zMqzVuKMQeZ6jLvF6x9DyqMUT3xpzxJbkcd2TSXci6G2S86uLdDVkmVrZs9JL78DcBKyOeXuGSp9mFPeNciDlc0C');
    $url = 'https://fcm.googleapis.com/fcm/send';
    $image_path = "";
    $save_image = "";
    if($image_location != ""){
    	$image_path = $siteroot.$image_location;
    	$save_image = $notification_image_name.".".$file_ext;
	}
    // prepare the message
    $message = array( 
        'title'     => trim($_POST['title']),
        'body'      => trim($_POST['content']),
        'image'     => $image_path,
        'status'    => 0,
        'create_at' => date('Y-m-d H:i:s'),
        'club_name' => $domain
    );
    $fields = array( 
        'registration_ids' => $allTokens, 
        'data'     => $message,
        'notification'     => $message
    );
    $headers = array( 
        'Authorization: key='.API_ACCESS_KEY, 
        'Content-Type: application/json'
    );
	 if(!empty($allTokens)){   
	    $ch = curl_init();
	    curl_setopt( $ch,CURLOPT_URL,$url);
	    curl_setopt( $ch,CURLOPT_POST,true);
	    curl_setopt( $ch,CURLOPT_HTTPHEADER,$headers);
	    curl_setopt( $ch,CURLOPT_RETURNTRANSFER,true);
	    curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER,false);
	    curl_setopt( $ch,CURLOPT_POSTFIELDS,json_encode($fields));
	    $result = curl_exec($ch);
	    curl_close($ch);
	 }   

 /*insert data in push notification*/
    $six_digit_random_number = mt_rand(100000, 999999);


    $pushQuery = "INSERT INTO pushnotification(title,description,image,status,create_at,unique_num, note_type) VALUES('$title','$content','$save_image','0','".date('Y-m-d H:i:s')."','$six_digit_random_number', '$note_type')";
    $stmt= $pdo3->prepare($pushQuery);
    $pushinsert = $stmt->execute();
    if($pushinsert){

       $_SESSION['successMessage'] = "Notification sent successfully!";
       header("Location: app-notifications.php");
       die();
    }else{

        $_SESSION['errorMessage'] = 'Something went wrong, please try again!';
        header("Location: app-notifications.php");
        die();
    }
