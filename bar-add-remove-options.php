<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// Get the purchase ID
	
	
	
	
	if (isset($_GET['purchaseid'])) {
		$purchaseid = $_GET['purchaseid'];
	} else if ($_POST['purchaseid'] != '') {
		$purchaseid = $_POST['purchaseid'];
	} else {
		handleError($lang['error-nopurchaseid'],"");
	}
	
	// Query to look for purchase
	$purchaseDetails = "SELECT category, productid, salesPrice, purchasePrice FROM b_purchases WHERE purchaseid = $purchaseid";
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

					
		// Query to look for category
		$categoryDetails = "SELECT name FROM b_categories WHERE id = $category";
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
			$categoryName = $row['name'];
			
		// Query to look for product
		$selectProducts = "SELECT name from b_products WHERE productid = $productid";
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
  if(isset($_SESSION['BarinternalStash'])){
  	    $internalStash = $_SESSION['BarinternalStash'];
  }  
  if(isset($_SESSION['BarexternalStash'])){
  	    $externalStash = $_SESSION['BarexternalStash'];
  }  
  if(isset($_SESSION['BartotalProduct'])){
  	    $totalProduct = $_SESSION['BartotalProduct'];
  }
?>
<div id="ctawrap">
	<a href="bar-add-or-remove.php?purchaseid=<?php echo $purchaseid ?>" class="cta1"><span class="ctatext"><?php echo $lang['addremove-removefrom-dispense'] ?></span> (In dispensary: <?php echo $totalProduct; ?> u.)</a>
	<a href="bar-add-or-remove-warehouse.php?internal&purchaseid=<?php echo $purchaseid ?>" class="cta1"><span class="ctatext"><?php echo $lang['addremove-removefrom-internalstash']; ?></span> (In Internal Stash: <?php echo $internalStash; ?> u.)</a>
	<a href="bar-add-or-remove-warehouse.php?external&purchaseid=<?php echo $purchaseid ?>" class="cta1"><span class="ctatext"><?php echo $lang['addremove-removefrom-externalstash'];  ?></span> (In Extrenal Stash: <?php echo $externalStash; ?> u.)</a>
</div>

<?php displayFooter(); ?>
