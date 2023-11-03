<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
		
	$domain = $_SESSION['domain'];
	
	if (isset($_POST['numberOfMembers'])) {
		$numberOfMembers = $_POST['numberOfMembers'];
	} else {
		$numberOfMembers = 5;
	}		
		
	pageStart($lang['title-dispensary'], NULL, NULL, "pdispensary", "product admin", $lang['topspenderscaps'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
?>

	 <table class='default' id='cloneTable' style='text-align: left;'>
      <tr class='nonhover'>
       <td><center>
		<div style='display: inline-block; border: 2px solid #5aa242; padding: 10px;'>
		&nbsp;<strong><?php echo $lang['number-members']; ?>:</strong><br /> 
        <form action='' method='POST' style='margin-top: 3px;'>
	     <input type='number' name='numberOfMembers' class='fourDigit' value='<?php echo $numberOfMembers; ?>'><br />
	     <button>OK</button>
        </form>
        </div></center>
       </td>
      </tr>
     </table>

<h3 class='title'><?php echo $lang['global-total']; ?></h3>

<br />
<div class='displaybox adminHidden'>
<?php 

$currMonth = date("F");

echo "<h3>$currMonth</h3>
 <table class='winnerStats default'>";
 
		// Look up this months sales
		$selectTopUsers = "SELECT u.user_id, u.first_name, u.memberno, u.photoExt, SUM(d.amount) from salesdetails d JOIN sales s ON s.saleid = d.saleid JOIN users u ON s.userid = u.user_id WHERE MONTH(s.saletime) = MONTH(NOW()) AND YEAR(s.saletime) = YEAR(NOW()) GROUP BY u.user_id ORDER BY SUM(d.amount) DESC LIMIT $numberOfMembers";
		try
		{
			$result = $pdo3->prepare("$selectTopUsers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		$i = 1;
		while ($topUser = $result->fetch()) {
			$user_id = $topUser['user_id'];
			$first_name = $topUser['first_name'];
			$memberno = $topUser['memberno'];
			$photoExt = $topUser['photoExt'];
			$amount = $topUser['SUM(d.amount)'];
			$amount = number_format($amount,0);
			
			// if i is first then do the image, otherwise don't
			if ($i == 1) {
			echo <<<EOD
  <tr>
   <td colspan='3'><img class='winnerPic' src='images/_$domain/members/$user_id.$photoExt' /></td>
  </tr>
  <tr class='first'>
   <td class='first'>
    <a class='winnerLink' href='profile.php?user_id=$user_id'>$first_name <span class='smallerfont'>#</span>$memberno</a>
   </td>
   <td>$amount <span class='smallerfont'>&euro;</span></td>
  </tr>
		
EOD;
			} else {
			
			echo <<<EOD
  <tr>
   <td class='first'>
    <a class='winnerLink' href='profile.php?user_id=$user_id'>$first_name <span class='smallerfont'>#</span>$memberno</a>
   </td>
   <td>$amount <span class='smallerfont'>&euro;</span></td>
  </tr>
		
EOD;
			}
			
$i++;

}
		

?>
 </table>
</div>

<div class='displaybox adminHidden'>

<?php 

$currMonth = date("F", strtotime("first day of last month"));
echo "<h3>$currMonth</h3>
 <table class='winnerStats default'>";
		// Look up this months sales
		
		$selectTopUsers = "SELECT u.user_id, u.first_name, u.memberno, u.photoExt, SUM(d.amount) from salesdetails d JOIN sales s ON s.saleid = d.saleid JOIN users u ON s.userid = u.user_id WHERE MONTH(s.saletime) = MONTH(DATE_ADD((NOW()), INTERVAL -1 MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -1 MONTH)) GROUP BY u.user_id ORDER BY SUM(d.amount) DESC LIMIT $numberOfMembers";
		try
		{
			$result = $pdo3->prepare("$selectTopUsers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		$i = 1;
		while ($topUser = $result->fetch()) {
			$user_id = $topUser['user_id'];
			$first_name = $topUser['first_name'];
			$memberno = $topUser['memberno'];
			$photoExt = $topUser['photoExt'];
			$amount = $topUser['SUM(d.amount)'];
			$amount = number_format($amount,0);

			
			// if i is first then do the image, otherwise don't
			if ($i == 1) {
			echo <<<EOD
  <tr>
   <td colspan='3'><img class='winnerPic' src='images/_$domain/members/$user_id.$photoExt' /></td>
  </tr>
  <tr class='first'>
   <td class='first'>
    <a class='winnerLink' href='profile.php?user_id=$user_id'>$first_name <span class='smallerfont'>#</span>$memberno</a>
   </td>
   <td>$amount <span class='smallerfont'>&euro;</span></td>
  </tr>
		
EOD;
			} else {
			
			echo <<<EOD
  <tr>
   <td class='first'>
    <a class='winnerLink' href='profile.php?user_id=$user_id'>$first_name <span class='smallerfont'>#</span>$memberno</a>
   </td>
   <td>$amount <span class='smallerfont'>&euro;</span></td>
  </tr>
		
EOD;
			}
			
$i++;

}
		

?>
 </table>
</div>

<div class='displaybox adminHidden'>

<?php 

$currMonth = date("F", strtotime("-1 months", strtotime("first day of last month") ));
echo "<h3>$currMonth</h3>
 <table class='winnerStats default'>";
		// Look up this months sales
		$selectTopUsers = "SELECT u.user_id, u.first_name, u.memberno, u.photoExt, SUM(d.amount) from salesdetails d JOIN sales s ON s.saleid = d.saleid JOIN users u ON s.userid = u.user_id WHERE MONTH(s.saletime) = MONTH(DATE_ADD((NOW()), INTERVAL -2 MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -2 MONTH)) GROUP BY u.user_id ORDER BY SUM(d.amount) DESC LIMIT $numberOfMembers";
		try
		{
			$result = $pdo3->prepare("$selectTopUsers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		$i = 1;
		while ($topUser = $result->fetch()) {
			$user_id = $topUser['user_id'];
			$first_name = $topUser['first_name'];
			$memberno = $topUser['memberno'];
			$photoExt = $topUser['photoExt'];
			$amount = $topUser['SUM(d.amount)'];
			$amount = number_format($amount,0);

			
			// if i is first then do the image, otherwise don't
			if ($i == 1) {
			echo <<<EOD
  <tr>
   <td colspan='3'><img class='winnerPic' src='images/_$domain/members/$user_id.$photoExt' /></td>
  </tr>
  <tr class='first'>
   <td class='first'>
    <a class='winnerLink' href='profile.php?user_id=$user_id'>$first_name <span class='smallerfont'>#</span>$memberno</a>
   </td>
   <td>$amount <span class='smallerfont'>&euro;</span></td>
  </tr>
		
EOD;
			} else {
			
			echo <<<EOD
  <tr>
   <td class='first'>
    <a class='winnerLink' href='profile.php?user_id=$user_id'>$first_name <span class='smallerfont'>#</span>$memberno</a>
   </td>
   <td>$amount <span class='smallerfont'>&euro;</span></td>
  </tr>
		
EOD;
			}
			
$i++;

}
		

?>
 </table>
</div>




















<br /><br />
<h3 class='title'><?php echo $lang['global-flowers']; ?></h3>
<br />

<div class='displaybox adminHidden'>

<?php

$currMonth = date("F");

echo "<h3>$currMonth</h3>
 <table class='winnerStats default'>";
 
		// Look up this months sales
		$selectTopUsers = "SELECT u.user_id, u.first_name, u.memberno, u.photoExt, SUM(d.amount), SUM(d.quantity) from salesdetails d JOIN sales s ON s.saleid = d.saleid JOIN users u ON s.userid = u.user_id WHERE MONTH(s.saletime) = MONTH(NOW()) AND YEAR(s.saletime) = YEAR(NOW()) AND d.category = 1 GROUP BY u.user_id ORDER BY SUM(d.amount) DESC LIMIT $numberOfMembers";
		try
		{
			$result = $pdo3->prepare("$selectTopUsers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		$i = 1;
		while ($topUser = $result->fetch()) {
			$user_id = $topUser['user_id'];
			$first_name = $topUser['first_name'];
			$memberno = $topUser['memberno'];
			$photoExt = $topUser['photoExt'];
			$amount = $topUser['SUM(d.amount)'];
			$amount = number_format($amount,0);
			$quantity = $topUser['SUM(d.quantity)'];
			$quantity = number_format($quantity,0);
			
			// if i is first then do the image, otherwise don't
			if ($i == 1) {
			echo <<<EOD
  <tr>
   <td colspan='3'><img class='winnerPic' src='images/_$domain/members/$user_id.$photoExt' /></td>
  </tr>
  <tr class='first'>
   <td class='first'>
    <a class='winnerLink' href='profile.php?user_id=$user_id'>$first_name <span class='smallerfont'>#</span>$memberno</a>
   </td>
   <td>$quantity <span class='smallerfont'>g.</span></td>
   <td>$amount <span class='smallerfont'>&euro;</span></td>
  </tr>
		
EOD;
			} else {
			
			echo <<<EOD
  <tr>
   <td class='first'>
    <a class='winnerLink' href='profile.php?user_id=$user_id'>$first_name <span class='smallerfont'>#</span>$memberno</a>
   </td>
   <td>$quantity <span class='smallerfont'>g.</span></td>
   <td>$amount <span class='smallerfont'>&euro;</span></td>
  </tr>
		
EOD;
			}
			
$i++;

}
		

?>
 </table>
</div>

<div class='displaybox adminHidden'>

<?php 

$currMonth = date("F", strtotime("first day of last month"));
echo "<h3>$currMonth</h3>
 <table class='winnerStats default'>";
		// Look up this months sales
		
		$selectTopUsers = "SELECT u.user_id, u.first_name, u.memberno, u.photoExt, SUM(d.amount), SUM(d.quantity) from salesdetails d JOIN sales s ON s.saleid = d.saleid JOIN users u ON s.userid = u.user_id WHERE MONTH(s.saletime) = MONTH(DATE_ADD((NOW()), INTERVAL -1 MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -1 MONTH)) AND d.category = 1 GROUP BY u.user_id ORDER BY SUM(d.amount) DESC LIMIT $numberOfMembers";
		try
		{
			$result = $pdo3->prepare("$selectTopUsers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		$i = 1;
		while ($topUser = $result->fetch()) {
			$user_id = $topUser['user_id'];
			$first_name = $topUser['first_name'];
			$memberno = $topUser['memberno'];
			$photoExt = $topUser['photoExt'];
			$amount = $topUser['SUM(d.amount)'];
			$amount = number_format($amount,0);
			$quantity = $topUser['SUM(d.quantity)'];
			$quantity = number_format($quantity,0);

			
			// if i is first then do the image, otherwise don't
			if ($i == 1) {
			echo <<<EOD
  <tr>
   <td colspan='3'><img class='winnerPic' src='images/_$domain/members/$user_id.$photoExt' /></td>
  </tr>
  <tr class='first'>
   <td class='first'>
    <a class='winnerLink' href='profile.php?user_id=$user_id'>$first_name <span class='smallerfont'>#</span>$memberno</a>
   </td>
   <td>$quantity <span class='smallerfont'>g.</span></td>
   <td>$amount <span class='smallerfont'>&euro;</span></td>
  </tr>
		
EOD;
			} else {
			
			echo <<<EOD
  <tr>
   <td class='first'>
    <a class='winnerLink' href='profile.php?user_id=$user_id'>$first_name <span class='smallerfont'>#</span>$memberno</a>
   </td>
   <td>$quantity <span class='smallerfont'>g.</span></td>
   <td>$amount <span class='smallerfont'>&euro;</span></td>
  </tr>
		
EOD;
			}
			
$i++;

}
		

?>
 </table>
</div>

<div class='displaybox adminHidden'>

<?php 

$currMonth = date("F", strtotime("-1 months", strtotime("first day of last month") ));
echo "<h3>$currMonth</h3>
 <table class='winnerStats default'>";
		// Look up this months sales
		$selectTopUsers = "SELECT u.user_id, u.first_name, u.memberno, u.photoExt, SUM(d.amount), SUM(d.quantity) from salesdetails d JOIN sales s ON s.saleid = d.saleid JOIN users u ON s.userid = u.user_id WHERE MONTH(s.saletime) = MONTH(DATE_ADD((NOW()), INTERVAL -2 MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -2 MONTH)) AND d.category = 1 GROUP BY u.user_id ORDER BY SUM(d.amount) DESC LIMIT $numberOfMembers";
		try
		{
			$result = $pdo3->prepare("$selectTopUsers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		$i = 1;
		while ($topUser = $result->fetch()) {
			$user_id = $topUser['user_id'];
			$first_name = $topUser['first_name'];
			$memberno = $topUser['memberno'];
			$photoExt = $topUser['photoExt'];
			$amount = $topUser['SUM(d.amount)'];
			$amount = number_format($amount,0);
			$quantity = $topUser['SUM(d.quantity)'];
			$quantity = number_format($quantity,0);
			
			// if i is first then do the image, otherwise don't
			if ($i == 1) {
			echo <<<EOD
  <tr>
   <td colspan='3'><img class='winnerPic' src='images/_$domain/members/$user_id.$photoExt' /></td>
  </tr>
  <tr class='first'>
   <td class='first'>
    <a class='winnerLink' href='profile.php?user_id=$user_id'>$first_name <span class='smallerfont'>#</span>$memberno</a>
   </td>
   <td>$quantity <span class='smallerfont'>g.</span></td>
   <td>$amount <span class='smallerfont'>&euro;</span></td>
  </tr>
		
EOD;
			} else {
			
			echo <<<EOD
  <tr>
   <td class='first'>
    <a class='winnerLink' href='profile.php?user_id=$user_id'>$first_name <span class='smallerfont'>#</span>$memberno</a>
   </td>
   <td>$quantity <span class='smallerfont'>g.</span></td>
   <td>$amount <span class='smallerfont'>&euro;</span></td>
  </tr>
		
EOD;
			}
			
$i++;

}
		

?>
 </table>
</div>




















<br /><br />
<h3 class='title'><?php echo $lang['global-extracts']; ?></h3>
<br />

<div class='displaybox adminHidden'>

<?php

$currMonth = date("F");

echo "<h3>$currMonth</h3>
 <table class='winnerStats default'>";
 
		// Look up this months sales
		$selectTopUsers = "SELECT u.user_id, u.first_name, u.memberno, u.photoExt, SUM(d.amount), SUM(d.quantity) from salesdetails d JOIN sales s ON s.saleid = d.saleid JOIN users u ON s.userid = u.user_id WHERE MONTH(s.saletime) = MONTH(NOW()) AND YEAR(s.saletime) = YEAR(NOW()) AND d.category = 2 GROUP BY u.user_id ORDER BY SUM(d.amount) DESC LIMIT $numberOfMembers";
		try
		{
			$result = $pdo3->prepare("$selectTopUsers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		$i = 1;
		while ($topUser = $result->fetch()) {
			$user_id = $topUser['user_id'];
			$first_name = $topUser['first_name'];
			$memberno = $topUser['memberno'];
			$photoExt = $topUser['photoExt'];
			$amount = $topUser['SUM(d.amount)'];
			$amount = number_format($amount,0);
			$quantity = $topUser['SUM(d.quantity)'];
			$quantity = number_format($quantity,0);
			
			// if i is first then do the image, otherwise don't
			if ($i == 1) {
			echo <<<EOD
  <tr>
   <td colspan='3'><img class='winnerPic' src='images/_$domain/members/$user_id.$photoExt' /></td>
  </tr>
  <tr class='first'>
   <td class='first'>
    <a class='winnerLink' href='profile.php?user_id=$user_id'>$first_name <span class='smallerfont'>#</span>$memberno</a>
   </td>
   <td>$quantity <span class='smallerfont'>g.</span></td>
   <td>$amount <span class='smallerfont'>&euro;</span></td>
  </tr>
		
EOD;
			} else {
			
			echo <<<EOD
  <tr>
   <td class='first'>
    <a class='winnerLink' href='profile.php?user_id=$user_id'>$first_name <span class='smallerfont'>#</span>$memberno</a>
   </td>
   <td>$quantity <span class='smallerfont'>g.</span></td>
   <td>$amount <span class='smallerfont'>&euro;</span></td>
  </tr>
		
EOD;
			}
			
$i++;

}
		

?>
 </table>
</div>

<div class='displaybox adminHidden'>

<?php 

$currMonth = date("F", strtotime("first day of last month"));
echo "<h3>$currMonth</h3>
 <table class='winnerStats default'>";
		// Look up this months sales
		
		$selectTopUsers = "SELECT u.user_id, u.first_name, u.memberno, u.photoExt, SUM(d.amount), SUM(d.quantity) from salesdetails d JOIN sales s ON s.saleid = d.saleid JOIN users u ON s.userid = u.user_id WHERE MONTH(s.saletime) = MONTH(DATE_ADD((NOW()), INTERVAL -1 MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -1 MONTH)) AND d.category = 2 GROUP BY u.user_id ORDER BY SUM(d.amount) DESC LIMIT $numberOfMembers";
		try
		{
			$result = $pdo3->prepare("$selectTopUsers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		$i = 1;
		while ($topUser = $result->fetch()) {
			$user_id = $topUser['user_id'];
			$first_name = $topUser['first_name'];
			$memberno = $topUser['memberno'];
			$photoExt = $topUser['photoExt'];
			$amount = $topUser['SUM(d.amount)'];
			$amount = number_format($amount,0);
			$quantity = $topUser['SUM(d.quantity)'];
			$quantity = number_format($quantity,0);

			
			// if i is first then do the image, otherwise don't
			if ($i == 1) {
			echo <<<EOD
  <tr>
   <td colspan='3'><img class='winnerPic' src='images/_$domain/members/$user_id.$photoExt' /></td>
  </tr>
  <tr class='first'>
   <td class='first'>
    <a class='winnerLink' href='profile.php?user_id=$user_id'>$first_name <span class='smallerfont'>#</span>$memberno</a>
   </td>
   <td>$quantity <span class='smallerfont'>g.</span></td>
   <td>$amount <span class='smallerfont'>&euro;</span></td>
  </tr>
		
EOD;
			} else {
			
			echo <<<EOD
  <tr>
   <td class='first'>
    <a class='winnerLink' href='profile.php?user_id=$user_id'>$first_name <span class='smallerfont'>#</span>$memberno</a>
   </td>
   <td>$quantity <span class='smallerfont'>g.</span></td>
   <td>$amount <span class='smallerfont'>&euro;</span></td>
  </tr>
		
EOD;
			}
			
$i++;

}
		

?>
 </table>
</div>

<div class='displaybox adminHidden'>

<?php 

$currMonth = date("F", strtotime("-1 months", strtotime("first day of last month") ));
echo "<h3>$currMonth</h3>
 <table class='winnerStats default'>";
		// Look up this months sales
		$selectTopUsers = "SELECT u.user_id, u.first_name, u.memberno, u.photoExt, SUM(d.amount), SUM(d.quantity) from salesdetails d JOIN sales s ON s.saleid = d.saleid JOIN users u ON s.userid = u.user_id WHERE MONTH(s.saletime) = MONTH(DATE_ADD((NOW()), INTERVAL -2 MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -2 MONTH)) AND d.category = 2 GROUP BY u.user_id ORDER BY SUM(d.amount) DESC LIMIT $numberOfMembers";
		try
		{
			$result = $pdo3->prepare("$selectTopUsers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		$i = 1;
		while ($topUser = $result->fetch()) {
			$user_id = $topUser['user_id'];
			$first_name = $topUser['first_name'];
			$memberno = $topUser['memberno'];
			$photoExt = $topUser['photoExt'];
			$amount = $topUser['SUM(d.amount)'];
			$amount = number_format($amount,0);
			$quantity = $topUser['SUM(d.quantity)'];
			$quantity = number_format($quantity,0);
			
			// if i is first then do the image, otherwise don't
			if ($i == 1) {
			echo <<<EOD
  <tr>
   <td colspan='3'><img class='winnerPic' src='images/_$domain/members/$user_id.$photoExt' /></td>
  </tr>
  <tr class='first'>
   <td class='first'>
    <a class='winnerLink' href='profile.php?user_id=$user_id'>$first_name <span class='smallerfont'>#</span>$memberno</a>
   </td>
   <td>$quantity <span class='smallerfont'>g.</span></td>
   <td>$amount <span class='smallerfont'>&euro;</span></td>
  </tr>
		
EOD;
			} else {
			
			echo <<<EOD
  <tr>
   <td class='first'>
    <a class='winnerLink' href='profile.php?user_id=$user_id'>$first_name <span class='smallerfont'>#</span>$memberno</a>
   </td>
   <td>$quantity <span class='smallerfont'>g.</span></td>
   <td>$amount <span class='smallerfont'>&euro;</span></td>
  </tr>
		
EOD;
			}
			
$i++;

}
		

?>
 </table>
</div>
























<?php
		// Query to look up categories, then products in each category
		$selectCats = "SELECT id, name, description from categories ORDER by id ASC";
		try
		{
			$resultCats = $pdo3->prepare("$selectCats");
			$resultCats->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		$i = 1;
		while ($category = $resultCats->fetch()) {
			
			$categoryname = $category['name'];
			$categoryid = $category['id'];
			
			echo "<br /><br /><h3 class='title'>$categoryname</h3><br />";
?>


<div class='displaybox adminHidden'>

<?php

$currMonth = date("F");

echo "<h3>$currMonth</h3>
 <table class='winnerStats default'>";
 
		// Look up this months sales
		$selectTopUsers = "SELECT u.user_id, u.first_name, u.memberno, u.photoExt, SUM(d.amount), SUM(d.quantity) from salesdetails d JOIN sales s ON s.saleid = d.saleid JOIN users u ON s.userid = u.user_id WHERE MONTH(s.saletime) = MONTH(NOW()) AND YEAR(s.saletime) = YEAR(NOW()) AND d.category = $categoryid GROUP BY u.user_id ORDER BY SUM(d.amount) DESC LIMIT $numberOfMembers";
		try
		{
			$result = $pdo3->prepare("$selectTopUsers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		$i = 1;
		while ($topUser = $result->fetch()) {
			$user_id = $topUser['user_id'];
			$first_name = $topUser['first_name'];
			$memberno = $topUser['memberno'];
			$photoExt = $topUser['photoExt'];
			$amount = $topUser['SUM(d.amount)'];
			$amount = number_format($amount,0);
			$quantity = $topUser['SUM(d.quantity)'];
			$quantity = number_format($quantity,0);
			
			// if i is first then do the image, otherwise don't
			if ($i == 1) {
			echo <<<EOD
  <tr>
   <td colspan='3'><img class='winnerPic' src='images/_$domain/members/$user_id.$photoExt' /></td>
  </tr>
  <tr class='first'>
   <td class='first'>
    <a class='winnerLink' href='profile.php?user_id=$user_id'>$first_name <span class='smallerfont'>#</span>$memberno</a>
   </td>
   <td>$quantity <span class='smallerfont'>u.</span></td>
   <td>$amount <span class='smallerfont'>&euro;</span></td>
  </tr>
		
EOD;
			} else {
			
			echo <<<EOD
  <tr>
   <td class='first'>
    <a class='winnerLink' href='profile.php?user_id=$user_id'>$first_name <span class='smallerfont'>#</span>$memberno</a>
   </td>
   <td>$quantity <span class='smallerfont'>u.</span></td>
   <td>$amount <span class='smallerfont'>&euro;</span></td>
  </tr>
		
EOD;
			}
			
$i++;

}
		

?>
 </table>
</div>

<div class='displaybox adminHidden'>

<?php 

$currMonth = date("F", strtotime("first day of last month"));
echo "<h3>$currMonth</h3>
 <table class='winnerStats default'>";
		// Look up this months sales
		
		$selectTopUsers = "SELECT u.user_id, u.first_name, u.memberno, u.photoExt, SUM(d.amount), SUM(d.quantity) from salesdetails d JOIN sales s ON s.saleid = d.saleid JOIN users u ON s.userid = u.user_id WHERE MONTH(s.saletime) = MONTH(DATE_ADD((NOW()), INTERVAL -1 MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -1 MONTH)) AND d.category = $categoryid GROUP BY u.user_id ORDER BY SUM(d.amount) DESC LIMIT $numberOfMembers";
		try
		{
			$result = $pdo3->prepare("$selectTopUsers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		$i = 1;
		while ($topUser = $result->fetch()) {
			$user_id = $topUser['user_id'];
			$first_name = $topUser['first_name'];
			$memberno = $topUser['memberno'];
			$photoExt = $topUser['photoExt'];
			$amount = $topUser['SUM(d.amount)'];
			$amount = number_format($amount,0);
			$quantity = $topUser['SUM(d.quantity)'];
			$quantity = number_format($quantity,0);

			
			// if i is first then do the image, otherwise don't
			if ($i == 1) {
			echo <<<EOD
  <tr>
   <td colspan='3'><img class='winnerPic' src='images/_$domain/members/$user_id.$photoExt' /></td>
  </tr>
  <tr class='first'>
   <td class='first'>
    <a class='winnerLink' href='profile.php?user_id=$user_id'>$first_name <span class='smallerfont'>#</span>$memberno</a>
   </td>
   <td>$quantity <span class='smallerfont'>u.</span></td>
   <td>$amount <span class='smallerfont'>&euro;</span></td>
  </tr>
		
EOD;
			} else {
			
			echo <<<EOD
  <tr>
   <td class='first'>
    <a class='winnerLink' href='profile.php?user_id=$user_id'>$first_name <span class='smallerfont'>#</span>$memberno</a>
   </td>
   <td>$quantity <span class='smallerfont'>u.</span></td>
   <td>$amount <span class='smallerfont'>&euro;</span></td>
  </tr>
		
EOD;
			}
			
$i++;

}
		

?>
 </table>
</div>

<div class='displaybox adminHidden'>

<?php 

$currMonth = date("F", strtotime("-1 months", strtotime("first day of last month") ));
echo "<h3>$currMonth</h3>
 <table class='winnerStats default'>";
		// Look up this months sales
		$selectTopUsers = "SELECT u.user_id, u.first_name, u.memberno, u.photoExt, SUM(d.amount), SUM(d.quantity) from salesdetails d JOIN sales s ON s.saleid = d.saleid JOIN users u ON s.userid = u.user_id WHERE MONTH(s.saletime) = MONTH(DATE_ADD((NOW()), INTERVAL -2 MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -2 MONTH)) AND d.category = $categoryid GROUP BY u.user_id ORDER BY SUM(d.amount) DESC LIMIT $numberOfMembers";
		try
		{
			$result = $pdo3->prepare("$selectTopUsers");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		
		$i = 1;
		while ($topUser = $result->fetch()) {
			$user_id = $topUser['user_id'];
			$first_name = $topUser['first_name'];
			$memberno = $topUser['memberno'];
			$photoExt = $topUser['photoExt'];
			$amount = $topUser['SUM(d.amount)'];
			$amount = number_format($amount,0);
			$quantity = $topUser['SUM(d.quantity)'];
			$quantity = number_format($quantity,0);
			
			// if i is first then do the image, otherwise don't
			if ($i == 1) {
			echo <<<EOD
  <tr>
   <td colspan='3'><img class='winnerPic' src='images/_$domain/members/$user_id.$photoExt' /></td>
  </tr>
  <tr class='first'>
   <td class='first'>
    <a class='winnerLink' href='profile.php?user_id=$user_id'>$first_name <span class='smallerfont'>#</span>$memberno</a>
   </td>
   <td>$quantity <span class='smallerfont'>u.</span></td>
   <td>$amount <span class='smallerfont'>&euro;</span></td>
  </tr>
		
EOD;
			} else {
			
			echo <<<EOD
  <tr>
   <td class='first'>
    <a class='winnerLink' href='profile.php?user_id=$user_id'>$first_name <span class='smallerfont'>#</span>$memberno</a>
   </td>
   <td>$quantity <span class='smallerfont'>u.</span></td>
   <td>$amount <span class='smallerfont'>&euro;</span></td>
  </tr>
		
EOD;
			}
			
$i++;

}
		
?>
 </table>
</div>


<?php } ?>