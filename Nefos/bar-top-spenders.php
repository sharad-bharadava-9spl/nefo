<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
		
	pageStart($lang['bar'], NULL, NULL, "pdispensary", "product admin", $lang['topspenderscaps'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
?>


<h3 class='title'><?php echo $lang['global-total']; ?></h3>

<br />
<div class='displaybox adminHidden'>
<?php 

$currMonth = date("F");

echo "<h3>$currMonth</h3>
 <table class='winnerStats default'>";
 
		// Look up this months sales
		$selectTopUsers = "SELECT u.user_id, u.first_name, u.memberno, u.photoExt, SUM(d.amount) from b_salesdetails d JOIN b_sales s ON s.saleid = d.saleid JOIN users u ON s.userid = u.user_id WHERE MONTH(s.saletime) = MONTH(NOW()) AND YEAR(s.saletime) = YEAR(NOW()) GROUP BY u.user_id ORDER BY SUM(d.amount) DESC LIMIT 5";
 
		$result = mysql_query($selectTopUsers)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$i = 1;
		while ($topUser = mysql_fetch_array($result)) {
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
   <td colspan='3'><img class='winnerPic' src='images/members/$user_id.$photoExt' /></td>
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
		
		$selectTopUsers = "SELECT u.user_id, u.first_name, u.memberno, u.photoExt, SUM(d.amount) from b_salesdetails d JOIN b_sales s ON s.saleid = d.saleid JOIN users u ON s.userid = u.user_id WHERE MONTH(s.saletime) = MONTH(DATE_ADD((NOW()), INTERVAL -1 MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -1 MONTH)) GROUP BY u.user_id ORDER BY SUM(d.amount) DESC LIMIT 5";


		$result = mysql_query($selectTopUsers)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$i = 1;
		while ($topUser = mysql_fetch_array($result)) {
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
   <td colspan='3'><img class='winnerPic' src='images/members/$user_id.$photoExt' /></td>
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
		$selectTopUsers = "SELECT u.user_id, u.first_name, u.memberno, u.photoExt, SUM(d.amount) from b_salesdetails d JOIN b_sales s ON s.saleid = d.saleid JOIN users u ON s.userid = u.user_id WHERE MONTH(s.saletime) = MONTH(DATE_ADD((NOW()), INTERVAL -2 MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -2 MONTH)) GROUP BY u.user_id ORDER BY SUM(d.amount) DESC LIMIT 5";

		$result = mysql_query($selectTopUsers)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$i = 1;
		while ($topUser = mysql_fetch_array($result)) {
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
   <td colspan='3'><img class='winnerPic' src='images/members/$user_id.$photoExt' /></td>
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
























<?php
		// Query to look up categories, then products in each category
		$selectCats = "SELECT id, name, description from b_categories ORDER by id ASC";
	
		$resultCats = mysql_query($selectCats)
			or handleError($lang['error-loadflowers'],"Error loading flower from db: " . mysql_error());
	
		while ($category = mysql_fetch_array($resultCats)) {
			
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
		$selectTopUsers = "SELECT u.user_id, u.first_name, u.memberno, u.photoExt, SUM(d.amount), SUM(d.quantity) from b_salesdetails d JOIN b_sales s ON s.saleid = d.saleid JOIN users u ON s.userid = u.user_id WHERE MONTH(s.saletime) = MONTH(NOW()) AND YEAR(s.saletime) = YEAR(NOW()) AND d.category = $categoryid GROUP BY u.user_id ORDER BY SUM(d.amount) DESC LIMIT 5";
 
		$result = mysql_query($selectTopUsers)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$i = 1;
		while ($topUser = mysql_fetch_array($result)) {
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
   <td colspan='3'><img class='winnerPic' src='images/members/$user_id.$photoExt' /></td>
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
		
		$selectTopUsers = "SELECT u.user_id, u.first_name, u.memberno, u.photoExt, SUM(d.amount), SUM(d.quantity) from b_salesdetails d JOIN b_sales s ON s.saleid = d.saleid JOIN users u ON s.userid = u.user_id WHERE MONTH(s.saletime) = MONTH(DATE_ADD((NOW()), INTERVAL -1 MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -1 MONTH)) AND d.category = $categoryid GROUP BY u.user_id ORDER BY SUM(d.amount) DESC LIMIT 5";


		$result = mysql_query($selectTopUsers)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$i = 1;
		while ($topUser = mysql_fetch_array($result)) {
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
   <td colspan='3'><img class='winnerPic' src='images/members/$user_id.$photoExt' /></td>
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
		$selectTopUsers = "SELECT u.user_id, u.first_name, u.memberno, u.photoExt, SUM(d.amount), SUM(d.quantity) from b_salesdetails d JOIN b_sales s ON s.saleid = d.saleid JOIN users u ON s.userid = u.user_id WHERE MONTH(s.saletime) = MONTH(DATE_ADD((NOW()), INTERVAL -2 MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -2 MONTH)) AND d.category = $categoryid GROUP BY u.user_id ORDER BY SUM(d.amount) DESC LIMIT 5";

		$result = mysql_query($selectTopUsers)
			or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$i = 1;
		while ($topUser = mysql_fetch_array($result)) {
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
   <td colspan='3'><img class='winnerPic' src='images/members/$user_id.$photoExt' /></td>
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