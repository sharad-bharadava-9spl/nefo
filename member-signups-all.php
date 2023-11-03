<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
		
	pageStart($lang['admin-signups'], NULL, NULL, "pdispensary", "product admin", $lang['admin-signups'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
			
			
			
			// AND NOW MONTH BY MONTH //
			
		// Look up this months sales
		$selectSales = "SELECT COUNT(memberno) from users WHERE MONTH(registeredSince) = MONTH(NOW()) AND YEAR(registeredSince) = YEAR(NOW()) ";

		$result = mysql_query($selectSales)
			or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$salesMonth = $row['COUNT(memberno)'];
			$quantityMonth = $row['SUM(quantity)'];
			
		// Look up monthly sales -1
		$selectSales = "SELECT COUNT(memberno) from users WHERE MONTH(registeredSince) = MONTH(DATE_ADD((NOW()), INTERVAL -1 MONTH)) AND YEAR(registeredSince) = YEAR(DATE_ADD((NOW()), INTERVAL -1 MONTH))";

		$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$salesMonthMinus1 = $row['COUNT(memberno)'];
			$quantityMonthMinus1 = $row['SUM(quantity)'];
			
		// Look up monthly sales -2
		$selectSales = "SELECT COUNT(memberno) from users WHERE MONTH(registeredSince) = MONTH(DATE_ADD((NOW()), INTERVAL -2 MONTH)) AND YEAR(registeredSince) = YEAR(DATE_ADD((NOW()), INTERVAL -2 MONTH))";

		$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$salesMonthMinus2 = $row['COUNT(memberno)'];
			$quantityMonthMinus2 = $row['SUM(quantity)'];
			
		// Look up monthly sales -3
		$selectSales = "SELECT COUNT(memberno) from users WHERE MONTH(registeredSince) = MONTH(DATE_ADD((NOW()), INTERVAL -3 MONTH)) AND YEAR(registeredSince) = YEAR(DATE_ADD((NOW()), INTERVAL -3 MONTH))";

		$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$salesMonthMinus3 = $row['COUNT(memberno)'];
			$quantityMonthMinus3 = $row['SUM(quantity)'];
			
		// Look up monthly sales -4
		$selectSales = "SELECT COUNT(memberno) from users WHERE MONTH(registeredSince) = MONTH(DATE_ADD((NOW()), INTERVAL -4 MONTH)) AND YEAR(registeredSince) = YEAR(DATE_ADD((NOW()), INTERVAL -4 MONTH))";

		$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$salesMonthMinus4 = $row['COUNT(memberno)'];
			$quantityMonthMinus4 = $row['SUM(quantity)'];
			
		// Look up monthly sales -5
		$selectSales = "SELECT COUNT(memberno) from users WHERE MONTH(registeredSince) = MONTH(DATE_ADD((NOW()), INTERVAL -5 MONTH)) AND YEAR(registeredSince) = YEAR(DATE_ADD((NOW()), INTERVAL -5 MONTH))";

		$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$salesMonthMinus5 = $row['COUNT(memberno)'];
			$quantityMonthMinus5 = $row['SUM(quantity)'];
			
		// Look up monthly sales -6
		$selectSales = "SELECT COUNT(memberno) from users WHERE MONTH(registeredSince) = MONTH(DATE_ADD((NOW()), INTERVAL -6 MONTH)) AND YEAR(registeredSince) = YEAR(DATE_ADD((NOW()), INTERVAL -6 MONTH))";

		$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$salesMonthMinus6 = $row['COUNT(memberno)'];
			$quantityMonthMinus6 = $row['SUM(quantity)'];

		// Look up monthly sales -7
		$selectSales = "SELECT COUNT(memberno) from users WHERE MONTH(registeredSince) = MONTH(DATE_ADD((NOW()), INTERVAL -7 MONTH)) AND YEAR(registeredSince) = YEAR(DATE_ADD((NOW()), INTERVAL -7 MONTH))";

		$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$salesMonthMinus7 = $row['COUNT(memberno)'];
			$quantityMonthMinus7 = $row['SUM(quantity)'];
			
		// Look up monthly sales -8
		$selectSales = "SELECT COUNT(memberno) from users WHERE MONTH(registeredSince) = MONTH(DATE_ADD((NOW()), INTERVAL -8 MONTH)) AND YEAR(registeredSince) = YEAR(DATE_ADD((NOW()), INTERVAL -8 MONTH))";

		$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$salesMonthMinus8 = $row['COUNT(memberno)'];
			$quantityMonthMinus8 = $row['SUM(quantity)'];
			
		// Look up monthly sales -9
		$selectSales = "SELECT COUNT(memberno) from users WHERE MONTH(registeredSince) = MONTH(DATE_ADD((NOW()), INTERVAL -9 MONTH)) AND YEAR(registeredSince) = YEAR(DATE_ADD((NOW()), INTERVAL -9 MONTH))";

		$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$salesMonthMinus9 = $row['COUNT(memberno)'];
			$quantityMonthMinus9 = $row['SUM(quantity)'];
			
		// Look up monthly sales -10
		$selectSales = "SELECT COUNT(memberno) from users WHERE MONTH(registeredSince) = MONTH(DATE_ADD((NOW()), INTERVAL -10 MONTH)) AND YEAR(registeredSince) = YEAR(DATE_ADD((NOW()), INTERVAL -10 MONTH))";

		$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$salesMonthMinus10 = $row['COUNT(memberno)'];
			$quantityMonthMinus10 = $row['SUM(quantity)'];
			
		// Look up monthly sales -11
		$selectSales = "SELECT COUNT(memberno) from users WHERE MONTH(registeredSince) = MONTH(DATE_ADD((NOW()), INTERVAL -11 MONTH)) AND YEAR(registeredSince) = YEAR(DATE_ADD((NOW()), INTERVAL -11 MONTH))";

		$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$salesMonthMinus11 = $row['COUNT(memberno)'];
			$quantityMonthMinus11 = $row['SUM(quantity)'];
			
		// Look up monthly sales -12
		$selectSales = "SELECT COUNT(memberno) from users WHERE MONTH(registeredSince) = MONTH(DATE_ADD((NOW()), INTERVAL -12 MONTH)) AND YEAR(registeredSince) = YEAR(DATE_ADD((NOW()), INTERVAL -12 MONTH))";

		$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$salesMonthMinus12 = $row['COUNT(memberno)'];
			$quantityMonthMinus12 = $row['SUM(quantity)'];
			
		// Look up monthly sales -13
		$selectSales = "SELECT COUNT(memberno) from users WHERE MONTH(registeredSince) = MONTH(DATE_ADD((NOW()), INTERVAL -13 MONTH)) AND YEAR(registeredSince) = YEAR(DATE_ADD((NOW()), INTERVAL -13 MONTH))";

		$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$salesMonthMinus13 = $row['COUNT(memberno)'];
			$quantityMonthMinus13 = $row['SUM(quantity)'];
			
		// Look up monthly sales -14
		$selectSales = "SELECT COUNT(memberno) from users WHERE MONTH(registeredSince) = MONTH(DATE_ADD((NOW()), INTERVAL -14 MONTH)) AND YEAR(registeredSince) = YEAR(DATE_ADD((NOW()), INTERVAL -14 MONTH))";

		$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$salesMonthMinus14 = $row['COUNT(memberno)'];
			$quantityMonthMinus14 = $row['SUM(quantity)'];
			
		// Look up monthly sales -15
		$selectSales = "SELECT COUNT(memberno) from users WHERE MONTH(registeredSince) = MONTH(DATE_ADD((NOW()), INTERVAL -15 MONTH)) AND YEAR(registeredSince) = YEAR(DATE_ADD((NOW()), INTERVAL -15 MONTH))";

		$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$salesMonthMinus15 = $row['COUNT(memberno)'];
			$quantityMonthMinus15 = $row['SUM(quantity)'];
			
		// Look up monthly sales -16
		$selectSales = "SELECT COUNT(memberno) from users WHERE MONTH(registeredSince) = MONTH(DATE_ADD((NOW()), INTERVAL -16 MONTH)) AND YEAR(registeredSince) = YEAR(DATE_ADD((NOW()), INTERVAL -16 MONTH))";

		$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$salesMonthMinus16 = $row['COUNT(memberno)'];
			$quantityMonthMinus16 = $row['SUM(quantity)'];
			
		// Look up monthly sales -17
		$selectSales = "SELECT COUNT(memberno) from users WHERE MONTH(registeredSince) = MONTH(DATE_ADD((NOW()), INTERVAL -17 MONTH)) AND YEAR(registeredSince) = YEAR(DATE_ADD((NOW()), INTERVAL -17 MONTH))";

		$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$salesMonthMinus17 = $row['COUNT(memberno)'];
			$quantityMonthMinus17 = $row['SUM(quantity)'];

			
		// Look up monthly sales -18
		$selectSales = "SELECT COUNT(memberno) from users WHERE MONTH(registeredSince) = MONTH(DATE_ADD((NOW()), INTERVAL -18 MONTH)) AND YEAR(registeredSince) = YEAR(DATE_ADD((NOW()), INTERVAL -18 MONTH))";

		$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$salesMonthMinus18 = $row['COUNT(memberno)'];
			$quantityMonthMinus18 = $row['SUM(quantity)'];

			
		// Look up monthly sales -19
		$selectSales = "SELECT COUNT(memberno) from users WHERE MONTH(registeredSince) = MONTH(DATE_ADD((NOW()), INTERVAL -19 MONTH)) AND YEAR(registeredSince) = YEAR(DATE_ADD((NOW()), INTERVAL -19 MONTH))";

		$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$salesMonthMinus19 = $row['COUNT(memberno)'];
			$quantityMonthMinus19 = $row['SUM(quantity)'];

			
		// Look up monthly sales -20
		$selectSales = "SELECT COUNT(memberno) from users WHERE MONTH(registeredSince) = MONTH(DATE_ADD((NOW()), INTERVAL -20 MONTH)) AND YEAR(registeredSince) = YEAR(DATE_ADD((NOW()), INTERVAL -20 MONTH))";

		$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$salesMonthMinus20 = $row['COUNT(memberno)'];
			$quantityMonthMinus20 = $row['SUM(quantity)'];

			
		// Look up monthly sales -21
		$selectSales = "SELECT COUNT(memberno) from users WHERE MONTH(registeredSince) = MONTH(DATE_ADD((NOW()), INTERVAL -21 MONTH)) AND YEAR(registeredSince) = YEAR(DATE_ADD((NOW()), INTERVAL -21 MONTH))";

		$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$salesMonthMinus21 = $row['COUNT(memberno)'];
			$quantityMonthMinus21 = $row['SUM(quantity)'];

			
		// Look up monthly sales -22
		$selectSales = "SELECT COUNT(memberno) from users WHERE MONTH(registeredSince) = MONTH(DATE_ADD((NOW()), INTERVAL -22 MONTH)) AND YEAR(registeredSince) = YEAR(DATE_ADD((NOW()), INTERVAL -22 MONTH))";

		$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$salesMonthMinus22 = $row['COUNT(memberno)'];
			$quantityMonthMinus22 = $row['SUM(quantity)'];

			
		// Look up monthly sales -23
		$selectSales = "SELECT COUNT(memberno) from users WHERE MONTH(registeredSince) = MONTH(DATE_ADD((NOW()), INTERVAL -23 MONTH)) AND YEAR(registeredSince) = YEAR(DATE_ADD((NOW()), INTERVAL -23 MONTH))";

		$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$salesMonthMinus23 = $row['COUNT(memberno)'];
			$quantityMonthMinus23 = $row['SUM(quantity)'];

			
		// Look up monthly sales -24
		$selectSales = "SELECT COUNT(memberno) from users WHERE MONTH(registeredSince) = MONTH(DATE_ADD((NOW()), INTERVAL -24 MONTH)) AND YEAR(registeredSince) = YEAR(DATE_ADD((NOW()), INTERVAL -24 MONTH))";

		$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$salesMonthMinus24 = $row['COUNT(memberno)'];
			$quantityMonthMinus24 = $row['SUM(quantity)'];

?>


<table class="dayByDay displaybox adminHidden">
 <tr>
  <td colspan="4"><h3><?php echo $lang['dispensary-monthtomonth']; ?></h3>
</td>
 </tr>
 <tr>
  <td class="first"><?php echo $lang['dispensary-thismonth']; ?>:</td>
  <td><?php echo number_format($salesMonth,0); ?> </td>
  <td class="evolution"><?php
  $evolution = (($salesMonth - $salesMonthMinus1) /  $salesMonthMinus1) * 100;
  if ($salesMonth > $salesMonthMinus1) {
	  // Improvement
	  echo number_format($evolution,0) . "% <img src='images/positive2.png' />";
  } else if ($salesMonth < $salesMonthMinus1) {
	  // Decline
	  echo number_format($evolution,0) . "% <img src='images/negative2.png' />";
  }
?>
  </td>
 </tr>
 <tr>
  <td class="first"><?php echo date("F Y", strtotime("first day of last month")); ?>:</td>
  <td><?php echo number_format($salesMonthMinus1,0); ?> </td>
  <td class="evolution"><?php
  $evolution = (($salesMonthMinus1 - $salesMonthMinus2) /  $salesMonthMinus2) * 100;
  if ($salesMonthMinus1 > $salesMonthMinus2) {
	  // Improvement
	  echo number_format($evolution,0) . "% <img src='images/positive2.png' />";
  } else if ($salesMonthMinus1 < $salesMonthMinus2) {
	  // Decline
	  echo number_format($evolution,0) . "% <img src='images/negative2.png' />";
  }
?>
  </td>
 </tr>
 <tr>
  <td class="first"><?php echo date("F", strtotime("-1 months", strtotime("first day of last month") )); ?>:</td>
  <td><?php echo number_format($salesMonthMinus2,0); ?> </td>
  <td class="evolution"><?php
  $evolution = (($salesMonthMinus2 - $salesMonthMinus3) /  $salesMonthMinus3) * 100;
  if ($salesMonthMinus2 > $salesMonthMinus3) {
	  // Improvement
	  echo number_format($evolution,0) . "% <img src='images/positive2.png' />";
  } else if ($salesMonthMinus2 < $salesMonthMinus3) {
	  // Decline
	  echo number_format($evolution,0) . "% <img src='images/negative2.png' />";
  }
?>
  </td>
 </tr>
 <tr>
  <td class="first"><?php echo date("F", strtotime("-2 months", strtotime("first day of last month") )); ?>:</td>
  <td><?php echo number_format($salesMonthMinus3,0); ?> </td>
  <td class="evolution"><?php
  $evolution = (($salesMonthMinus3 - $salesMonthMinus4) /  $salesMonthMinus4) * 100;
  if ($salesMonthMinus3 > $salesMonthMinus4) {
	  // Improvement
	  echo number_format($evolution,0) . "% <img src='images/positive2.png' />";
  } else if ($salesMonthMinus3 < $salesMonthMinus4) {
	  // Decline
	  echo number_format($evolution,0) . "% <img src='images/negative2.png' />";
  }
?>
  </td>
 </tr>
 <tr>
  <td class="first"><?php echo date("F", strtotime("-3 months", strtotime("first day of last month") )); ?>:</td>
  <td><?php echo number_format($salesMonthMinus4,0); ?> </td>
  <td class="evolution"><?php
  $evolution = (($salesMonthMinus4 - $salesMonthMinus5) /  $salesMonthMinus5) * 100;
  if ($salesMonthMinus4 > $salesMonthMinus5) {
	  // Improvement
	  echo number_format($evolution,0) . "% <img src='images/positive2.png' />";
  } else if ($salesMonthMinus4 < $salesMonthMinus5) {
	  // Decline
	  echo number_format($evolution,0) . "% <img src='images/negative2.png' />";
  }
?>
  </td>
 </tr>
 <tr>
  <td class="first"><?php echo date("F", strtotime("-4 months", strtotime("first day of last month") )); ?>:</td>
  <td><?php echo number_format($salesMonthMinus5,0); ?> </td>
  <td class="evolution"><?php
  $evolution = (($salesMonthMinus5 - $salesMonthMinus6) /  $salesMonthMinus6) * 100;
  if ($salesMonthMinus5 > $salesMonthMinus6) {
	  // Improvement
	  echo number_format($evolution,0) . "% <img src='images/positive2.png' />";
  } else if ($salesMonthMinus5 < $salesMonthMinus6) {
	  // Decline
	  echo number_format($evolution,0) . "% <img src='images/negative2.png' />";
  }
?>
  </td>
 </tr>
 <tr>
  <td class="first"><?php echo date("F", strtotime("-5 months", strtotime("first day of last month") )); ?>:</td>
  <td><?php echo number_format($salesMonthMinus6,0); ?> </td>
  <td class="evolution"><?php
  $evolution = (($salesMonthMinus6 - $salesMonthMinus7) /  $salesMonthMinus7) * 100;
  if ($salesMonthMinus6 > $salesMonthMinus7) {
	  // Improvement
	  echo number_format($evolution,0) . "% <img src='images/positive2.png' />";
  } else if ($salesMonthMinus6 < $salesMonthMinus7) {
	  // Decline
	  echo number_format($evolution,0) . "% <img src='images/negative2.png' />";
  }
?>
  </td>
 </tr>
 
 
 
 
 
 
 
 <tr>
  <td class="first"><?php echo date("F", strtotime("-6 months", strtotime("first day of last month") )); ?>:</td>
  <td><?php echo number_format($salesMonthMinus7,0); ?> </td>
  <td class="evolution"><?php
  $evolution = (($salesMonthMinus7 - $salesMonthMinus8) /  $salesMonthMinus8) * 100;
  if ($salesMonthMinus7 > $salesMonthMinus8) {
	  // Improvement
	  echo number_format($evolution,0) . "% <img src='images/positive2.png' />";
  } else if ($salesMonthMinus7 < $salesMonthMinus8) {
	  // Decline
	  echo number_format($evolution,0) . "% <img src='images/negative2.png' />";
  }
?>
  </td>
 </tr>
 <tr>
  <td class="first"><?php echo date("F", strtotime("-7 months", strtotime("first day of last month") )); ?>:</td>
  <td><?php echo number_format($salesMonthMinus8,0); ?> </td>
  <td class="evolution"><?php
  $evolution = (($salesMonthMinus8 - $salesMonthMinus9) /  $salesMonthMinus9) * 100;
  if ($salesMonthMinus8 > $salesMonthMinus9) {
	  // Improvement
	  echo number_format($evolution,0) . "% <img src='images/positive2.png' />";
  } else if ($salesMonthMinus8 < $salesMonthMinus9) {
	  // Decline
	  echo number_format($evolution,0) . "% <img src='images/negative2.png' />";
  }
?>
  </td>
 </tr>
 <tr>
  <td class="first"><?php echo date("F", strtotime("-8 months", strtotime("first day of last month") )); ?>:</td>
  <td><?php echo number_format($salesMonthMinus9,0); ?> </td>
  <td class="evolution"><?php
  $evolution = (($salesMonthMinus9 - $salesMonthMinus10) /  $salesMonthMinus10) * 100;
  if ($salesMonthMinus9 > $salesMonthMinus10) {
	  // Improvement
	  echo number_format($evolution,0) . "% <img src='images/positive2.png' />";
  } else if ($salesMonthMinus9 < $salesMonthMinus10) {
	  // Decline
	  echo number_format($evolution,0) . "% <img src='images/negative2.png' />";
  }
?>
  </td>
 </tr>
 <tr>
  <td class="first"><?php echo date("F", strtotime("-9 months", strtotime("first day of last month") )); ?>:</td>
  <td><?php echo number_format($salesMonthMinus10,0); ?> </td>
  <td class="evolution"><?php
  $evolution = (($salesMonthMinus10 - $salesMonthMinus11) /  $salesMonthMinus11) * 100;
  if ($salesMonthMinus10 > $salesMonthMinus11) {
	  // Improvement
	  echo number_format($evolution,0) . "% <img src='images/positive2.png' />";
  } else if ($salesMonthMinus10 < $salesMonthMinus11) {
	  // Decline
	  echo number_format($evolution,0) . "% <img src='images/negative2.png' />";
  }
?>
  </td>
 </tr>
 <tr>
  <td class="first"><?php echo date("F", strtotime("-10 months", strtotime("first day of last month") )); ?>:</td>
  <td><?php echo number_format($salesMonthMinus11,0); ?> </td>
  <td class="evolution"><?php
  $evolution = (($salesMonthMinus11 - $salesMonthMinus12) /  $salesMonthMinus12) * 100;
  if ($salesMonthMinus11 > $salesMonthMinus12) {
	  // Improvement
	  echo number_format($evolution,0) . "% <img src='images/positive2.png' />";
  } else if ($salesMonthMinus11 < $salesMonthMinus12) {
	  // Decline
	  echo number_format($evolution,0) . "% <img src='images/negative2.png' />";
  }
?>
  </td>
 </tr>
 <tr>
  <td class="first"><?php echo date("F", strtotime("-11 months", strtotime("first day of last month") )); ?>:</td>
  <td><?php echo number_format($salesMonthMinus12,0); ?> </td>
  <td class="evolution"><?php
  $evolution = (($salesMonthMinus12 - $salesMonthMinus13) /  $salesMonthMinus13) * 100;
  if ($salesMonthMinus12 > $salesMonthMinus13) {
	  // Improvement
	  echo number_format($evolution,0) . "% <img src='images/positive2.png' />";
  } else if ($salesMonthMinus12 < $salesMonthMinus13) {
	  // Decline
	  echo number_format($evolution,0) . "% <img src='images/negative2.png' />";
  }
?>
  </td>
 </tr>
 <tr>
  <td class="first"><?php echo date("F", strtotime("-12 months", strtotime("first day of last month") )); ?>:</td>
  <td><?php echo number_format($salesMonthMinus13,0); ?> </td>
  <td class="evolution"><?php
  $evolution = (($salesMonthMinus13 - $salesMonthMinus14) /  $salesMonthMinus14) * 100;
  if ($salesMonthMinus13 > $salesMonthMinus14) {
	  // Improvement
	  echo number_format($evolution,0) . "% <img src='images/positive2.png' />";
  } else if ($salesMonthMinus13 < $salesMonthMinus14) {
	  // Decline
	  echo number_format($evolution,0) . "% <img src='images/negative2.png' />";
  }
?>
  </td>
 </tr>
 <tr>
  <td class="first"><?php echo date("F", strtotime("-13 months", strtotime("first day of last month") )); ?>:</td>
  <td><?php echo number_format($salesMonthMinus14,0); ?> </td>
  <td class="evolution"><?php
  $evolution = (($salesMonthMinus14 - $salesMonthMinus15) /  $salesMonthMinus15) * 100;
  if ($salesMonthMinus14 > $salesMonthMinus15) {
	  // Improvement
	  echo number_format($evolution,0) . "% <img src='images/positive2.png' />";
  } else if ($salesMonthMinus14 < $salesMonthMinus15) {
	  // Decline
	  echo number_format($evolution,0) . "% <img src='images/negative2.png' />";
  }
?>
  </td>
 </tr>
 <tr>
  <td class="first"><?php echo date("F", strtotime("-14 months", strtotime("first day of last month") )); ?>:</td>
  <td><?php echo number_format($salesMonthMinus15,0); ?> </td>
  <td class="evolution"><?php
  $evolution = (($salesMonthMinus15 - $salesMonthMinus16) /  $salesMonthMinus16) * 100;
  if ($salesMonthMinus15 > $salesMonthMinus16) {
	  // Improvement
	  echo number_format($evolution,0) . "% <img src='images/positive2.png' />";
  } else if ($salesMonthMinus15 < $salesMonthMinus16) {
	  // Decline
	  echo number_format($evolution,0) . "% <img src='images/negative2.png' />";
  }
?>
  </td>
 </tr>

 
 
 
 
 
 
 
 
 <tr>
  <td class="first"><?php echo date("F", strtotime("-15 months", strtotime("first day of last month") )); ?>:</td>
  <td><?php echo number_format($salesMonthMinus16,0); ?> </td>
  <td class="evolution"><?php
  $evolution = (($salesMonthMinus16 - $salesMonthMinus17) /  $salesMonthMinus17) * 100;
  if ($salesMonthMinus16 > $salesMonthMinus17) {
	  // Improvement
	  echo number_format($evolution,0) . "% <img src='images/positive2.png' />";
  } else if ($salesMonthMinus16 < $salesMonthMinus17) {
	  // Decline
	  echo number_format($evolution,0) . "% <img src='images/negative2.png' />";
  }
?>
  </td>
 </tr>
 <tr>
  <td class="first"><?php echo date("F", strtotime("-16 months", strtotime("first day of last month") )); ?>:</td>
  <td><?php echo number_format($salesMonthMinus17,0); ?> </td>
  <td class="evolution"><?php
  $evolution = (($salesMonthMinus17 - $salesMonthMinus18) /  $salesMonthMinus18) * 100;
  if ($salesMonthMinus17 > $salesMonthMinus18) {
	  // Improvement
	  echo number_format($evolution,0) . "% <img src='images/positive2.png' />";
  } else if ($salesMonthMinus17 < $salesMonthMinus18) {
	  // Decline
	  echo number_format($evolution,0) . "% <img src='images/negative2.png' />";
  }
?>
  </td>
 </tr>
 <tr>
  <td class="first"><?php echo date("F", strtotime("-17 months", strtotime("first day of last month") )); ?>:</td>
  <td><?php echo number_format($salesMonthMinus18,0); ?> </td>
  <td class="evolution"><?php
  $evolution = (($salesMonthMinus18 - $salesMonthMinus19) /  $salesMonthMinus19) * 100;
  if ($salesMonthMinus18 > $salesMonthMinus19) {
	  // Improvement
	  echo number_format($evolution,0) . "% <img src='images/positive2.png' />";
  } else if ($salesMonthMinus18 < $salesMonthMinus19) {
	  // Decline
	  echo number_format($evolution,0) . "% <img src='images/negative2.png' />";
  }
?>
  </td>
 </tr>
 <tr>
  <td class="first"><?php echo date("F", strtotime("-18 months", strtotime("first day of last month") )); ?>:</td>
  <td><?php echo number_format($salesMonthMinus19,0); ?> </td>
  <td class="evolution"><?php
  $evolution = (($salesMonthMinus19 - $salesMonthMinus20) /  $salesMonthMinus20) * 100;
  if ($salesMonthMinus19 > $salesMonthMinus20) {
	  // Improvement
	  echo number_format($evolution,0) . "% <img src='images/positive2.png' />";
  } else if ($salesMonthMinus19 < $salesMonthMinus20) {
	  // Decline
	  echo number_format($evolution,0) . "% <img src='images/negative2.png' />";
  }
?>
  </td>
 </tr>
 <tr>
  <td class="first"><?php echo date("F", strtotime("-19 months", strtotime("first day of last month") )); ?>:</td>
  <td><?php echo number_format($salesMonthMinus20,0); ?> </td>
  <td class="evolution"><?php
  $evolution = (($salesMonthMinus20 - $salesMonthMinus21) /  $salesMonthMinus21) * 100;
  if ($salesMonthMinus20 > $salesMonthMinus21) {
	  // Improvement
	  echo number_format($evolution,0) . "% <img src='images/positive2.png' />";
  } else if ($salesMonthMinus20 < $salesMonthMinus21) {
	  // Decline
	  echo number_format($evolution,0) . "% <img src='images/negative2.png' />";
  }
?>
  </td>
 </tr>
 <tr>
  <td class="first"><?php echo date("F", strtotime("-20 months", strtotime("first day of last month") )); ?>:</td>
  <td><?php echo number_format($salesMonthMinus21,0); ?> </td>
  <td class="evolution"><?php
  $evolution = (($salesMonthMinus21 - $salesMonthMinus22) /  $salesMonthMinus22) * 100;
  if ($salesMonthMinus21 > $salesMonthMinus22) {
	  // Improvement
	  echo number_format($evolution,0) . "% <img src='images/positive2.png' />";
  } else if ($salesMonthMinus21 < $salesMonthMinus22) {
	  // Decline
	  echo number_format($evolution,0) . "% <img src='images/negative2.png' />";
  }
?>
  </td>
 </tr>
 <tr>
  <td class="first"><?php echo date("F", strtotime("-21 months", strtotime("first day of last month") )); ?>:</td>
  <td><?php echo number_format($salesMonthMinus22,0); ?> </td>
  <td class="evolution"><?php
  $evolution = (($salesMonthMinus22 - $salesMonthMinus23) /  $salesMonthMinus23) * 100;
  if ($salesMonthMinus22 > $salesMonthMinus23) {
	  // Improvement
	  echo number_format($evolution,0) . "% <img src='images/positive2.png' />";
  } else if ($salesMonthMinus22 < $salesMonthMinus23) {
	  // Decline
	  echo number_format($evolution,0) . "% <img src='images/negative2.png' />";
  }
?>
  </td>
 </tr>
 <tr>
  <td class="first"><?php echo date("F", strtotime("-22 months", strtotime("first day of last month") )); ?>:</td>
  <td><?php echo number_format($salesMonthMinus23,0); ?> </td>
  <td class="evolution"><?php
  $evolution = (($salesMonthMinus23 - $salesMonthMinus24) /  $salesMonthMinus24) * 100;
  if ($salesMonthMinus23 > $salesMonthMinus24) {
	  // Improvement
	  echo number_format($evolution,0) . "% <img src='images/positive2.png' />";
  } else if ($salesMonthMinus23 < $salesMonthMinus24) {
	  // Decline
	  echo number_format($evolution,0) . "% <img src='images/negative2.png' />";
  }
?>
  </td>
 </tr>
 <tr>
  <td class="first"><?php echo date("F", strtotime("-23 months", strtotime("first day of last month") )); ?>:</td>
  <td><?php echo number_format($salesMonthMinus24,0); ?> </td>
 </tr>
</table>














</div>



<?php displayFooter(); ?>
