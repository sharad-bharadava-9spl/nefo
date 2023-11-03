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
	$accessLevel = '1';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Did this page re-submit with a form? If so, check & store details
	if (isset($_POST['closingMail'])) {
		
		$closingMail = $_POST['closingMail'];
		$minAge = $_POST['minAge'];
		$highRollerWeekly = $_POST['highRollerWeekly'];
		$dispensaryGift = $_POST['dispensaryGift'];
		$barGift = $_POST['barGift'];
		$menuType = $_POST['menuType'];
		$medicalDiscount = $_POST['medicalDiscount'];
		$logouttime = $_POST['logouttime'];
		$logoutredir = $_POST['logoutredir'];
		$dispDonate = $_POST['dispDonate'];
		$dispExpired = $_POST['dispExpired'];
		$dispenseLimit = $_POST['dispenseLimit'];
		$showAge = $_POST['showAge'];
		$showGender = $_POST['showGender'];
		$keepNumber = $_POST['keepNumber'];
		$membershipFees = $_POST['membershipFees'];
		$medicalDiscountPercentage = $_POST['medicalDiscountPercentage'];
		$bankPayments = $_POST['bankPayments'];
		$creditOrDirect = $_POST['creditOrDirect'];
		$visitRegistration = $_POST['visitRegistration'];
		$cropOrNot = $_POST['cropOrNot'];
		$puestosOrNot = $_POST['puestosOrNot'];
		$openAndClose = $_POST['openAndClose'];
		$barMenuType = $_POST['barMenuType'];
		$flowerLimit = $_POST['flowerLimit'];
		$extractLimit = $_POST['extractLimit'];
		$gramLimit = $_POST['gramLimit'];
		$realWeight = $_POST['realWeight'];
		$showStock = $_POST['showStock'];
		$showOrigPrice = $_POST['showOrigPrice'];
		$checkoutDiscount = $_POST['checkoutDiscount'];
		$consumptionMin = $_POST['consumptionMin'];
		$consumptionMax = $_POST['consumptionMax'];
		$showStockBar = $_POST['showStockBar'];
		$showOrigPriceBar = $_POST['showOrigPriceBar'];
		$barTouchscreen = $_POST['barTouchscreen'];
		$iPadReaders = $_POST['iPadReaders'];
		$cashdro = $_POST['cashdro'];
		$creditchange = $_POST['creditchange'];
		$expirychange = $_POST['expirychange'];
		$exentoset = $_POST['exentoset'];
		$menusortdisp = $_POST['menusortdisp'];
		$menusortbar = $_POST['menusortbar'];
		$dispsig = $_POST['dispsig'];
		$barsig = $_POST['barsig'];
		$openmenu = $_POST['openmenu'];
		$keypads = $_POST['keypads'];
		$moneycount = $_POST['moneycount'];
		$customws = $_POST['customws'];
		$negcredit = $_POST['negcredit'];
		$language = $_POST['language'];
		$nobar = $_POST['nobar'];
		$sigtablet = $_POST['sigtablet'];
		$entrysys = $_POST['entrysys'];
		$entrysysstay = $_POST['entrysysstay'];
		$entrysyssecs = $_POST['entrysyssecs'];
		$dooropener = $_POST['dooropener'];
		$cuotaincrement = $_POST['cuotaincrement'];
		$checkoutDiscountBar = $_POST['checkoutDiscountBar'];
		$chipcost = $_POST['chipcost'];
		$fingerprint = $_POST['fingerprint'];
		$pagination = $_POST['pagination'];
		$dooropenfor = $_POST['dooropenfor'];
		$workertracking = $_POST['workertracking'];
		$fullmenu = $_POST['fullmenu'];
		$barfullmenu = $_POST['barfullmenu'];
		$presignup = $_POST['presignup'];
		$signupcode = $_POST['signupcode'];
		$allowvisitors = $_POST['allowvisitors'];
		$flowerLimitPercentage = $_POST['flowerLimitPercentage'];
		$extractLimitPercentage = $_POST['extractLimitPercentage'];
		$fastVisitor = $_POST['fastVisitor'];
		$saldoGift = $_POST['saldoGift'];
		$export_number_format = $_POST['export_number_format'];
		$requiredniandsig = $_POST['requiredniandsig'];
		$currencyoperator = $_POST['currencyoperator'];
		$appointments = $_POST['appointments'];
		$setting3 = $_POST['setting3'];
		$openinghourreg = $_POST['openinghourreg'];
		$closinghourreg = $_POST['closinghourreg'];
		$qrmenu = $_POST['qrmenu'];
		$qrpin = $_POST['qrpin'];
		$qrpincode = $_POST['qrpincode'];
		$showAppPrice = $_POST['showAppPrice'];
		
		if ($requiredniandsig == '') {
			$requiredniandsig = 0;
		}
		if ($barfullmenu == '') {
			$barfullmenu = 0;
		}
		if ($cropOrNot == '') {
			$cropOrNot = 0;
		}
		
			
			
			$domain = $_SESSION['domain'];
			
		
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
		
			$qr_link = str_replace("'","\'",str_replace('%', '&#37;', trim($link)));
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
			

		
		if ($_POST['day1'] == '') {
			$day1 = 0;
		} else {
			$day1 = $_POST['day1'];			
		}
		if ($_POST['day2'] == '') {
			$day2 = 0;
		} else {
			$day2 = $_POST['day2'];			
		}
		if ($_POST['day3'] == '') {
			$day3 = 0;
		} else {
			$day3 = $_POST['day3'];			
		}
		if ($_POST['day4'] == '') {
			$day4 = 0;
		} else {
			$day4 = $_POST['day4'];			
		}
		if ($_POST['day5'] == '') {
			$day5 = 0;
		} else {
			$day5 = $_POST['day5'];			
		}
		if ($_POST['day6'] == '') {
			$day6 = 0;
		} else {
			$day6 = $_POST['day6'];			
		}
		if ($_POST['day7'] == '') {
			$day7 = 0;
		} else {
			$day7 = $_POST['day7'];			
		}
		
		// if Negcredit is set to OFF, reset all members to NOT ELIGIBLE
		if ($negcredit == 1) { // 1 = no
		
			$setIneligible = "UPDATE users SET creditEligible = 0, maxCredit = 0";
			try
			{
				$result = $pdo3->prepare("$setIneligible")->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
			
		}
		
		
		if ($medicalDiscountPercentage > 0) {
			$medicalDiscount = $medicalDiscountPercentage;
			$medicalDiscountPercentage = 1;
		} else if (($medicalDiscountPercentage == '' || $medicalDiscountPercentage == 0) && ($medicalDiscount == '' || $medicalDiscount == 0)) {
			$medicalDiscountPercentage = 0;
			$medicalDiscount = 0;
		} else {
			$medicalDiscountPercentage = 0;
		}

		$updateSettings = sprintf("UPDATE systemsettings SET highRollerWeekly = '%d', closingMail = '%d', minAge = '%d', dispensaryGift = '%d', barGift = '%d', menuType = '%d', medicalDiscount = '%f', logouttime = '%d', logoutredir = '%d', dispDonate = '%d', dispExpired = '%d', dispenseLimit = '%d', showAge = '%d', showGender = '%d', keepNumber = '%d', membershipFees = '%d', medicalDiscountPercentage = '%d', bankPayments = '%d', creditOrDirect = '%d', visitRegistration = '%d', cropOrNot = '%d', puestosOrNot = '%d', openAndClose = '%d', barMenuType = '%d', flowerLimit = '%f', extractLimit = '%f', realWeight = '%d', showStock = '%d', showOrigPrice = '%d', checkoutDiscount = '%d', consumptionMin = '%f', consumptionMax = '%f', showStockBar = '%d', showOrigPriceBar = '%d', barTouchscreen = '%d', iPadReaders = '%d', cashdro = '%d', creditchange = '%d', expirychange = '%d', exentoset = '%d', menusortdisp = '%d', menusortbar = '%d', dispsig = '%d', barsig = '%d', openmenu = '%d', keypads = '%d', moneycount = '%d', customws = '%d', negcredit = '%d', language = '%d', nobar = '%d', sigtablet = '%d', entrysys = '%d', entrysysstay = '%d', entrysyssecs = '%d', dooropener = '%d', cuotaincrement = '%d', checkoutDiscountBar = '%d', chipcost = '%d', fingerprint = '%d', pagination = '%d', dooropenfor = '%d', workertracking = '%d', fullmenu = '%d', barfullmenu = '%d', presignup = '%d', signupcode = '%s', allowvisitors = '%d', flowerLimitPercentage = '%d', extractLimitPercentage = '%d', fastVisitor = '%d', saldoGift = '%d', export_number_format = '%s', requiredniandsig = '%s', currencyoperator = '%s', appointments = '%d', setting3 = '%d', openinghourreg = '%s', closinghourreg = '%s', regday1 = '%d', regday2 = '%d', regday3 = '%d', regday4 = '%d', regday5 = '%d', regday6 = '%d', regday7 = '%d', qrmenu = '%d', qrpin = '%d', qrpincode = '%s', gramLimit = '%f', showprice_option = '%d';",
$highRollerWeekly,
$closingMail,
$minAge,
$dispensaryGift,
$barGift,
$menuType,
$medicalDiscount,
$logouttime,
$logoutredir,
$dispDonate,
$dispExpired,
$dispenseLimit,
$showAge,
$showGender,
$keepNumber,
$membershipFees,
$medicalDiscountPercentage,
$bankPayments,
$creditOrDirect,
$visitRegistration,
$cropOrNot,
$puestosOrNot,
$openAndClose,
$barMenuType,
$flowerLimit,
$extractLimit,
$realWeight,
$showStock,
$showOrigPrice,
$checkoutDiscount,
$consumptionMin,
$consumptionMax,
$showStockBar,
$showOrigPriceBar,
$barTouchscreen,
$iPadReaders,
$cashdro,
$creditchange,
$expirychange,
$exentoset,
$menusortdisp,
$menusortbar,
$dispsig,
$barsig,
$openmenu,
$keypads,
$moneycount,
$customws,
$negcredit,
$language,
$nobar,
$sigtablet,
$entrysys,
$entrysysstay,
$entrysyssecs,
$dooropener,
$cuotaincrement,
$checkoutDiscountBar,
$chipcost,
$fingerprint,
$pagination,
$dooropenfor,
$workertracking,
$fullmenu,
$barfullmenu,
$presignup,
$signupcode,
$allowvisitors,
$flowerLimitPercentage,
$extractLimitPercentage,
$fastVisitor,
$saldoGift,
$export_number_format,
$requiredniandsig,
$currencyoperator,
$appointments,
$setting3,
$openinghourreg,
$closinghourreg,
$day1,
$day2,
$day3,
$day4,
$day5,
$day6,
$day7,
$qrmenu,
$qrpin,
$qrpincode,
$gramLimit,
$showAppPrice
);
		
		try
		{
			$result = $pdo3->prepare("$updateSettings")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		if ($presignup == 2) {
			
			$query = "DELETE FROM signupcodes WHERE domain = '{$_SESSION['domain']}'";
			try
			{
				$result = $pdo->prepare("$query")->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
			
			if ($signupcode == '') {
				
				$query = "UPDATE systemsettings SET signupcode = ''";
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
			
			}
			
		} else if ($signupcode != '') {
			
			// Check if signup code exists.
			$query = "SELECT id FROM signupcodes WHERE code = '$signupcode' AND domain <> '{$_SESSION['domain']}'";
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
				
			if ($id > 0) {
				
				// Code does exist
				$_SESSION['errorMessage'] = $lang['code-exists'] . "<br /><br />";
  				pageStart($lang['system-settings'], NULL, $medicalScript, "pexpenses", "admin", $lang['system-settingsC'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
				exit();
				
			} else {
				
				// Code doesn't exist, save it to systemsettings and mastertable
				
				// Delete from master
				$query = "DELETE FROM signupcodes WHERE domain = '{$_SESSION['domain']}'";
				try
				{
					$result = $pdo->prepare("$query")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
				
				// Add to master
				// Delete from master
				$query = "INSERT INTO signupcodes (domain, code) VALUES ('{$_SESSION['domain']}', '$signupcode')";
				try
				{
					$result = $pdo->prepare("$query")->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
				// Add to systemsettings
				$query = "UPDATE systemsettings SET signupcode = '$signupcode'";
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
				
			}
			
		}
			
		$_SESSION['successMessage'] = $lang['settings-updated'];
		header("Location: admin.php");
		exit();

	}

	$selectSettings = "SELECT highRollerWeekly, minAge, closingMail, dispensaryGift, barGift, menuType, medicalDiscount, logouttime, logoutredir, dispDonate, dispExpired, dispenseLimit, showAge, showGender, keepNumber, membershipFees, medicalDiscountPercentage, bankPayments, creditOrDirect, visitRegistration, cropOrNot, puestosOrNot, openAndClose, barMenuType, flowerLimit, extractLimit, realWeight, showStock, showOrigPrice, checkoutDiscount, consumptionMin, consumptionMax, showStockBar, showOrigPriceBar, barTouchscreen, iPadReaders, cashdro, creditchange, expirychange, exentoset, menusortdisp, menusortbar, dispsig, barsig, openmenu, keypads, moneycount, customws, negcredit, language, nobar, sigtablet, entrysys, entrysysstay, entrysyssecs, dooropener, cuotaincrement, checkoutDiscountBar, chipcost, fingerprint, pagination, dooropenfor, workertracking, fullmenu, barfullmenu, signupcode, presignup, allowvisitors, flowerLimitPercentage, extractLimitPercentage, fastVisitor, saldoGift, export_number_format, requiredniandsig, currencyoperator, appointments, setting3, openinghourreg, closinghourreg, regday1, regday2, regday3, regday4, regday5, regday6, regday7, qrmenu, qrpin, qrpincode, gramLimit, showprice_option FROM systemsettings";
	try
	{
		$result = $pdo3->prepare("$selectSettings");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
  	    $highRollerWeekly = $row['highRollerWeekly'];
  	    $minAge = $row['minAge'];
  	    $closingMail = $row['closingMail'];
  	    $dispensaryGift = $row['dispensaryGift'];
  	    $barGift = $row['barGift'];
  	    $menuType = $row['menuType'];
  	    $medicalDiscount = $row['medicalDiscount'];
		$logouttime = $row['logouttime'];
		$logoutredir = $row['logoutredir'];
		$dispDonate = $row['dispDonate'];
		$dispExpired = $row['dispExpired'];
		$dispenseLimit = $row['dispenseLimit'];
		$showAge = $row['showAge'];
		$showGender = $row['showGender'];
		$keepNumber = $row['keepNumber'];
		$membershipFees = $row['membershipFees'];
		$medicalDiscountPercentage = $row['medicalDiscountPercentage'];
		$bankPayments = $row['bankPayments'];
		$creditOrDirect = $row['creditOrDirect'];
		$visitRegistration  = $row['visitRegistration'];
		$cropOrNot  = $row['cropOrNot'];
		$puestosOrNot  = $row['puestosOrNot'];
		$openAndClose  = $row['openAndClose'];
		$barMenuType  = $row['barMenuType'];
		$flowerLimit  = $row['flowerLimit'];
		$extractLimit  = $row['extractLimit'];
		$realWeight  = $row['realWeight'];
		$showStock  = $row['showStock'];
		$showOrigPrice  = $row['showOrigPrice'];
		$checkoutDiscount  = $row['checkoutDiscount'];
		$consumptionMin  = $row['consumptionMin'];
		$consumptionMax  = $row['consumptionMax'];
		$showStockBar  = $row['showStockBar'];
		$showOrigPriceBar  = $row['showOrigPriceBar'];
		$barTouchscreen  = $row['barTouchscreen'];
		$iPadReaders  = $row['iPadReaders'];
		$cashdro  = $row['cashdro'];
		$creditchange  = $row['creditchange'];
		$expirychange  = $row['expirychange'];
		$exentoset  = $row['exentoset'];
		$menusortdisp  = $row['menusortdisp'];
		$menusortbar  = $row['menusortbar'];
		$dispsig  = $row['dispsig'];
		$barsig  = $row['barsig'];
		$openmenu  = $row['openmenu'];
		$keypads  = $row['keypads'];
		$moneycount  = $row['moneycount'];
		$customws  = $row['customws'];
		$negcredit  = $row['negcredit'];
		$language  = $row['language'];
		$nobar  = $row['nobar'];
		$sigtablet  = $row['sigtablet'];
		$entrysys  = $row['entrysys'];
		$entrysysstay  = $row['entrysysstay'];
		$entrysyssecs  = $row['entrysyssecs'];
		$dooropener  = $row['dooropener'];
		$cuotaincrement  = $row['cuotaincrement'];
		$checkoutDiscountBar  = $row['checkoutDiscountBar'];
		$chipcost  = $row['chipcost'];
		$fingerprint  = $row['fingerprint'];
		$pagination  = $row['pagination'];
		$dooropenfor  = $row['dooropenfor'];
		$workertracking  = $row['workertracking'];
		$fullmenu  = $row['fullmenu'];
		$barfullmenu  = $row['barfullmenu'];
		$presignup  = $row['presignup'];
		$signupcode  = $row['signupcode'];
		$allowvisitors  = $row['allowvisitors'];
		$flowerLimitPercentage  = $row['flowerLimitPercentage'];
		$extractLimitPercentage  = $row['extractLimitPercentage'];
		$fastVisitor  = $row['fastVisitor'];
		$saldoGift  = $row['saldoGift'];
		$export_number_format  = $row['export_number_format'];
		$requiredniandsig  = $row['requiredniandsig'];
		$currencyoperator  = $row['currencyoperator'];
		$appointments  = $row['appointments'];
		$setting3  = $row['setting3'];
		$openinghourreg = $row['openinghourreg'];
		$closinghourreg = $row['closinghourreg'];
		$day1 = $row['regday1'];
		$day2 = $row['regday2'];
		$day3 = $row['regday3'];
		$day4 = $row['regday4'];
		$day5 = $row['regday5'];
		$day6 = $row['regday6'];
		$day7 = $row['regday7'];
		$qrmenu = $row['qrmenu'];
		$qrpin = $row['qrpin'];
		$qrpincode = $row['qrpincode'];
		$gramLimit = $row['gramLimit'];
		$showAppPrice = $row['showprice_option'];
		
		if ($medicalDiscountPercentage == 1) {
			$medicalDiscountPercentage = $medicalDiscount;
			$medicalDiscount = '';
		} else {
			$medicalDiscountPercentage = '';
		}
		
	$medicalScript = <<<EOD
    $(document).ready(function() {
	    
		$('.timepicker').timepicker({
		    showPeriodLabels: false,
			altField: '#timeFull',
			minutes: {
	      	  interval: 15
	    	},
		    hourText: 'Hour',
		    minuteText: 'Min',
		    defaultTime: ''
		});
		
	  $('#registerForm').validate({
		  rules: {
			  pagination: {
				  range: [1,100000]
			  }
    	},
		  errorPlacement: function(error, element) {
			  if ( element.is(":radio") || element.is(":checkbox")){
				 error.appendTo(element.parent());
			} else {
				return true;
			}
		},
    	  submitHandler: function() {
   $(".oneClick").attr("disabled", true);
   form.submit();
	    	  }
	  }); // end validate


	    
	    
$('#medicalDiscount').bind('keypress keyup blur', function() {
  if($(this).val() != ''){
    $('#medicalDiscountPercentage').val('');
  }
});

$('#medicalDiscountPercentage').bind('keypress keyup blur', function() {
  if($(this).val() != ''){
    $('#medicalDiscount').val('');
  }
});

  	$("#setting1").on({
 		"mouseover" : function() {
		 	$("#helpBox1").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox1").css("display", "none");
	  	}
	});
  	$("#setting2").on({
 		"mouseover" : function() {
		 	$("#helpBox2").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox2").css("display", "none");
	  	}
	});
  	$("#setting3").on({
 		"mouseover" : function() {
		 	$("#helpBox3").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox3").css("display", "none");
	  	}
	});
  	$("#setting4").on({
 		"mouseover" : function() {
		 	$("#helpBox4").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox4").css("display", "none");
	  	}
	});
  	$("#setting5").on({
 		"mouseover" : function() {
		 	$("#helpBox5").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox5").css("display", "none");
	  	}
	});
  	$("#setting6").on({
 		"mouseover" : function() {
		 	$("#helpBox6").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox6").css("display", "none");
	  	}
	});
  	$("#setting7").on({
 		"mouseover" : function() {
		 	$("#helpBox7").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox7").css("display", "none");
	  	}
	});
  	$("#setting8").on({
 		"mouseover" : function() {
		 	$("#helpBox8").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox8").css("display", "none");
	  	}
	});
  	$("#setting9").on({
 		"mouseover" : function() {
		 	$("#helpBox9").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox9").css("display", "none");
	  	}
	});
  	$("#setting10").on({
 		"mouseover" : function() {
		 	$("#helpBox10").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox10").css("display", "none");
	  	}
	});
  	$("#setting11").on({
 		"mouseover" : function() {
		 	$("#helpBox11").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox11").css("display", "none");
	  	}
	});
  	$("#setting12").on({
 		"mouseover" : function() {
		 	$("#helpBox12").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox12").css("display", "none");
	  	}
	});
  	$("#setting13").on({
 		"mouseover" : function() {
		 	$("#helpBox13").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox13").css("display", "none");
	  	}
	});
  	$("#setting14").on({
 		"mouseover" : function() {
		 	$("#helpBox14").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox14").css("display", "none");
	  	}
	});
  	$("#setting15").on({
 		"mouseover" : function() {
		 	$("#helpBox15").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox15").css("display", "none");
	  	}
	});
  	$("#setting16").on({
 		"mouseover" : function() {
		 	$("#helpBox16").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox16").css("display", "none");
	  	}
	});
  	$("#setting17").on({
 		"mouseover" : function() {
		 	$("#helpBox17").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox17").css("display", "none");
	  	}
	});
  	$("#setting18").on({
 		"mouseover" : function() {
		 	$("#helpBox18").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox18").css("display", "none");
	  	}
	});
  	$("#setting19").on({
 		"mouseover" : function() {
		 	$("#helpBox19").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox19").css("display", "none");
	  	}
	});
  	$("#setting20").on({
 		"mouseover" : function() {
		 	$("#helpBox20").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox20").css("display", "none");
	  	}
	});
  	$("#setting21").on({
 		"mouseover" : function() {
		 	$("#helpBox21").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox21").css("display", "none");
	  	}
	});
  	$("#setting22").on({
 		"mouseover" : function() {
		 	$("#helpBox22").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox22").css("display", "none");
	  	}
	});
  	$("#setting23").on({
 		"mouseover" : function() {
		 	$("#helpBox23").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox23").css("display", "none");
	  	}
	});
  	$("#setting24").on({
 		"mouseover" : function() {
		 	$("#helpBox24").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox24").css("display", "none");
	  	}
	});
  	$("#setting25").on({
 		"mouseover" : function() {
		 	$("#helpBox25").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox25").css("display", "none");
	  	}
	});
  	$("#setting26").on({
 		"mouseover" : function() {
		 	$("#helpBox26").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox26").css("display", "none");
	  	}
	});
  	$("#setting27").on({
 		"mouseover" : function() {
		 	$("#helpBox27").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox27").css("display", "none");
	  	}
	});
  	$("#setting28").on({
 		"mouseover" : function() {
		 	$("#helpBox28").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox28").css("display", "none");
	  	}
	});
  	$("#setting29").on({
 		"mouseover" : function() {
		 	$("#helpBox29").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox29").css("display", "none");
	  	}
	});
  	$("#setting30").on({
 		"mouseover" : function() {
		 	$("#helpBox30").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox30").css("display", "none");
	  	}
	});
  	$("#setting31").on({
 		"mouseover" : function() {
		 	$("#helpBox31").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox31").css("display", "none");
	  	}
	});
  	$("#setting32").on({
 		"mouseover" : function() {
		 	$("#helpBox32").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox32").css("display", "none");
	  	}
	});
  	$("#setting33").on({
 		"mouseover" : function() {
		 	$("#helpBox33").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox33").css("display", "none");
	  	}
	});
  	$("#setting34").on({
 		"mouseover" : function() {
		 	$("#helpBox34").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox34").css("display", "none");
	  	}
	});
  	$("#setting35").on({
 		"mouseover" : function() {
		 	$("#helpBox35").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox35").css("display", "none");
	  	}
	});
  	$("#setting36").on({
 		"mouseover" : function() {
		 	$("#helpBox36").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox36").css("display", "none");
	  	}
	});
  	$("#setting37").on({
 		"mouseover" : function() {
		 	$("#helpBox37").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox37").css("display", "none");
	  	}
	});
  	$("#setting38").on({
 		"mouseover" : function() {
		 	$("#helpBox38").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox38").css("display", "none");
	  	}
	});
  	$("#setting39").on({
 		"mouseover" : function() {
		 	$("#helpBox39").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox39").css("display", "none");
	  	}
	});
  	$("#setting40").on({
 		"mouseover" : function() {
		 	$("#helpBox40").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox40").css("display", "none");
	  	}
	});
  	$("#setting41").on({
 		"mouseover" : function() {
		 	$("#helpBox41").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox41").css("display", "none");
	  	}
	});
  	$("#setting42").on({
 		"mouseover" : function() {
		 	$("#helpBox42").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox42").css("display", "none");
	  	}
	});
  	$("#setting43").on({
 		"mouseover" : function() {
		 	$("#helpBox43").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox43").css("display", "none");
	  	}
	});
  	$("#setting44").on({
 		"mouseover" : function() {
		 	$("#helpBox44").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox44").css("display", "none");
	  	}
	});
  	$("#setting45").on({
 		"mouseover" : function() {
		 	$("#helpBox45").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox45").css("display", "none");
	  	}
	});
  	$("#setting46").on({
 		"mouseover" : function() {
		 	$("#helpBox46").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox46").css("display", "none");
	  	}
	});
  	$("#setting47").on({
 		"mouseover" : function() {
		 	$("#helpBox47").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox47").css("display", "none");
	  	}
	});
  	$("#setting48").on({
 		"mouseover" : function() {
		 	$("#helpBox48").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox48").css("display", "none");
	  	}
	});
  	$("#setting49").on({
 		"mouseover" : function() {
		 	$("#helpBox49").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox49").css("display", "none");
	  	}
	});
  	$("#setting50").on({
 		"mouseover" : function() {
		 	$("#helpBox50").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox50").css("display", "none");
	  	}
	});
  	$("#setting51").on({
 		"mouseover" : function() {
		 	$("#helpBox51").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox51").css("display", "none");
	  	}
	});
  	$("#setting52").on({
 		"mouseover" : function() {
		 	$("#helpBox52").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox52").css("display", "none");
	  	}
	});
  	$("#setting53").on({
 		"mouseover" : function() {
		 	$("#helpBox53").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox53").css("display", "none");
	  	}
	});
  	$("#setting54").on({
 		"mouseover" : function() {
		 	$("#helpBox54").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox54").css("display", "none");
	  	}
	});
  	$("#setting55").on({
 		"mouseover" : function() {
		 	$("#helpBox55").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox55").css("display", "none");
	  	}
	});
  	$("#setting56").on({
 		"mouseover" : function() {
		 	$("#helpBox56").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox56").css("display", "none");
	  	}
	});
  	$("#setting57").on({
 		"mouseover" : function() {
		 	$("#helpBox57").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox57").css("display", "none");
	  	}
	});
  	$("#setting58").on({
 		"mouseover" : function() {
		 	$("#helpBox58").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox58").css("display", "none");
	  	}
	});
  	$("#setting59").on({
 		"mouseover" : function() {
		 	$("#helpBox59").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox59").css("display", "none");
	  	}
	});
  	$("#setting60").on({
 		"mouseover" : function() {
		 	$("#helpBox60").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox60").css("display", "none");
	  	}
	});
  	$("#setting61").on({
 		"mouseover" : function() {
		 	$("#helpBox61").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox61").css("display", "none");
	  	}
	});
  	$("#setting62").on({
 		"mouseover" : function() {
		 	$("#helpBox62").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox62").css("display", "none");
	  	}
	});
  	$("#setting63").on({
 		"mouseover" : function() {
		 	$("#helpBox63").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox63").css("display", "none");
	  	}
	});  	
	$("#setting64").on({
 		"mouseover" : function() {
		 	$("#helpBox64").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox64").css("display", "none");
	  	}
	});
	$("#setting65").on({
 		"mouseover" : function() {
		 	$("#helpBox65").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox65").css("display", "none");
	  	}
	});
	$("#setting66").on({
 		"mouseover" : function() {
		 	$("#helpBox66").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox66").css("display", "none");
	  	}
	});
	$("#setting67").on({
 		"mouseover" : function() {
		 	$("#helpBox67").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox67").css("display", "none");
	  	}
	});
	$("#setting68").on({
 		"mouseover" : function() {
		 	$("#helpBox68").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox68").css("display", "none");
	  	}
	});
	$("#setting69").on({
 		"mouseover" : function() {
		 	$("#helpBox69").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox69").css("display", "none");
	  	}
	});
	$("#setting70").on({
 		"mouseover" : function() {
		 	$("#helpBox70").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox70").css("display", "none");
	  	}
	});
	$("#setting71").on({
 		"mouseover" : function() {
		 	$("#helpBox71").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox71").css("display", "none");
	  	}
	});	
	$("#setting72").on({
 		"mouseover" : function() {
		 	$("#helpBox72").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox72").css("display", "none");
	  	}
	});	
	$("#setting73").on({
 		"mouseover" : function() {
		 	$("#helpBox73").css("display", "block");
  		},
  		"mouseout" : function() {
		 	$("#helpBox73").css("display", "none");
	  	}
	});
	
});
EOD;

  	pageStart($lang['system-settings'], NULL, $medicalScript, "settings", "admin", $lang['system-settingsC'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
  	
?>
<form id="registerForm" action="" method="POST" >
<center>
<div class="actionbox-np2">
 <div class='mainboxheader'>
 <img src='images/settings-reception.png' style='margin-bottom: -4px; margin-right: 10px;' /><?php echo $lang['reception']; ?>
 </div>
 <div class='boxcontent'>
	 <table class="settingstable">
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='padding: 1px;' id="setting39" />&nbsp;&nbsp;<?php echo $lang['credit-change']; ?><div id='helpBox39' class='helpBox'><?php echo $lang['ss-help39']; ?></div></td>
	    <td class='left'><input type="radio" name="creditchange" value="1" <?php if ($creditchange == 1) { echo 'checked'; } ?>><?php echo $lang['global-yes']; ?></input></td>
	    <td class='left'><input type="radio" name="creditchange" value="0" <?php if ($creditchange == 0) { echo 'checked'; } ?>><?php echo $lang['global-no']; ?></input></td>
	    <td class='left'></td>
	   </tr>
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='padding: 1px;' id="setting40" />&nbsp;&nbsp;<?php echo $lang['expiry-change']; ?><div id='helpBox40' class='helpBox'><?php echo $lang['ss-help40']; ?></div></td>
	    <td class='left'><input type="radio" name="expirychange" value="1" <?php if ($expirychange == 1) { echo 'checked'; } ?>><?php echo $lang['global-yes']; ?></input></td>
	    <td class='left'><input type="radio" name="expirychange" value="0" <?php if ($expirychange == 0) { echo 'checked'; } ?>><?php echo $lang['global-no']; ?></input></td>
	    <td class='left'></td>
	   </tr>
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='padding: 1px;' id="setting41" />&nbsp;&nbsp;<?php echo $lang['negative-credit']; ?><div id='helpBox41' class='helpBox'><?php echo $lang['ss-help41']; ?></div></td>
	    <td class='left'><input type="radio" name="negcredit" value="0" <?php if ($negcredit == 0) { echo 'checked'; } ?>><?php echo $lang['global-yes']; ?></input></td>
	    <td class='left'><input type="radio" name="negcredit" value="1" <?php if ($negcredit == 1) { echo 'checked'; } ?>><?php echo $lang['global-no']; ?></input></td>
	    <td class='left'></td>
	   </tr>
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='padding: 1px;' id="setting59" />&nbsp;&nbsp;<?php echo $lang['credit-gift']; ?><div id='helpBox59' class='helpBox'><?php echo $lang['ss-help59']; ?></div></td>
	    <td class='left'><input type="radio" name="saldoGift" value="1" <?php if ($saldoGift == 1) { echo 'checked'; } ?>><?php echo $lang['global-yes']; ?></input></td>
	    <td class='left'><input type="radio" name="saldoGift" value="0" <?php if ($saldoGift == 0) { echo 'checked'; } ?>><?php echo $lang['global-no']; ?></input></td>
	    <td class='left'></td>
	   </tr>
	  </table>
	 </div>
	</div>
	<br />
<div class="actionbox-np2">
 <div class='mainboxheader'>
 <img src='images/settings-dispensary.png' style='margin-bottom: -7px; margin-right: 10px;' /><?php echo $lang['dispensary']; ?>
 </div>
 <div class='boxcontent'>
	 <table class="settingstable">
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting5" />&nbsp;&nbsp;<?php echo $lang['dispensary-menu']; ?><div id='helpBox5' class='helpBox'><?php echo $lang['ss-help5']; ?></div></td>
	    <td class='left'><input type="radio" name="menuType" value="0" <?php if ($menuType == 0) { echo 'checked'; } ?>><?php echo $lang['normal']; ?></input></td>
	    <td class='left'><input type="radio" name="menuType" value="1" <?php if ($menuType == 1) { echo 'checked'; } ?>><?php echo $lang['list-only']; ?></input></td>
	    <td class='left'></td>
	   </tr>
<!--	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting57" />&nbsp;&nbsp;<?php echo $lang['product-display']; ?><div id='helpBox57' class='helpBox'><?php echo $lang['ss-help57']; ?></div></td>
	    <td class='left'><input type="radio" name="fullmenu" value="0" <?php if ($fullmenu == 0) { echo 'checked'; } ?>><?php echo $lang['minimized']; ?></input></td>
	    <td class='left'><input type="radio" name="fullmenu" value="1" <?php if ($fullmenu == 1) { echo 'checked'; } ?>><?php echo $lang['maximized']; ?></input></td>
	    <td class='left'></td>
	   </tr>-->
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting1" />&nbsp;&nbsp;<?php echo $lang['dispensary-gift']; ?><div id='helpBox1' class='helpBox'><?php echo $lang['ss-help1']; ?></div></td>
	    <td class='left'><input type="radio" name="dispensaryGift" value="1" <?php if ($dispensaryGift == 1) { echo 'checked'; } ?>><?php echo $lang['global-yes']; ?></input></td>
	    <td class='left'><input type="radio" name="dispensaryGift" value="0" <?php if ($dispensaryGift == 0) { echo 'checked'; } ?>><?php echo $lang['global-no']; ?></input></td>
	    <td class='left'></td>
	   </tr>
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting3" />&nbsp;&nbsp;<?php echo $lang['donate-dispensary']; ?><div id='helpBox3' class='helpBox'><?php echo $lang['ss-help3']; ?></div></td>
	    <td class='left'><input type="radio" name="dispDonate" value="1" <?php if ($dispDonate == 1) { echo 'checked'; } ?>><?php echo $lang['global-yes']; ?></input></td>
	    <td class='left'><input type="radio" name="dispDonate" value="0" <?php if ($dispDonate == 0) { echo 'checked'; } ?>><?php echo $lang['global-no']; ?></input></td>
	    <td class='left'></td>
	   </tr>
	   <tr> 
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting4" />&nbsp;&nbsp;<?php echo $lang['dispense-expired']; ?><div id='helpBox4' class='helpBox'><?php echo $lang['ss-help4']; ?></div></td>
	    <td class='left'><input type="radio" name="dispExpired" value="1" <?php if ($dispExpired == 1) { echo 'checked'; } ?>><?php echo $lang['global-yes']; ?></input></td>
	    <td class='left'><input type="radio" name="dispExpired" value="0" <?php if ($dispExpired == 0) { echo 'checked'; } ?>><?php echo $lang['global-no']; ?></input></td>
	    <td class='left'></td>
	   </tr>
	   <tr> 
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting26" />&nbsp;&nbsp;<?php echo $lang['ss-showstock']; ?><div id='helpBox26' class='helpBox'><?php echo $lang['ss-help26']; ?></div></td>
	    <td class='left'><input type="radio" name="showStock" value="1" <?php if ($showStock == 1) { echo 'checked'; } ?>><?php echo $lang['global-yes']; ?></input></td>
	    <td class='left'><input type="radio" name="showStock" value="0" <?php if ($showStock == 0) { echo 'checked'; } ?>><?php echo $lang['global-no']; ?></input></td>
	    <td class='left'></td>
	   </tr>
	   <tr> 
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting27" />&nbsp;&nbsp;<?php echo $lang['ss-showorigprice']; ?><div id='helpBox27' class='helpBox'><?php echo $lang['ss-help27']; ?></div></td>
	    <td class='left'><input type="radio" name="showOrigPrice" value="1" <?php if ($showOrigPrice == 1) { echo 'checked'; } ?>><?php echo $lang['global-yes']; ?></input></td>
	    <td class='left'><input type="radio" name="showOrigPrice" value="0" <?php if ($showOrigPrice == 0) { echo 'checked'; } ?>><?php echo $lang['global-no']; ?></input></td>
	    <td class='left'></td>
	   </tr>
	   <tr> 
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting28" />&nbsp;&nbsp;<?php echo $lang['ss-checkout-discount']; ?><div id='helpBox28' class='helpBox'><?php echo $lang['ss-help28']; ?></div></td>
	    <td class='left'><input type="radio" name="checkoutDiscount" value="1" <?php if ($checkoutDiscount == 1) { echo 'checked'; } ?>><?php echo $lang['global-yes']; ?></input></td>
	    <td class='left'><input type="radio" name="checkoutDiscount" value="0" <?php if ($checkoutDiscount == 0) { echo 'checked'; } ?>><?php echo $lang['global-no']; ?></input></td>
	    <td class='left'></td>
	   </tr>
	   <tr> 
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting38" />&nbsp;&nbsp;<?php echo $lang['ss-touchscreendispensary']; ?><div id='helpBox38' class='helpBox'><?php echo $lang['ss-help38']; ?></div></td>
	    <td class='left'><input type="radio" name="keypads" value="1" <?php if ($keypads == 1) { echo 'checked'; } ?>><?php echo $lang['global-yes']; ?></input></td>
	    <td class='left'><input type="radio" name="keypads" value="0" <?php if ($keypads == 0) { echo 'checked'; } ?>><?php echo $lang['global-no']; ?></input></td>
	    <td class='left'></td>
	   </tr>
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting29" />&nbsp;&nbsp;<?php echo $lang['ss-consumption-limit']; ?><div id='helpBox29' class='helpBox'><?php echo $lang['ss-help29']; ?></div></td>
	    <td class='left'>&nbsp;&nbsp;&nbsp;&nbsp;<input type="number" name="consumptionMin" class="fourDigit defaultinput-no-margin" value="<?php echo $consumptionMin; ?>" /> g. min.</td>
	    <td class='left'>&nbsp;&nbsp;&nbsp;&nbsp;<input type="number" name="consumptionMax" class="fourDigit defaultinput-no-margin" value="<?php echo $consumptionMax; ?>" /> g. max.</td>
	    <td class='left'></td>
	    </td>
	   </tr>
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting6" />&nbsp;&nbsp;<?php echo $lang['max-dispense-limit']; ?><div id='helpBox6' class='helpBox'><?php echo $lang['ss-help6']; ?></div></td>
	    <td colspan='3' class='left'>&nbsp;&nbsp;&nbsp;&nbsp;<input type="number" name="dispenseLimit" class="fourDigit defaultinput-no-margin" value="<?php echo $dispenseLimit; ?>" /> <?php echo $_SESSION['currencyoperator'] ?></td>
	   </tr>
	   <!--<tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting44" />&nbsp;&nbsp;<?php echo $lang['stock-warning']; ?><div id='helpBox44' class='helpBox'><?php echo $lang['ss-help44']; ?></div></td>
	    <td colspan='3' class='left'>&nbsp;&nbsp;&nbsp;&nbsp;<input type="number" name="dispenseLimit" class="fourDigit" value="<?php echo $dispenseLimit; ?>" /> $_SESSION['currencyoperator']</td>
	   </tr>-->
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting45" />&nbsp;&nbsp;<?php echo $lang['sort-order']; ?><div id='helpBox45' class='helpBox'><?php echo $lang['ss-help45']; ?></div></td>
	    <td class='left'><input type="radio" name="menusortdisp" value="0" <?php if ($menusortdisp == 0) { echo 'checked'; } ?>><?php echo $lang['by-price']; ?></input></td>
	    <td class='left'><input type="radio" name="menusortdisp" value="1" <?php if ($menusortdisp == 1) { echo 'checked'; } ?>><?php echo $lang['alphabetical']; ?></input></td>
	    <td class='left'></td>
	   </tr>
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting68" />&nbsp;&nbsp;<?php echo $lang['pre-orders']; ?><div id='helpBox68' class='helpBox'><?php echo $lang['ss-help68']; ?></div></td>
	    <td class='left'><input type="radio" name="setting3" value="1" <?php if ($setting3 == 1) { echo 'checked'; } ?>><?php echo $lang['global-yes']; ?></input></td>
	    <td class='left'><input type="radio" name="setting3" value="2" <?php if ($setting3 == 2) { echo 'checked'; } ?>><?php echo $lang['global-no']; ?></input></td>
	    <td class='left'></td>
	   </tr>
	   <tr>
	    <td class='left'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting46" />&nbsp;&nbsp;<?php echo $lang['require-signature']; ?><div id='helpBox46' class='helpBox'><?php echo $lang['ss-help46']; ?></div></td>
	    <td class='left'><input type="radio" name="dispsig" value="0" <?php if ($dispsig == 0) { echo 'checked'; } ?>><?php echo $lang['keyfob']; ?></input></td>
<?php
	if ($_SESSION['domain'] == 'california' || $_SESSION['domain'] == 'casper' || $_SESSION['domain'] == 'crystal' || $_SESSION['domain'] == 'cremeclub' || $_SESSION['domain'] == 'drgreen' || $_SESSION['domain'] == 'granvalle' || $_SESSION['domain'] == 'manoverde' || $_SESSION['domain'] == 'rafiki') {
?>
	    <td class='left'><input type="radio" name="dispsig" value="1" <?php if ($dispsig == 1) { echo 'checked'; } ?>>Topaz tablet</input></td>
<?php } else { ?>
	    <td class='left'><input type="radio" style='visibility: hidden;' name="dispsig" value="1" <?php if ($dispsig == 1) { echo 'checked'; } ?>><span style='visibility: hidden;'>Topaz tablet</span></input></td>
<?php } ?>
	    <td class='left'><input type="radio" name="dispsig" value="2" <?php if ($dispsig == 2) { echo 'checked'; } ?>><?php echo $lang['none']; ?></input></td>
	   </tr>
   
	   <tr> 
	    <td class='left' style='position: relative; border-left: 1px solid #656a66; border-top: 1px solid #656a66;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting25" />&nbsp;&nbsp;<?php echo $lang['enable-real-weight']; ?><div id='helpBox25' class='helpBox'><?php echo $lang['ss-help25']; ?></div></td>
	    <td class='left' style='border-top: 1px solid #656a66;'><input type="radio" name="realWeight" value="1" <?php if ($realWeight == 1) { echo 'checked'; } ?>><?php echo $lang['global-yes']; ?></input></td>
	    <td class='left' style='border-top: 1px solid #656a66;'><input type="radio" name="realWeight" value="0" <?php if ($realWeight == 0) { echo 'checked'; } ?>><?php echo $lang['global-no']; ?></input></td>
	    <td class='left' style='border-top: 1px solid #656a66; border-right: 1px solid #656a66;'></td>
	   </tr>
	   <tr>
	    <td class='left' style='position: relative; border-left: 1px solid #656a66;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting23" />&nbsp;&nbsp;<?php echo $lang['flower-limit']; ?><div id='helpBox23' class='helpBox'><?php echo $lang['ss-help23']; ?></div></td>
	    <td colspan='3' class='left' style='border-right: 1px solid #656a66;'>&nbsp;&nbsp;&nbsp;&nbsp;<input type="number" name="flowerLimit" class="fourDigit defaultinput-no-margin" value="<?php echo $flowerLimit; ?>" step="0.01" /> g.</td>
	   </tr>
	   <tr>
	    <td class='left' style='border-left: 1px solid #656a66; border-bottom: 1px solid #656a66;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting24" />&nbsp;&nbsp;<?php echo $lang['extract-limit']; ?><div id='helpBox24' class='helpBox'><?php echo $lang['ss-help24']; ?></div></td>
	    <td colspan='3' class='left' style='border-right: 1px solid #656a66; border-bottom: 1px solid #656a66;'>&nbsp;&nbsp;&nbsp;&nbsp;<input type="number" name="extractLimit" class="fourDigit defaultinput-no-margin" value="<?php echo $extractLimit; ?>" step="0.01" /> g.</td>
	   </tr>	   
	   <tr>
	    <td class='left' style='border-left: 1px solid #656a66; border-bottom: 1px solid #656a66;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting72" />&nbsp;&nbsp; Extra quantity allowed for other categories<div id='helpBox72' class='helpBox'><?php echo $lang['ss-help72']; ?></div></td>
	    <td colspan='3' class='left' style='border-right: 1px solid #656a66; border-bottom: 1px solid #656a66;'>&nbsp;&nbsp;&nbsp;&nbsp;<input type="number" name="gramLimit" class="fourDigit defaultinput-no-margin" value="<?php echo $gramLimit; ?>" step="0.01" /> g.</td>
	   </tr>
	   <tr>
	    <td class='left' style='position: relative;'id="setting71" ><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' />&nbsp;&nbsp;<?php echo $lang['qrmenu']; ?><sup style='color: red; font-size: 13px; font-weight: 600;'> <?php echo $lang['new-normal']; ?></sup><div id='helpBox71' class='helpBox'><?php echo $lang['ss-help71']; ?></div></td>
	    <td class='left' style='vertical-align: top;'>
	     <input type="radio" name="qrmenu" value="2" <?php if ($qrmenu == 2) { echo 'checked'; } ?>><?php echo $lang['with-prices']; ?></input><br /><br />
	     <input type="radio" name="qrpin" value="0" style='margin-bottom: -5px;' <?php if ($qrpin == 0) { echo 'checked'; } ?>><?php echo $lang['no-pin']; ?></input><br /><br /><br /><br />
<?php if ($qrmenu == 0) { ?>
	     <a href='generate-qr.php' target='_blank' style='margin: 0; padding: 2px 5px; width: initial; font-size: 14px;' class='cta1' onclick="document.getElementById('registerForm').submit();"><?php echo $lang['create-code']; ?></a>
<?php } else { ?>
	     <a href='view-qr.php' target='_blank' style='margin: 0; padding: 2px 5px; width: initial; font-size: 14px;' class='cta1' onclick="document.getElementById('registerForm').submit();"><?php echo $lang['view-code']; ?></a>
<?php } ?>
	    </td>
	    <td class='left' style='vertical-align: top;'>
	     <input type="radio" name="qrmenu" value="1" <?php if ($qrmenu == 1) { echo 'checked'; } ?>><?php echo $lang['without-prices']; ?></input><br /><br />
	    <input type="radio" name="qrpin" value="1" <?php if ($qrpin == 1) { echo 'checked'; } ?>><?php echo $lang['with-pin']; ?>:</input><br /><input type="text" name="qrpincode" id="qrpincode" class="fourDigit defaultinput-no-margin" value="<?php echo $qrpincode; ?>" style='margin-left: 0; margin-top: 8px;' /></td>
	    <td class='left' style='vertical-align: top;'>
	     <input type="radio" name="qrmenu" value="0" <?php if ($qrmenu == 0) { echo 'checked'; } ?>><?php echo $lang['global-no']; ?></input><br /><br />
	    </td>
	   </tr>

	  </table>
	 </div>
	</div>
	<br />
<div class="actionbox-np2">
 <div class='mainboxheader'>
 <img src='images/settings-bar.png' style='margin-bottom: -8px; margin-right: 10px;' /><?php echo $lang['bar']; ?>
 </div>
 <div class='boxcontent'>
	 <table class="settingstable">
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting2" />&nbsp;&nbsp;<?php echo $lang['bar-gift']; ?><div id='helpBox2' class='helpBox'><?php echo $lang['ss-help2']; ?></div></td>
	    <td class='left'><input type="radio" name="barGift" value="1" <?php if ($barGift == 1) { echo 'checked'; } ?>><?php echo $lang['global-yes']; ?></input></td>
	    <td class='left'><input type="radio" name="barGift" value="0" <?php if ($barGift == 0) { echo 'checked'; } ?>><?php echo $lang['global-no']; ?></input></td>
	    <td class='left'></td>
	   </tr>
<!--	   <tr>
		    <td class="left" style="position: relative;"><img src="images/questionmark-new.png" style="margin-bottom: -1px;" width="15" id="setting64">&nbsp;&nbsp;<?php echo $lang['product-display']; ?><div id="helpBox64" class="helpBox"><?php echo $lang['ss-help57']; ?></div></td>
		    <td class="left"><input type="radio" name="barfullmenu" value="0" <?php if ($barfullmenu == 0) { echo 'checked'; } ?>>Minimized</td>
		    <td class="left"><input type="radio" name="barfullmenu" value="1" <?php if ($barfullmenu == 1) { echo 'checked'; } ?>>Maximized</td>
		    <td class="left"></td>
		</tr>-->
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting22" />&nbsp;&nbsp;<?php echo $lang['bar-menu']; ?><div id='helpBox22' class='helpBox'><?php echo $lang['ss-help22']; ?></div></td>
	    <td class='left'><input type="radio" name="barMenuType" value="0" <?php if ($barMenuType == 0) { echo 'checked'; } ?>><?php echo $lang['normal']; ?></input></td>
	    <td class='left'><input type="radio" name="barMenuType" value="1" <?php if ($barMenuType == 1) { echo 'checked'; } ?>><?php echo $lang['list-only']; ?></input></td>
	    <td class='left'></td>
	   </tr>
	   <tr> 
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting30" />&nbsp;&nbsp;<?php echo $lang['ss-showstockbar']; ?><div id='helpBox30' class='helpBox'><?php echo $lang['ss-help30']; ?></div></td>
	    <td class='left'><input type="radio" name="showStockBar" value="1" <?php if ($showStockBar == 1) { echo 'checked'; } ?>><?php echo $lang['global-yes']; ?></input></td>
	    <td class='left'><input type="radio" name="showStockBar" value="0" <?php if ($showStockBar == 0) { echo 'checked'; } ?>><?php echo $lang['global-no']; ?></input></td>
	    <td class='left'></td>
	   </tr>
	   <tr> 
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting31" />&nbsp;&nbsp;<?php echo $lang['ss-showorigpricebar']; ?><div id='helpBox31' class='helpBox'><?php echo $lang['ss-help31']; ?></div></td>
	    <td class='left'><input type="radio" name="showOrigPriceBar" value="1" <?php if ($showOrigPriceBar == 1) { echo 'checked'; } ?>><?php echo $lang['global-yes']; ?></input></td>
	    <td class='left'><input type="radio" name="showOrigPriceBar" value="0" <?php if ($showOrigPriceBar == 0) { echo 'checked'; } ?>><?php echo $lang['global-no']; ?></input></td>
	    <td class='left'></td>
	   </tr>
	   <tr> 
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting37" />&nbsp;&nbsp;<?php echo $lang['ss-checkout-discount']; ?><div id='helpBox37' class='helpBox'><?php echo $lang['ss-help28']; ?></div></td>
	    <td class='left'><input type="radio" name="checkoutDiscountBar" value="1" <?php if ($checkoutDiscountBar == 1) { echo 'checked'; } ?>><?php echo $lang['global-yes']; ?></input></td>
	    <td class='left'><input type="radio" name="checkoutDiscountBar" value="0" <?php if ($checkoutDiscountBar == 0) { echo 'checked'; } ?>><?php echo $lang['global-no']; ?></input></td>
	    <td class='left'></td>
	   </tr>
	   
	   <tr> 
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting32" />&nbsp;&nbsp;<?php echo $lang['ss-touchscreenbar']; ?><div id='helpBox32' class='helpBox'><?php echo $lang['ss-help32']; ?></div></td>
	    <td class='left'><input type="radio" name="barTouchscreen" value="1" <?php if ($barTouchscreen == 1) { echo 'checked'; } ?>><?php echo $lang['global-yes']; ?></input></td>
	    <td class='left'><input type="radio" name="barTouchscreen" value="0" <?php if ($barTouchscreen == 0) { echo 'checked'; } ?>><?php echo $lang['global-no']; ?></input></td>
	    <td class='left'></td>
	   </tr>
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting47" />&nbsp;&nbsp;<?php echo $lang['sort-order']; ?><div id='helpBox47' class='helpBox'><?php echo $lang['ss-help47']; ?></div></td>
	    <td class='left'><input type="radio" name="menusortbar" value="0" <?php if ($menusortbar == 0) { echo 'checked'; } ?>><?php echo $lang['by-price']; ?></input></td>
	    <td class='left'><input type="radio" name="menusortbar" value="1" <?php if ($menusortbar == 1) { echo 'checked'; } ?>><?php echo $lang['alphabetical']; ?></input></td>
	    <td class='left'></td>
	   </tr>
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting48" />&nbsp;&nbsp;<?php echo $lang['require-signature']; ?><div id='helpBox48' class='helpBox'><?php echo $lang['ss-help48']; ?></div></td>
	    <td class='left'><input type="radio" name="barsig" value="0" <?php if ($barsig == 0) { echo 'checked'; } ?>><?php echo $lang['keyfob']; ?></input></td>
<?php
	if ($_SESSION['domain'] == 'california' || $_SESSION['domain'] == 'casper' || $_SESSION['domain'] == 'crystal' || $_SESSION['domain'] == 'cremeclub' || $_SESSION['domain'] == 'drgreen' || $_SESSION['domain'] == 'granvalle' || $_SESSION['domain'] == 'manoverde' || $_SESSION['domain'] == 'rafiki') {
?>
	    <td class='left'><input type="radio" name="barsig" value="1" <?php if ($barsig == 1) { echo 'checked'; } ?>>Topaz tablet</input></td>
<?php } else { ?>
	    <td class='left'><input type="radio" style='visibility: hidden;' name="barsig" value="1" <?php if ($barsig == 1) { echo 'checked'; } ?>><span style='visibility: hidden;'>Topaz tablet</span></input></td>
<?php } ?>
	    <td class='left'><input type="radio" name="barsig" value="2" <?php if ($barsig == 2) { echo 'checked'; } ?>><?php echo $lang['none']; ?></input></td>
	   </tr>
	  </table>
	 </div>
	</div>
	<br />
<div class="actionbox-np2">
 <div class='mainboxheader'>
 <img src='images/settings-members.png' style='margin-bottom: -8px; margin-right: 10px;' /><?php echo $lang['global-members']; ?>
 </div>
 <div class='boxcontent'>
	 <table class="settingstable">
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting7" />&nbsp;&nbsp;<?php echo $lang['show-age']; ?><div id='helpBox7' class='helpBox'><?php echo $lang['ss-help7']; ?></div></td>
	    <td class='left'><input type="radio" name="showAge" value="1" <?php if ($showAge == 1) { echo 'checked'; } ?>><?php echo $lang['global-yes']; ?></input></td>
	    <td class='left'><input type="radio" name="showAge" value="0" <?php if ($showAge == 0) { echo 'checked'; } ?>><?php echo $lang['global-no']; ?></input></td>
	    <td class='left'></td>
	   </tr>
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting8" />&nbsp;&nbsp;<?php echo $lang['show-gender']; ?><div id='helpBox8' class='helpBox'><?php echo $lang['ss-help8']; ?></div></td>
	    <td class='left'><input type="radio" name="showGender" value="1" <?php if ($showGender == 1) { echo 'checked'; } ?>><?php echo $lang['global-yes']; ?></input></td>
	    <td class='left'><input type="radio" name="showGender" value="0" <?php if ($showGender == 0) { echo 'checked'; } ?>><?php echo $lang['global-no']; ?></input></td>
	    <td class='left'></td>
	   </tr>
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting9" />&nbsp;&nbsp;<?php echo $lang['keep-membernumber']; ?><div id='helpBox9' class='helpBox'><?php echo $lang['ss-help9']; ?></div></td>
	    <td class='left'><input type="radio" name="keepNumber" value="1" <?php if ($keepNumber == 1) { echo 'checked'; } ?>><?php echo $lang['global-yes']; ?></input></td>
	    <td class='left'><input type="radio" name="keepNumber" value="0" <?php if ($keepNumber == 0) { echo 'checked'; } ?>><?php echo $lang['global-no']; ?></input></td>
	    <td class='left'></td>
	   </tr>
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting10" />&nbsp;&nbsp;<?php echo $lang['membership-fees']; ?><div id='helpBox10' class='helpBox'><?php echo $lang['ss-help10']; ?></div></td>
	    <td class='left'><input type="radio" name="membershipFees" value="1" <?php if ($membershipFees == 1) { echo 'checked'; } ?>><?php echo $lang['global-yes']; ?></input></td>
	    <td class='left'><input type="radio" name="membershipFees" value="0" <?php if ($membershipFees == 0) { echo 'checked'; } ?>><?php echo $lang['global-no']; ?></input><a href='javascript:void(0);' style='float: right; margin: 0; padding: 1px 5px; width: initial; font-size: 12px;' id="member_conf" class='cta1'><?php echo $lang['configure']; ?></a></td>
	    <td class='left'></td>
	   </tr>
	   <!--<tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting36" />&nbsp;&nbsp;<?php echo $lang['cuota-increment']; ?><div id='helpBox36' class='helpBox'><?php echo $lang['ss-help36']; ?></div></td>
	    <td class='left'><input type="radio" name="cuotaincrement" value="1" <?php if ($cuotaincrement == 1) { echo 'checked'; } ?>><?php echo $lang['global-yes']; ?></input></td>
	    <td class='left'><input type="radio" name="cuotaincrement" value="0" <?php if ($cuotaincrement == 0) { echo 'checked'; } ?>><?php echo $lang['global-no']; ?></input></td>
	   </tr>-->
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting11" />&nbsp;&nbsp;<?php echo $lang['high-roller-limit']; ?><div id='helpBox11' class='helpBox'><?php echo $lang['ss-help11']; ?></div></td>
	    <td colspan='3' class='left'>&nbsp;&nbsp;&nbsp;&nbsp;<input type="number" name="highRollerWeekly" class="fourDigit defaultinput-no-margin" value="<?php echo $highRollerWeekly; ?>" /> <?php echo $_SESSION['currencyoperator'] ?></td>
	   </tr>
	   <!--<tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting11" />&nbsp;&nbsp;<?php echo $lang['leftover product']; ?><div id='helpBox11' class='helpBox'><?php echo $lang['ss-help11']; ?></div></td>
	    <td colspan='3' class='left'>&nbsp;&nbsp;&nbsp;&nbsp;<input type="number" name="highRollerWeekly" class="fourDigit defaultinput-no-margin" value="<?php echo $highRollerWeekly; ?>" /> <?php echo $_SESSION['currencyoperator'] ?></td>
	   </tr>-->
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting12" />&nbsp;&nbsp;<?php echo $lang['medical-discount']; ?><div id='helpBox12' class='helpBox'><?php echo $lang['ss-help12']; ?></div></td>
	    <td class='left'>&nbsp;&nbsp;&nbsp;&nbsp;<input type="number" name="medicalDiscount" id="medicalDiscount"class="fourDigit defaultinput-no-margin" value="<?php echo $medicalDiscount; ?>" /> <?php echo $_SESSION['currencyoperator'] ?> per g.</td>
	    <td class='left'>&nbsp;&nbsp;&nbsp;&nbsp;<input type="number" name="medicalDiscountPercentage" id="medicalDiscountPercentage" class="fourDigit defaultinput-no-margin" value="<?php echo $medicalDiscountPercentage; ?>" /> %</td>
	    <td class='left'></td>
	   </tr>
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting35" />&nbsp;&nbsp;<?php echo $lang['set-exempt']; ?><div id='helpBox35' class='helpBox'><?php echo $lang['ss-help35']; ?></div></td>
	    <td class='left'><input type="radio" name="exentoset" value="0" <?php if ($exentoset == 0) { echo 'checked'; } ?>><?php echo $lang['admins']; ?></input></td>
	    <td class='left'><input type="radio" name="exentoset" value="1" <?php if ($exentoset == 1) { echo 'checked'; } ?>><?php echo $lang['everyone']; ?></input></td>
	    <td class='left'></td>
	   </tr>
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting54" />&nbsp;&nbsp;<?php echo $lang['pagination']; ?><div id='helpBox54' class='helpBox'><?php echo $lang['ss-help54']; ?></div></td>
	    <td colspan='3' class='left'>&nbsp;&nbsp;&nbsp;&nbsp;<input type="number" name="pagination" class="fourDigit defaultinput-no-margin" value="<?php echo $pagination; ?>" /></td>
	   </tr>
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting58" />&nbsp;&nbsp;<?php echo $lang['allow-pre-signup']; ?><div id='helpBox58' class='helpBox'><?php echo $lang['ss-help58']; ?></div></td>
	    <td class='left'><input type="radio" name="presignup" value="1" <?php if ($presignup == 1) { echo 'checked'; } ?>><?php echo $lang['global-yes']; ?></input></td>
	    <td class='left'><input type="radio" name="presignup" value="2" <?php if ($presignup == 2) { echo 'checked'; } ?>><?php echo $lang['global-no']; ?></input><div style='display: inline-block; position: relative;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['code']; ?>: <input type="text" name="signupcode" id="signupcode" class="fourDigit defaultinput-no-margin" value="<?php echo $signupcode; ?>" style='margin-left: 15px;' /><div id='codeerror' style='display: none; border: 2px solid red; background-color: yellow; position: absolute; bottom: 27px; right: 2px; padding: 5px;'>Invalid code!</div></div>
<script>
function checkCode(){

	var newCode = $("#signupcode").val();
	
    $.ajax({
      type:"post",
      url:"ajax/checkcode.php?newCode="+newCode,
      datatype:"text",
      success:function(data)
      {
        if( data != 'false' ) {
	    	$("#codeerror").show();
	    } else {
	    	$("#codeerror").hide();
	    }
      }
    });
    
};



$('#signupcode').on('keyup', checkCode);

</script>
	    
	    </td>
	    <td class='left'></td>
	   </tr>
  <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting70" />&nbsp;&nbsp;<?php echo $lang['opening-pre-reg']; ?><div id='helpBox70' class='helpBox'><?php echo $lang['ss-help70']; ?></div></td>
   <td colspan='3' class='left'>
    <table>
     <tr>
      <td style='background-color: #fff;'>
    <div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['monday']; ?>
	  <input type="checkbox" name="day1" value="1" <?php if ($day1 == 1) { echo 'checked'; } ?> />
	  <div class="fakebox"></div>
	 </label>
	</div>
	 </td>
     <td style='background-color: #fff;'>
   	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['tuesday']; ?>
	  <input type="checkbox" name="day2" value="1" <?php if ($day2 == 1) { echo 'checked'; } ?> />
	  <div class="fakebox"></div>
	 </label>
	</div>
	 </td>
     <td style='background-color: #fff;'>
   	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['wednesday']; ?>
	  <input type="checkbox" name="day3" value="1" <?php if ($day3 == 1) { echo 'checked'; } ?> />
	  <div class="fakebox"></div>
	 </label>
	</div>
	 </td>
     <td style='background-color: #fff;'>
   	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['thursday']; ?>
	  <input type="checkbox" name="day4" value="1" <?php if ($day4 == 1) { echo 'checked'; } ?> />
	  <div class="fakebox"></div>
	 </label>
	</div>
	 </td>
	</tr>
	<tr>
     <td>
   	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['friday']; ?>
	  <input type="checkbox" name="day5" value="1" <?php if ($day5 == 1) { echo 'checked'; } ?> />
	  <div class="fakebox"></div>
	 </label>
	</div>
	 </td>
     <td>
   	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['saturday']; ?>
	  <input type="checkbox" name="day6" value="1" <?php if ($day6 == 1) { echo 'checked'; } ?> />
	  <div class="fakebox"></div>
	 </label>
	</div>
	 </td>
     <td>
   	<div class='fakeboxholder'>	
	 <label class="control">
	  <span id="errorBox2"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $lang['sunday']; ?>
	  <input type="checkbox" name="day7" value="1" <?php if ($day7 == 1) { echo 'checked'; } ?> />
	  <div class="fakebox"></div>
	 </label>
 	 </td>
     <td>
	 </td>
    </tr>
    </table>

	</div><br />
&nbsp;&nbsp;&nbsp;&nbsp;
    <input type="text" name="openinghourreg" id="openinghourreg" class='timepicker defaultinput twoDigit' style='margin: 0;' value="<?php echo $openinghourreg; ?>" /> - &nbsp;&nbsp;&nbsp;<input type="text" name="closinghourreg" id="closinghourreg" class='timepicker defaultinput twoDigit' style='margin: 0;' value="<?php echo $closinghourreg; ?>" />
   </td>
  </tr>
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting63" />&nbsp;&nbsp;<?php echo $lang['day-visitor']; ?><div id='helpBox63' class='helpBox'><?php echo $lang['ss-help63']; ?></div></td>
	    <td class='left'><input type="radio" name="fastVisitor" value="1" <?php if ($fastVisitor == 1) { echo 'checked'; } ?>><?php echo $lang['global-yes']; ?></input></td>
	    <td class='left'><input type="radio" name="fastVisitor" value="0" <?php if ($fastVisitor == 0) { echo 'checked'; } ?>><?php echo $lang['global-no']; ?></input></td>
	    <td class='left'></td>
	   </tr>
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting69" />&nbsp;&nbsp;<?php echo $lang['appointments']; ?><div id='helpBox69' class='helpBox'><?php echo $lang['ss-help69']; ?></div></td>
	    <td class='left'><input type="radio" name="appointments" value="1" <?php if ($appointments == 1) { echo 'checked'; } ?>><?php echo $lang['global-yes']; ?></input></td>
	    <td class='left'><input type="radio" name="appointments" value="2" <?php if ($appointments == 2) { echo 'checked'; } ?>><?php echo $lang['global-no']; ?></input></td>
	    <td class='left'></td>
	   </tr>	   
	  </table>
	 </div>
	</div>
	<br />
<div class="actionbox-np2">
 <div class='mainboxheader'>
 <img src='images/settings-hw.png' style='margin-bottom: -10px; margin-right: 10px;' />CCS Hardware
 </div>
 <div class='boxcontent'>
	 <table class="settingstable">
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting33" />&nbsp;&nbsp;<?php echo $lang['tablet-readers']; ?><div id='helpBox33' class='helpBox'><?php echo $lang['ss-help33']; ?></div></td>
	    <td colspan='3' class='left'>&nbsp;&nbsp;&nbsp;&nbsp;<input type="number" name="iPadReaders" class="fourDigit defaultinput-no-margin" value="<?php echo $iPadReaders; ?>" /> </td>
	   </tr>
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting34" />&nbsp;&nbsp;<?php echo $lang['cashdro']; ?><div id='helpBox34' class='helpBox'><?php echo $lang['ss-help34']; ?></div></td>
	    <td class='left'><input type="radio" name="cashdro" value="1" <?php if ($cashdro == 1) { echo 'checked'; } ?>><?php echo $lang['global-yes']; ?></input></td>
	    <td class='left'><input type="radio" name="cashdro" value="0" <?php if ($cashdro == 0) { echo 'checked'; } ?>><?php echo $lang['global-no']; ?></input></td>
	    <td class='left'></td>
	   </tr>
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting49" />&nbsp;&nbsp;<?php echo $lang['signature-pad']; ?><div id='helpBox49' class='helpBox'><?php echo $lang['ss-help49']; ?></div></td>
	    <td class='left'><input type="radio" name="sigtablet" value="0" <?php if ($sigtablet == 0) { echo 'checked'; } ?>><?php echo $lang['mouse']; ?> / Huion</input></td>
	    <td class='left'><input type="radio" name="sigtablet" value="1" <?php if ($sigtablet == 1) { echo 'checked'; } ?>>CCS Tablet</input></td>
<?php
	if ($_SESSION['domain'] == 'california' || $_SESSION['domain'] == 'casper' || $_SESSION['domain'] == 'crystal' || $_SESSION['domain'] == 'cremeclub' || $_SESSION['domain'] == 'drgreen' || $_SESSION['domain'] == 'granvalle' || $_SESSION['domain'] == 'manoverde' || $_SESSION['domain'] == 'rafiki') {
?>
	    <td class='left'><input type="radio" name="sigtablet" value="2" <?php if ($sigtablet == 2) { echo 'checked'; } ?>>Topaz</input></td>
<?php } else { ?>
	    <td class='left'><input type="radio" style='visibility: hidden;' name="sigtablet" value="1" <?php if ($sigtablet == 2) { echo 'checked'; } ?>><span style='visibility: hidden;'>Topaz tablet</span></input></td>
<?php } ?>
	   </tr>
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting51" />&nbsp;&nbsp;<?php echo $lang['entrance-exit-show']; ?><div id='helpBox51' class='helpBox'><?php echo $lang['ss-help51']; ?></div></td>
	    <td colspan='3' class='left'>&nbsp;&nbsp;&nbsp;&nbsp;<input type="number" name="entrysyssecs" class="fourDigit defaultinput-no-margin" value="<?php echo $entrysyssecs; ?>" /> sec.</td>
	   </tr>
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting52" />&nbsp;&nbsp;<?php echo $lang['door-open-sec']; ?><div id='helpBox52' class='helpBox'><?php echo $lang['ss-help52']; ?></div></td>
	    <td colspan='3' class='left'>&nbsp;&nbsp;&nbsp;&nbsp;<input type="number" name="dooropener" class="fourDigit defaultinput-no-margin" value="<?php echo $dooropener; ?>" /> sec.</td>
	   </tr>
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting53" />&nbsp;&nbsp;<?php echo $lang['fingerprint-reader']; ?><div id='helpBox53' class='helpBox'><?php echo $lang['ss-help53']; ?></div></td>
	    <td class='left'><input type="radio" name="fingerprint" value="1" <?php if ($fingerprint == 1) { echo 'checked'; } ?>><?php echo $lang['global-yes']; ?></input></td>
	    <td class='left'><input type="radio" name="fingerprint" value="0" <?php if ($fingerprint == 0) { echo 'checked'; } ?>><?php echo $lang['global-no']; ?></input></td>
	    <td class='left'></td>
	   </tr>
	  </table>
	 </div>
	</div>
	<br />
<div class="actionbox-np2">
 <div class='mainboxheader'>
 <img src='images/settings-admin.png' style='margin-bottom: -9px; margin-right: 10px;' /><?php echo $lang['global-administration']; ?>
 </div>
 <div class='boxcontent'>
	 <table class="settingstable">
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting13" />&nbsp;&nbsp;<?php echo $lang['rec-email']; ?><div id='helpBox13' class='helpBox'><?php echo $lang['ss-help13']; ?></div></td>
	    <td class='left'><input type="radio" name="closingMail" value="1" <?php if ($closingMail == 1) { echo 'checked'; } ?>><?php echo $lang['global-yes']; ?></input></td>
	    <td class='left'><input type="radio" name="closingMail" value="0" <?php if ($closingMail == 0) { echo 'checked'; } ?>><?php echo $lang['global-no']; ?></input> <a id='admin_conf' href='javascript:void(0);'  style='float: right; margin: 0; padding: 1px 5px; width: initial; font-size: 12px;' class='cta1'><?php echo $lang['configure']; ?></a>
	    <td class='left'></td>
	    </td>
	   </tr>
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting14" />&nbsp;&nbsp;<?php echo $lang['allow-bank-payments']; ?><div id='helpBox14' class='helpBox'><?php echo $lang['ss-help14']; ?></div></td>
	    <td class='left'><input type="radio" name="bankPayments" value="1" <?php if ($bankPayments == 1) { echo 'checked'; } ?>><?php echo $lang['global-yes']; ?></input></td>
	    <td class='left'><input type="radio" name="bankPayments" value="0" <?php if ($bankPayments == 0) { echo 'checked'; } ?>><?php echo $lang['global-no']; ?></input></td>
	    <td class='left'></td>
	   </tr>
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting15" />&nbsp;&nbsp;<?php echo $lang['credit-or-direct']; ?><div id='helpBox15' class='helpBox'><?php echo $lang['ss-help15']; ?></div></td>
	    <td class='left'><input type="radio" name="creditOrDirect" value="1" <?php if ($creditOrDirect == 1) { echo 'checked'; } ?>><?php echo $lang['global-credit']; ?></input></td>
	    <td class='left'><input type="radio" name="creditOrDirect" value="0" <?php if ($creditOrDirect == 0) { echo 'checked'; } ?>><?php echo $lang['direct']; ?></input></td>
	    <td class='left'></td>
	   </tr>
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting16" />&nbsp;&nbsp;<?php echo $lang['visit-registration']; ?><div id='helpBox16' class='helpBox'><?php echo $lang['ss-help16']; ?></div></td>
	    <td class='left'><input type="radio" name="visitRegistration" value="1" <?php if ($visitRegistration == 1) { echo 'checked'; } ?>><?php echo $lang['automatic']; ?></input></td>
	    <td class='left'><input type="radio" name="visitRegistration" value="0" <?php if ($visitRegistration == 0) { echo 'checked'; } ?>><?php echo $lang['manual']; ?></input></td>
	    <td class='left'></td>
	   </tr>
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='padding: 1px;' id="setting50" />&nbsp;&nbsp;<?php echo $lang['entrance-exit-stay']; ?><div id='helpBox50' class='helpBox'><?php echo $lang['ss-help50']; ?></div></td>
	    <td colspan='3' class='left'>&nbsp;&nbsp;&nbsp;&nbsp;<input type="number" name="entrysysstay" class="fourDigit defaultinput-no-margin" value="<?php echo $entrysysstay; ?>" /> min.</td>
	   </tr>

<!--	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting19" />&nbsp;&nbsp;<?php echo $lang['crop-images']; ?><div id='helpBox19' class='helpBox'><?php echo $lang['ss-help19']; ?></div></td>
	    <td class='left'><input type="radio" name="cropOrNot" value="1" <?php if ($cropOrNot == 1) { echo 'checked'; } ?>><?php echo $lang['global-yes']; ?></input></td>
	    <td class='left'><input type="radio" name="cropOrNot" value="0" <?php if ($cropOrNot == 0) { echo 'checked'; } ?>><?php echo $lang['global-no']; ?></input></td>
	    <td class='left'></td>
	   </tr>-->
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting20" />&nbsp;&nbsp;<?php echo $lang['workstation-extension']; ?><div id='helpBox20' class='helpBox'><?php echo $lang['ss-help20']; ?></div></td>
	    <td class='left'><input type="radio" name="puestosOrNot" value="1" <?php if ($puestosOrNot == 1) { echo 'checked'; } ?>><?php echo $lang['global-yes']; ?></input></td>
	    <td class='left'><input type="radio" name="puestosOrNot" value="0" <?php if ($puestosOrNot == 0) { echo 'checked'; } ?>><?php echo $lang['global-no']; ?></input></td>
	    <td class='left'></td>
	   </tr>
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting56" />&nbsp;&nbsp;<?php echo $lang['worker-tracking']; ?><div id='helpBox56' class='helpBox'><?php echo $lang['ss-help56']; ?></div></td>
	    <td class='left'><input type="radio" name="workertracking" value="1" <?php if ($workertracking == 1) { echo 'checked'; } ?>><?php echo $lang['global-yes']; ?></input></td>
	    <td class='left'><input type="radio" name="workertracking" value="0" <?php if ($workertracking == 0) { echo 'checked'; } ?>><?php echo $lang['global-no']; ?></input></td>
	    <td class='left'></td>
	   </tr>
	   
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting21" />&nbsp;&nbsp;<?php echo $lang['accountability-type']; ?><div id='helpBox21' class='helpBox'><?php echo $lang['ss-help21']; ?></div></td>
	    <td class='left'>
	     <div style='margin-bottom: 8px;'><input type="radio" name="openAndClose" value="0" <?php if ($openAndClose == 0) { echo 'checked'; } ?>><?php echo $lang['none']; ?></input></div>
	     <!--<div style='margin-bottom: 8px;'><input type="radio" name="openAndClose" value="0" <?php if ($openAndClose == 1) { echo 'checked'; } ?>><?php echo $lang['automatic']; ?></input></div>-->
	     <div style='margin-bottom: 8px;'><input type="radio" name="openAndClose" value="2" <?php if ($openAndClose == 2) { echo 'checked'; } ?>><?php echo $lang['only-close']; ?></input></div>
	     <div style='margin-bottom: 8px;'><input type="radio" name="openAndClose" value="3" <?php if ($openAndClose == 3) { echo 'checked'; } ?>><?php echo $lang['open-and-close']; ?></input></div>
	     <input type="radio" name="openAndClose" value="4" <?php if ($openAndClose == 4) { echo 'checked'; } ?>><?php echo $lang['open-and-close-shifts']; ?></input>
	    </td>
	    <td class='left'></td>
	    <td class='left'></td>
	   </tr>
	   
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting17" />&nbsp;&nbsp;<?php echo $lang['minimum-age']; ?><div id='helpBox17' class='helpBox'><?php echo $lang['ss-help17']; ?></div></td>
	    <td colspan='3' class='left'>&nbsp;&nbsp;&nbsp;&nbsp;<input type="number" name="minAge" class="fourDigit defaultinput-no-margin" value="<?php echo $minAge; ?>" /> <?php echo $lang['years']; ?></td>
	   </tr>
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting18" />&nbsp;&nbsp;<?php echo $lang['auto-logout']; ?><div id='helpBox18' class='helpBox'><?php echo $lang['ss-help18']; ?></div></td>
	    <td class='left'>&nbsp;&nbsp;&nbsp;&nbsp;<input type="number" name="logouttime" class="fourDigit defaultinput-no-margin" value="<?php echo $logouttime; ?>" /> minutes</td>
	    <td class='left'><input type="checkbox" name="logoutredir" value="1" style="width: 12px; margin-left: 20px;" <?php if ($logoutredir == 1) { echo 'checked'; } ?>/> <?php echo $lang['redirect-to']; ?> www.google.es?</td>
	    <td class='left'></td>
	   </tr>
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting42" />&nbsp;&nbsp;<?php echo $lang['chip-cost']; ?><div id='helpBox42' class='helpBox'><?php echo $lang['ss-help42']; ?></div></td>
	    <td colspan='3' class='left'>&nbsp;&nbsp;&nbsp;&nbsp;<input type="number" name="chipcost" class="fourDigit defaultinput-no-margin" value="<?php echo $chipcost; ?>" /> <?php echo $_SESSION['currencyoperator'] ?></td>
	   </tr>
<!--	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='padding: 1px;' id="setting60" />&nbsp;&nbsp;<?php echo $lang['export-number-format']; ?><div id='helpBox60' class='helpBox'><?php echo $lang['ss-help60']; ?></div></td>
	    <td class='left'><input type="radio" name="export_number_format" value="," <?php if ($export_number_format == ',') { echo 'checked'; } ?>><?php echo $lang['comma']; ?></input></td>
	    <td class='left'><input type="radio" name="export_number_format" value="." <?php if ($export_number_format == '.') { echo 'checked'; } ?>><?php echo $lang['dot']; ?></input></td>
	    <td class='left'></td>
	   </tr>
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='padding: 1px;' id="setting61" />&nbsp;&nbsp;<?php echo $lang['block-dni-sig']; ?><div id='helpBox61' class='helpBox'><?php echo $lang['ss-help61']; ?></div></td>
	    <td class='left'><input type="radio" name="requiredniandsig" value="1" <?php if ($requiredniandsig == 1) { echo 'checked'; } ?>><?php echo $lang['global-yes']; ?></input></td>
	    <td class='left'><input type="radio" name="requiredniandsig" value="0" <?php if ($requiredniandsig == 0) { echo 'checked'; } ?>><?php echo $lang['global-no']; ?></input></td>
	    <td class='left'></td>
	   </tr>-->
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='padding: 1px;' id="setting62" />&nbsp;&nbsp;<?php echo $lang['currency-symbol']; ?><div id='helpBox62' class='helpBox'><?php echo $lang['ss-help62']; ?></div></td>
	    <td colspan='3' class='left'>&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="currencyoperator" class="fourDigit defaultinput-no-margin" value="<?php echo $currencyoperator; ?>" /></td>
	    <td class='left'></td>
	   </tr>
<!--	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting43" />&nbsp;&nbsp;<?php echo $lang['login-language']; ?><div id='helpBox43' class='helpBox'><?php echo $lang['ss-help43']; ?></div></td>
	    <td class='left'><input type="radio" name="language" value="0" <?php if ($language == 0) { echo 'checked'; } ?>><?php echo $lang['none']; ?></input></td>
	    <td class='left'><input type="radio" name="language" value="1" <?php if ($language == 1) { echo 'checked'; } ?>><?php echo $lang['index-spanish']; ?></input></td>
	    <td class='left'><input type="radio" name="language" value="2" <?php if ($language == 2) { echo 'checked'; } ?>><?php echo $lang['index-english']; ?></input></td>
	   </tr>-->
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='padding: 1px;' id="setting73" />&nbsp;&nbsp;Show Prices In app?<div id='helpBox73' class='helpBox'></div></td>
	    <td class='left'><input type="radio" name="showAppPrice" value="1" <?php if ($showAppPrice == 1) { echo 'checked'; } ?>><?php echo $lang['global-yes']; ?></input></td>
	    <td class='left'><input type="radio" name="showAppPrice" value="0" <?php if ($showAppPrice == 0) { echo 'checked'; } ?>><?php echo $lang['global-no']; ?></input></td>
	    <td class='left'></td>
	   </tr>
	 </table>
	 </div>
	</div>
	<br />
     <button class='cta1' name='oneClick' type="submit"><?php echo $lang['global-savechanges']; ?></button>

     <input type='hidden' name="fullmenu" value="<?php echo $fullmenu; ?>" />
</form>
<div id="member-dialog" style="display: none">
	<div  id="member_content">
	</div>
</div>
<div id="admin-dialog" style="display: none">
	<div  id="admin_content">
	</div>
</div>
	 <br /><br /><br />
<script  type="text/javascript">
	function delete_cuota(cuotaid) {
			if (confirm("")) {
					window.location = "uTil/delete-cuota.php?cuotaid=" + cuotaid;
				}
		}
	function delete_email(emailid) {
		if (confirm("")) {
				window.location = "uTil/delete-email.php?emailid=" + emailid;
				}
	}
</script>	

 <script type="text/javascript">
$(function () {
    $("#member-dialog").dialog({
        autoOpen: false,
        modal: true,
        title: "<?php echo $lang['set-fees']; ?>",
        width: 'auto',
        draggable: false,
        open: function( event, ui ) {
                //center the dialog within the viewport (i.e. visible area of the screen)
               var top = Math.max($(window).height() / 2 - $(this)[0].offsetHeight / 2, 0);
               var left = Math.max($(window).width() / 2 - $(this)[0].offsetWidth / 2, 0);
               $(this).parent().css('top', 0 + "px");
               $(this).parent().css('left', 500 + "px");
               $(this).parent().css('position', 'fixed');                
            },

    });    
    $("#admin-dialog").dialog({
        autoOpen: false,
        modal: true,
        title: "<?php echo $lang['set-emails']; ?>",
        width: 'auto',
        draggable: false,
        open: function( event, ui ) {
                //center the dialog within the viewport (i.e. visible area of the screen)
               var top = Math.max($(window).height() / 2 - $(this)[0].offsetHeight / 2, 0);
               var left = Math.max($(window).width() / 2 - $(this)[0].offsetWidth / 2, 0);
               $(this).parent().css('top', 80 + "px");
               $(this).parent().css('left', 550 + "px");
               $(this).parent().css('position', 'fixed');                
            },

    });
  

    $("#member_conf").click(function () {
        $.ajax({
            type: "POST",
            url: "cuotas.php",
            data: {"name": "member_email"},
            dataType: "json",
            success: function (r) {
               $("#member-dialog").dialog("open");
               $("#member_content").html(r.member_data);
            }
        });
    });   

     $("#admin_conf").click(function () {
        $.ajax({
            type: "POST",
            url: "closing-mails.php",
            data: {"name": "admin_email"},
            dataType: "json",
            success: function (r) {
               $("#admin-dialog").dialog("open");
               $("#admin_content").html(r.admin_data);
            }
        });
    });


	// this is the id of the form
	$(document).on("submit","#registerForm2",function(e) {

	    e.preventDefault(); // avoid to execute the actual submit of the form.

	    var form = $(this);
	    var actionUrl = form.attr('action');
	    
	    $.ajax({
	        type: "POST",
	        url: actionUrl,
	        dataType: 'json',
	        data: form.serialize(), // serializes the form's elements.
	        success: function(data)
	        {
	          alert(data.successMessage); // show response from the php script.
	          $("#admin-dialog").dialog("close");
	        }
	    });
	    
	});	

	// this is the id of the form
	$(document).on("submit","#registerForm1",function(e) {

	    e.preventDefault(); // avoid to execute the actual submit of the form.

	    var form1 = $(this);
	    var actionUrl = form1.attr('action');
	    
	    $.ajax({
	        type: "POST",
	        url: actionUrl,
	        dataType: 'json',
	        data: form1.serialize(), // serializes the form's elements.
	        success: function(data)
	        {
	          alert(data.successMessage); // show response from the php script.
	          $("#member-dialog").dialog("close");
	        }
	    });
	    
	});
});
</script>
<?php displayFooter(); ?>
