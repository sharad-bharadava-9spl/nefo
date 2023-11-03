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
	if (isset($_GET['productid'])) {
		$productid = $_GET['productid'];
	} else {
		handleError($lang['error-noextractid'],"");
	}

	// Query to look up extract
	$extractDetails = "SELECT productid, registeredSince, name, description, medicaldescription, productnumber, category, sativaPercentage, THC, CBD, CBN FROM products WHERE productid = $productid";
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
		$productid = $row['productid'];
		$registeredSince = $row['registeredSince'];
		$name = $row['name'];
		$description = $row['description'];
		$medicaldescription = $row['medicaldescription'];
		$productnumber = $row['productnumber'];
		$category = $row['category'];
		$sativaPercentage = $row['sativaPercentage'];
		$THC = $row['THC'];
		$CBD = $row['CBD'];
		$CBN = $row['CBN'];
		$registeredTime = date("d M y", strtotime($registeredSince));

	pageStart($lang['global-product'], NULL, NULL, "pstrain", 'dev-align-center', $name, $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
		// Query to look up category
		$selectCats = "SELECT type from categories WHERE id = $category";
		try
		{
			$result = $pdo3->prepare("$selectCats");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
	  	    $catType = $row['type'];
	  	    


?>

<center><a href="edit-product.php?productid=<?php echo $productid;?>" class="cta1"><?php echo $lang['global-edit']; ?></a></center>
<div class="actionbox-np2 productDisplay">
	<div class="boxcontent">
 <table class='padOnly purchasetable'>
<?php
if ($catType == 0) {

	if ($productnumber) {
		
		echo "<tr><td class='left'>" . $lang['product-number'] . ":<span class='purchaseNumber'><strong>" . sprintf('%03d', $productnumber) . "</strong></span></td></tr>";
		
	}
	echo "<tr><td class='left'>" . $lang['global-registered'] . ":<span class='purchaseNumber'><strong>" . $registeredTime . "</strong></span></td></tr></table>";

	if ($description) {
		echo "<br /><strong>" . $lang['extracts-description'] . ": <span class='purchaseNumber'></strong></span><br />" . $description . "<br />";
	}
	if ($medicaldescription) {
		echo "<br /><span class='purchaseNumber'><strong>" . $lang['extracts-medicaldesc'] . ": </strong></span><br />" . $medicaldescription . "<br />";
	}
} else { 

	if ($productnumber) {
		
		echo "<tr><td class='left'>" . $lang['product-number'] . ":<span class='purchaseNumber'><strong>" . sprintf('%03d', $flowernumber) . "</strong></span></td></tr>";
		
	}
	echo "<tr><td class='left'>" . $lang['global-registered'] . ":<span class='purchaseNumber'><strong>" . $registeredTime . "</strong></span></td></tr></table>";
	
	echo "<span class='profilesecond'>" . $flowertype . "</span> ";
	
		$indicaPercentage = 100 - $sativaPercentage;
		echo "(Sativa: " . $sativaPercentage . "% / Indica: " . $indicaPercentage . "%)";
		
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
	
}
?>

<span class="profilefourth">
</span>
	</div>
  </div> <!-- END RIGHTPANE -->
 </div> <!-- END DETAILEDINFO -->
<?php displayFooter(); ?>
