<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);

	// Get the extract ID
	if (isset($_GET['extractid'])) {
		$extractid = $_GET['extractid'];
	} else {
		handleError($lang['error-noextractid'],"");
	}

	// Query to look up extract
	$extractDetails = "SELECT extractid, registeredSince, name, extracttype, extract, description, medicaldescription, THC, CBD, CBN, extractnumber FROM extract WHERE extractid = '{$extractid}'";
	
	// Does extract ID exist?
	$extractCheck = mysql_query($extractDetails);
	if(mysql_num_rows($extractCheck) == 0) {
   		handleError($lang['error-extractidnotexist'],"");
	}
	
	$result = mysql_query($extractDetails)
		or handleError($lang['error-errorloadingextract'],"Error loading extract: " . mysql_error());
	
	if ($result) {
	$row = mysql_fetch_array($result);
	$extractid = $row['extractid'];
	$registeredSince = $row['registeredSince'];
	$name = $row['name'];
	$extract = $row['extract'];
	$extracttype = $row['extracttype'];
	$description = $row['description'];
	$medicaldescription = $row['medicaldescription'];
	$extractnumber = $row['extractnumber'];
	$THC = $row['THC'];
	$CBD = $row['CBD'];
	$CBN = $row['CBN'];
	$registeredTime = date("d M y", strtotime($registeredSince));

	
} else {
		handle_error($lang['error-findinginfo'],"Error locating extract with ID {$extract_id}");
}

	pageStart($lang['title-extract'], NULL, NULL, "pstrain", NULL, $name, $_SESSION['successMessage'], $_SESSION['errorMessage']);

?>

<center><a href="edit-extract.php?extractid=<?php echo $extractid;?>" class="cta"><?php echo $lang['global-edit']; ?></a></center>
<div class="actionbox productDisplay">
 <table class='padOnly'>
<?php

if ($productnumber) {
	
	echo "<tr><td class='left'>" . $lang['product-number'] . ":</td><td class='left'><strong>" . sprintf('%03d', $flowernumber) . "</strong></td></tr>";
	
}
	echo "<tr><td class='left'>" . $lang['global-registered'] . ":</td><td class='left'><strong>" . $registeredTime . "</strong></td></tr></table>";

	echo "<span class='profilesecond'>" . $extracttype . "</span> ";
	if ($extract) {
		echo $lang['global-extract'] . ": <strong>" . $extract . "</strong>";
	} 
?>
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
