<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
		
	$deleteDonationScript = <<<EOD
	
	    $(document).ready(function() {
		    
			$('#cloneTable').width($('#mainTable').width());

		    
		  		
			
		});

var tablesToExcel = (function () {
    var uri = 'data:application/vnd.ms-excel;base64,'
    , template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets>'
    , templateend = '</x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--><meta http-equiv="content-type" content="text/plain; charset=UTF-8"/></head>'
    , body = '<body>'
    , tablevar = '<table>{table'
    , tablevarend = '}</table>'
    , bodyend = '</body></html>'
    , worksheet = '<x:ExcelWorksheet><x:Name>'
    , worksheetend = '</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet>'
    , worksheetvar = '{worksheet'
    , worksheetvarend = '}'
    , base64 = function (s) { return window.btoa(unescape(encodeURIComponent(s))) }
    , format = function (s, c) { return s.replace(/{(\w+)}/g, function (m, p) { return c[p]; }) }
    , wstemplate = ''
    , tabletemplate = '';

    return function (table, name, filename) {
        var tables = table;

        for (var i = 0; i < tables.length; ++i) {
            wstemplate += worksheet + worksheetvar + i + worksheetvarend + worksheetend;
            tabletemplate += tablevar + i + tablevarend;
        }

        var allTemplate = template + wstemplate + templateend;
        var allWorksheet = body + tabletemplate + bodyend;
        var allOfIt = allTemplate + allWorksheet;

        var ctx = {};
        for (var j = 0; j < tables.length; ++j) {
            ctx['worksheet' + j] = name[j];
        }

        for (var k = 0; k < tables.length; ++k) {
            var exceltable;
            if (!tables[k].nodeType) exceltable = document.getElementById(tables[k]);
            if(exceltable != null){
            	ctx['table' + k] = exceltable.innerHTML;
        	}
        }

        //document.getElementById("dlink").href = uri + base64(format(template, ctx));
        //document.getElementById("dlink").download = filename;
        //document.getElementById("dlink").click();

        window.location.href = uri + base64(format(allOfIt, ctx));

    }
})();
EOD;

	pageStart("Visitas", NULL, $deleteDonationScript, "pdispensary", "product admin dev-align-center", "Visitas", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
?>

<center><img src="images/excel-new.png" style="cursor: pointer;" onclick="loadExcel();" value="Export to Excel" /></center>

<?php
		// Look up todays sales
		$selectSales = "SELECT COUNT(scanin) FROM newvisits WHERE DATE(scanin) = DATE(NOW())";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$salesToday = $row['COUNT(scanin)'];
			$unitsToday = $row['SUM(units)'];
			$quantityToday = $row['SUM(realQuantity)'];
			
		// Look up daily sales -1
		$selectSales = "SELECT COUNT(scanin) FROM newvisits WHERE DATE(scanin) = DATE_ADD(DATE(NOW()), INTERVAL -1 DAY)";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$salesTodayMinus1 = $row['COUNT(scanin)'];
			$unitsTodayMinus1 = $row['SUM(units)'];
			$quantityTodayMinus1 = $row['SUM(realQuantity)'];
			
		// Look up daily sales -2
		$selectSales = "SELECT COUNT(scanin) FROM newvisits WHERE DATE(scanin) = DATE_ADD(DATE(NOW()), INTERVAL -2 DAY)";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$salesTodayMinus2 = $row['COUNT(scanin)'];
			$unitsTodayMinus2 = $row['SUM(units)'];
			$quantityTodayMinus2 = $row['SUM(realQuantity)'];
			
		// Look up daily sales -3
		$selectSales = "SELECT COUNT(scanin) FROM newvisits WHERE DATE(scanin) = DATE_ADD(DATE(NOW()), INTERVAL -3 DAY)";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$salesTodayMinus3 = $row['COUNT(scanin)'];
			$unitsTodayMinus3 = $row['SUM(units)'];
			$quantityTodayMinus3 = $row['SUM(realQuantity)'];
			
		// Look up daily sales -4
		$selectSales = "SELECT COUNT(scanin) FROM newvisits WHERE DATE(scanin) = DATE_ADD(DATE(NOW()), INTERVAL -4 DAY)";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$salesTodayMinus4 = $row['COUNT(scanin)'];
			$unitsTodayMinus4 = $row['SUM(units)'];
			$quantityTodayMinus4 = $row['SUM(realQuantity)'];
			
		// Look up daily sales -5
		$selectSales = "SELECT COUNT(scanin) FROM newvisits WHERE DATE(scanin) = DATE_ADD(DATE(NOW()), INTERVAL -5 DAY)";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$salesTodayMinus5 = $row['COUNT(scanin)'];
			$unitsTodayMinus5 = $row['SUM(units)'];
			$quantityTodayMinus5 = $row['SUM(realQuantity)'];
			
		// Look up daily sales -6
		$selectSales = "SELECT COUNT(scanin) FROM newvisits WHERE DATE(scanin) = DATE_ADD(DATE(NOW()), INTERVAL -6 DAY)";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$salesTodayMinus6 = $row['COUNT(scanin)'];
			$unitsTodayMinus6 = $row['SUM(units)'];
			$quantityTodayMinus6 = $row['SUM(realQuantity)'];

		// Look up daily sales -7
		$selectSales = "SELECT COUNT(scanin) FROM newvisits WHERE DATE(scanin) = DATE_ADD(DATE(NOW()), INTERVAL -7 DAY)";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$salesTodayMinus7 = $row['COUNT(scanin)'];
			$unitsTodayMinus7 = $row['SUM(units)'];
			$quantityTodayMinus7 = $row['SUM(realQuantity)'];

			
			
			// AND NOW WEEK BY WEEK //
			
		// Look up this weeks sales
		$selectSales = "SELECT COUNT(scanin) FROM newvisits WHERE WEEK(scanin,1) = WEEK(NOW(),1) AND YEAR(scanin) = YEAR(NOW()) ";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$salesWeek = $row['COUNT(scanin)'];
			$unitsWeek = $row['SUM(units)'];
			$quantityWeek = $row['SUM(realQuantity)'];
			
		// Look up weekly sales -1
		$selectSales = "SELECT COUNT(scanin) FROM newvisits WHERE WEEK(scanin,1) = WEEK(DATE_ADD((NOW()), INTERVAL -1 WEEK),1) AND YEAR(scanin) = YEAR(DATE_ADD((NOW()), INTERVAL -1 WEEK))";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$salesWeekMinus1 = $row['COUNT(scanin)'];
			$unitsWeekMinus1 = $row['SUM(units)'];
			$quantityWeekMinus1 = $row['SUM(realQuantity)'];
			
		// Look up weekly sales -2
		$selectSales = "SELECT COUNT(scanin) FROM newvisits WHERE WEEK(scanin,1) = WEEK(DATE_ADD((NOW()), INTERVAL -2 WEEK),1) AND YEAR(scanin) = YEAR(DATE_ADD((NOW()), INTERVAL -2 WEEK))";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$salesWeekMinus2 = $row['COUNT(scanin)'];
			$unitsWeekMinus2 = $row['SUM(units)'];
			$quantityWeekMinus2 = $row['SUM(realQuantity)'];
			
		// Look up weekly sales -3
		$selectSales = "SELECT COUNT(scanin) FROM newvisits WHERE WEEK(scanin,1) = WEEK(DATE_ADD((NOW()), INTERVAL -3 WEEK),1) AND YEAR(scanin) = YEAR(DATE_ADD((NOW()), INTERVAL -3 WEEK))";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$salesWeekMinus3 = $row['COUNT(scanin)'];
			$unitsWeekMinus3 = $row['SUM(units)'];
			$quantityWeekMinus3 = $row['SUM(realQuantity)'];
			
		// Look up weekly sales -4
		$selectSales = "SELECT COUNT(scanin) FROM newvisits WHERE WEEK(scanin,1) = WEEK(DATE_ADD((NOW()), INTERVAL -4 WEEK),1) AND YEAR(scanin) = YEAR(DATE_ADD((NOW()), INTERVAL -4 WEEK))";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$salesWeekMinus4 = $row['COUNT(scanin)'];
			$unitsWeekMinus4 = $row['SUM(units)'];
			$quantityWeekMinus4 = $row['SUM(realQuantity)'];
			
		// Look up weekly sales -5
		$selectSales = "SELECT COUNT(scanin) FROM newvisits WHERE WEEK(scanin,1) = WEEK(DATE_ADD((NOW()), INTERVAL -5 WEEK),1) AND YEAR(scanin) = YEAR(DATE_ADD((NOW()), INTERVAL -5 WEEK))";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$salesWeekMinus5 = $row['COUNT(scanin)'];
			$unitsWeekMinus5 = $row['SUM(units)'];
			$quantityWeekMinus5 = $row['SUM(realQuantity)'];
			
		// Look up weekly sales -6
		$selectSales = "SELECT COUNT(scanin) FROM newvisits WHERE WEEK(scanin,1) = WEEK(DATE_ADD((NOW()), INTERVAL -6 WEEK),1) AND YEAR(scanin) = YEAR(DATE_ADD((NOW()), INTERVAL -6 WEEK))";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$salesWeekMinus6 = $row['COUNT(scanin)'];
			$unitsWeekMinus6 = $row['SUM(units)'];
			$quantityWeekMinus6 = $row['SUM(realQuantity)'];

		// Look up weekly sales -7
		$selectSales = "SELECT COUNT(scanin) FROM newvisits WHERE WEEK(scanin,1) = WEEK(DATE_ADD((NOW()), INTERVAL -7 WEEK),1) AND YEAR(scanin) = YEAR(DATE_ADD((NOW()), INTERVAL -7 WEEK))";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$salesWeekMinus7 = $row['COUNT(scanin)'];
			$unitsWeekMinus7 = $row['SUM(units)'];
			$quantityWeekMinus7 = $row['SUM(realQuantity)'];
			
			
			
			// AND NOW MONTH BY MONTH //
			
		// Look up this months sales
		$selectSales = "SELECT COUNT(scanin) FROM newvisits WHERE MONTH(scanin) = MONTH(NOW()) AND YEAR(scanin) = YEAR(NOW()) ";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$salesMonth = $row['COUNT(scanin)'];
			$unitsMonth = $row['SUM(units)'];
			$quantityMonth = $row['SUM(realQuantity)'];
			
		// Look up monthly sales -1
		$selectSales = "SELECT COUNT(scanin) FROM newvisits WHERE MONTH(scanin) = MONTH(DATE_ADD((NOW()), INTERVAL -1 MONTH)) AND YEAR(scanin) = YEAR(DATE_ADD((NOW()), INTERVAL -1 MONTH))";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$salesMonthMinus1 = $row['COUNT(scanin)'];
			$unitsMonthMinus1 = $row['SUM(units)'];
			$quantityMonthMinus1 = $row['SUM(realQuantity)'];
			
		// Look up monthly sales -2
		$selectSales = "SELECT COUNT(scanin) FROM newvisits WHERE MONTH(scanin) = MONTH(DATE_ADD((NOW()), INTERVAL -2 MONTH)) AND YEAR(scanin) = YEAR(DATE_ADD((NOW()), INTERVAL -2 MONTH))";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$salesMonthMinus2 = $row['COUNT(scanin)'];
			$unitsMonthMinus2 = $row['SUM(units)'];
			$quantityMonthMinus2 = $row['SUM(realQuantity)'];
			
		// Look up monthly sales -3
		$selectSales = "SELECT COUNT(scanin) FROM newvisits WHERE MONTH(scanin) = MONTH(DATE_ADD((NOW()), INTERVAL -3 MONTH)) AND YEAR(scanin) = YEAR(DATE_ADD((NOW()), INTERVAL -3 MONTH))";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$salesMonthMinus3 = $row['COUNT(scanin)'];
			$unitsMonthMinus3 = $row['SUM(units)'];
			$quantityMonthMinus3 = $row['SUM(realQuantity)'];
			
		// Look up monthly sales -4
		$selectSales = "SELECT COUNT(scanin) FROM newvisits WHERE MONTH(scanin) = MONTH(DATE_ADD((NOW()), INTERVAL -4 MONTH)) AND YEAR(scanin) = YEAR(DATE_ADD((NOW()), INTERVAL -4 MONTH))";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$salesMonthMinus4 = $row['COUNT(scanin)'];
			$unitsMonthMinus4 = $row['SUM(units)'];
			$quantityMonthMinus4 = $row['SUM(realQuantity)'];
			
		// Look up monthly sales -5
		$selectSales = "SELECT COUNT(scanin) FROM newvisits WHERE MONTH(scanin) = MONTH(DATE_ADD((NOW()), INTERVAL -5 MONTH)) AND YEAR(scanin) = YEAR(DATE_ADD((NOW()), INTERVAL -5 MONTH))";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$salesMonthMinus5 = $row['COUNT(scanin)'];
			$unitsMonthMinus5 = $row['SUM(units)'];
			$quantityMonthMinus5 = $row['SUM(realQuantity)'];
			
		// Look up monthly sales -6
		$selectSales = "SELECT COUNT(scanin) FROM newvisits WHERE MONTH(scanin) = MONTH(DATE_ADD((NOW()), INTERVAL -6 MONTH)) AND YEAR(scanin) = YEAR(DATE_ADD((NOW()), INTERVAL -6 MONTH))";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$salesMonthMinus6 = $row['COUNT(scanin)'];
			$unitsMonthMinus6 = $row['SUM(units)'];
			$quantityMonthMinus6 = $row['SUM(realQuantity)'];

		// Look up monthly sales -7
		$selectSales = "SELECT COUNT(scanin) FROM newvisits WHERE MONTH(scanin) = MONTH(DATE_ADD((NOW()), INTERVAL -7 MONTH)) AND YEAR(scanin) = YEAR(DATE_ADD((NOW()), INTERVAL -7 MONTH))";
		try
		{
			$result = $pdo3->prepare("$selectSales");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$salesMonthMinus7 = $row['COUNT(scanin)'];
			$unitsMonthMinus7 = $row['SUM(units)'];
			$quantityMonthMinus7 = $row['SUM(realQuantity)'];
			

?>
<br />
<h3 class='title'>TOTAL</h3>
<div class="historybox">
	 <span class="winnerboxheader"><?php echo $lang['dispensary-daytoday']; ?></span><br><br>
	<table class="dayByDay historytable" id="t1">
	 <tr>
	  <td class="first"><?php echo $lang['dispensary-today']; ?>:</td>
	  <td><?php echo number_format($salesToday,0); ?> </td>
	 </tr>
	 <tr>
	  <td class="first"><?php echo $lang['dispensary-yesterday']; ?>:</td>
	  <td><?php echo number_format($salesTodayMinus1,0); ?> </td>
	 </tr>
	 <tr>
	  <td class="first"><?php echo date("l", strtotime("-2 days")); ?>:</td>
	  <td><?php echo number_format($salesTodayMinus2,0); ?> </td>
	 </tr>
	 <tr>
	  <td class="first"><?php echo date("l", strtotime("-3 days")); ?>:</td>
	  <td><?php echo number_format($salesTodayMinus3,0); ?> </td>
	 </tr>
	 <tr>
	  <td class="first"><?php echo date("l", strtotime("-4 days")); ?>:</td>
	  <td><?php echo number_format($salesTodayMinus4,0); ?> </td>
	 </tr>
	 <tr>
	  <td class="first"><?php echo date("l", strtotime("-5 days")); ?>:</td>
	  <td><?php echo number_format($salesTodayMinus5,0); ?> </td>
	 </tr>
	 <tr>
	  <td class="first"><?php echo date("l", strtotime("-6 days")); ?>:</td>
	  <td><?php echo number_format($salesTodayMinus6,0); ?> </td>
	 </tr>
	 <tr>
	  <td class="first"><?php echo date("l", strtotime("-7 days")); ?>:</td>
	  <td><?php echo number_format($salesTodayMinus7,0); ?> </td>
	 </tr>
	</table>
</div>
<div class="historybox">
	<span class="winnerboxheader"><?php echo $lang['dispensary-weektoweek']; ?></span><br><br>
	<table class="dayByDay historytable" id="t2">
	 <tr>
	  <td class="first"><?php echo $lang['dispensary-thisweek']; ?>:</td>
	  <td><?php echo number_format($salesWeek,0); ?> </td>
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
	  <td><?php echo number_format($salesWeekMinus1,0); ?> </td>
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
	  <td><?php echo number_format($salesWeekMinus2,0); ?> </td>
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
	  <td><?php echo number_format($salesWeekMinus3,0); ?> </td>
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
	  <td><?php echo number_format($salesWeekMinus4,0); ?> </td>
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
	  <td><?php echo number_format($salesWeekMinus5,0); ?> </td>
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
	  <td><?php echo number_format($salesWeekMinus6,0); ?> </td>
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
	  <td><?php echo number_format($salesWeekMinus7,0); ?> </td>
	 </tr>
	</table>
</div>
<div class="historybox">
	<span class="winnerboxheader"><?php echo $lang['dispensary-monthtomonth']; ?></span><br><br>
<table class="dayByDay historytable adminHidden" id="t3">

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
  <td class="first"><?php echo date("F", strtotime("first day of last month")); ?>:</td>
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
 </tr>
</table>
</div>
</div>



<?php displayFooter(); ?>
<script type="text/javascript">
	 function loadExcel(){
 			$("#load").show();
       		window.location.href = 'visit-history-report.php';
       		    setTimeout(function () {
			        $("#load").hide();
			    }, 5000);   
       }
</script>
