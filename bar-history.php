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

	pageStart($lang['bar-history'], NULL, $deleteDonationScript, "history", "product admin", $lang['bar-history'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
?>
<div id="load"></div>
<center><img src="images/excel-new.png" style="cursor: pointer;" onclick="loadExcel();" value="Export to Excel" />
<br />
<?php

	$day_row = <<<EOD
<div class='historybox'>
 <span class='winnerboxheader'>{$lang['dispensary-daytoday']}</span><br /><br />
  <div class='boxcontent'>
 <div id = "container1" style = "height: 400px; margin: 0 auto"></div><br />
<table class="dayByDay historytable" id="dayByDay" style='vertical-align: top;'>
<tbody>
 <tr>
  <td></td>
  <td class='centered' style='text-transform: uppercase; font-weight: 600;'>{$lang['units']} (u.)</td>
  <td class='centered' style='text-transform: uppercase; font-weight: 600;'>{$lang['amount']} ({$_SESSION['currencyoperator']})</td>
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
		$selectSales = "SELECT SUM(amount), SUM(unitsTot) from b_sales WHERE DATE(saletime) = $dateOperator";

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
			$b_salesToday = number_format($row['SUM(amount)'],2, '.', '');
			$unitsTotToday = number_format($row['SUM(unitsTot)'],2,'.','');
			
		
		$day_row .= <<<EOD
 <tr>
  <td class="first">$timestamp:</td>
  <td>$unitsTotToday </td>
  <td>$b_salesToday </td>
 </tr>
EOD;

	}
	
	$day_row .= <<<EOD
 <tr id="loadMore">
  <td class="centered" colspan="4"><a href="#" onclick="event.preventDefault(); loadMoreDays()" class='yellow' style='font-size: 12px;'>[{$lang['load-more']}]</a></td>
 </tr>
 </tbody>
</table>
</div>
</div>
EOD;

echo $day_row;
	

	// THEN WEEK TO WEEK
	$week_row = <<<EOD
<div class='historybox'>
 <span class='winnerboxheader'>{$lang['dispensary-weektoweek']}</span><br /><br />
 <div class='boxcontent'>
  <div id = 'container2' style = 'height: 400px; margin: 0 auto'></div><br />	
<table class="dayByDay historytable" id="weekByWeek" style='vertical-align: top;'>
<tbody>
 <tr>
  <td></td>
  <td class='centered' style='text-transform: uppercase; font-weight: 600;'>{$lang['units']} (u.)</td>
  <td class='centered' style='text-transform: uppercase; font-weight: 600;'>{$lang['amount']} ({$_SESSION['currencyoperator']})</td>
 </tr>
EOD;
	
	for ($a = 0; $a < 8; $a++) {
		
		if ($a == 0) {
			$dateOperator = "YEARWEEK(saletime,1) = YEARWEEK(NOW(),1)";
			$timestamp = $lang['dispensary-thisweek'];
		} else if ($a == 1) {
			$dateOperator = "YEARWEEK(saletime,1) = YEARWEEK(DATE_ADD((NOW()), INTERVAL -$a WEEK),1)";
			$timestamp = $lang['dispensary-lastweek'];
		} else {
			$dateOperator = "YEARWEEK(saletime,1) = YEARWEEK(DATE_ADD((NOW()), INTERVAL -$a WEEK),1)";
			$timestamp = $lang['dispensary-weeksago-1'] . $a . $lang['dispensary-weeksago-2'];
		}
	
		// Look up todays sales
		$selectSales = "SELECT SUM(amount), SUM(unitsTot) from b_sales WHERE $dateOperator";

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
			$b_salesToday = number_format($row['SUM(amount)'],2,'.','');
			$unitsTotToday = number_format($row['SUM(unitsTot)'],2,'.','');
			
		$week_row .= <<<EOD
 <tr>
  <td class="first">$timestamp:</td>
  <td>$unitsTotToday </td>
  <td>$b_salesToday </td>
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
</div>
EOD;

echo $week_row;


	// THEN MONTH TO MONTH
	$month_row = <<<EOD
<div class='historybox'>
 <span class='winnerboxheader'>{$lang['dispensary-monthtomonth']}</span><br /><br />
   <div class='boxcontent'>
   <div id = 'container3' style = 'height: 400px; margin: 0 auto'></div><br />	
<table class="dayByDay historytable" id="monthByMonth" style='vertical-align: top;'>
<tbody>
 <tr>
  <td></td>
  <td class='centered' style='text-transform: uppercase; font-weight: 600;'>{$lang['units']} (u.)</td>
  <td class='centered' style='text-transform: uppercase; font-weight: 600;'>{$lang['amount']} ({$_SESSION['currencyoperator']})</td>
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
		$selectSales = "SELECT SUM(amount), SUM(unitsTot) from b_sales WHERE $dateOperator";

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
			$b_salesToday = number_format($row['SUM(amount)'],2, '.', '');
			$unitsTotToday = number_format($row['SUM(unitsTot)'],2, '.', '');
			
		
		$month_row .= <<<EOD
 <tr>
  <td class="first">$timestamp:</td>
  <td>$unitsTotToday </td>
  <td>$b_salesToday </td>
 </tr>
EOD;

	}
	
		
	$month_row .= <<<EOD
 <tr id="loadMore3">
  <td class="centered" colspan="3"><a href="#" onclick="event.preventDefault(); loadMoreMonths()" class='yellow' style='font-size: 12px;'>[{$lang['load-more']}]</a></td>
 </tr>
 </tbody>
</table>
</div>
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
<script src = "scripts/highchart/highcharts.js"></script> 
<script src = "scripts/highchart/data.js"></script>
<script>

 function isAnchor(str){
	return /^\<a.*\>.*\<\/a\>/i.test(str);
}


function loadChart(table_id, container){
		var data = {
       table: table_id,
       //startRow: 1,
    };
    var chart = {
       type: 'line',
       backgroundColor: '#fbfbfb',
       borderColor: '#e2e7df',
       borderWidth: 1,
    };
    var title = {
       text:  ""  
    };      
    var yAxis = {
       allowDecimals: true,
       title: {
          text: ''
       }
    };
    var xAxis = {
    	reversed: true,
    	//showFirstLabel: false,
		labels: {
		  step: 0,
	      formatter: function() {
	        if (isAnchor(this.value)) {
	          return null;
	        } else {
	          return this.value
	        }
	      },
	    },
	  };
    var tooltip = {
       backgroundColor: '#FFFFFF',
       formatter: function () {
          return '<b>' + this.series.name + '</b><br/>' +
             this.point.y + ' <br/>' + this.point.name.toLowerCase();
       }
    };
    var credits = {
       enabled: false
    };  
    var json = {};   
    json.chart = chart; 
    json.title = title; 
    json.data = data;
    json.yAxis = yAxis;
    json.xAxis = xAxis;
    json.credits = credits;  
    json.tooltip = tooltip;  
    $('#'+container).highcharts(json);
    var series1 = $('#'+container).highcharts().series[0];
    var series2 = $('#'+container).highcharts().series[1];
    series1.update({color: '#f3b149', visible: false});
    series2.update({color: '#86c469'});
}
 $(document).ready(function() {
 	loadChart('dayByDay', 'container1');
 	loadChart('weekByWeek', 'container2');
 	loadChart('monthByMonth', 'container3');
 });
function loadMoreDays(){
	
	// Add 'Loading' text
	$("#loadMore").remove();
	$("#dayByDay").append("<tr id='dayLoading'><td colspan='3' class='centered'>Loading. Please wait...</td></tr>");

	// Take dayID, send it to Ajax
	var dayJSID = parseInt($("#dayID").val());	
    $.ajax({
      type:"post",
      url:"getdaysB.php?day="+dayJSID,
      datatype:"text",
      success:function(data)
      {
			$("#dayLoading").remove();
	       	$('#dayByDay tbody').append(data);
	       	loadChart('dayByDay', 'container1');
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
      url:"getweeksB.php?day="+weekJSID,
      datatype:"text",
      success:function(data)
      {
			$("#weekLoading").remove();
	       	$('#weekByWeek tbody').append(data);
	       	loadChart('weekByWeek', 'container2');
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
      url:"getmonthsB.php?day="+monthJSID,
      datatype:"text",
      success:function(data)
      {
			$("#monthLoading").remove();
	       	$('#monthByMonth tbody').append(data);
	       	loadChart('monthByMonth', 'container3');
      }
    });
	
	$("#monthID").val(monthJSID + 8);
    
};
 function loadExcel(){
 			$("#load").show();
 			var dayJSID = parseInt($("#dayID").val());	
 			var weekJSID = parseInt($("#weekID").val());
 	        var monthJSID = parseInt($("#monthID").val());	 
       		window.location.href = 'bar-history-report.php?day_id='+dayJSID+'&week_id='+weekJSID+'&month_id='+monthJSID;
       		    setTimeout(function () {
			        $("#load").hide();
			    }, 5000);   
       }

</script>

<?php displayFooter(); ?>
