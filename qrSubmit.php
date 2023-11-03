<?php
	// created by konstant for Task-14980311 on 03-12-2021
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';

	require "vendor/autoload.php";
	use Endroid\QrCode\QrCode;
	use Endroid\QrCode\Writer\PngWriter;
	use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
	use Endroid\QrCode\Color\Color;
	use Endroid\QrCode\Logo\Logo;
	use Endroid\QrCode\Label\Label;

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$domain = $_SESSION['domain'];

	// Did this page re-submit with a form? If so, check & store details
	if (isset($_POST['qr_link'])) {

		$qr_link = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['qr_link'])));
		$use_logo = $_POST['use_logo'];


		// (B) CREATE QR CODE
		$qr = QrCode::create($qr_link)
		  // (B1) CORRECTION LEVEL
		  ->setErrorCorrectionLevel(new ErrorCorrectionLevelHigh())
		  // (B2) SIZE & MARGIN
		  ->setSize(300)
		  ->setMargin(10)
		  // (B3) COLORS
		  ->setForegroundColor(new Color(0, 0, 0))
		  ->setBackgroundColor(new Color(255, 255, 255));

		// (B4) ATTACH LOGO
		if($use_logo == "Yes"){
		/*	$logo = Logo::create(__DIR__ . "/qr-logo.png")
		  ->setResizeToWidth(120);*/
			$logo = Logo::create(__DIR__ . "/qr-logo.png");
		}

		// (B5) ATTACH LABEL
		/*$label = Label::create("CODE BOXX")
		  ->setTextColor(new Color(0, 0, 0));*/

		// (C) OUTPUT QR CODE
		$writer = new PngWriter();
		//$result = $writer->write($qr, $logo);
		if (!file_exists("images/_$domain/qrcodes")) {
    		mkdir("images/_$domain/qrcodes", 0777, true);
		}
		
		$QRsavePath = "images/_$domain/qrcodes/menuqr.png";
		if($use_logo == "Yes"){
			$result = $writer->write($qr, $logo)->saveToFile($QRsavePath);
		}else{
			$result = $writer->write($qr)->saveToFile($QRsavePath);
		}
		// On success: redirect.
		$_SESSION['successMessage'] = 'QR code added successfully!';
		
	}
	/***** FORM SUBMIT END *****/


	pageStart("QR Code", NULL, NULL, "newpurchase", "admin", "QR Code", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	
?>
<center>
<div id="mainbox-no-width">
	<div class='boxcontent'>
		<?php 
			$QRImage = '';
			if(count($_POST) > 0){   
					$QRImage = "<img src='images/_$domain/qrcodes/menuqr.png' />";
					echo $QRImage;
			   } 
		 ?>
		 
	</div>
</div>
<br>
<br>
<a class="cta1" href="generateQRcode.php">Generate QR Code</a>
<?php displayFooter(); ?>

