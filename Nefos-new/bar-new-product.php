<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	

	pageStart('New bar product', NULL, NULL, "newpurchase", "admin", 'NEW BAR PRODUCT', $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
?>

<div class="actionbox">
 <h2><span>Choose category</span></h2>
  <form id="registerForm" action="bar-new-product-2.php" method="POST">
    <input type="hidden" name="category" value="1" />
   <select class="fakeInput" name="prePurchase">
    <option value=""><?php echo $lang['addremove-pleaseselect']; ?></option>
<?php
	// Query to look up categories
	$selectFlower = "SELECT id, name FROM b_categories WHERE id > 48 OR (id = 35 OR id = 34 OR id = 30 OR id = 19) ORDER by name ASC";
		try
		{
			$results = $pdo3->prepare("$selectFlower");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($flower = $results->fetch()) {
		
				$flower_row = sprintf("<option value='%d'>%s</option>",
			  						 $flower['id'], $flower['name']);
			  	echo $flower_row;
		  		}
	
?>
  </select><br />
  <button type="submit"><?php echo $lang['global-select']; ?></button>
   </form>
   <br />
   <?php echo $lang['member-orcaps']; ?>
   <br />
   <a href="bar-new-category.php">New category</a>
</div>
  
  
  

<?php displayFooter(); ?>

