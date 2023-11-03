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
		try
		{
			$result = $pdo3->prepare("$extractDetails");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
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


	pageStart($lang['title-extract'], NULL, NULL, "pstrain", 'dev-align-center', $name, $_SESSION['successMessage'], $_SESSION['errorMessage']);

?>

<center><a href="edit-extract.php?extractid=<?php echo $extractid;?>" class="cta1"><?php echo $lang['global-edit']; ?></a></center>
<div class="actionbox-np2 productDisplay">
	<div class="boxcontent">
 <table class= 'purchasetable padOnly'>
<?php

if ($productnumber) {
	
	echo "<tr><td class='left'>" . $lang['product-number'] . ":<span class='purchaseNumber left'><strong>" . sprintf('%03d', $flowernumber) . "</strong></span></td></tr>";
	
}
	echo "<tr><td class='left'>" . $lang['global-registered'] . ":<span class='purchaseNumber left'><strong>" . $registeredTime . "</strong></span></td></tr></table>";

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
	</table>
  </div> <!-- END RIGHTPANE -->
 </div> <!-- END DETAILEDINFO -->
<?php displayFooter(); ?>
