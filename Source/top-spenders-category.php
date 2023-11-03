<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
		
	pageStart($lang['title-dispensary'], NULL, NULL, "pdispensary", "product admin", $lang['topspenderscaps'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
?>



<br />
<div class='displaybox adminHidden'>

<?php 

$currMonth = date("F");

echo "<h3>$currMonth</h3>
 <table class='winnerStats default'>";

		// Look up this months sales
		$selectTopUsers = "SELECT u.user_id, u.first_name, u.memberno, SUM(s.amount), SUM(s.quantity), u.photoExt from sales s, users u WHERE u.user_id = s.userid AND MONTH(s.saletime) = MONTH(NOW()) AND YEAR(s.saletime) = YEAR(NOW()) GROUP BY u.user_id ORDER BY SUM(s.amount) DESC LIMIT 5";

		$result = mysql_query($selectTopUsers)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$i = 1;
		while ($topUser = mysql_fetch_array($result)) {
			$user_id = $topUser['user_id'];
			$first_name = $topUser['first_name'];
			$memberno = $topUser['memberno'];
			$photoExt = $topUser['photoExt'];
			$amount = $topUser['SUM(s.amount)'];
			$amount = number_format($amount,0);
			$quantity = $topUser['SUM(s.quantity)'];
			$quantity = number_format($quantity,0);
//			echo "firstname: " . $memberno . $first_name . "<br />amount: " .  $amount . "<br />quantity: " . $quantity . ".<br /><br />";

			
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
		$selectTopUsers = "SELECT u.user_id, u.first_name, u.memberno, SUM(s.amount), SUM(s.quantity), u.photoExt from sales s, users u WHERE u.user_id = s.userid AND MONTH(saletime) = MONTH(DATE_ADD((NOW()), INTERVAL -1 MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -1 MONTH)) GROUP BY u.user_id ORDER BY SUM(s.amount) DESC LIMIT 5";

		$result = mysql_query($selectTopUsers)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$i = 1;
		while ($topUser = mysql_fetch_array($result)) {
			$user_id = $topUser['user_id'];
			$first_name = $topUser['first_name'];
			$memberno = $topUser['memberno'];
			$photoExt = $topUser['photoExt'];
			$amount = $topUser['SUM(s.amount)'];
			$amount = number_format($amount,0);
			$quantity = $topUser['SUM(s.quantity)'];
			$quantity = number_format($quantity,0);
//			echo "firstname: " . $memberno . $first_name . "<br />amount: " .  $amount . "<br />quantity: " . $quantity . ".<br /><br />";

			
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
		$selectTopUsers = "SELECT u.user_id, u.first_name, u.memberno, SUM(s.amount), SUM(s.quantity), u.photoExt from sales s, users u WHERE u.user_id = s.userid AND MONTH(saletime) = MONTH(DATE_ADD((NOW()), INTERVAL -2 MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -2 MONTH)) GROUP BY u.user_id ORDER BY SUM(s.amount) DESC LIMIT 5";

		$result = mysql_query($selectTopUsers)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$i = 1;
		while ($topUser = mysql_fetch_array($result)) {
			$user_id = $topUser['user_id'];
			$first_name = $topUser['first_name'];
			$memberno = $topUser['memberno'];
			$photoExt = $topUser['photoExt'];
			$amount = $topUser['SUM(s.amount)'];
			$amount = number_format($amount,0);
			$quantity = $topUser['SUM(s.quantity)'];
			$quantity = number_format($quantity,0);
//			echo "firstname: " . $memberno . $first_name . "<br />amount: " .  $amount . "<br />quantity: " . $quantity . ".<br /><br />";

			
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

$currMonth = date("F", strtotime("-2 months", strtotime("first day of last month") ));
echo "<h3>$currMonth</h3>
 <table class='winnerStats default'>";
		// Look up this months sales
		$selectTopUsers = "SELECT u.user_id, u.first_name, u.memberno, SUM(s.amount), SUM(s.quantity), u.photoExt from sales s, users u WHERE u.user_id = s.userid AND MONTH(saletime) = MONTH(DATE_ADD((NOW()), INTERVAL -3 MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -3 MONTH)) GROUP BY u.user_id ORDER BY SUM(s.amount) DESC LIMIT 5";

		$result = mysql_query($selectTopUsers)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$i = 1;
		while ($topUser = mysql_fetch_array($result)) {
			$user_id = $topUser['user_id'];
			$first_name = $topUser['first_name'];
			$memberno = $topUser['memberno'];
			$photoExt = $topUser['photoExt'];
			$amount = $topUser['SUM(s.amount)'];
			$amount = number_format($amount,0);
			$quantity = $topUser['SUM(s.quantity)'];
			$quantity = number_format($quantity,0);
//			echo "firstname: " . $memberno . $first_name . "<br />amount: " .  $amount . "<br />quantity: " . $quantity . ".<br /><br />";

			
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



<?php displayFooter(); ?>
