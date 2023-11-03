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

	pageStart("CCS | Member signups", NULL, $deleteDonationScript, "historymembers", "product admin", "MEMBER SIGNUPS", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
?>

<center><img src="images/excel-new.png" style="cursor: pointer;" onclick="loadExcel();" value="Export to Excel" />
<br />

<?php

	// DAY BY DAY FIRST
	$day_row = <<<EOD
<div class='historybox'>
 <span class='winnerboxheader'>{$lang['dispensary-daytoday']}</span>
<table class="dayByDay historytable" id="dayByDay" style='vertical-align: top;'>
<tbody>
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
		$selectSales = "SELECT COUNT(memberno) from users WHERE DATE(registeredSince) = $dateOperator";
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
			$salesToday = $row['COUNT(memberno)'];
			
			
		
		$day_row .= <<<EOD
 <tr>
  <td class="first">$timestamp:</td>
  <td><a href='members.php?period=day&limit=$a' class='yellow'>$salesToday</a></td>
 </tr>
EOD;

	}
	
		
	$day_row .= <<<EOD
 <tr id="loadMore">
  <td class="centered" colspan="2"><a href="#" onclick="event.preventDefault(); loadMoreDays()" class='yellow' style='font-size: 12px;'>[{$lang['load-more']}]</a></td>
 </tr>
 </tbody>
</table>
</div>
EOD;

echo $day_row;

	// THEN WEEK TO WEEK
	$week_row = <<<EOD
<div class='historybox'>
 <span class='winnerboxheader'>{$lang['dispensary-weektoweek']}</span>
<table class="dayByDay historytable" id="weekByWeek" style='vertical-align: top;'>
<tbody>
EOD;
	
	for ($a = 0; $a < 8; $a++) {
		
		if ($a == 0) {
			$dateOperator = "WEEK(registeredSince,1) = WEEK(NOW(),1) AND YEAR(registeredSince) = YEAR(NOW())";
			$timestamp = $lang['dispensary-thisweek'];
		} else if ($a == 1) {
			$dateOperator = "WEEK(registeredSince,1) = WEEK(DATE_ADD((NOW()), INTERVAL -$a WEEK),1) AND YEAR(registeredSince) = YEAR(DATE_ADD((NOW()), INTERVAL -$a WEEK))";
			$timestamp = $lang['dispensary-lastweek'];
		} else {
			$dateOperator = "WEEK(registeredSince,1) = WEEK(DATE_ADD((NOW()), INTERVAL -$a WEEK),1) AND YEAR(registeredSince) = YEAR(DATE_ADD((NOW()), INTERVAL -$a WEEK))";
			$timestamp = $lang['dispensary-weeksago-1'] . $a . $lang['dispensary-weeksago-2'];
		}
	
		// Look up todays sales
		$selectSales = "SELECT COUNT(memberno) from users WHERE $dateOperator";
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
			$salesToday = $row['COUNT(memberno)'];
			
		
		$week_row .= <<<EOD
 <tr>
  <td class="first">$timestamp:</td>
  <td><a href='members.php?period=week&limit=$a' class='yellow'>$salesToday</a></td>
 </tr>
EOD;

	}
	
		
	$week_row .= <<<EOD
 <tr id="loadMore2">
  <td class="centered" colspan="4"><a href="#" onclick="event.preventDefault(); loadMoreWeeks()" class='yellow' style='font-size: 12px;'>[{$lang['load-more']}]</a></td>
 </tr>
 </tbody>
</table>
</div>
EOD;

echo $week_row;

	// THEN MONTH TO MONTH
	$month_row = <<<EOD
<div class='historybox'>
 <span class='winnerboxheader'>{$lang['dispensary-monthtomonth']}</span>
<table class="dayByDay historytable" id="monthByMonth" style='vertical-align: top;'>
<tbody>
EOD;
	
	for ($a = 0; $a < 8; $a++) {
		
		if ($a == 0) {
			$dateOperator = "MONTH(registeredSince) = MONTH(NOW()) AND YEAR(registeredSince) = YEAR(NOW())";
			$timestamp = $lang['dispensary-thismonth'];
		} else {
			$dateOperator = "MONTH(registeredSince) = MONTH(DATE_ADD((NOW()), INTERVAL -$a MONTH)) AND YEAR(registeredSince) = YEAR(DATE_ADD((NOW()), INTERVAL -$a MONTH))";
			$timestamp = date("m-Y", strtotime("-$a months", strtotime("first day of this month") ));
		}
	
		// Look up todays sales
		$selectSales = "SELECT COUNT(memberno) from users WHERE $dateOperator";
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
			$salesToday = $row['COUNT(memberno)'];
			
		
		$month_row .= <<<EOD
 <tr>
  <td class="first">$timestamp:</td>
  <td><a href='members.php?period=month&limit=$a' class='yellow'>$salesToday</a></td>
 </tr>
EOD;

	}
	
		
	$month_row .= <<<EOD
 <tr id="loadMore3">
  <td class="centered" colspan="4"><a href="#" onclick="event.preventDefault(); loadMoreMonths()" class='yellow' style='font-size: 12px;'>[{$lang['load-more']}]</a></td>
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
	$("#dayByDay").append("<tr id='dayLoading'><td colspan='3' class='centered'>Loading. Please wait...</td></tr>");

	// Take dayID, send it to Ajax
	var dayJSID = parseInt($("#dayID").val());	
    $.ajax({
      type:"post",
      url:"getdaysM.php?day="+dayJSID,
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
      url:"getweeksM.php?day="+weekJSID,
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
      url:"getmonthsM.php?day="+monthJSID,
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
       		window.location.href = 'member-signups-report.php?day_id='+dayJSID+'&week_id='+weekJSID+'&month_id='+monthJSID;
       		    setTimeout(function () {
			        $("#load").hide();
			    }, 5000);   
       }

</script>

<?php displayFooter(); ?>
