<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	?>
	<style type="text/css">
		#load
		{
		   
		    display: none;
		    position : fixed;
		    z-index: 100;
		    background-image : url('images/loading-small.gif');
		    background-color:#666;
		    opacity : 0.4;
		    background-repeat : no-repeat;
		    background-position : center;
		    left : 0;
		    bottom : 0;
		    right : 0;
		    top : 0;
		}
	</style>
	<?php
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
            ctx['table' + k] = exceltable.innerHTML;
        }

        //document.getElementById("dlink").href = uri + base64(format(template, ctx));
        //document.getElementById("dlink").download = filename;
        //document.getElementById("dlink").click();

        window.location.href = uri + base64(format(allOfIt, ctx));

    }
})();
EOD;

	pageStart($lang['title-dispensary'], NULL, $deleteDonationScript, "pdispensary", "product admin", $lang['global-dispensary'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
?>
 <div id="load">
 </div>
<center><img src="images/excel.png" style="cursor: pointer;"  onclick="loadExcel();" value="Export to Excel" /></center>

<?php

	// DAY BY DAY FIRST
	$day_row = <<<EOD
<h3 class='title'>TOTAL</h3><table class="dayByDay displaybox" id="dayByDay" style='vertical-align: top;'>
<tbody>
 <tr>
  <td colspan="4"><h3>{$lang['dispensary-daytoday']}</h3> 
  </td>
 </tr>
EOD;
	
	for ($a = 0; $a < 8; $a++) {
		
		if ($a == 0) {
			$dateOperator = "DATE(NOW())";
			$timestamp = date("d-m-Y");
		} else {
			$dateOperator = "DATE_ADD(DATE(NOW()), INTERVAL -$a DAY)";
			$timestamp = date("d-m-Y", strtotime("-$a days"));
		}
	
		// Look up todays sales
		$selectSales = "SELECT SUM(amount), SUM(units), SUM(realQuantity) from sales WHERE DATE(saletime) = $dateOperator";

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
			$sales = number_format($row['SUM(amount)'],0);
			$units = number_format($row['SUM(units)'],0);
			$quantity = number_format($row['SUM(realQuantity)'],1);
			
		
		$day_row .= <<<EOD
 <tr>
  <td class="first">$timestamp:</td>
  <td>$quantity <span class="smallerfont">g.</span></td>
  <td>$units <span class="smallerfont">u.</span></td>
  <td>$sales <span class="smallerfont">&euro;</span></td>
 </tr>
EOD;

	}
	
		
	$day_row .= <<<EOD
 <tr id="loadMore">
  <td class="centered" colspan="4"><a href="#" onclick="event.preventDefault(); loadMoreDays()" class='yellow' style='font-size: 12px;'>[{$lang['load-more']}]</a></td>
 </tr>
 </tbody>
</table>
EOD;

echo $day_row;

	// THEN WEEK TO WEEK
	$week_row = <<<EOD
<table class="dayByDay displaybox" id="weekByWeek" style='vertical-align: top;'>
<tbody>
 <tr>
  <td colspan="4"><h3>{$lang['dispensary-weektoweek']}</h3> 
  </td>
 </tr>
EOD;
	
	for ($a = 0; $a < 8; $a++) {
		
		if ($a == 0) {
			$dateOperator = "WEEK(saletime,1) = WEEK(NOW(),1) AND YEAR(saletime) = YEAR(NOW())";
			$timestamp = $lang['dispensary-thisweek'];
		} else if ($a == 1) {
			$dateOperator = "WEEK(saletime,1) = WEEK(DATE_ADD((NOW()), INTERVAL -$a WEEK),1) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -$a WEEK))";
			$timestamp = $lang['dispensary-lastweek'];
		} else {
			$dateOperator = "WEEK(saletime,1) = WEEK(DATE_ADD((NOW()), INTERVAL -$a WEEK),1) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -$a WEEK))";
			$timestamp = $lang['dispensary-weeksago-1'] . $a . $lang['dispensary-weeksago-2'];
		}
	
		// Look up todays sales
		$selectSales = "SELECT SUM(amount), SUM(units), SUM(realQuantity) from sales WHERE $dateOperator";

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
			$sales = number_format($row['SUM(amount)'],0);
			$units = number_format($row['SUM(units)'],0);
			$quantity = number_format($row['SUM(realQuantity)'],1);
			
		
		$week_row .= <<<EOD
 <tr>
  <td class="first">$timestamp:</td>
  <td>$quantity <span class="smallerfont">g.</span></td>
  <td>$units <span class="smallerfont">u.</span></td>
  <td>$sales <span class="smallerfont">&euro;</span></td>
 </tr>
EOD;

	}
	
		
	$week_row .= <<<EOD
 <tr id="loadMore2">
  <td class="centered" colspan="4"><a href="#" onclick="event.preventDefault(); loadMoreWeeks()" class='yellow' style='font-size: 12px;'>[{$lang['load-more']}]</a></td>
 </tr>
 </tbody>
</table>
EOD;

echo $week_row;

	// THEN MONTH TO MONTH
	$month_row = <<<EOD
<table class="dayByDay displaybox" id="monthByMonth" style='vertical-align: top;'>
<tbody>
 <tr>
  <td colspan="4"><h3>{$lang['dispensary-monthtomonth']}</h3> 
  </td>
 </tr>
EOD;
	
	for ($a = 0; $a < 8; $a++) {
		
		if ($a == 0) {
			$dateOperator = "MONTH(saletime) = MONTH(NOW()) AND YEAR(saletime) = YEAR(NOW())";
			$timestamp = $lang['dispensary-thismonth'];
		} else {
			$dateOperator = "MONTH(saletime) = MONTH(DATE_ADD((NOW()), INTERVAL -$a MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -$a MONTH))";
			$timestamp = date("m-Y", strtotime("-$a months", strtotime("first day of this month") ));
		}
	
		// Look up todays sales
		$selectSales = "SELECT SUM(amount), SUM(units), SUM(realQuantity) from sales WHERE $dateOperator";
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
			$sales = number_format($row['SUM(amount)'],0);
			$units = number_format($row['SUM(units)'],0);
			$quantity = number_format($row['SUM(realQuantity)'],1);
			
		
		$month_row .= <<<EOD
 <tr>
  <td class="first">$timestamp:</td>
  <td>$quantity <span class="smallerfont">g.</span></td>
  <td>$units <span class="smallerfont">u.</span></td>
  <td>$sales <span class="smallerfont">&euro;</span></td>
 </tr>
EOD;

	}
	
		
	$month_row .= <<<EOD
 <tr id="loadMore3">
  <td class="centered" colspan="4"><a href="#" onclick="event.preventDefault(); loadMoreMonths()" class='yellow' style='font-size: 12px;'>[{$lang['load-more']}]</a></td>
 </tr>
 </tbody>
</table>
EOD;

echo $month_row;

	// DAY BY DAY FIRST - FLOWERS ONLY

	$day_row = <<<EOD
<br /><br />
<h3 class='title'>{$lang['global-flowerscaps']}</h3><table class="dayByDay displaybox" style='vertical-align: top;'>
<tbody>
 <tr>
  <td colspan="3"><h3>{$lang['dispensary-daytoday']}</h3> 
  </td>
 </tr>
EOD;
	
	for ($a = 0; $a < 8; $a++) {
		
		if ($a == 0) {
			$dateOperator = "DATE(NOW())";
			$timestamp = date("d-m-Y");
		} else {
			$dateOperator = "DATE_ADD(DATE(NOW()), INTERVAL -$a DAY)";
			$timestamp = date("d-m-Y", strtotime("-$a days"));
		}
	
		// Look up todays sales
		$selectSales = "SELECT SUM(d.amount), SUM(d.realQuantity) from sales s, salesdetails d WHERE d.saleid = s.saleid AND d.category = 1 AND DATE(saletime) = $dateOperator";

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
			$sales = number_format($row['SUM(d.amount)'],0);
			$quantity = number_format($row['SUM(d.realQuantity)'],1);
			
		
		$day_row .= <<<EOD
 <tr>
  <td class="first">$timestamp:</td>
  <td>$quantity <span class="smallerfont">g.</span></td>
  <td>$sales <span class="smallerfont">&euro;</span></td>
 </tr>
EOD;

	}
	
		
	$day_row .= <<<EOD
 </tbody>
</table>
EOD;

echo $day_row;

	// THEN WEEK TO WEEK - FLOWERS ONLY
	$week_row = <<<EOD
<table class="dayByDay displaybox" style='vertical-align: top;'>
<tbody>
 <tr>
  <td colspan="3"><h3>{$lang['dispensary-weektoweek']}</h3> 
  </td>
 </tr>
EOD;
	
	for ($a = 0; $a < 8; $a++) {
		
		if ($a == 0) {
			$dateOperator = "WEEK(saletime,1) = WEEK(NOW(),1) AND YEAR(saletime) = YEAR(NOW())";
			$timestamp = $lang['dispensary-thisweek'];
		} else if ($a == 1) {
			$dateOperator = "WEEK(saletime,1) = WEEK(DATE_ADD((NOW()), INTERVAL -$a WEEK),1) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -$a WEEK))";
			$timestamp = $lang['dispensary-lastweek'];
		} else {
			$dateOperator = "WEEK(saletime,1) = WEEK(DATE_ADD((NOW()), INTERVAL -$a WEEK),1) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -$a WEEK))";
			$timestamp = $lang['dispensary-weeksago-1'] . $a . $lang['dispensary-weeksago-2'];
		}
	
		// Look up todays sales
		$selectSales = "SELECT SUM(d.amount), SUM(d.realQuantity) from sales s, salesdetails d WHERE d.saleid = s.saleid AND d.category = 1 AND $dateOperator";

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
			$sales = number_format($row['SUM(d.amount)'],0);
			$quantity = number_format($row['SUM(d.realQuantity)'],1);
			
		
		$week_row .= <<<EOD
 <tr>
  <td class="first">$timestamp:</td>
  <td>$quantity <span class="smallerfont">g.</span></td>
  <td>$sales <span class="smallerfont">&euro;</span></td>
 </tr>
EOD;

	}
	
		
	$week_row .= <<<EOD
 </tbody>
</table>
EOD;

echo $week_row;

	// THEN MONTH TO MONTH - FLOWERS ONLY
	$month_row = <<<EOD
<table class="dayByDay displaybox" style='vertical-align: top;'>
<tbody>
 <tr>
  <td colspan="3"><h3>{$lang['dispensary-monthtomonth']}</h3> 
  </td>
 </tr>
EOD;
	
	for ($a = 0; $a < 8; $a++) {
		
		if ($a == 0) {
			$dateOperator = "MONTH(saletime) = MONTH(NOW()) AND YEAR(saletime) = YEAR(NOW())";
			$timestamp = $lang['dispensary-thismonth'];
		} else {
			$dateOperator = "MONTH(saletime) = MONTH(DATE_ADD((NOW()), INTERVAL -$a MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -$a MONTH))";
			$timestamp = date("m-Y", strtotime("-$a months", strtotime("first day of this month") ));
		}
	
		// Look up todays sales
		$selectSales = "SELECT SUM(d.amount), SUM(d.realQuantity) from sales s, salesdetails d WHERE d.saleid = s.saleid AND d.category = 1 AND $dateOperator";

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
			$sales = number_format($row['SUM(d.amount)'],0);
			$quantity = number_format($row['SUM(d.realQuantity)'],1);
			
		
		$month_row .= <<<EOD
 <tr>
  <td class="first">$timestamp:</td>
  <td>$quantity <span class="smallerfont">g.</span></td>
  <td>$sales <span class="smallerfont">&euro;</span></td>
 </tr>
EOD;

	}
	
		
	$month_row .= <<<EOD
 </tbody>
</table>
EOD;

echo $month_row;

	// DAY BY DAY FIRST - EXTRACTS ONLY

	$day_row = <<<EOD
<br /><br />
<h3 class='title'>{$lang['global-extractscaps']}</h3><table class="dayByDay displaybox" style='vertical-align: top;'>
<tbody>
 <tr>
  <td colspan="3"><h3>{$lang['dispensary-daytoday']}</h3> 
  </td>
 </tr>
EOD;
	
	for ($a = 0; $a < 8; $a++) {
		
		if ($a == 0) {
			$dateOperator = "DATE(NOW())";
			$timestamp = date("d-m-Y");
		} else {
			$dateOperator = "DATE_ADD(DATE(NOW()), INTERVAL -$a DAY)";
			$timestamp = date("d-m-Y", strtotime("-$a days"));
		}
	
		// Look up todays sales
		$selectSales = "SELECT SUM(d.amount), SUM(d.realQuantity) from sales s, salesdetails d WHERE d.saleid = s.saleid AND d.category = 2 AND DATE(saletime) = $dateOperator";

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
			$sales = number_format($row['SUM(d.amount)'],0);
			$quantity = number_format($row['SUM(d.realQuantity)'],1);
			
		
		$day_row .= <<<EOD
 <tr>
  <td class="first">$timestamp:</td>
  <td>$quantity <span class="smallerfont">g.</span></td>
  <td>$sales <span class="smallerfont">&euro;</span></td>
 </tr>
EOD;

	}
	
		
	$day_row .= <<<EOD
 </tbody>
</table>
EOD;

echo $day_row;

	// THEN WEEK TO WEEK - EXTRACTS ONLY
	$week_row = <<<EOD
<table class="dayByDay displaybox" style='vertical-align: top;'>
<tbody>
 <tr>
  <td colspan="3"><h3>{$lang['dispensary-weektoweek']}</h3> 
  </td>
 </tr>
EOD;
	
	for ($a = 0; $a < 8; $a++) {
		
		if ($a == 0) {
			$dateOperator = "WEEK(saletime,1) = WEEK(NOW(),1) AND YEAR(saletime) = YEAR(NOW())";
			$timestamp = $lang['dispensary-thisweek'];
		} else if ($a == 1) {
			$dateOperator = "WEEK(saletime,1) = WEEK(DATE_ADD((NOW()), INTERVAL -$a WEEK),1) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -$a WEEK))";
			$timestamp = $lang['dispensary-lastweek'];
		} else {
			$dateOperator = "WEEK(saletime,1) = WEEK(DATE_ADD((NOW()), INTERVAL -$a WEEK),1) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -$a WEEK))";
			$timestamp = $lang['dispensary-weeksago-1'] . $a . $lang['dispensary-weeksago-2'];
		}
	
		// Look up todays sales
		$selectSales = "SELECT SUM(d.amount), SUM(d.realQuantity) from sales s, salesdetails d WHERE d.saleid = s.saleid AND d.category = 2 AND $dateOperator";

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
			$sales = number_format($row['SUM(d.amount)'],0);
			$quantity = number_format($row['SUM(d.realQuantity)'],1);
			
		
		$week_row .= <<<EOD
 <tr>
  <td class="first">$timestamp:</td>
  <td>$quantity <span class="smallerfont">g.</span></td>
  <td>$sales <span class="smallerfont">&euro;</span></td>
 </tr>
EOD;

	}
	
		
	$week_row .= <<<EOD
 </tbody>
</table>
EOD;

echo $week_row;

	// THEN MONTH TO MONTH - EXTRACTS ONLY
	$month_row = <<<EOD
<table class="dayByDay displaybox" style='vertical-align: top;'>
<tbody>
 <tr>
  <td colspan="3"><h3>{$lang['dispensary-monthtomonth']}</h3> 
  </td>
 </tr>
EOD;
	
	for ($a = 0; $a < 8; $a++) {
		
		if ($a == 0) {
			$dateOperator = "MONTH(saletime) = MONTH(NOW()) AND YEAR(saletime) = YEAR(NOW())";
			$timestamp = $lang['dispensary-thismonth'];
		} else {
			$dateOperator = "MONTH(saletime) = MONTH(DATE_ADD((NOW()), INTERVAL -$a MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -$a MONTH))";
			$timestamp = date("m-Y", strtotime("-$a months", strtotime("first day of this month") ));
		}
	
		// Look up todays sales
		$selectSales = "SELECT SUM(d.amount), SUM(d.realQuantity) from sales s, salesdetails d WHERE d.saleid = s.saleid AND d.category = 2 AND $dateOperator";

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
			$sales = number_format($row['SUM(d.amount)'],0);
			$quantity = number_format($row['SUM(d.realQuantity)'],1);
			
		
		$month_row .= <<<EOD
 <tr>
  <td class="first">$timestamp:</td>
  <td>$quantity <span class="smallerfont">g.</span></td>
  <td>$sales <span class="smallerfont">&euro;</span></td>
 </tr>
EOD;

	}
	
		
	$month_row .= <<<EOD
 </tbody>
</table>
EOD;

echo $month_row;



		// Query to look up categories, then products in each category
		$selectCats = "SELECT id, name, description, type from categories WHERE id > 2 ORDER by id ASC";
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
			$type = $category['type'];
			
	if ($type == 1) {

	// DAY BY DAY FIRST - OTHER CATEGORIES

	$day_row = <<<EOD
<br /><br />
<h3 class='title'>$categoryname</h3><table class="dayByDay displaybox" style='vertical-align: top;'>
<tbody>
 <tr>
  <td colspan="3"><h3>{$lang['dispensary-daytoday']}</h3> 
  </td>
 </tr>
EOD;
	
	for ($a = 0; $a < 8; $a++) {
		
		if ($a == 0) {
			$dateOperator = "DATE(NOW())";
			$timestamp = date("d-m-Y");
		} else {
			$dateOperator = "DATE_ADD(DATE(NOW()), INTERVAL -$a DAY)";
			$timestamp = date("d-m-Y", strtotime("-$a days"));
		}
	
		// Look up todays sales
		$selectSales = "SELECT SUM(d.amount), SUM(d.realQuantity) from sales s, salesdetails d WHERE d.saleid = s.saleid AND d.category = $categoryid AND DATE(saletime) = $dateOperator";

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
			$sales = number_format($row['SUM(d.amount)'],0);
			$quantity = number_format($row['SUM(d.realQuantity)'],1);
			
		
		$day_row .= <<<EOD
 <tr>
  <td class="first">$timestamp:</td>
  <td>$quantity <span class="smallerfont">g.</span></td>
  <td>$sales <span class="smallerfont">&euro;</span></td>
 </tr>
EOD;

	}
	
		
	$day_row .= <<<EOD
 </tbody>
</table>
EOD;

echo $day_row;

	// THEN WEEK TO WEEK - OTHER CATEGORIES
	$week_row = <<<EOD
<table class="dayByDay displaybox" style='vertical-align: top;'>
<tbody>
 <tr>
  <td colspan="3"><h3>{$lang['dispensary-weektoweek']}</h3> 
  </td>
 </tr>
EOD;
	
	for ($a = 0; $a < 8; $a++) {
		
		if ($a == 0) {
			$dateOperator = "WEEK(saletime,1) = WEEK(NOW(),1) AND YEAR(saletime) = YEAR(NOW())";
			$timestamp = $lang['dispensary-thisweek'];
		} else if ($a == 1) {
			$dateOperator = "WEEK(saletime,1) = WEEK(DATE_ADD((NOW()), INTERVAL -$a WEEK),1) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -$a WEEK))";
			$timestamp = $lang['dispensary-lastweek'];
		} else {
			$dateOperator = "WEEK(saletime,1) = WEEK(DATE_ADD((NOW()), INTERVAL -$a WEEK),1) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -$a WEEK))";
			$timestamp = $lang['dispensary-weeksago-1'] . $a . $lang['dispensary-weeksago-2'];
		}
	
		// Look up todays sales
		$selectSales = "SELECT SUM(d.amount), SUM(d.realQuantity) from sales s, salesdetails d WHERE d.saleid = s.saleid AND d.category = $categoryid AND $dateOperator";

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
			$sales = number_format($row['SUM(d.amount)'],0);
			$quantity = number_format($row['SUM(d.realQuantity)'],1);
			
		
		$week_row .= <<<EOD
 <tr>
  <td class="first">$timestamp:</td>
  <td>$quantity <span class="smallerfont">g.</span></td>
  <td>$sales <span class="smallerfont">&euro;</span></td>
 </tr>
EOD;

	}
	
		
	$week_row .= <<<EOD
 </tbody>
</table>
EOD;

echo $week_row;

	// THEN MONTH TO MONTH - OTHER CATEGORIES
	$month_row = <<<EOD
<table class="dayByDay displaybox" style='vertical-align: top;'>
<tbody>
 <tr>
  <td colspan="3"><h3>{$lang['dispensary-monthtomonth']}</h3> 
  </td>
 </tr>
EOD;
	
	for ($a = 0; $a < 8; $a++) {
		
		if ($a == 0) {
			$dateOperator = "MONTH(saletime) = MONTH(NOW()) AND YEAR(saletime) = YEAR(NOW())";
			$timestamp = $lang['dispensary-thismonth'];
		} else {
			$dateOperator = "MONTH(saletime) = MONTH(DATE_ADD((NOW()), INTERVAL -$a MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -$a MONTH))";
			$timestamp = date("m-Y", strtotime("-$a months", strtotime("first day of this month") ));
		}
	
		// Look up todays sales
		$selectSales = "SELECT SUM(d.amount), SUM(d.realQuantity) from sales s, salesdetails d WHERE d.saleid = s.saleid AND d.category = $categoryid AND $dateOperator";

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
			$sales = number_format($row['SUM(d.amount)'],0);
			$quantity = number_format($row['SUM(d.realQuantity)'],1);
			
		
		$month_row .= <<<EOD
 <tr>
  <td class="first">$timestamp:</td>
  <td>$quantity <span class="smallerfont">g.</span></td>
  <td>$sales <span class="smallerfont">&euro;</span></td>
 </tr>
EOD;

	}
	
		
	$month_row .= <<<EOD
 </tbody>
</table>
EOD;

echo $month_row;

	} else {
		
	// DAY BY DAY FIRST - OTHER CATEGORIES

	$day_row = <<<EOD
<br /><br />
<h3 class='title'>$categoryname</h3><table class="dayByDay displaybox" style='vertical-align: top;'>
<tbody>
 <tr>
  <td colspan="3"><h3>{$lang['dispensary-daytoday']}</h3> 
  </td>
 </tr>
EOD;
	
	for ($a = 0; $a < 8; $a++) {
		
		if ($a == 0) {
			$dateOperator = "DATE(NOW())";
			$timestamp = date("d-m-Y");
		} else {
			$dateOperator = "DATE_ADD(DATE(NOW()), INTERVAL -$a DAY)";
			$timestamp = date("d-m-Y", strtotime("-$a days"));
		}
	
		// Look up todays sales
		$selectSales = "SELECT SUM(d.amount), SUM(d.realQuantity) from sales s, salesdetails d WHERE d.saleid = s.saleid AND d.category = $categoryid AND DATE(saletime) = $dateOperator";

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
			$sales = number_format($row['SUM(d.amount)'],0);
			$quantity = number_format($row['SUM(d.realQuantity)'],1);
			
		
		$day_row .= <<<EOD
 <tr>
  <td class="first">$timestamp:</td>
  <td>$quantity <span class="smallerfont">u.</span></td>
  <td>$sales <span class="smallerfont">&euro;</span></td>
 </tr>
EOD;

	}
	
		
	$day_row .= <<<EOD
 </tbody>
</table>
EOD;

echo $day_row;

	// THEN WEEK TO WEEK - OTHER CATEGORIES
	$week_row = <<<EOD
<table class="dayByDay displaybox" style='vertical-align: top;'>
<tbody>
 <tr>
  <td colspan="3"><h3>{$lang['dispensary-weektoweek']}</h3> 
  </td>
 </tr>
EOD;
	
	for ($a = 0; $a < 8; $a++) {
		
		if ($a == 0) {
			$dateOperator = "WEEK(saletime,1) = WEEK(NOW(),1) AND YEAR(saletime) = YEAR(NOW())";
			$timestamp = $lang['dispensary-thisweek'];
		} else if ($a == 1) {
			$dateOperator = "WEEK(saletime,1) = WEEK(DATE_ADD((NOW()), INTERVAL -$a WEEK),1) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -$a WEEK))";
			$timestamp = $lang['dispensary-lastweek'];
		} else {
			$dateOperator = "WEEK(saletime,1) = WEEK(DATE_ADD((NOW()), INTERVAL -$a WEEK),1) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -$a WEEK))";
			$timestamp = $lang['dispensary-weeksago-1'] . $a . $lang['dispensary-weeksago-2'];
		}
	
		// Look up todays sales
		$selectSales = "SELECT SUM(d.amount), SUM(d.realQuantity) from sales s, salesdetails d WHERE d.saleid = s.saleid AND d.category = $categoryid AND $dateOperator";

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
			$sales = number_format($row['SUM(d.amount)'],0);
			$quantity = number_format($row['SUM(d.realQuantity)'],1);
			
		
		$week_row .= <<<EOD
 <tr>
  <td class="first">$timestamp:</td>
  <td>$quantity <span class="smallerfont">u.</span></td>
  <td>$sales <span class="smallerfont">&euro;</span></td>
 </tr>
EOD;

	}
	
		
	$week_row .= <<<EOD
 </tbody>
</table>
EOD;

echo $week_row;

	// THEN MONTH TO MONTH - OTHER CATEGORIES
	$month_row = <<<EOD
<table class="dayByDay displaybox" style='vertical-align: top;'>
<tbody>
 <tr>
  <td colspan="3"><h3>{$lang['dispensary-monthtomonth']}</h3> 
  </td>
 </tr>
EOD;
	
	for ($a = 0; $a < 8; $a++) {
		
		if ($a == 0) {
			$dateOperator = "MONTH(saletime) = MONTH(NOW()) AND YEAR(saletime) = YEAR(NOW())";
			$timestamp = $lang['dispensary-thismonth'];
		} else {
			$dateOperator = "MONTH(saletime) = MONTH(DATE_ADD((NOW()), INTERVAL -$a MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -$a MONTH))";
			$timestamp = date("m-Y", strtotime("-$a months", strtotime("first day of this month") ));
		}
	
		// Look up todays sales
		$selectSales = "SELECT SUM(d.amount), SUM(d.realQuantity) from sales s, salesdetails d WHERE d.saleid = s.saleid AND d.category = $categoryid AND $dateOperator";

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
			$sales = number_format($row['SUM(d.amount)'],0);
			$quantity = number_format($row['SUM(d.realQuantity)'],1);
			
		
		$month_row .= <<<EOD
 <tr>
  <td class="first">$timestamp:</td>
  <td>$quantity <span class="smallerfont">u.</span></td>
  <td>$sales <span class="smallerfont">&euro;</span></td>
 </tr>
EOD;

	}
	
		
	$month_row .= <<<EOD
 </tbody>
</table>
EOD;

echo $month_row;

	}

}
?>
<form>
 <input type="hidden" id="dayID" value="8" />
 <input type="hidden" id="weekID" value="8" />
 <input type="hidden" id="monthID" value="8" />
</form>


</div>

<script>
function loadMoreDays(){
	
	// Add 'Loading' text
	$("#loadMore").remove();
	$("#dayByDay").append("<tr id='dayLoading'><td colspan='3' class='centered'>Loading. Please wait...</td></tr>");

	// Take dayID, send it to Ajax
	var dayJSID = parseInt($("#dayID").val());	
    $.ajax({
      type:"post",
      url:"getdays.php?day="+dayJSID,
      datatype:"text",
      success:function(data)
      {
			$("#dayLoading").remove();
	       	$('#dayByDay tbody').append(data);
      }
    });
	
	$("#dayID").val(dayJSID + 8);
    
};
function loadMoreWeeks(){
	
	// Add 'Loading' text
	$("#loadMore2").remove();
	$("#weekByWeek").append("<tr id='weekLoading'><td colspan='3' class='centered'>Loading. Please wait...</td></tr>");

	// Take dayID, send it to Ajax
	var weekJSID = parseInt($("#weekID").val());	
    $.ajax({
      type:"post",
      url:"getweeks.php?day="+weekJSID,
      datatype:"text",
      success:function(data)
      {
			$("#weekLoading").remove();
	       	$('#weekByWeek tbody').append(data);
      }
    });
	
	$("#weekID").val(weekJSID + 8);
    
};
function loadMoreMonths(){
	
	// Add 'Loading' text
	$("#loadMore3").remove();
	$("#monthByMonth").append("<tr id='monthLoading'><td colspan='3' class='centered'>Loading. Please wait...</td></tr>");

	// Take dayID, send it to Ajax
	var monthJSID = parseInt($("#monthID").val());	
    $.ajax({
      type:"post",
      url:"getmonths.php?day="+monthJSID,
      datatype:"text",
      success:function(data)
      {
			$("#monthLoading").remove();
	       	$('#monthByMonth tbody').append(data);
      }
    });
	
	$("#monthID").val(monthJSID + 8);
    
};

 function loadExcel(){
 			$("#load").show();
 			var dayJSID = parseInt($("#dayID").val());	
 			var weekJSID = parseInt($("#weekID").val());
 	        var monthJSID = parseInt($("#monthID").val());	 
       		window.location.href = 'dispensary-history-report.php?day_id='+dayJSID+'&week_id='+weekJSID+'&month_id='+monthJSID;
       		    setTimeout(function () {
			        $("#load").hide();
			    }, 5000);   
       }
</script>

<?php displayFooter(); ?>
