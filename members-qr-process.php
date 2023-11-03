<?php 
// created by konstant for Task-14980311 on 07-12-2021
// include autoloader
require_once 'vendor/autoload.php';
require_once 'cOnfig/connection.php';

ini_set("log_errors", TRUE);  
  
// setting the logging file in php.ini 
ini_set('error_log', "error.log"); 
// reference the Dompdf namespace
	require "vendor/autoload.php";
	use Endroid\QrCode\QrCode;
	use Endroid\QrCode\Writer\PngWriter;
	use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
	use Endroid\QrCode\Color\Color;
	use Endroid\QrCode\Logo\Logo;
	use Endroid\QrCode\Label\Label;

	$domain = $_SESSION['domain'];

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

$countItem = $_GET['count'];
$count = $_GET['totalCount'];
$page_size = 100;

	// excel count query
if($_GET['count'] == 0){
	$qrCountQuery  =   "SELECT * FROM users WHERE qrcode = '' order by user_id ASC";
   $countResults= $pdo3->prepare("$qrCountQuery");
   $countResults->execute();

	$total_records=$countResults->rowCount();
	if($total_records == 0){
				$_SESSION['errorMessage'] = "No new member found to generate QR code!";
				header("Location: generate-members-qr.php");
				exit();
			}
	

   $count=ceil($total_records/$page_size);  
}

if($_GET['count'] <= $_GET['totalCount']){
		//$offset_var = $countItem * $page_size;
		$offset_var = 0;
	   $selectQR = "SELECT user_id FROM users WHERE qrcode = '' order by user_id ASC limit ".$page_size." OFFSET ".$offset_var;

		try
		{
		   $qr_result = $pdo3->prepare("$selectQR");
		   $qr_result->execute();
		}
		catch (PDOException $e)
		{
			   $error = 'Error fetching user: ' . $e->getMessage();
			   echo $error;
			   exit();
		}

		$qrCount = $qr_result->rowCount();


		while($qr_row = $qr_result->fetch()){

				$user_id = $qr_row['user_id'];

				// check qr code
				$randomQRString = generateRandomCharecters();
				$unique_qrcode = checkQRcode($randomQRString);

				// update qr code
				$updateQR = "UPDATE users SET qrcode = '$unique_qrcode' WHERE user_id=".$user_id;

				try
				{
				   $pdo3->prepare("$updateQR")->execute();
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



		}
		 $countItem++;
		 header('Refresh: 0; members-qr-process.php?count='.$countItem.'&totalCount='.$count.'&redirect=1');
		exit();
	}

$_SESSION['successMessage'] = "QR generated successfully!";
//header("Location: invoice-section.php");

echo "<script>window.location.replace('generate-members-qr.php');</script>";