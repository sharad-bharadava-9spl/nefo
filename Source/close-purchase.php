<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);

	pageStart($lang['title-closeproduct'], NULL, NULL, "pclosePurchase", "", $lang['closeproduct-closeproduct'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
?>

<div class="actionbox">

<h3><?php echo $lang['global-flowers']; ?></h3>
<?php
		
	$selectFlower = "SELECT g.breed2, g.name, p.purchaseid FROM flower g, purchases p WHERE p.category = 1 AND p.productid = g.flowerid AND p.closedAt IS NULL ORDER BY g.name ASC;";
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
	
		while ($flower = $result->fetch()) {
			if ($flower['breed2'] != '') {
			$name = $flower['name'] . " x " . $flower['breed2'];
		} else {
			$name = $flower['name'];
		}


	$flower_row = sprintf("
	<a href='close-purchase-2.php?purchaseid=%d'>%s</a><br />",
	  $flower['purchaseid'], $name
	  );
	  echo $flower_row;
  }
echo "</div><div class='actionbox'><h3>{$lang['global-extracts']}</h3>";
  
  // AND NOW THE H TABLE:

  	$selectExtract = "SELECT h.name, p.purchaseid FROM extract h, purchases p WHERE p.category = 2 AND p.productid = h.extractid AND p.closedAt IS NULL ORDER BY h.name ASC;";
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
	
		while ($extract = $result->fetch()) {

	$extract_row =	sprintf("
	<a href='close-purchase-2.php?purchaseid=%d'>%s</a><br />",
	  $extract['purchaseid'], $extract['name']
	  );
	  echo $extract_row;
  }
  
echo "</div>";
  
  
  // AND NOW THE OTHER CATEGORIES:
  
	// Query to look up categories
	$selectCats = "SELECT id, name from categories WHERE id > 2 ORDER by id ASC";
		try
		{
			$results = $pdo3->prepare("$selectCats");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($category = $results->fetch()) {
		
		$categoryid = $category['id'];
		$name = $category['name'];
		
		echo "<div class='actionbox'><h3>$name</h3>";
		
		// For each cat, look up products
		$selectProducts = "SELECT pr.name, p.purchaseid from products pr, purchases p WHERE p.category = $categoryid AND pr.productid = p.productid AND p.closedAt IS NULL ORDER BY pr.name ASC;";
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
	
		while ($product = $result->fetch()) {
	

			$product_row = sprintf("
			<a href='close-purchase-2.php?purchaseid=%d'>%s</a><br />",
			  $product['purchaseid'], $product['name']
			  );
			  echo $product_row;
			  
		}
		
		echo "</div>";

	}
  
displayFooter(); ?>

