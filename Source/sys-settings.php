<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
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
		$presignup = $_POST['presignup'];
		$signupcode = $_POST['signupcode'];

		
		
		
		
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

		$updateSettings = sprintf("UPDATE systemsettings SET highRollerWeekly = '%d', closingMail = '%d', minAge = '%d', dispensaryGift = '%d', barGift = '%d', menuType = '%d', medicalDiscount = '%f', logouttime = '%d', logoutredir = '%d', dispDonate = '%d', dispExpired = '%d', dispenseLimit = '%d', showAge = '%d', showGender = '%d', keepNumber = '%d', membershipFees = '%d', medicalDiscountPercentage = '%d', bankPayments = '%d', creditOrDirect = '%d', visitRegistration = '%d', cropOrNot = '%d', puestosOrNot = '%d', openAndClose = '%d', barMenuType = '%d', flowerLimit = '%f', extractLimit = '%f', realWeight = '%d', showStock = '%d', showOrigPrice = '%d', checkoutDiscount = '%d', consumptionMin = '%f', consumptionMax = '%f', showStockBar = '%d', showOrigPriceBar = '%d', barTouchscreen = '%d', iPadReaders = '%d', cashdro = '%d', creditchange = '%d', expirychange = '%d', exentoset = '%d', menusortdisp = '%d', menusortbar = '%d', dispsig = '%d', barsig = '%d', openmenu = '%d', keypads = '%d', moneycount = '%d', customws = '%d', negcredit = '%d', language = '%d', nobar = '%d', sigtablet = '%d', entrysys = '%d', entrysysstay = '%d', entrysyssecs = '%d', dooropener = '%d', cuotaincrement = '%d', checkoutDiscountBar = '%d', chipcost = '%d', fingerprint = '%d', pagination = '%d', dooropenfor = '%d', workertracking = '%d', fullmenu = '%d', presignup = '%d';",
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
$presignup
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
	
	// Query to look up settings
	$selectSettings = "SELECT highRollerWeekly, minAge, closingMail, dispensaryGift, barGift, menuType, medicalDiscount, logouttime, logoutredir, dispDonate, dispExpired, dispenseLimit, showAge, showGender, keepNumber, membershipFees, medicalDiscountPercentage, bankPayments, creditOrDirect, visitRegistration, cropOrNot, puestosOrNot, openAndClose, barMenuType, flowerLimit, extractLimit, realWeight, showStock, showOrigPrice, checkoutDiscount, consumptionMin, consumptionMax, showStockBar, showOrigPriceBar, barTouchscreen, iPadReaders, cashdro, creditchange, expirychange, exentoset, menusortdisp, menusortbar, dispsig, barsig, openmenu, keypads, moneycount, customws, negcredit, language, nobar, sigtablet, entrysys, entrysysstay, entrysyssecs, dooropener, cuotaincrement, checkoutDiscountBar, chipcost, fingerprint, pagination, dooropenfor, workertracking, fullmenu, signupcode, presignup FROM systemsettings";
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
		$presignup  = $row['presignup'];
		$signupcode  = $row['signupcode'];
		
		
		if ($medicalDiscountPercentage == 1) {
			$medicalDiscountPercentage = $medicalDiscount;
			$medicalDiscount = '';
		} else {
			$medicalDiscountPercentage = '';
		}
		
	$medicalScript = <<<EOD
    $(document).ready(function() {
	    
	    
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
  	$("#helpBox58").on({
 		"mouseover" : function() {
		 	$("#helpBox58").css("display", "block");
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
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting39" />&nbsp;&nbsp;<?php echo $lang['credit-change']; ?><div id='helpBox39' class='helpBox'><?php echo $lang['ss-help39']; ?></div></td>
	    <td class='left'><input type="radio" name="creditchange" value="1" <?php if ($creditchange == 1) { echo 'checked'; } ?>><?php echo $lang['global-yes']; ?></input></td>
	    <td class='left'><input type="radio" name="creditchange" value="0" <?php if ($creditchange == 0) { echo 'checked'; } ?>><?php echo $lang['global-no']; ?></input></td>
	    <td class='left'></td>
	   </tr>
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting40" />&nbsp;&nbsp;<?php echo $lang['expiry-change']; ?><div id='helpBox40' class='helpBox'><?php echo $lang['ss-help40']; ?></div></td>
	    <td class='left'><input type="radio" name="expirychange" value="1" <?php if ($expirychange == 1) { echo 'checked'; } ?>><?php echo $lang['global-yes']; ?></input></td>
	    <td class='left'><input type="radio" name="expirychange" value="0" <?php if ($expirychange == 0) { echo 'checked'; } ?>><?php echo $lang['global-no']; ?></input></td>
	    <td class='left'></td>
	   </tr>
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting41" />&nbsp;&nbsp;<?php echo $lang['negative-credit']; ?><div id='helpBox41' class='helpBox'><?php echo $lang['ss-help41']; ?></div></td>
	    <td class='left'><input type="radio" name="negcredit" value="0" <?php if ($negcredit == 0) { echo 'checked'; } ?>><?php echo $lang['global-yes']; ?></input></td>
	    <td class='left'><input type="radio" name="negcredit" value="1" <?php if ($negcredit == 1) { echo 'checked'; } ?>><?php echo $lang['global-no']; ?></input></td>
	    <td class='left'></td>
	   </tr>
	  </table>
	 </div>
	</div>
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
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting57" />&nbsp;&nbsp;<?php echo $lang['product-display']; ?><div id='helpBox57' class='helpBox'><?php echo $lang['ss-help57']; ?></div></td>
	    <td class='left'><input type="radio" name="fullmenu" value="0" <?php if ($fullmenu == 0) { echo 'checked'; } ?>><?php echo $lang['minimized']; ?></input></td>
	    <td class='left'><input type="radio" name="fullmenu" value="1" <?php if ($fullmenu == 1) { echo 'checked'; } ?>><?php echo $lang['maximized']; ?></input></td>
	    <td class='left'></td>
	   </tr>
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
	    <td colspan='3' class='left'>&nbsp;&nbsp;&nbsp;&nbsp;<input type="number" name="dispenseLimit" class="fourDigit defaultinput-no-margin" value="<?php echo $dispenseLimit; ?>" /> &euro;</td>
	   </tr>
	   <!--<tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting44" />&nbsp;&nbsp;<?php echo $lang['stock-warning']; ?><div id='helpBox44' class='helpBox'><?php echo $lang['ss-help44']; ?></div></td>
	    <td colspan='3' class='left'>&nbsp;&nbsp;&nbsp;&nbsp;<input type="number" name="dispenseLimit" class="fourDigit" value="<?php echo $dispenseLimit; ?>" /> &euro;</td>
	   </tr>-->
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting45" />&nbsp;&nbsp;<?php echo $lang['sort-order']; ?><div id='helpBox45' class='helpBox'><?php echo $lang['ss-help45']; ?></div></td>
	    <td class='left'><input type="radio" name="menusortdisp" value="0" <?php if ($menusortdisp == 0) { echo 'checked'; } ?>><?php echo $lang['by-price']; ?></input></td>
	    <td class='left'><input type="radio" name="menusortdisp" value="1" <?php if ($menusortdisp == 1) { echo 'checked'; } ?>><?php echo $lang['alphabetical']; ?></input></td>
	    <td class='left'></td>
	   </tr>
	   <tr>
	    <td class='left'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting46" />&nbsp;&nbsp;<?php echo $lang['require-signature']; ?><div id='helpBox46' class='helpBox'><?php echo $lang['ss-help46']; ?></div></td>
	    <td class='left'><input type="radio" name="dispsig" value="0" <?php if ($dispsig == 0) { echo 'checked'; } ?>><?php echo $lang['keyfob']; ?></input></td>
	    <td class='left'><input type="radio" name="dispsig" value="1" <?php if ($dispsig == 1) { echo 'checked'; } ?>>Topaz tablet</input></td>
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
	  </table>
	 </div>
	</div>
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
	    <td class='left'><input type="radio" name="barsig" value="1" <?php if ($barsig == 1) { echo 'checked'; } ?>>Topaz tablet</input></td>
	    <td class='left'><input type="radio" name="barsig" value="2" <?php if ($barsig == 2) { echo 'checked'; } ?>><?php echo $lang['none']; ?></input></td>
	   </tr>
	  </table>
	 </div>
	</div>
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
	    <td class='left'><input type="radio" name="membershipFees" value="0" <?php if ($membershipFees == 0) { echo 'checked'; } ?>><?php echo $lang['global-no']; ?></input><a href='cuotas.php' style='float: right; margin: 0; padding: 1px 5px; width: initial; font-size: 12px;' class='cta1'><?php echo $lang['configure']; ?></a></td>
	    <td class='left'></td>
	   </tr>
	   <!--<tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting36" />&nbsp;&nbsp;<?php echo $lang['cuota-increment']; ?><div id='helpBox36' class='helpBox'><?php echo $lang['ss-help36']; ?></div></td>
	    <td class='left'><input type="radio" name="cuotaincrement" value="1" <?php if ($cuotaincrement == 1) { echo 'checked'; } ?>><?php echo $lang['global-yes']; ?></input></td>
	    <td class='left'><input type="radio" name="cuotaincrement" value="0" <?php if ($cuotaincrement == 0) { echo 'checked'; } ?>><?php echo $lang['global-no']; ?></input></td>
	   </tr>-->
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting11" />&nbsp;&nbsp;<?php echo $lang['high-roller-limit']; ?><div id='helpBox11' class='helpBox'><?php echo $lang['ss-help11']; ?></div></td>
	    <td colspan='3' class='left'>&nbsp;&nbsp;&nbsp;&nbsp;<input type="number" name="highRollerWeekly" class="fourDigit defaultinput-no-margin" value="<?php echo $highRollerWeekly; ?>" /> &euro;</td>
	   </tr>
	   <!--<tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting11" />&nbsp;&nbsp;<?php echo $lang['leftover product']; ?><div id='helpBox11' class='helpBox'><?php echo $lang['ss-help11']; ?></div></td>
	    <td colspan='3' class='left'>&nbsp;&nbsp;&nbsp;&nbsp;<input type="number" name="highRollerWeekly" class="fourDigit defaultinput-no-margin" value="<?php echo $highRollerWeekly; ?>" /> &euro;</td>
	   </tr>-->
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting12" />&nbsp;&nbsp;<?php echo $lang['medical-discount']; ?><div id='helpBox12' class='helpBox'><?php echo $lang['ss-help12']; ?></div></td>
	    <td class='left'>&nbsp;&nbsp;&nbsp;&nbsp;<input type="number" name="medicalDiscount" id="medicalDiscount"class="fourDigit defaultinput-no-margin" value="<?php echo $medicalDiscount; ?>" /> &euro; per g.</td>
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
	  </table>
	 </div>
	</div>
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
	    <td class='left'><input type="radio" name="sigtablet" value="2" <?php if ($sigtablet == 2) { echo 'checked'; } ?>>Topaz</input></td>
	   </tr>
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting50" />&nbsp;&nbsp;<?php echo $lang['entrance-exit-stay']; ?><div id='helpBox50' class='helpBox'><?php echo $lang['ss-help50']; ?></div></td>
	    <td colspan='3' class='left'>&nbsp;&nbsp;&nbsp;&nbsp;<input type="number" name="entrysysstay" class="fourDigit defaultinput-no-margin" value="<?php echo $entrysysstay; ?>" /> sec.</td>
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
<div class="actionbox-np2">
 <div class='mainboxheader'>
 <img src='images/settings-admin.png' style='margin-bottom: -9px; margin-right: 10px;' /><?php echo $lang['global-administration']; ?>
 </div>
 <div class='boxcontent'>
	 <table class="settingstable">
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting13" />&nbsp;&nbsp;<?php echo $lang['rec-email']; ?><div id='helpBox13' class='helpBox'><?php echo $lang['ss-help13']; ?></div></td>
	    <td class='left'><input type="radio" name="closingMail" value="1" <?php if ($closingMail == 1) { echo 'checked'; } ?>><?php echo $lang['global-yes']; ?></input></td>
	    <td class='left'><input type="radio" name="closingMail" value="0" <?php if ($closingMail == 0) { echo 'checked'; } ?>><?php echo $lang['global-no']; ?></input> <a href='closing-mails.php'  style='float: right; margin: 0; padding: 1px 5px; width: initial; font-size: 12px;' class='cta1'><?php echo $lang['configure']; ?></a>
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
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting19" />&nbsp;&nbsp;<?php echo $lang['crop-images']; ?><div id='helpBox19' class='helpBox'><?php echo $lang['ss-help19']; ?></div></td>
	    <td class='left'><input type="radio" name="cropOrNot" value="1" <?php if ($cropOrNot == 1) { echo 'checked'; } ?>><?php echo $lang['global-yes']; ?></input></td>
	    <td class='left'><input type="radio" name="cropOrNot" value="0" <?php if ($cropOrNot == 0) { echo 'checked'; } ?>><?php echo $lang['global-no']; ?></input></td>
	    <td class='left'></td>
	   </tr>
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
	    <td colspan='3' class='left'>&nbsp;&nbsp;&nbsp;&nbsp;<input type="number" name="chipcost" class="fourDigit defaultinput-no-margin" value="<?php echo $chipcost; ?>" /> €</td>
	   </tr>
<!--	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark-new.png' style='margin-bottom: -1px;' width='15' id="setting43" />&nbsp;&nbsp;<?php echo $lang['login-language']; ?><div id='helpBox43' class='helpBox'><?php echo $lang['ss-help43']; ?></div></td>
	    <td class='left'><input type="radio" name="language" value="0" <?php if ($language == 0) { echo 'checked'; } ?>><?php echo $lang['none']; ?></input></td>
	    <td class='left'><input type="radio" name="language" value="1" <?php if ($language == 1) { echo 'checked'; } ?>><?php echo $lang['index-spanish']; ?></input></td>
	    <td class='left'><input type="radio" name="language" value="2" <?php if ($language == 2) { echo 'checked'; } ?>><?php echo $lang['index-english']; ?></input></td>
	   </tr>-->
	 </table>
	 </div>
	</div>
	<br />
     <button class='cta1' name='oneClick' type="submit"><?php echo $lang['global-savechanges']; ?></button>

</form>
	 <br /><br /><br />
	 
<?php displayFooter(); ?>
