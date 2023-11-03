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
	$selectCats = "SELECT id, name, type FROM categories WHERE id > 2 ORDER by id ASC";
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

<a href="new-flower.php" class='ctalink'>
 <table>
  <tr>
   <td style='height: 70px;'><img src="images/icon-flower.png" class="midalign" /></td>
  </tr>
  <tr>
   <td><?php echo $lang['global-flower']; ?></td>
  </tr>
  <tr>
   <td><span class='usergrouptext2' style='color: #444; text-transform: initial;'>gr</span></td>
  </tr>
 </table>
</a>
 
<a href="new-extract.php" class='ctalink'>
 <table>
  <tr>
   <td style='height: 70px;'><img src="images/icon-extract.png" class="midalign" /></td>
  </tr>
  <tr>
   <td><?php echo $lang['global-extract']; ?></td>
  </tr>
  <tr>
   <td><span class='usergrouptext2' style='color: #444; text-transform: initial;'>gr</span></td>
  </tr>
 </table>
</a>

<?php
while ($category = $result->fetch()) {
	$categoryid = $category['id'];
	$categoryname = $category['name'];
    $catType = $category['type'];

	if ($catType == 1) {
		$catT = 'gr';
	} else {
		$catT = 'u';
	}

	
	echo "
<a href='new-goods.php?id=$categoryid' class='ctalink'>
 <table>
  <tr>
   <td style='height: 70px;'><img src='images/icon-cannabis.png' class='midalign' /></td>
  </tr>
  <tr>
   <td>$categoryname</td>
  </tr>
  <tr>
   <td><span class='usergrouptext2' style='color: #444; text-transform: initial;'>$catT</span></td>
  </tr>
 </table>
</a>
";

}
?>

  

<?php displayFooter(); ?>

