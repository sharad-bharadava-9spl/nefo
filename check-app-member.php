<?php
	// file created by konstant for CCS app requests on 24-02-2022
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings();
	
	require "vendor/autoload.php";
	use Endroid\QrCode\QrCode;
	use Endroid\QrCode\Writer\PngWriter;
	use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
	use Endroid\QrCode\Color\Color;
	use Endroid\QrCode\Logo\Logo;
	use Endroid\QrCode\Label\Label;
	
	$domain = $_SESSION['domain'];

	// Get the user ID
     if (isset($_POST['user_id'])) {
		$user_id = $_POST['user_id'];
	} else if (isset($_GET['user_id'])) {
		$user_id = $_GET['user_id'];
	} else {
		handleError($lang['error-nouserid'],"");
	}

	$search_email = '';
	if(isset($_REQUEST['key'])){
		$search_email = base64_decode($_REQUEST['key']);
	}
    // code update start by konstnat for Task-14980311 on 29-11-2021
	function generateRandomCharecters($length = 20) {
	    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $charactersLength = strlen($characters);
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, $charactersLength - 1)];
	    }
	    return $randomString;
	}

	// create a function to check if qr code exist

	function checkQRcode($qrString){
		global $pdo;
		$domain =  $_SESSION['domain'];
		$insertTime = date("Y-m-d H:i:s");

		$check_status = false;

		$checkQuery = "SELECT qrcode FROM qr_code WHERE qrcode = '$qrString'";

			try
			{
				$result = $pdo->prepare("$checkQuery");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}

			$count = $result->rowCount();

			if($count == 0){
				$insrtQRcode = sprintf("INSERT into qr_code (domain, qrcode, created_at) VALUES ('%s', '%s', '%s');", $domain, $qrString, $insertTime);
				try
				{
					$pdo->prepare("$insrtQRcode")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
				$check_status = true;
			}else{
				$randomQRString = generateRandomCharecters();
				$qrString = $randomQRString;
				checkQRcode($qrString);
			}
		return $qrString;
	}

	$checkAppStatus = "SELECT b.allow_request, b.id, a.fcm_key, a.id  AS member_id FROM members a, app_requests b WHERE a.id = b.member_id AND a.email = '".$search_email."' AND b.club_name = '".$domain."'";

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
			$member_count = $app_results->rowCount();
			$member_row = $app_results->fetch();
				$request_id = $member_row['id'];
				$member_id = $member_row['member_id'];
				$member_request = $member_row['allow_request'];
				$fcm_token = $member_row['fcm_key'];

	// overwrite email and add app request
	if(isset($_POST['oneClick'])){
		$club_email = $_POST['club_email'];
		$app_email = $_POST['app_email'];
		$overWrite = $_POST['overwriteEmail'];	
		
		if($overWrite == 1){
			// overwrite email to club usser
			$updateEmail = "UPDATE users SET email = '$app_email' WHERE user_id = ".$user_id;
				try
				{
					$pdo3->prepare("$updateEmail")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
		}

		// check qr code
		$selectUser = "SELECT qrcode FROM users WHERE user_id = ".$user_id;
		try{
			$user_result = $pdo3->prepare("$selectUser");
			$user_result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		$user_row = $user_result->fetch();
		 $user_qrcode = $user_row['qrcode'];
		 $update_user_qr = "";
		 if($user_qrcode == ''){
				$randomQRString = generateRandomCharecters();
				$unique_qrcode = checkQRcode($randomQRString);
				$update_user_qr = ", qrcode = '$unique_qrcode'";
		 }

				$updateAppMember = "UPDATE users SET app_member = '$member_id' $update_user_qr WHERE user_id = '$user_id'";
				try
				{
					$pdo3->prepare("$updateAppMember")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
					// (B) CREATE QR CODE
				$qr = QrCode::create($unique_qrcode)
				  // (B1) CORRECTION LEVEL
				  ->setErrorCorrectionLevel(new ErrorCorrectionLevelHigh())
				  // (B2) SIZE & MARGIN
				  ->setSize(300)
				  ->setMargin(10)
				  // (B3) COLORS
				  ->setForegroundColor(new Color(0, 0, 0))
				  ->setBackgroundColor(new Color(255, 255, 255));

				// (B4) ATTACH LOGO
				$logo = Logo::create(__DIR__ . "/qr-logo.png")
				  ->setResizeToWidth(120);	

				$writer = new PngWriter();

				if (!file_exists("images/_$domain/qrcodes")) {
			    	mkdir("images/_$domain/qrcodes", 0777, true);
				}

				$QRsavePath = "images/_$domain/qrcodes/" . $user_id . ".png";
				$resultQR = $writer->write($qr, $logo)->saveToFile($QRsavePath);

				// update the app request
				$updateAppRequest = sprintf("UPDATE app_requests SET allow_request = 1 WHERE id = '%d';",  $request_id);
				try
				{
					$pdo->prepare("$updateAppRequest")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}

				// send push notification to user
            $title = 'Club Access Approved!';
            $description = 'You can access the '.$domain.' club now.';
            $image = "";
            $pushToken = $fcm_token;

	          // API access key from Google API's Console
	          define('API_ACCESS_KEY','AAAAeJy7Bm0:APA91bEF1NNNbKfZA9p0vBl0r9S1IT4nZTFodgbdcdv5CQL7PcR_zMqzVuKMQeZ6jLvF6x9DyqMUT3xpzxJbkcd2TSXci6G2S86uLdDVkmVrZs9JL78DcBKyOeXuGSp9mFPeNciDlc0C');
	          $url = 'https://fcm.googleapis.com/fcm/send';
	          $registrationIds = array($pushToken);
	          // prepare the message
	          $message = array( 
	              'title'     => $title,
	              'body'      => $description,
	              'status'    => 0,
	              'create_at' => date('Y-m-d H:i:s')
	          );
	          $fields = array( 
	              'registration_ids' => $registrationIds, 
	              'data'             => $message
	          );
	          $headers = array( 
	              'Authorization: key='.API_ACCESS_KEY, 
	              'Content-Type: application/json'
	          );
	          $ch = curl_init();
	          curl_setopt( $ch,CURLOPT_URL,$url);
	          curl_setopt( $ch,CURLOPT_POST,true);
	          curl_setopt( $ch,CURLOPT_HTTPHEADER,$headers);
	          curl_setopt( $ch,CURLOPT_RETURNTRANSFER,true);
	          curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER,false);
	          curl_setopt( $ch,CURLOPT_POSTFIELDS,json_encode($fields));
	          $result = curl_exec($ch);
	          curl_close($ch);
	          //echo $result;


	       /*insert data in push notification*/
	          $six_digit_random_number = mt_rand(100000, 999999);


	          $pushQuery = "INSERT INTO pushnotification(title,user_id,description,image,status,create_at,unique_num,note_type) VALUES('$title','$user_id','$description','$image','0','".date('Y-m-d H:i:s')."','$six_digit_random_number','1')";
	          $stmt= $pdo3->prepare($pushQuery);
	          $pushinsert = $stmt->execute();


				$_SESSION['successMessage'] = "Request updated succesfully!";
				header("Location: app-requests.php");
				exit();	

	}

   	$memberScript = <<<EOD

		function change_request(id, allow_request, member_id){
			if(confirm('Are you sure to change app request for this member?')){
				window.location = "app-requests.php?request_id=" + id + "&allow="+ allow_request + "&member_id="+member_id;
			}
		}
EOD;


   pageStart("Check App Member", NULL, $memberScript, "pmembership", NULL, "Check App Member", $_SESSION['successMessage'], $_SESSION['errorMessage']);



	 $selectUsers = "SELECT email, app_member FROM users WHERE user_id = ".$user_id;

		try
		{
			$results = $pdo3->prepare("$selectUsers");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		$userCount = $results->rowCount();
		$result_row = $results->fetch();
		$club_email = $result_row['email'];
		$app_member = $result_row['app_member'];


?>
<style type="text/css">
	table.np-table tr td{
		width: auto !important;
	}
	.purchaseNumber{
		width: auto !important;
		font-size: 12px;
		text-transform: none;
	}
</style>
<center>
	<div class="actionbox-np2">
		 <div class="mainboxheader">Registered Emails </div>
		 <div class="boxcontent">
		 	<form action="" method="POST">
			  <table class="np-table">
				   <tr>
					    <td class="biggerFont">Club Email : <span class="purchaseNumber"><?php echo $club_email; ?></span>
					    	<input type="hidden" name="club_email" value="<?php echo $club_email ?>">
					    </td>
					</tr>
					<tr>    
					    <td class="biggerFont">App Registered Email : <span class="purchaseNumber"><?php echo $search_email; ?></span>
					    	<input type="hidden" name="app_email" value="<?php echo $search_email; ?>">
					    </td>
					</tr>
					<tr>    
					    <td class="biggerFont left"><label for="overwriteEmail">Overwrite club db e-mail with the member's app e-mail?</label> <input type="checkbox" name="overwriteEmail" id="overwriteEmail" style="width: 12px; margin-left: 30px;" value="1"></td>
				   </tr>
			  </table>
			  <?php    if($member_request == 1 || !empty($app_member)){ ?>
			  	<span style="font-weight:bold; color: green;">This member has already allowed to access the app !</span>
			 <?php }else{?>
			  	<button class="oneClick cta1nm" name="oneClick" type="submit" style="border: 0;">Allow App Request</button>
			<?php } ?>
			</form>
		</div>
	</div>
</center>

<?php  displayFooter(); ?>