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
		
		
		if ($medicalDiscountPercentage > 0) {
			$medicalDiscount = $medicalDiscountPercentage;
			$medicalDiscountPercentage = 1;
		} else if (($medicalDiscountPercentage == '' || $medicalDiscountPercentage == 0) && ($medicalDiscount == '' || $medicalDiscount == 0)) {
			$medicalDiscountPercentage = 0;
			$medicalDiscount = 0;
		} else {
			$medicalDiscountPercentage = 0;
		}
		
		$updateSettings = sprintf("UPDATE systemsettings SET highRollerWeekly = '%d', closingMail = '%d', minAge = '%d', dispensaryGift = '%d', barGift = '%d', menuType = '%d', medicalDiscount = '%f', logouttime = '%d', logoutredir = '%d', dispDonate = '%d', dispExpired = '%d', dispenseLimit = '%d', showAge = '%d', showGender = '%d', keepNumber = '%d', membershipFees = '%d', medicalDiscountPercentage = '%d', bankPayments = '%d', creditOrDirect = '%d', visitRegistration = '%d', cropOrNot = '%d', puestosOrNot = '%d', openAndClose = '%d', barMenuType = '%d', flowerLimit = '%f', extractLimit = '%f', realWeight = '%d';",
mysql_real_escape_string($highRollerWeekly),
mysql_real_escape_string($closingMail),
mysql_real_escape_string($minAge),
mysql_real_escape_string($dispensaryGift),
mysql_real_escape_string($barGift),
mysql_real_escape_string($menuType),
mysql_real_escape_string($medicalDiscount),
mysql_real_escape_string($logouttime),
mysql_real_escape_string($logoutredir),
mysql_real_escape_string($dispDonate),
mysql_real_escape_string($dispExpired),
mysql_real_escape_string($dispenseLimit),
mysql_real_escape_string($showAge),
mysql_real_escape_string($showGender),
mysql_real_escape_string($keepNumber),
mysql_real_escape_string($membershipFees),
mysql_real_escape_string($medicalDiscountPercentage),
mysql_real_escape_string($bankPayments),
mysql_real_escape_string($creditOrDirect),
mysql_real_escape_string($visitRegistration),
mysql_real_escape_string($cropOrNot),
mysql_real_escape_string($puestosOrNot),
mysql_real_escape_string($openAndClose),
mysql_real_escape_string($barMenuType),
mysql_real_escape_string($flowerLimit),
mysql_real_escape_string($extractLimit),
mysql_real_escape_string($realWeight)

);
		
		mysql_query($updateSettings)
			or handleError($lang['error-savedata'],"Error inserting user: " . mysql_error());
			
		$_SESSION['successMessage'] = $lang['settings-updated'];
		header("Location: admin.php");
		exit();

	}

	
	// Query to look up settings
	$selectSettings = "SELECT highRollerWeekly, minAge, closingMail, dispensaryGift, barGift, menuType, medicalDiscount, logouttime, logoutredir, dispDonate, dispExpired, dispenseLimit, showAge, showGender, keepNumber, membershipFees, medicalDiscountPercentage, bankPayments, creditOrDirect, visitRegistration, cropOrNot, puestosOrNot, openAndClose, barMenuType, flowerLimit, extractLimit, realWeight FROM systemsettings";

	$result = mysql_query($selectSettings)
		or handleError($lang['error-expensesload'],"Error loading expense from db: " . mysql_error());
		
	$row = mysql_fetch_array($result);
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

});
EOD;

  	pageStart($lang['system-settings'], NULL, $medicalScript, "pexpenses", "admin", $lang['system-settingsC'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
  	
?>
<form id="registerForm" action="" method="POST" >

<h2><?php echo $lang['dispensary-bar']; ?></h2>
	 <table class="default nonhover">
	   <tr>
	    <td class='left'></td>
	    <td class='left'></td>
	    <td class='left'></td>
	   </tr>
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark.png' style='padding: 1px;' id="setting1" />&nbsp;&nbsp;<?php echo $lang['dispensary-gift']; ?><div id='helpBox1' class='helpBox'><?php echo $lang['ss-help1']; ?></div></td>
	    <td class='left'><input type="radio" name="dispensaryGift" value="1" <?php if ($dispensaryGift == 1) { echo 'checked'; } ?>><?php echo $lang['global-yes']; ?></input></td>
	    <td class='left'><input type="radio" name="dispensaryGift" value="0" <?php if ($dispensaryGift == 0) { echo 'checked'; } ?>><?php echo $lang['global-no']; ?></input></td>
	   </tr>
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark.png' style='padding: 1px;' id="setting2" />&nbsp;&nbsp;<?php echo $lang['bar-gift']; ?><div id='helpBox2' class='helpBox'><?php echo $lang['ss-help2']; ?></div></td>
	    <td class='left'><input type="radio" name="barGift" value="1" <?php if ($barGift == 1) { echo 'checked'; } ?>><?php echo $lang['global-yes']; ?></input></td>
	    <td class='left'><input type="radio" name="barGift" value="0" <?php if ($barGift == 0) { echo 'checked'; } ?>><?php echo $lang['global-no']; ?></input></td>
	   </tr>
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark.png' style='padding: 1px;' id="setting3" />&nbsp;&nbsp;<?php echo $lang['donate-dispensary']; ?><div id='helpBox3' class='helpBox'><?php echo $lang['ss-help3']; ?></div></td>
	    <td class='left'><input type="radio" name="dispDonate" value="1" <?php if ($dispDonate == 1) { echo 'checked'; } ?>><?php echo $lang['global-yes']; ?></input></td>
	    <td class='left'><input type="radio" name="dispDonate" value="0" <?php if ($dispDonate == 0) { echo 'checked'; } ?>><?php echo $lang['global-no']; ?></input></td>
	   </tr>
	   <tr> 
	    <td class='left' style='position: relative;'><img src='images/questionmark.png' style='padding: 1px;' id="setting4" />&nbsp;&nbsp;<?php echo $lang['dispense-expired']; ?><div id='helpBox4' class='helpBox'><?php echo $lang['ss-help4']; ?></div></td>
	    <td class='left'><input type="radio" name="dispExpired" value="1" <?php if ($dispExpired == 1) { echo 'checked'; } ?>><?php echo $lang['global-yes']; ?></input></td>
	    <td class='left'><input type="radio" name="dispExpired" value="0" <?php if ($dispExpired == 0) { echo 'checked'; } ?>><?php echo $lang['global-no']; ?></input></td>
	   </tr>
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark.png' style='padding: 1px;' id="setting5" />&nbsp;&nbsp;<?php echo $lang['dispensary-menu']; ?><div id='helpBox5' class='helpBox'><?php echo $lang['ss-help5']; ?></div></td>
	    <td class='left'><input type="radio" name="menuType" value="0" <?php if ($menuType == 0) { echo 'checked'; } ?>><?php echo $lang['normal']; ?></input></td>
	    <td class='left'><input type="radio" name="menuType" value="1" <?php if ($menuType == 1) { echo 'checked'; } ?>><?php echo $lang['list-only']; ?></input></td>
	   </tr>
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark.png' style='padding: 1px;' id="setting22" />&nbsp;&nbsp;<?php echo $lang['bar-menu']; ?><div id='helpBox22' class='helpBox'><?php echo $lang['ss-help22']; ?></div></td>
	    <td class='left'><input type="radio" name="barMenuType" value="0" <?php if ($barMenuType == 0) { echo 'checked'; } ?>><?php echo $lang['normal']; ?></input></td>
	    <td class='left'><input type="radio" name="barMenuType" value="1" <?php if ($barMenuType == 1) { echo 'checked'; } ?>><?php echo $lang['list-only']; ?></input></td>
	   </tr>
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark.png' style='padding: 1px;' id="setting6" />&nbsp;&nbsp;<?php echo $lang['max-dispense-limit']; ?><div id='helpBox6' class='helpBox'><?php echo $lang['ss-help6']; ?></div></td>
	    <td colspan='2' class='left'>&nbsp;&nbsp;&nbsp;&nbsp;<input type="number" name="dispenseLimit" class="fourDigit" value="<?php echo $dispenseLimit; ?>" /> &euro;</td>
	   </tr>
	   
	   <tr> 
	    <td class='left' style='position: relative; border-top: 2px solid #5aa242; border-left: 2px solid #5aa242;'><img src='images/questionmark.png' style='padding: 1px;' id="setting25" />&nbsp;&nbsp;<?php echo $lang['enable-real-weight']; ?><div id='helpBox25' class='helpBox'><?php echo $lang['ss-help25']; ?></div></td>
	    <td class='left' style='border-top: 2px solid #5aa242;'><input type="radio" name="realWeight" value="1" <?php if ($realWeight == 1) { echo 'checked'; } ?>><?php echo $lang['global-yes']; ?></input></td>
	    <td class='left' style='border-top: 2px solid #5aa242; border-right: 2px solid #5aa242;'><input type="radio" name="realWeight" value="0" <?php if ($realWeight == 0) { echo 'checked'; } ?>><?php echo $lang['global-no']; ?></input></td>
	   </tr>
	   <tr>
	    <td class='left' style='position: relative; border-left: 2px solid #5aa242;'><img src='images/questionmark.png' style='padding: 1px;' id="setting23" />&nbsp;&nbsp;<?php echo $lang['flower-limit']; ?><div id='helpBox23' class='helpBox'><?php echo $lang['ss-help23']; ?></div></td>
	    <td colspan='2' class='left' style='border-right: 2px solid #5aa242;'>&nbsp;&nbsp;&nbsp;&nbsp;<input type="number" name="flowerLimit" class="fourDigit" value="<?php echo $flowerLimit; ?>" step="0.01" /> g.</td>
	   </tr>
	   <tr>
	    <td class='left' style='position: relative; border-left: 2px solid #5aa242; border-bottom: 2px solid #5aa242;'><img src='images/questionmark.png' style='padding: 1px;' id="setting24" />&nbsp;&nbsp;<?php echo $lang['extract-limit']; ?><div id='helpBox24' class='helpBox'><?php echo $lang['ss-help24']; ?></div></td>
	    <td colspan='2' class='left' style='border-right: 2px solid #5aa242; border-bottom: 2px solid #5aa242;'>&nbsp;&nbsp;&nbsp;&nbsp;<input type="number" name="extractLimit" class="fourDigit" value="<?php echo $extractLimit; ?>" step="0.01" /> g.</td>
	   </tr>
	   
	   
	   <tr>
	    <td colspan='4'><br /><br /><h2><?php echo $lang['global-members']; ?></h2></td>
	   </tr>
	   
	   
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark.png' style='padding: 1px;' id="setting7" />&nbsp;&nbsp;<?php echo $lang['show-age']; ?><div id='helpBox7' class='helpBox'><?php echo $lang['ss-help7']; ?></div></td>
	    <td class='left'><input type="radio" name="showAge" value="1" <?php if ($showAge == 1) { echo 'checked'; } ?>><?php echo $lang['global-yes']; ?></input></td>
	    <td class='left'><input type="radio" name="showAge" value="0" <?php if ($showAge == 0) { echo 'checked'; } ?>><?php echo $lang['global-no']; ?></input></td>
	   </tr>
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark.png' style='padding: 1px;' id="setting8" />&nbsp;&nbsp;<?php echo $lang['show-gender']; ?><div id='helpBox8' class='helpBox'><?php echo $lang['ss-help8']; ?></div></td>
	    <td class='left'><input type="radio" name="showGender" value="1" <?php if ($showGender == 1) { echo 'checked'; } ?>><?php echo $lang['global-yes']; ?></input></td>
	    <td class='left'><input type="radio" name="showGender" value="0" <?php if ($showGender == 0) { echo 'checked'; } ?>><?php echo $lang['global-no']; ?></input></td>
	   </tr>
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark.png' style='padding: 1px;' id="setting9" />&nbsp;&nbsp;<?php echo $lang['keep-membernumber']; ?><div id='helpBox9' class='helpBox'><?php echo $lang['ss-help9']; ?></div></td>
	    <td class='left'><input type="radio" name="keepNumber" value="1" <?php if ($keepNumber == 1) { echo 'checked'; } ?>><?php echo $lang['global-yes']; ?></input></td>
	    <td class='left'><input type="radio" name="keepNumber" value="0" <?php if ($keepNumber == 0) { echo 'checked'; } ?>><?php echo $lang['global-no']; ?></input></td>
	   </tr>
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark.png' style='padding: 1px;' id="setting10" />&nbsp;&nbsp;<?php echo $lang['membership-fees']; ?><div id='helpBox10' class='helpBox'><?php echo $lang['ss-help10']; ?></div></td>
	    <td class='left'><input type="radio" name="membershipFees" value="1" <?php if ($membershipFees == 1) { echo 'checked'; } ?>><?php echo $lang['global-yes']; ?></input></td>
	    <td class='left'><input type="radio" name="membershipFees" value="0" <?php if ($membershipFees == 0) { echo 'checked'; } ?>><?php echo $lang['global-no']; ?></input></td>
	   </tr>
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark.png' style='padding: 1px;' id="setting11" />&nbsp;&nbsp;<?php echo $lang['high-roller-limit']; ?><div id='helpBox11' class='helpBox'><?php echo $lang['ss-help11']; ?></div></td>
	    <td colspan='2' class='left'>&nbsp;&nbsp;&nbsp;&nbsp;<input type="number" name="highRollerWeekly" class="fourDigit" value="<?php echo $highRollerWeekly; ?>" /> &euro;</td>
	   </tr>
	   <!--<tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark.png' style='padding: 1px;' id="setting11" />&nbsp;&nbsp;<?php echo $lang['leftover product']; ?><div id='helpBox11' class='helpBox'><?php echo $lang['ss-help11']; ?></div></td>
	    <td colspan='2' class='left'>&nbsp;&nbsp;&nbsp;&nbsp;<input type="number" name="highRollerWeekly" class="fourDigit" value="<?php echo $highRollerWeekly; ?>" /> &euro;</td>
	   </tr>-->
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark.png' style='padding: 1px;' id="setting12" />&nbsp;&nbsp;<?php echo $lang['medical-discount']; ?><div id='helpBox12' class='helpBox'><?php echo $lang['ss-help12']; ?></div></td>
	    <td class='left'>&nbsp;&nbsp;&nbsp;&nbsp;<input type="number" name="medicalDiscount" id="medicalDiscount"class="fourDigit" value="<?php echo $medicalDiscount; ?>" /> &euro; per g.</td>
	    <td class='left'>&nbsp;&nbsp;&nbsp;&nbsp;<input type="number" name="medicalDiscountPercentage" id="medicalDiscountPercentage"class="fourDigit" value="<?php echo $medicalDiscountPercentage; ?>" /> %</td>
	   </tr>
	   
	   
	   <tr>
	    <td colspan='4'><br /><br /><h2><?php echo $lang['title-administration']; ?></h2></td>
	   </tr>
	   
	   
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark.png' style='padding: 1px;' id="setting13" />&nbsp;&nbsp;<?php echo $lang['rec-email']; ?><div id='helpBox13' class='helpBox'><?php echo $lang['ss-help13']; ?></div></td>
	    <td class='left'><input type="radio" name="closingMail" value="1" <?php if ($closingMail == 1) { echo 'checked'; } ?>><?php echo $lang['global-yes']; ?></input></td>
	    <td class='left'><input type="radio" name="closingMail" value="0" <?php if ($closingMail == 0) { echo 'checked'; } ?>><?php echo $lang['global-no']; ?></input> <a href='closing-mails.php' target='_blank' style='float: right;'><?php echo $lang['configure']; ?></a>
	    </td>
	   </tr>
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark.png' style='padding: 1px;' id="setting14" />&nbsp;&nbsp;<?php echo $lang['allow-bank-payments']; ?><div id='helpBox14' class='helpBox'><?php echo $lang['ss-help14']; ?></div></td>
	    <td class='left'><input type="radio" name="bankPayments" value="1" <?php if ($bankPayments == 1) { echo 'checked'; } ?>><?php echo $lang['global-yes']; ?></input></td>
	    <td class='left'><input type="radio" name="bankPayments" value="0" <?php if ($bankPayments == 0) { echo 'checked'; } ?>><?php echo $lang['global-no']; ?></input></td>
	   </tr>
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark.png' style='padding: 1px;' id="setting15" />&nbsp;&nbsp;<?php echo $lang['credit-or-direct']; ?><div id='helpBox15' class='helpBox'><?php echo $lang['ss-help15']; ?></div></td>
	    <td class='left'><input type="radio" name="creditOrDirect" value="1" <?php if ($creditOrDirect == 1) { echo 'checked'; } ?>><?php echo $lang['global-credit']; ?></input></td>
	    <td class='left'><input type="radio" name="creditOrDirect" value="0" <?php if ($creditOrDirect == 0) { echo 'checked'; } ?>><?php echo $lang['direct']; ?></input></td>
	   </tr>
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark.png' style='padding: 1px;' id="setting16" />&nbsp;&nbsp;<?php echo $lang['visit-registration']; ?><div id='helpBox16' class='helpBox'><?php echo $lang['ss-help16']; ?></div></td>
	    <td class='left'><input type="radio" name="visitRegistration" value="1" <?php if ($visitRegistration == 1) { echo 'checked'; } ?>><?php echo $lang['automatic']; ?></input></td>
	    <td class='left'><input type="radio" name="visitRegistration" value="0" <?php if ($visitRegistration == 0) { echo 'checked'; } ?>><?php echo $lang['manual']; ?></input></td>
	   </tr>
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark.png' style='padding: 1px;' id="setting19" />&nbsp;&nbsp;<?php echo $lang['crop-images']; ?><div id='helpBox19' class='helpBox'><?php echo $lang['ss-help19']; ?></div></td>
	    <td class='left'><input type="radio" name="cropOrNot" value="1" <?php if ($cropOrNot == 1) { echo 'checked'; } ?>><?php echo $lang['global-yes']; ?></input></td>
	    <td class='left'><input type="radio" name="cropOrNot" value="0" <?php if ($cropOrNot == 0) { echo 'checked'; } ?>><?php echo $lang['global-no']; ?></input></td>
	   </tr>
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark.png' style='padding: 1px;' id="setting20" />&nbsp;&nbsp;<?php echo $lang['workstation-extension']; ?><div id='helpBox20' class='helpBox'><?php echo $lang['ss-help20']; ?></div></td>
	    <td class='left'><input type="radio" name="puestosOrNot" value="1" <?php if ($puestosOrNot == 1) { echo 'checked'; } ?>><?php echo $lang['global-yes']; ?></input></td>
	    <td class='left'><input type="radio" name="puestosOrNot" value="0" <?php if ($puestosOrNot == 0) { echo 'checked'; } ?>><?php echo $lang['global-no']; ?></input></td>
	   </tr>
	   
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark.png' style='padding: 1px;' id="setting21" />&nbsp;&nbsp;<?php echo $lang['accountability-type']; ?><div id='helpBox21' class='helpBox'><?php echo $lang['ss-help21']; ?></div></td>
	    <td class='left'>
	     <div style='margin-bottom: 8px;'><input type="radio" name="openAndClose" value="0" <?php if ($openAndClose == 0) { echo 'checked'; } ?>><?php echo $lang['none']; ?></input></div>
	     <!--<div style='margin-bottom: 8px;'><input type="radio" name="openAndClose" value="0" <?php if ($openAndClose == 1) { echo 'checked'; } ?>><?php echo $lang['automatic']; ?></input></div>-->
	     <div style='margin-bottom: 8px;'><input type="radio" name="openAndClose" value="2" <?php if ($openAndClose == 2) { echo 'checked'; } ?>><?php echo $lang['only-close']; ?></input></div>
	     <div style='margin-bottom: 8px;'><input type="radio" name="openAndClose" value="3" <?php if ($openAndClose == 3) { echo 'checked'; } ?>><?php echo $lang['open-and-close']; ?></input></div>
	     <input type="radio" name="openAndClose" value="4" <?php if ($openAndClose == 4) { echo 'checked'; } ?>><?php echo $lang['open-and-close-shifts']; ?></input>
	    </td>
	    <td class='left'></td>
	   </tr>
	   
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark.png' style='padding: 1px;' id="setting17" />&nbsp;&nbsp;<?php echo $lang['minimum-age']; ?><div id='helpBox17' class='helpBox'><?php echo $lang['ss-help17']; ?></div></td>
	    <td colspan='2' class='left'>&nbsp;&nbsp;&nbsp;&nbsp;<input type="number" name="minAge" class="fourDigit" value="<?php echo $minAge; ?>" /> <?php echo $lang['years']; ?></td>
	   </tr>
	   <tr>
	    <td class='left' style='position: relative;'><img src='images/questionmark.png' style='padding: 1px;' id="setting18" />&nbsp;&nbsp;<?php echo $lang['auto-logout']; ?><div id='helpBox18' class='helpBox'><?php echo $lang['ss-help18']; ?></div></td>
	    <td class='left'>&nbsp;&nbsp;&nbsp;&nbsp;<input type="number" name="logouttime" class="fourDigit" value="<?php echo $logouttime; ?>" /> minutes</td>
	    <td class='left'><input type="checkbox" name="logoutredir" value="1" style="width: 12px; margin-left: 20px;" <?php if ($logoutredir == 1) { echo 'checked'; } ?>/> <?php echo $lang['redirect-to']; ?> www.google.es?</td>
	   </tr>
	 </table>
	 <br /><br />
     <button class='oneClick' name='oneClick' type="submit"><?php echo $lang['global-savechanges']; ?></button>

</form>
	 <br /><br /><br />
	 
<?php displayFooter(); ?>
