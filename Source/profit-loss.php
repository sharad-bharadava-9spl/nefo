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
            ctx['table' + k] = exceltable.innerHTML;
        }

        //document.getElementById("dlink").href = uri + base64(format(template, ctx));
        //document.getElementById("dlink").download = filename;
        //document.getElementById("dlink").click();

        window.location.href = uri + base64(format(allOfIt, ctx));

    }
})();
EOD;

	pageStart($lang['profit-and-loss'], NULL, $deleteDonationScript, "pdispensary", "product admin", $lang['profit-and-loss'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
?>

<center><img src="images/excel-new.png" style="cursor: pointer;" onclick="tablesToExcel(['dayByDay', 'weekByWeek', 'monthByMonth'], ['dayByDay', 'weekByWeek', 'monthByMonth'], 'myfile.xls')" value="Export to Excel" />
<br />
<?php
	// DAY BY DAY FIRST
	$day_row = <<<EOD

<div class='historybox'>
 <span class='winnerboxheader'>{$lang['dispensary-daytoday']}</span><br /><br />
<table class="dayByDay historytable" id="dayByDay" style='vertical-align: top;'>
<tbody>
 <tr>
  <td></td>
  <td class='centered' style='text-transform: uppercase; font-weight: 600;'>{$lang['title-donations']}</td>
  <td class='centered' style='text-transform: uppercase; font-weight: 600;'>{$lang['fees']}</td>
  <td class='centered' style='text-transform: uppercase; font-weight: 600;'>Ingresos</td>
  <td class='centered' style='text-transform: uppercase; font-weight: 600;'>Gastos</td>
  <td class='centered' style='text-transform: uppercase; font-weight: 600;'>+</td>
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
	
		// Look up todays donations
		$selectDonations = "SELECT SUM(amount) FROM donations WHERE donatedTo <> 3 AND DATE(donationTime) = $dateOperator";
		try
		{
			$result = $pdo3->prepare("$selectDonations");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$donationsToday = $row['SUM(amount)'];
			
		// And now membership fees
		$selectFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE DATE(paymentdate) = $dateOperator";
		try
		{
			$result = $pdo3->prepare("$selectFees");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$feesToday = $row['SUM(amountPaid)'];
			
		// Look up direct dispensed today
		$selectSales = "SELECT SUM(amount) from sales WHERE DATE(saletime) = $dateOperator AND direct < 3";
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
			$salesTodayCash = $row['SUM(amount)'];
			
		// Look up direct bar sales today
		$selectSales = "SELECT SUM(amount) from b_sales WHERE DATE(saletime) = $dateOperator AND direct < 3";
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
			$salesTodayBarCash = $row['SUM(amount)'];
		
		// And finally expenses
		$selectFees = "SELECT SUM(amount) FROM expenses WHERE DATE(registertime) = $dateOperator";
		try
		{
			$result = $pdo3->prepare("$selectFees");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$expensesToday = $row['SUM(amount)'];
			
			
		$totalToday = $donationsToday + $feesToday + $salesTodayCash + $salesTodayBarCash;
		
		$plusToday = $totalToday - $expensesToday;
			
		$day_row .= <<<EOD
 <tr>
  <td class="first">$timestamp:</td>
  <td>$donationsToday <span class="smallerfont">&euro;</span></td>
  <td>$feesToday <span class="smallerfont">&euro;</span></td>
  <td>$totalToday <span class="smallerfont">&euro;</span></td>
  <td>$expensesToday <span class="smallerfont">&euro;</span></td>
  <td>$plusToday <span class="smallerfont">&euro;</span></td>
 </tr>
EOD;

	}
	
		
	$day_row .= <<<EOD
 <tr id="loadMore">
  <td class="centered" colspan="6"><a href="#" onclick="event.preventDefault(); loadMoreDays()" class='yellow' style='font-size: 12px;'>[{$lang['load-more']}]</a></td>
 </tr>
 </tbody>
</table>
</div>
EOD;

echo $day_row;

	// THEN WEEK TO WEEK
	$week_row = <<<EOD
<div class='historybox'>
 <span class='winnerboxheader'>{$lang['dispensary-weektoweek']}</span><br /><br />
<table class="dayByDay historytable" id="weekByWeek" style='vertical-align: top;'>
<tbody>
 <tr>
  <td></td>
  <td class='centered' style='text-transform: uppercase; font-weight: 600;'>{$lang['title-donations']}</td>
  <td class='centered' style='text-transform: uppercase; font-weight: 600;'>{$lang['fees']}</td>
  <td class='centered' style='text-transform: uppercase; font-weight: 600;'>Ingresos</td>
  <td class='centered' style='text-transform: uppercase; font-weight: 600;'>Gastos</td>
  <td class='centered' style='text-transform: uppercase; font-weight: 600;'>+</td>
 </tr>
EOD;
	
	for ($a = 0; $a < 8; $a++) {
		
		if ($a == 0) {
			$dateOperator = "WEEK(saletime,1) = WEEK(NOW(),1) AND YEAR(saletime) = YEAR(NOW())";
			$dateOperator2 = "WEEK(paymentdate,1) = WEEK(NOW(),1) AND YEAR(paymentdate) = YEAR(NOW())";
			$dateOperator3 = "WEEK(donationTime,1) = WEEK(NOW(),1) AND YEAR(donationTime) = YEAR(NOW())";
			$dateOperator4 = "WEEK(registertime,1) = WEEK(NOW(),1) AND YEAR(registertime) = YEAR(NOW())";
			$timestamp = $lang['dispensary-thisweek'];
		} else if ($a == 1) {
			$dateOperator = "WEEK(saletime,1) = WEEK(DATE_ADD((NOW()), INTERVAL -$a WEEK),1) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -$a WEEK))";
			$dateOperator2 = "WEEK(paymentdate,1) = WEEK(DATE_ADD((NOW()), INTERVAL -$a WEEK),1) AND YEAR(paymentdate) = YEAR(DATE_ADD((NOW()), INTERVAL -$a WEEK))";
			$dateOperator3 = "WEEK(donationTime,1) = WEEK(DATE_ADD((NOW()), INTERVAL -$a WEEK),1) AND YEAR(donationTime) = YEAR(DATE_ADD((NOW()), INTERVAL -$a WEEK))";
			$dateOperator4 = "WEEK(registertime,1) = WEEK(DATE_ADD((NOW()), INTERVAL -$a WEEK),1) AND YEAR(registertime) = YEAR(DATE_ADD((NOW()), INTERVAL -$a WEEK))";
			$timestamp = $lang['dispensary-lastweek'];
		} else {
			$dateOperator = "WEEK(saletime,1) = WEEK(DATE_ADD((NOW()), INTERVAL -$a WEEK),1) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -$a WEEK))";
			$dateOperator2 = "WEEK(paymentdate,1) = WEEK(DATE_ADD((NOW()), INTERVAL -$a WEEK),1) AND YEAR(paymentdate) = YEAR(DATE_ADD((NOW()), INTERVAL -$a WEEK))";
			$dateOperator3 = "WEEK(donationTime,1) = WEEK(DATE_ADD((NOW()), INTERVAL -$a WEEK),1) AND YEAR(donationTime) = YEAR(DATE_ADD((NOW()), INTERVAL -$a WEEK))";
			$dateOperator4 = "WEEK(registertime,1) = WEEK(DATE_ADD((NOW()), INTERVAL -$a WEEK),1) AND YEAR(registertime) = YEAR(DATE_ADD((NOW()), INTERVAL -$a WEEK))";
			$timestamp = $lang['dispensary-weeksago-1'] . $a . $lang['dispensary-weeksago-2'];
		}
	
	
		// Look up todays donations
		$selectDonations = "SELECT SUM(amount) FROM donations WHERE donatedTo <> 3 AND $dateOperator3";
		try
		{
			$result = $pdo3->prepare("$selectDonations");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$donationsToday = $row['SUM(amount)'];
			
		// And now membership fees
		$selectFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE $dateOperator2";
		try
		{
			$result = $pdo3->prepare("$selectFees");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$feesToday = $row['SUM(amountPaid)'];
			
		// Look up direct dispensed today
		$selectSales = "SELECT SUM(amount) from sales WHERE $dateOperator AND direct < 3";
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
			$salesTodayCash = $row['SUM(amount)'];
			
		// Look up direct bar sales today
		$selectSales = "SELECT SUM(amount) from b_sales WHERE $dateOperator AND direct < 3";
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
			$salesTodayBarCash = $row['SUM(amount)'];
		
		// And finally expenses
		$selectFees = "SELECT SUM(amount) FROM expenses WHERE $dateOperator4";
		try
		{
			$result = $pdo3->prepare("$selectFees");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$expensesToday = $row['SUM(amount)'];
			
			
		$totalToday = $donationsToday + $feesToday + $salesTodayCash + $salesTodayBarCash;
		
		$plusToday = $totalToday - $expensesToday;
			
		
		$week_row .= <<<EOD
 <tr>
  <td class="first">$timestamp:</td>
  <td>$donationsToday <span class="smallerfont">&euro;</span></td>
  <td>$feesToday <span class="smallerfont">&euro;</span></td>
  <td>$totalToday <span class="smallerfont">&euro;</span></td>
  <td>$expensesToday <span class="smallerfont">&euro;</span></td>
  <td>$plusToday <span class="smallerfont">&euro;</span></td>
 </tr>
EOD;
	}
	
		
	$week_row .= <<<EOD
 <tr id="loadMore2">
  <td class="centered" colspan="6"><a href="#" onclick="event.preventDefault(); loadMoreWeeks()" class='yellow' style='font-size: 12px;'>[{$lang['load-more']}]</a></td>
 </tr>
 </tbody>
</table>
</div>
EOD;

echo $week_row;

	// THEN MONTH TO MONTH
	$month_row = <<<EOD
<div class='historybox'>
 <span class='winnerboxheader'>{$lang['dispensary-monthtomonth']}</span><br /><br />
<table class="dayByDay historytable" id="monthByMonth" style='vertical-align: top;'>
<tbody>
 <tr>
  <td></td>
  <td class='centered' style='text-transform: uppercase; font-weight: 600;'>{$lang['title-donations']}</td>
  <td class='centered' style='text-transform: uppercase; font-weight: 600;'>{$lang['fees']}</td>
  <td class='centered' style='text-transform: uppercase; font-weight: 600;'>Ingresos</td>
  <td class='centered' style='text-transform: uppercase; font-weight: 600;'>Gastos</td>
  <td class='centered' style='text-transform: uppercase; font-weight: 600;'>+</td>
 </tr>
EOD;
	
	for ($a = 0; $a < 8; $a++) {
		
		if ($a == 0) {
			$dateOperator = "MONTH(saletime) = MONTH(NOW()) AND YEAR(saletime) = YEAR(NOW())";
			$dateOperator2 = "MONTH(paymentdate) = MONTH(NOW()) AND YEAR(paymentdate) = YEAR(NOW())";
			$dateOperator3 = "MONTH(donationTime) = MONTH(NOW()) AND YEAR(donationTime) = YEAR(NOW())";
			$dateOperator4 = "MONTH(registertime) = MONTH(NOW()) AND YEAR(registertime) = YEAR(NOW())";
			$timestamp = $lang['dispensary-thismonth'];
		} else {
			$dateOperator = "MONTH(saletime) = MONTH(DATE_ADD((NOW()), INTERVAL -$a MONTH)) AND YEAR(saletime) = YEAR(DATE_ADD((NOW()), INTERVAL -$a MONTH))";
			$dateOperator2 = "MONTH(paymentdate) = MONTH(DATE_ADD((NOW()), INTERVAL -$a MONTH)) AND YEAR(paymentdate) = YEAR(DATE_ADD((NOW()), INTERVAL -$a MONTH))";
			$dateOperator3 = "MONTH(donationTime) = MONTH(DATE_ADD((NOW()), INTERVAL -$a MONTH)) AND YEAR(donationTime) = YEAR(DATE_ADD((NOW()), INTERVAL -$a MONTH))";
			$dateOperator4 = "MONTH(registertime) = MONTH(DATE_ADD((NOW()), INTERVAL -$a MONTH)) AND YEAR(registertime) = YEAR(DATE_ADD((NOW()), INTERVAL -$a MONTH))";
			$timestamp = date("m-Y", strtotime("-$a months", strtotime("first day of this month") ));
		}
	
		// Look up todays donations
		$selectDonations = "SELECT SUM(amount) FROM donations WHERE donatedTo <> 3 AND $dateOperator3";
		try
		{
			$result = $pdo3->prepare("$selectDonations");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$donationsToday = $row['SUM(amount)'];
			
		// And now membership fees
		$selectFees = "SELECT SUM(amountPaid) FROM memberpayments WHERE $dateOperator2";
		try
		{
			$result = $pdo3->prepare("$selectFees");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$feesToday = $row['SUM(amountPaid)'];
			
		// Look up direct dispensed today
		$selectSales = "SELECT SUM(amount) from sales WHERE $dateOperator AND direct < 3";
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
			$salesTodayCash = $row['SUM(amount)'];
			
		// Look up direct bar sales today
		$selectSales = "SELECT SUM(amount) from b_sales WHERE $dateOperator AND direct < 3";
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
			$salesTodayBarCash = $row['SUM(amount)'];
		
		// And finally expenses
		$selectFees = "SELECT SUM(amount) FROM expenses WHERE $dateOperator4";
		try
		{
			$result = $pdo3->prepare("$selectFees");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$expensesToday = $row['SUM(amount)'];
			
			
		$totalToday = $donationsToday + $feesToday + $salesTodayCash + $salesTodayBarCash;
		
		$plusToday = $totalToday - $expensesToday;
			
		
		$month_row .= <<<EOD
 <tr>
  <td class="first">$timestamp:</td>
  <td>$donationsToday <span class="smallerfont">&euro;</span></td>
  <td>$feesToday <span class="smallerfont">&euro;</span></td>
  <td>$totalToday <span class="smallerfont">&euro;</span></td>
  <td>$expensesToday <span class="smallerfont">&euro;</span></td>
  <td>$plusToday <span class="smallerfont">&euro;</span></td>
 </tr>
EOD;

	}
	
		
	$month_row .= <<<EOD
 <tr id="loadMore3">
  <td class="centered" colspan="6"><a href="#" onclick="event.preventDefault(); loadMoreMonths()" class='yellow' style='font-size: 12px;'>[{$lang['load-more']}]</a></td>
 </tr>
 </tbody>
</table>
</div>
EOD;

echo $month_row;

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
	$("#dayByDay").append("<tr id='dayLoading'><td colspan='6' class='centered'>Loading. Please wait...</td></tr>");

	// Take dayID, send it to Ajax
	var dayJSID = parseInt($("#dayID").val());	
    $.ajax({
      type:"post",
      url:"getdaysRL.php?day="+dayJSID,
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
	$("#weekByWeek").append("<tr id='weekLoading'><td colspan='6' class='centered'>Loading. Please wait...</td></tr>");

	// Take dayID, send it to Ajax
	var weekJSID = parseInt($("#weekID").val());	
    $.ajax({
      type:"post",
      url:"getweeksRL.php?day="+weekJSID,
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
	$("#monthByMonth").append("<tr id='monthLoading'><td colspan='6' class='centered'>Loading. Please wait...</td></tr>");

	// Take dayID, send it to Ajax
	var monthJSID = parseInt($("#monthID").val());	
    $.ajax({
      type:"post",
      url:"getmonthsRL.php?day="+monthJSID,
      datatype:"text",
      success:function(data)
      {
			$("#monthLoading").remove();
	       	$('#monthByMonth tbody').append(data);
      }
    });
	
	$("#monthID").val(monthJSID + 8);
    
};


</script>

<?php displayFooter(); ?>
