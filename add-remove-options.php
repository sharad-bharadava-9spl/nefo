<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	/***** FORM SUBMIT END *****/
	
	
	
	// Get the purchase ID
	if (isset($_GET['purchaseid'])) {
		$purchaseid = $_GET['purchaseid'];
	} else if ($_POST['purchaseid'] != '') {
		$purchaseid = $_POST['purchaseid'];
	} else {
		handleError($lang['error-nopurchaseid'],"");
	}
		// Query to look for purchase
	$purchaseDetails = "SELECT category, productid, salesPrice, purchasePrice FROM purchases WHERE purchaseid = $purchaseid";
		try
		{
			$result = $pdo3->prepare("$purchaseDetails");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$category = $row['category'];
		$productid = $row['productid'];
		$salesPrice = $row['salesPrice'];
		$purchasePrice = $row['purchasePrice'];
		if ($category == 1) {
			// Query to look up flowers
			$selectFlower = "SELECT flowerid, breed2, name FROM flower WHERE flowerid = {$productid}";
			try
			{
				$result = $pdo3->prepare("$selectFlower");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
				$name = $row['name'];
				$breed2 = $row['breed2'];
				$categoryName = 'Flower (g.)';
				
			if ($breed2 != '') {
				$name = $name . " x " . $breed2;
			}
		}
		else if ($category == 2) {
				// Query to look up extract
				$selectExtract = "SELECT extractid, extracttype, extract, name FROM extract WHERE extractid = {$productid}";
				try
				{
					$result = $pdo3->prepare("$selectExtract");
					$result->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			
				$row = $result->fetch();
					$name = $row['name'];
					$categoryName = 'Extract (g.)';
			}
			else{
			
					// Query to look for category
					$categoryDetails = "SELECT name, type FROM categories WHERE id = $category";
					try
					{
						$result = $pdo3->prepare("$categoryDetails");
						$result->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
				
					$row = $result->fetch();
						$type = $row['type'];
						
					if ($type == 0) {
						$type = "u";
					} else {
						$type = "g";
					}

						$categoryName = $row['name'] . " ($type.)";
						
					// Query to look for product
					$selectProducts = "SELECT name from products WHERE productid = $productid";
					try
					{
						$result = $pdo3->prepare("$selectProducts");
						$result->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
				
					$row = $result->fetch();
							$name = $row['name'];
			}

	pageStart($lang['title-addorremove-option'], NULL, '', "ppurchase", "admin dev-align-center", $lang['addremove-addorremove-option'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

	
?>


<div id='productoverview'>
 <table>
  <tr>
   <td><?php echo $lang['global-category']; ?>:</td>
   <td class='yellow fat'><?php echo $categoryName; ?></td>
  </tr>
  <tr>
   <td><?php echo $lang['global-strain']; ?>:</td>
   <td class='yellow fat'><a href='purchase.php?purchaseid=<?php echo $purchaseid; ?>'><?php echo $name; ?></a></td>
  </tr>
 </table>
</div>
<br />
<?php
  if(isset($_SESSION['internalStash'])){
  	    $internalStash = $_SESSION['internalStash'];
  }  
  if(isset($_SESSION['externalStash'])){
  	    $externalStash = $_SESSION['externalStash'];
  }  
  if(isset($_SESSION['totalProduct'])){
  	    $totalProduct = $_SESSION['totalProduct'];
  }
?>
<div id="ctawrap">
	<a href="add-or-remove.php?purchaseid=<?php echo $purchaseid ?>" class="cta1"><span class="ctatext"><?php echo $lang['addremove-removefrom-dispense'] ?></span> (In dispensary: <?php echo $totalProduct; ?> g.)</a>
	<a href="add-or-remove-warehouse.php?internal&purchaseid=<?php echo $purchaseid ?>" class="cta1"><span class="ctatext"><?php echo $lang['addremove-removefrom-internalstash']; ?></span> (In Internal Stash: <?php echo $internalStash; ?> g.)</a>
	<a href="add-or-remove-warehouse.php?external&purchaseid=<?php echo $purchaseid ?>" class="cta1"><span class="ctatext"><?php echo $lang['addremove-removefrom-externalstash'];  ?></span> (In External Stash: <?php echo $externalStash; ?> g.)</a>
</div>
<?php  displayFooter(); ?>
