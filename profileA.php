<?php

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
	
	pageStart("QR Code", NULL, $validationScript, "pprofile", "statutes dev-align-center", "QR Code", $_SESSION['successMessage'], $_SESSION['errorMessage']);

	$query = "SELECT id, customer, menuhash FROM db_access WHERE domain = '$domain'";
	try
	{
		$result = $pdo->prepare("$query");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$id = $row['id'];
		$customer = $row['customer'];
		$menuhash = $row['menuhash'];
		
	$link = "https://ccsnube.com/membermenu/index.php?i=$id&h=$menuhash&n=$customer";

	echo $link;
	$qr_link = $link;
	$use_logo = "Yes";


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
		$logo = Logo::create(__DIR__ . "/qr-logo.png")
	  ->setResizeToWidth(120);
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



	// https://ccsnubev2.com/v6/menu/index.php?domain=drgreen&i=192&h=84818