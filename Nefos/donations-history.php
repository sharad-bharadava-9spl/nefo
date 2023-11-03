<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
		
	pageStart($lang['title-dispensary'], NULL, NULL, "pdispensary", "product admin", $lang['global-dispensary'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	

		// Look up todays sales
		$selectSales = "SELECT SUM(amount), SUM(amountpaid), SUM(quantity) from sales WHERE DATE(saletime) = DATE(NOW())";

		$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$salesToday = $row['SUM(amount)'];
			$quantityToday = $row['SUM(quantity)'];
			
		// Look up daily sales -1
		$selectSales = "SELECT SUM(amount), SUM(amountpaid), SUM(quantity) from sales WHERE DATE(saletime) = DATE_ADD(DATE(NOW()), INTERVAL -1 DAY)";

		$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$salesTodayMinus1 = $row['SUM(amount)'];
			$quantityTodayMinus1 = $row['SUM(quantity)'];
			
		// Look up daily sales -2
		$selectSales = "SELECT SUM(amount), SUM(amountpaid), SUM(quantity) from sales WHERE DATE(saletime) = DATE_ADD(DATE(NOW()), INTERVAL -2 DAY)";

		$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$salesTodayMinus2 = $row['SUM(amount)'];
			$quantityTodayMinus2 = $row['SUM(quantity)'];
			
		// Look up daily sales -3
		$selectSales = "SELECT SUM(amount), SUM(amountpaid), SUM(quantity) from sales WHERE DATE(saletime) = DATE_ADD(DATE(NOW()), INTERVAL -3 DAY)";

		$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$salesTodayMinus3 = $row['SUM(amount)'];
			$quantityTodayMinus3 = $row['SUM(quantity)'];
			
		// Look up daily sales -4
		$selectSales = "SELECT SUM(amount), SUM(amountpaid), SUM(quantity) from sales WHERE DATE(saletime) = DATE_ADD(DATE(NOW()), INTERVAL -4 DAY)";

		$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$salesTodayMinus4 = $row['SUM(amount)'];
			$quantityTodayMinus4 = $row['SUM(quantity)'];
			
		// Look up daily sales -5
		$selectSales = "SELECT SUM(amount), SUM(amountpaid), SUM(quantity) from sales WHERE DATE(saletime) = DATE_ADD(DATE(NOW()), INTERVAL -5 DAY)";

		$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$salesTodayMinus5 = $row['SUM(amount)'];
			$quantityTodayMinus5 = $row['SUM(quantity)'];
			
		// Look up daily sales -6
		$selectSales = "SELECT SUM(amount), SUM(amountpaid), SUM(quantity) from sales WHERE DATE(saletime) = DATE_ADD(DATE(NOW()), INTERVAL -6 DAY)";

		$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$salesTodayMinus6 = $row['SUM(amount)'];
			$quantityTodayMinus6 = $row['SUM(quantity)'];

		// Look up daily sales -7
		$selectSales = "SELECT SUM(amount), SUM(amountpaid), SUM(quantity) from sales WHERE DATE(saletime) = DATE_ADD(DATE(NOW()), INTERVAL -7 DAY)";

		$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$salesTodayMinus7 = $row['SUM(amount)'];
			$quantityTodayMinus7 = $row['SUM(quantity)'];

			
			
			// AND NOW WEEK BY WEEK //
			
		// Look up this weeks sales
		$selectSales = "SELECT SUM(amount), SUM(amountpaid), SUM(quantity) from sales WHERE WEEK(saletime,1) = WEEK(NOW(),1) AND YEAR(saletime) = YEAR(NOW()) ";

		$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$salesWeek = $row['SUM(amount)'];
			$quantityWeek = $row['SUM(quantity)'];
			
		// Look up weekly sales -1
		$selectSales = "SELECT SUM(amount), SUM(amountpaid), SUM(quantity) from sales WHERE WEEK(saletime,1) = WEEK(DATE_ADD((NOW()), INTERVAL -1 WEEK),1) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -1 WEEK))";

		$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$salesWeekMinus1 = $row['SUM(amount)'];
			$quantityWeekMinus1 = $row['SUM(quantity)'];
			
		// Look up weekly sales -2
		$selectSales = "SELECT SUM(amount), SUM(amountpaid), SUM(quantity) from sales WHERE WEEK(saletime,1) = WEEK(DATE_ADD((NOW()), INTERVAL -2 WEEK),1) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -2 WEEK))";

		$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$salesWeekMinus2 = $row['SUM(amount)'];
			$quantityWeekMinus2 = $row['SUM(quantity)'];
			
		// Look up weekly sales -3
		$selectSales = "SELECT SUM(amount), SUM(amountpaid), SUM(quantity) from sales WHERE WEEK(saletime,1) = WEEK(DATE_ADD((NOW()), INTERVAL -3 WEEK),1) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -3 WEEK))";

		$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$salesWeekMinus3 = $row['SUM(amount)'];
			$quantityWeekMinus3 = $row['SUM(quantity)'];
			
		// Look up weekly sales -4
		$selectSales = "SELECT SUM(amount), SUM(amountpaid), SUM(quantity) from sales WHERE WEEK(saletime,1) = WEEK(DATE_ADD((NOW()), INTERVAL -4 WEEK),1) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -4 WEEK))";

		$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$salesWeekMinus4 = $row['SUM(amount)'];
			$quantityWeekMinus4 = $row['SUM(quantity)'];
			
		// Look up weekly sales -5
		$selectSales = "SELECT SUM(amount), SUM(amountpaid), SUM(quantity) from sales WHERE WEEK(saletime,1) = WEEK(DATE_ADD((NOW()), INTERVAL -5 WEEK),1) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -5 WEEK))";

		$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$salesWeekMinus5 = $row['SUM(amount)'];
			$quantityWeekMinus5 = $row['SUM(quantity)'];
			
		// Look up weekly sales -6
		$selectSales = "SELECT SUM(amount), SUM(amountpaid), SUM(quantity) from sales WHERE WEEK(saletime,1) = WEEK(DATE_ADD((NOW()), INTERVAL -6 WEEK),1) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -6 WEEK))";

		$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$salesWeekMinus6 = $row['SUM(amount)'];
			$quantityWeekMinus6 = $row['SUM(quantity)'];

		// Look up weekly sales -7
		$selectSales = "SELECT SUM(amount), SUM(amountpaid), SUM(quantity) from sales WHERE WEEK(saletime,1) = WEEK(DATE_ADD((NOW()), INTERVAL -7 WEEK),1) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -7 WEEK))";

		$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$salesWeekMinus7 = $row['SUM(amount)'];
			$quantityWeekMinus7 = $row['SUM(quantity)'];
			
			
			
			// AND NOW MONTH BY MONTH //
			
		// Look up this months sales
		$selectSales = "SELECT SUM(amount), SUM(amountpaid), SUM(quantity) from sales WHERE MONTH(saletime) = MONTH(NOW()) AND YEAR(saletime) = YEAR(NOW()) ";

		$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$salesMonth = $row['SUM(amount)'];
			$quantityMonth = $row['SUM(quantity)'];
			
		// Look up monthly sales -1
		$selectSales = "SELECT SUM(amount), SUM(amountpaid), SUM(quantity) from sales WHERE MONTH(saletime) = MONTH(DATE_ADD((NOW()), INTERVAL -1 MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -1 MONTH))";

		$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$salesMonthMinus1 = $row['SUM(amount)'];
			$quantityMonthMinus1 = $row['SUM(quantity)'];
			
		// Look up monthly sales -2
		$selectSales = "SELECT SUM(amount), SUM(amountpaid), SUM(quantity) from sales WHERE MONTH(saletime) = MONTH(DATE_ADD((NOW()), INTERVAL -2 MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -2 MONTH))";

		$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$salesMonthMinus2 = $row['SUM(amount)'];
			$quantityMonthMinus2 = $row['SUM(quantity)'];
			
		// Look up monthly sales -3
		$selectSales = "SELECT SUM(amount), SUM(amountpaid), SUM(quantity) from sales WHERE MONTH(saletime) = MONTH(DATE_ADD((NOW()), INTERVAL -3 MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -3 MONTH))";

		$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$salesMonthMinus3 = $row['SUM(amount)'];
			$quantityMonthMinus3 = $row['SUM(quantity)'];
			
		// Look up monthly sales -4
		$selectSales = "SELECT SUM(amount), SUM(amountpaid), SUM(quantity) from sales WHERE MONTH(saletime) = MONTH(DATE_ADD((NOW()), INTERVAL -4 MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -4 MONTH))";

		$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$salesMonthMinus4 = $row['SUM(amount)'];
			$quantityMonthMinus4 = $row['SUM(quantity)'];
			
		// Look up monthly sales -5
		$selectSales = "SELECT SUM(amount), SUM(amountpaid), SUM(quantity) from sales WHERE MONTH(saletime) = MONTH(DATE_ADD((NOW()), INTERVAL -5 MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -5 MONTH))";

		$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$salesMonthMinus5 = $row['SUM(amount)'];
			$quantityMonthMinus5 = $row['SUM(quantity)'];
			
		// Look up monthly sales -6
		$selectSales = "SELECT SUM(amount), SUM(amountpaid), SUM(quantity) from sales WHERE MONTH(saletime) = MONTH(DATE_ADD((NOW()), INTERVAL -6 MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -6 MONTH))";

		$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$salesMonthMinus6 = $row['SUM(amount)'];
			$quantityMonthMinus6 = $row['SUM(quantity)'];

		// Look up monthly sales -7
		$selectSales = "SELECT SUM(amount), SUM(amountpaid), SUM(quantity) from sales WHERE MONTH(saletime) = MONTH(DATE_ADD((NOW()), INTERVAL -7 MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -7 MONTH))";

		$result = mysql_query($selectSales)
		or handleError($lang['error-loadsales'],"Error loading sale from db: " . mysql_error());
		
		$row = mysql_fetch_array($result);
			$salesMonthMinus7 = $row['SUM(amount)'];
			$quantityMonthMinus7 = $row['SUM(quantity)'];
			

?>

<table class="dayByDay displaybox">
 <tr>
  <td colspan="3"><h3><?php echo $lang['dispensary-daytoday']; ?></h3>
</td>
 </tr>
 <tr>
  <td class="first"><?php echo $lang['dispensary-today']; ?>:</td>
  <td><?php echo number_format($quantityToday,0); ?> <span class="smallerfont">g.</span></td>
  <td><?php echo number_format($salesToday,0); ?> <span class="smallerfont">&euro;</span></td>
 </tr>
 <tr>
  <td class="first"><?php echo $lang['dispensary-yesterday']; ?>:</td>
  <td><?php echo number_format($quantityTodayMinus1,0); ?> <span class="smallerfont">g.</span></td>
  <td><?php echo number_format($salesTodayMinus1,0); ?> <span class="smallerfont">&euro;</span></td>
 </tr>
 <tr>
  <td class="first"><?php echo date("l", strtotime("-2 days")); ?>:</td>
  <td><?php echo number_format($quantityTodayMinus2,0); ?> <span class="smallerfont">g.</span></td>
  <td><?php echo number_format($salesTodayMinus2,0); ?> <span class="smallerfont">&euro;</span></td>
 </tr>
 <tr>
  <td class="first"><?php echo date("l", strtotime("-3 days")); ?>:</td>
  <td><?php echo number_format($quantityTodayMinus3,0); ?> <span class="smallerfont">g.</span></td>
  <td><?php echo number_format($salesTodayMinus3,0); ?> <span class="smallerfont">&euro;</span></td>
 </tr>
 <tr>
  <td class="first"><?php echo date("l", strtotime("-4 days")); ?>:</td>
  <td><?php echo number_format($quantityTodayMinus4,0); ?> <span class="smallerfont">g.</span></td>
  <td><?php echo number_format($salesTodayMinus4,0); ?> <span class="smallerfont">&euro;</span></td>
 </tr>
 <tr>
  <td class="first"><?php echo date("l", strtotime("-5 days")); ?>:</td>
  <td><?php echo number_format($quantityTodayMinus5,0); ?> <span class="smallerfont">g.</span></td>
  <td><?php echo number_format($salesTodayMinus5,0); ?> <span class="smallerfont">&euro;</span></td>
 </tr>
 <tr>
  <td class="first"><?php echo date("l", strtotime("-6 days")); ?>:</td>
  <td><?php echo number_format($quantityTodayMinus6,0); ?> <span class="smallerfont">g.</span></td>
  <td><?php echo number_format($salesTodayMinus6,0); ?> <span class="smallerfont">&euro;</span></td>
 </tr>
 <tr>
  <td class="first"><?php echo date("l", strtotime("-7 days")); ?>:</td>
  <td><?php echo number_format($quantityTodayMinus7,0); ?> <span class="smallerfont">g.</span></td>
  <td><?php echo number_format($salesTodayMinus7,0); ?> <span class="smallerfont">&euro;</span></td>
 </tr>
</table>
<table class="dayByDay displaybox adminHidden">
 <tr>
  <td colspan="4"><h3><?php echo $lang['dispensary-weektoweek']; ?></h3>
</td>
 </tr>
 <tr>
  <td class="first"><?php echo $lang['dispensary-thisweek']; ?>:</td>
  <td><?php echo number_format($quantityWeek,0); ?> <span class="smallerfont">g.</span></td>
  <td><?php echo number_format($salesWeek,0); ?> <span class="smallerfont">&euro;</span></td>
  <td class="evolution"><?php
  $evolution = (($salesWeek - $salesWeekMinus1) /  $salesWeekMinus1) * 100;
  if ($salesWeek > $salesWeekMinus1) {
	  // Improvement
	  echo number_format($evolution,0) . "% <img src='images/positive2.png' />";
  } else if ($salesWeek < $salesWeekMinus1) {
	  // Decline
	  echo number_format($evolution,0) . "% <img src='images/negative2.png' />";
  }
?>
  </td>
 </tr>
 <tr>
  <td class="first"><?php echo $lang['dispensary-lastweek']; ?>:</td>
  <td><?php echo number_format($quantityWeekMinus1,0); ?> <span class="smallerfont">g.</span></td>
  <td><?php echo number_format($salesWeekMinus1,0); ?> <span class="smallerfont">&euro;</span></td>
  <td class="evolution"><?php
  $evolution = (($salesWeekMinus1 - $salesWeekMinus2) /  $salesWeekMinus2) * 100;
  if ($salesWeekMinus1 > $salesWeekMinus2) {
	  // Improvement
	  echo number_format($evolution,0) . "% <img src='images/positive2.png' />";
  } else if ($salesWeekMinus1 < $salesWeekMinus2) {
	  // Decline
	  echo number_format($evolution,0) . "% <img src='images/negative2.png' />";
  }
?>
  </td>
 </tr>
 <tr>
  <td class="first"><?php echo $lang['dispensary-twoweeksago']; ?>:</td>
  <td><?php echo number_format($quantityWeekMinus2,0); ?> <span class="smallerfont">g.</span></td>
  <td><?php echo number_format($salesWeekMinus2,0); ?> <span class="smallerfont">&euro;</span></td>
  <td class="evolution"><?php
  $evolution = (($salesWeekMinus2 - $salesWeekMinus3) /  $salesWeekMinus3) * 100;
  if ($salesWeekMinus2 > $salesWeekMinus3) {
	  // Improvement
	  echo number_format($evolution,0) . "% <img src='images/positive2.png' />";
  } else if ($salesWeekMinus2 < $salesWeekMinus3) {
	  // Decline
	  echo number_format($evolution,0) . "% <img src='images/negative2.png' />";
  }
?>
  </td>
 </tr>
 <tr>
  <td class="first"><?php echo $lang['dispensary-threeweeksago']; ?>:</td>
  <td><?php echo number_format($quantityWeekMinus3,0); ?> <span class="smallerfont">g.</span></td>
  <td><?php echo number_format($salesWeekMinus3,0); ?> <span class="smallerfont">&euro;</span></td>
  <td class="evolution"><?php
  $evolution = (($salesWeekMinus3 - $salesWeekMinus4) /  $salesWeekMinus4) * 100;
  if ($salesWeekMinus3 > $salesWeekMinus4) {
	  // Improvement
	  echo number_format($evolution,0) . "% <img src='images/positive2.png' />";
  } else if ($salesWeekMinus3 < $salesWeekMinus4) {
	  // Decline
	  echo number_format($evolution,0) . "% <img src='images/negative2.png' />";
  }
?>
  </td>
 </tr>
 <tr>
  <td class="first"><?php echo $lang['dispensary-fourweeksago']; ?>:</td>
  <td><?php echo number_format($quantityWeekMinus4,0); ?> <span class="smallerfont">g.</span></td>
  <td><?php echo number_format($salesWeekMinus4,0); ?> <span class="smallerfont">&euro;</span></td>
  <td class="evolution"><?php
  $evolution = (($salesWeekMinus4 - $salesWeekMinus5) /  $salesWeekMinus5) * 100;
  if ($salesWeekMinus4 > $salesWeekMinus5) {
	  // Improvement
	  echo number_format($evolution,0) . "% <img src='images/positive2.png' />";
  } else if ($salesWeekMinus4 < $salesWeekMinus5) {
	  // Decline
	  echo number_format($evolution,0) . "% <img src='images/negative2.png' />";
  }
?>
  </td>
 </tr>
 <tr>
  <td class="first"><?php echo $lang['dispensary-fiveweeksago']; ?>:</td>
  <td><?php echo number_format($quantityWeekMinus5,0); ?> <span class="smallerfont">g.</span></td>
  <td><?php echo number_format($salesWeekMinus5,0); ?> <span class="smallerfont">&euro;</span></td>
  <td class="evolution"><?php
  $evolution = (($salesWeekMinus5 - $salesWeekMinus6) /  $salesWeekMinus6) * 100;
  if ($salesWeekMinus5 > $salesWeekMinus6) {
	  // Improvement
	  echo number_format($evolution,0) . "% <img src='images/positive2.png' />";
  } else if ($salesWeekMinus5 < $salesWeekMinus6) {
	  // Decline
	  echo number_format($evolution,0) . "% <img src='images/negative2.png' />";
  }
?>
  </td>
 </tr>
 <tr>
  <td class="first"><?php echo $lang['dispensary-sixweeksago']; ?>:</td>
  <td><?php echo number_format($quantityWeekMinus6,0); ?> <span class="smallerfont">g.</span></td>
  <td><?php echo number_format($salesWeekMinus6,0); ?> <span class="smallerfont">&euro;</span></td>
  <td class="evolution"><?php
  $evolution = (($salesWeekMinus6 - $salesWeekMinus7) /  $salesWeekMinus7) * 100;
  if ($salesWeekMinus6 > $salesWeekMinus7) {
	  // Improvement
	  echo number_format($evolution,0) . "% <img src='images/positive2.png' />";
  } else if ($salesWeekMinus6 < $salesWeekMinus7) {
	  // Decline
	  echo number_format($evolution,0) . "% <img src='images/negative2.png' />";
  }
?>
  </td>
 </tr>
 <tr>
  <td class="first"><?php echo $lang['dispensary-sevenweeksago']; ?>:</td>
  <td><?php echo number_format($quantityWeekMinus7,0); ?> <span class="smallerfont">g.</span></td>
  <td><?php echo number_format($salesWeekMinus7,0); ?> <span class="smallerfont">&euro;</span></td>
 </tr>
</table>

<table class="dayByDay displaybox adminHidden">
 <tr>
  <td colspan="4"><h3><?php echo $lang['dispensary-monthtomonth']; ?></h3>
</td>
 </tr>
 <tr>
  <td class="first"><?php echo $lang['dispensary-thismonth']; ?>:</td>
  <td><?php echo number_format($quantityMonth,0); ?> <span class="smallerfont">g.</span></td>
  <td><?php echo number_format($salesMonth,0); ?> <span class="smallerfont">&euro;</span></td>
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
  <td class="first"><?php echo date("F", strtotime("first day of last month")); ?>:</td>
  <td><?php echo number_format($quantityMonthMinus1,0); ?> <span class="smallerfont">g.</span></td>
  <td><?php echo number_format($salesMonthMinus1,0); ?> <span class="smallerfont">&euro;</span></td>
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
  <td><?php echo number_format($quantityMonthMinus2,0); ?> <span class="smallerfont">g.</span></td>
  <td><?php echo number_format($salesMonthMinus2,0); ?> <span class="smallerfont">&euro;</span></td>
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
  <td><?php echo number_format($quantityMonthMinus3,0); ?> <span class="smallerfont">g.</span></td>
  <td><?php echo number_format($salesMonthMinus3,0); ?> <span class="smallerfont">&euro;</span></td>
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
  <td><?php echo number_format($quantityMonthMinus4,0); ?> <span class="smallerfont">g.</span></td>
  <td><?php echo number_format($salesMonthMinus4,0); ?> <span class="smallerfont">&euro;</span></td>
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
  <td><?php echo number_format($quantityMonthMinus5,0); ?> <span class="smallerfont">g.</span></td>
  <td><?php echo number_format($salesMonthMinus5,0); ?> <span class="smallerfont">&euro;</span></td>
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
  <td><?php echo number_format($quantityMonthMinus6,0); ?> <span class="smallerfont">g.</span></td>
  <td><?php echo number_format($salesMonthMinus6,0); ?> <span class="smallerfont">&euro;</span></td>
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
  <td><?php echo number_format($quantityMonthMinus7,0); ?> <span class="smallerfont">g.</span></td>
  <td><?php echo number_format($salesMonthMinus7,0); ?> <span class="smallerfont">&euro;</span></td>
 </tr>
</table>














</div>



<?php displayFooter(); ?>
