<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$sortScript = <<<EOD
	    $(document).ready(function() {
		    
			$.tablesorter.addParser({
			  id: 'dates',
			  is: function(s) { return false },
			  format: function(s) {
			    var dateArray = s.split('-');
			    return dateArray[2].substring(0,4) + dateArray[1] + dateArray[0];
			  },
			  type: 'numeric'
			});
			
			
			$('#mainTable').tablesorter({
				usNumberFormat: true,
			}); 
			
		});
EOD;
	
	pageStart("Ticket summary", NULL, $sortScript, "pmembership", NULL, "Ticket summary", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	$query = "SELECT MONTH(created_at), YEAR(created_at), COUNT(id) FROM `feedback` WHERE reason <> 'Suggestion' GROUP BY MONTH(created_at), YEAR(created_at) ORDER BY YEAR(created_at) DESC, MONTH(created_at) DESC";
	try
	{
		$results = $pdo3->prepare("$query");
		$results->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	while ($row = $results->fetch()) {
		$month = sprintf('%02d', $row['MONTH(created_at)']);
		$year = $row['YEAR(created_at)'];
		$period = "$month-$year";
		$tickets = $row['COUNT(id)'];
		$days = date("t", strtotime("$year-$month-1"));
		
		if ($period == date("m-Y")) {
			$days = date("d");
		}
		$dailyTickets = number_format($tickets / $days,1);
		
		$query = "SELECT COUNT(customer) FROM invoices WHERE MONTH(invDate) = $month AND YEAR(invDate) = $year AND brand = 'SW'";
		try
		{
			$result = $pdo->prepare("$query");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$clients = $row['COUNT(customer)'];	
			
		$dailyAvg = number_format($dailyTickets / $clients * 100,1);
		
		$query = "SELECT AVG(rating) FROM feedback WHERE status = 3 AND id > 395 AND rating > 0 AND MONTH(created_at) = $month AND YEAR(created_at) = $year AND reason <> 'Suggestion'";
		try
		{
			$result = $pdo3->prepare("$query");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$rating = number_format($row['AVG(rating)'],1);
			
		if ($rating == '0.0') {
			$rating = '';
		}
			
		if ($period != '05-2020') {
		
			$tableContent .= "
		  <tr>
		   <td class='right'>$period</td>
		   <td class='centered'>$tickets</td>
		   <td class='centered'>$dailyTickets</td>
		   <td class='centered'>$clients</td>
		   <td class='centered'>$dailyAvg</td>
		   <td class='centered'>$rating</td>
		  </tr>
			";
			
		}
		
	}
	
?>
	<div id = "container1" style = "width: 550px; height: 400px; margin: 0 auto"></div><br>
	<table class="default" id="mainTable">
	 <thead>
	  <tr>
	   <th>Period</th>
	   <th># tickets</th>
	   <th>Per day</th>
	   <th># clients</th>
	   <th>% of clients opening a daily ticket</th>
	   <th>Rating</th>
	  </tr>
	 </thead>
	 <tbody>
<?php echo $tableContent; ?>
	 </tbody>
	</table>


<?php displayFooter(); ?>

<script src = "scripts/highchart/highcharts.js"></script> 
<script src = "scripts/highchart/data.js"></script>
<script type="text/javascript">
function isAnchor(str){
	return /^\<a.*\>.*\<\/a\>/i.test(str);
}

function stripHtml(html)
{
   let tmp = document.createElement("DIV");
   tmp.innerHTML = html;
   return tmp.textContent || tmp.innerText || "";
}
function loadChart(table_id, container, chart_title){
		var data = {
       table: table_id,
       //startRow: 1,
    };
    var chart = {
       type: 'line',
    };
    var title = {
       text:  chart_title  
    };      
    var yAxis = {
       allowDecimals: true,
       title: {
          text: ''
       },
        labels: {
	      formatter: function() {
	        if (isAnchor(this.value)) {
	          return null;
	        } else {
	          return stripHtml(this.value)
	        }
	      },
	    },
    };
    var xAxis = {
    	reversed: true,
    	//showFirstLabel: false,
		labels: {
	      formatter: function() {
	        if (isAnchor(this.value)) {
	          return null;
	        } else {
	          return stripHtml(this.value)
	        }
	      },
	    },
	  };
    var tooltip = {
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
}
 $(document).ready(function() {
 	var title = "Ticket summary";
 	loadChart('mainTable', 'container1', title);
 	var series1 = $('#container1').highcharts().series[0];
 	var series2 = $('#container1').highcharts().series[1];
 	var series3 = $('#container1').highcharts().series[2];
 	series1.update({visible: false});
 	series2.update({visible: false});
 	series3.update({visible: false});
 });
</script>