<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	

	pageStart($lang['pur-newproduct'], NULL, NULL, "pnewstrain", "admin", $lang['pur-newproduct'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

	// Query to look up categories
	$selectCats = "SELECT id, name, type from categories ORDER by id ASC";
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
	
?>


<?php
while ($category = $result->fetch()) {
	$categoryid = $category['id'];
	$categoryname = $category['name'];
    $catType = $category['type'];

	if ($catType == 1) {
		$catT = '(g.)';
	} else {
		$catT = '(u.)';
	}

	
	echo "
<a href='new-goods.php?id=$categoryid'>
 <div class='actionbox'>
  <h2><img src='images/goods-icon.png' class='midalign' />&nbsp;&nbsp;&nbsp;<span>$categoryname $catT</span></h2>
 </div>
</a>
";

}
?>

  

<?php displayFooter(); ?>

