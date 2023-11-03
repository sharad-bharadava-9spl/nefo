<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);

	// Get the flower ID
	if (isset($_GET['flowerid'])) {
		$flowerid = $_GET['flowerid'];
	} else {
		handleError($lang['error-noflowerid'],"");
	}

	// Query to look up flowers
	$flowerDetails = "SELECT flowerid, registeredSince, name, flowertype, description, medicaldescription, breed2, sativaPercentage, THC, CBD, CBN, flowernumber FROM flower WHERE flowerid = '{$flowerid}'";
	
	// Does flower ID exist?
	$flowerCheck = mysql_query($flowerDetails);
	if(mysql_num_rows($flowerCheck) == 0) {
   		handleError($lang['error-floweridnotexist'],"");
	}
	
	$result = mysql_query($flowerDetails)
		or handleError($lang['error-errorloadingflower'],"Error loading flower: " . mysql_error());
	
	if ($result) {
	$row = mysql_fetch_array($result);
	$flowerid = $row['flowerid'];
	$registeredSince = $row['registeredSince'];
	$name = $row['name'];
	$flowertype = $row['flowertype'];
	$description = $row['description'];
	$medicaldescription = $row['medicaldescription'];
	$breed2 = $row['breed2'];
	$sativaPercentage = $row['sativaPercentage'];
	$flowernumber = $row['flowernumber'];
	$THC = $row['THC'];
	$CBD = $row['CBD'];
	$CBN = $row['CBN'];
	$registeredTime = date("d M y", strtotime($registeredSince));

	
} else {
		handle_error($lang['error-findinginfo'],"Error locating flower with ID {$flower_id}");
}

	if ($breed2) {
		$strainname = $name . " x " . $breed2;
		} else {
		$strainname = $name;
	}
	
	
	pageStart($lang['title-flower'], NULL, NULL, "pstrain", NULL, $strainname, $_SESSION['successMessage'], $_SESSION['errorMessage']);


?>

<center><a href="edit-flower.php?flowerid=<?php echo $flowerid;?>" class="cta"><?php echo $lang['global-edit']; ?></a></center>
<div class="actionbox productDisplay">
 <table class='padOnly'>
<?php

if ($productnumber) {
	
	echo "<tr><td class='left'>" . $lang['product-number'] . ":</td><td class='left'><strong>" . sprintf('%03d', $flowernumber) . "</strong></td></tr>";
	
}
	echo "<tr><td class='left'>" . $lang['global-registered'] . ":</td><td class='left'><strong>" . $registeredTime . "</strong></td></tr></table>";
	
	echo "<span class='profilesecond'>" . $flowertype . "</span> ";
	
	if ($flowertype == 'Sativa') {
		echo "(" . $sativaPercentage . "%)";
	} else if ($flowertype == 'Indica') {
		$indicaPercentage = 100 - $sativaPercentage;
		echo "(" . $indicaPercentage . "%)";
	} else if ($flowertype == 'Hybrid') {
		$indicaPercentage = 100 - $sativaPercentage;
		echo "(Sativa: " . $sativaPercentage . "% / Indica: " . $indicaPercentage . "%)";
	}	?>
</span><br />

      <?php echo "THC: " . $THC . " %"; ?><br />
      <?php echo "CBD: " . $CBD . " %"; ?><br />
      <?php echo "CBN: " . $CBN . " %"; ?><br /><br />
<?php
	if ($description) {
		echo "<br /><strong>" . $lang['extracts-description'] . ": </strong><br />" . $description . "<br />";
	}
	if ($medicaldescription) {
		echo "<br /><strong>" . $lang['extracts-medicaldesc'] . ": </strong><br />" . $medicaldescription . "<br />";
	}
?>
  </div> <!-- END RIGHTPANE -->
 </div> <!-- END DETAILEDINFO -->
<?php displayFooter(); ?>
