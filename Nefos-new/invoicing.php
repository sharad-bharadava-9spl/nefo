<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings();
	
	$sortscript = <<<EOD
	  
	    $(document).ready(function() {
			
			$('#mainTable').tablesorter({
				usNumberFormat: true,
				headers: {
					3: {
						sorter: "currency"
					},
					4: {
						sorter: "currency"
					},
					6: {
						sorter: "currency"
					},
					8: {
						sorter: "currency"
					}
				},
				sortList: [[0,1]]
			}); 
			
		});

EOD;
	
	if (isset($_GET['brand'])) {
		
		if ($_GET['brand'] == 'HW') {
			
			$brand = 'HW';
			
		} else if ($_GET['brand'] == 'COMMISSION') {
			
			$brand = 'SW - COMMISSION';
			
		} else if ($_GET['brand'] == 'SW') {
		
			$brand = 'SW';
			
		}
		
		
	} else {
		
		$brand = 'SW';
		
	}
		
		pageStart("$brand Invoicing", NULL, $sortscript, "ppurchases", "purchases admin", "$brand Invoicing", $_SESSION['successMessage'], $_SESSION['errorMessage']);

		$query = "SELECT YEAR(invdate), MONTH(invdate), COUNT(invno), COUNT(DISTINCT customer), SUM(amount) FROM invoices WHERE brand = '$brand' AND DATE(invdate) > '2019-12-31' GROUP BY YEAR(invdate), MONTH(invdate) ORDER BY YEAR(invdate) ASC, MONTH(invdate) ASC";
		
	$chart_json = [];
	echo "<div id = 'container1' style = 'width: 550px; height: 400px; margin: 0 auto'></div><br />
		<center>
				<a href='?brand=SW' class='cta1'>SW</a>
				<a href='?brand=HW' class='cta1'>HW</a>
				<a href='?brand=COMMISSION' class='cta1'>COMMISSION</a>
		</center>
<br />
<table class='default' id='mainTable'>
 <thead>
  <tr>
   <th>Period</th>
   <th>Clients invoiced</th>
   <th>Invoices</th>
   <th>Amount incl. VAT</th>
   <th>Evolution</th>
   <th>New clients</th>
   <th>Value</th>
   <th>Returning clients</th>
   <th>Value</th>
   <th>Lost clients</th>
   <th>Value</th>
  </tr>
 </thead>
 <tbody>";
 
	
	//$query = "SELECT YEAR(invdate), MONTH(invdate), COUNT(invno), COUNT(DISTINCT customer), SUM(amount) FROM invoices WHERE brand = 'SW' AND DATE(invdate) > '2019-12-31' AND (MONTH(invdate) = 09 OR MONTH(invdate) = 10 OR MONTH(invdate) = 11) AND YEAR(invdate) = 2020 GROUP BY YEAR(invdate), MONTH(invdate) ORDER BY YEAR(invdate) ASC, MONTH(invdate) ASC";
	//$query = "SELECT YEAR(invdate), MONTH(invdate), COUNT(invno), COUNT(DISTINCT customer), SUM(amount) FROM invoices WHERE brand = 'SW' AND DATE(invdate) > '2019-12-31' AND (MONTH(invdate) = 11) AND YEAR(invdate) = 2020 GROUP BY YEAR(invdate), MONTH(invdate) ORDER BY YEAR(invdate) ASC, MONTH(invdate) ASC";
	try
	{
		$results = $pdo->prepare("$query");
		$results->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user123: ' . $e->getMessage();
			echo $error;
			exit();
	}
	
	// Three arrays:
	// 1: All customers since the beginning (to identify NEW clients)
	// 2: All customers this month (for comparison)
	// 3: All customers previous month (to identify LOST clients)
	// All customers this month + history but NOT previous month (returning clients)
	// Merge this month + history arrays
	
	
	// print_r($arr1);

	$_SESSION['amount'] = '';
	
	$yearOperator = date('Y');
	$i=0;
	while ($row = $results->fetch()) {
		
		unset($arr1);
		unset($arr2);
		unset($arr3);
		unset($arr4);
		unset($arrSuper);
		unset($arrDiffs);
		$arrDiffList = '';
		$arrDiffList3 = '';
		$arrDiffList4 = '';
		$arrSuperList = '';
		
		$month = $row['MONTH(invdate)'];
		$year = $row['YEAR(invdate)'];
		$customers = $row['COUNT(DISTINCT customer)'];
		$invoices = $row['COUNT(invno)'];
		$amount = $row['SUM(amount)'];
		$amountDisp = number_format($row['SUM(amount)'],2);
		$chart_json[$i]['amount_tax'] = number_format($row['SUM(amount)'], 2,'.','');
		$dateOperator = "$year-$month-01";

		// Array 1:
		$arr1query = "SELECT DISTINCT customer FROM invoices WHERE brand = '$brand' AND DATE(invdate) > '2019-12-31' AND invdate < '$dateOperator'";
		try
		{
			$resultsArr1 = $pdo->prepare("$arr1query");
			$resultsArr1->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching userA: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($rowArr1 = $resultsArr1->fetch()) {
			
			$arr1[] = $rowArr1['customer'];
			
		}
        
		// Aray 2:
		$arr2query = "SELECT DISTINCT customer FROM invoices WHERE brand = '$brand' AND MONTH(invdate) = $month AND YEAR(invdate) = $year";
		try
		{
			$resultsArr2 = $pdo->prepare("$arr2query");
			$resultsArr2->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching userB: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($rowArr2 = $resultsArr2->fetch()) {
			
			$arr2[] = $rowArr2['customer'];
			
		}		
        
		$arrDiffs = array_diff($arr2, $arr1);
		
		foreach ($arrDiffs as $arrDiff)  {
			
            $arrDiffList .= "$arrDiff, ";
            
        }
        
        $arrDiffList = substr($arrDiffList, 0, -2);
        $arrDiffNo = count($arrDiffs);
        
        if ($arrDiffNo != 0) {
        
	        // Look up amount invoiced for this array
			$query = "SELECT SUM(amount) FROM invoices WHERE brand = '$brand' AND DATE(invdate) > '2019-12-31' AND MONTH(invdate) = $month AND YEAR(invdate) = $year AND customer IN ($arrDiffList)";
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
				$amountNew = number_format($row['SUM(amount)'],0) . " €";
				$chart_json[$i]['value'] =  number_format($row['SUM(amount)'],2,'.','');
		} else {
			
			$amountNew = "0 €";
			$chart_json[$i]['value'] =  number_format(0 ,2,'.','');
		}
		
		// Aray 3:
		$monthOperator = $month - 1;
		
		if ($monthOperator == 0) {

			$monthOperator = 12;
			$yearOperator = $year - 1;

		} else {
			
			$yearOperator = $year;
			
		}

		$arr3query = "SELECT DISTINCT customer FROM invoices WHERE brand = '$brand' AND MONTH(invdate) = $monthOperator AND YEAR(invdate) = $yearOperator";
		try
		{
			$resultsArr3 = $pdo->prepare("$arr3query");
			$resultsArr3->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching userB: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($rowArr3 = $resultsArr3->fetch()) {
			
			$arr3[] = $rowArr3['customer'];
			
		}
		
		$arrDiffs3 = array_diff($arr3, $arr2);
		
		foreach ($arrDiffs3 as $arrDiff3)  {
			
            $arrDiffList3 .= "$arrDiff3, ";
            
        }
        
        $arrDiffList3 = substr($arrDiffList3, 0, -2);
        $arrDiffNo3 = count($arrDiffs3);
        
        if ($arrDiffNo3 != 0) {
        
	        // Look up amount invoiced for this array
			$query = "SELECT SUM(amount) FROM invoices WHERE brand = '$brand' AND DATE(invdate) > '2019-12-31' AND MONTH(invdate) = $monthOperator AND YEAR(invdate) = $yearOperator AND customer IN ($arrDiffList3)";
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
				$amountNew3 = number_format($row['SUM(amount)'],0) . " €";
				$chart_json[$i]['value3'] = number_format($row['SUM(amount)'],2,'.','');
				
		} else {
			
			$amountNew3 = "0 €";
			$chart_json[$i]['value3'] = number_format(0,2,'.','');
			
		}

		// Get returning clients
		$arrDiffs4 = array_diff($arr2, $arr3);
		
        $arrSuper = array_intersect($arrDiffs4, $arr1);
        
		foreach ($arrSuper as $arrSuperItem)  {
			
            $arrSuperList .= "$arrSuperItem, ";
            
        }
        
       	$arrSuperList = substr($arrSuperList, 0, -2);
       	$arrDiffNo4 = count($arrSuper);
        
        if ($arrDiffNo4 != 0) {
        
	        // Look up amount invoiced for this array
			$query = "SELECT SUM(amount) FROM invoices WHERE brand = '$brand' AND DATE(invdate) > '2019-12-31' AND MONTH(invdate) = $month AND YEAR(invdate) = $year AND customer IN ($arrSuperList)";
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
				$amountNew4 = number_format($row['SUM(amount)'],0) . " €";
				$chart_json[$i]['value2'] = number_format($row['SUM(amount)'],2,'.','');
		} else {
			
			$amountNew4 = "0 €";
			$chart_json[$i]['value2'] = number_format(0,2,'.','');
			
		}


        
		// Calculate evolution
		if ($_SESSION['amount'] != '') {
			
			$difference = $amount - $_SESSION['amount'];
			$evolution = number_format($difference / $amount * 100,0);

			$chart_json[$i]['evolution'] = number_format($difference / $amount * 100 ,2,'.','');
			
			if ($difference < 0) {
				
				$evolution = "<span style='color: red;'>$evolution %</span>";
				
			} else {
				
				$evolution = "<span style='color: green;'>$evolution %</span>";
				
			}
			
		} else {
			$chart_json[$i]['evolution'] = number_format(0 ,2,'.','');
			$arrDiffNo = '';
			$amountNew = '';
			$arrDiffNo4 = '';
			$amountNew = '';
			$arrDiffNo3 = '';
			$amountNew = '';
			$amountNew4 = '';
			$amountNew3 = '';
			
		}
		
		$monthDisp = sprintf('%02d', $month);

		$chart_json[$i]['period'] = $year."-".$monthDisp;
		$chart_json[$i]['clients'] = number_format($customers ,2,'.','');
		$chart_json[$i]['invoices'] = number_format($invoices, 2,'.','');
		//$chart_json[$i]['amount_tax'] = number_format($amountDisp, 2,'.','');
		$chart_json[$i]['new_clients'] = number_format($arrDiffNo,2,'.','' );
		//$chart_json[$i]['value'] =  $amountNew;
		$chart_json[$i]['returning_clients'] = number_format($arrDiffNo4,2,'.',''  );
		//$chart_json[$i]['value2'] = $amountNew4;
		$chart_json[$i]['lost_clients'] = number_format($arrDiffNo3,2,'.',''  );
		//$chart_json[$i]['value3'] = $amountNew3;
		
		echo "
  <tr>
   <td>$year-$monthDisp</td>
   <td class='centered'>$customers</td>
   <td class='centered'>$invoices</td>
   <td class='right'>$amountDisp €</td>
   <td class='right'>$evolution</td>
   <td class='centered'>$arrDiffNo</td>
   <td class='right'>$amountNew</td>
   <td class='centered'>$arrDiffNo4</td>
   <td class='right'>$amountNew4</td>
   <td class='centered'>$arrDiffNo3</td>
   <td class='right'>$amountNew3</td>
  </tr>";
  
  	$_SESSION['amount'] = $amount;
  	
  	//exit();
  		$i++;
	}

		
	echo "</tbody></table></div></div>";

	
	
	displayFooter();
?>

<script src = "scripts/highchart/highcharts.js"></script> 
<script src = "scripts/highchart/data.js"></script>
<script type="text/javascript">
var jsonChart = <?php echo json_encode($chart_json); ?>;
//console.log(jsonChart);
var xdata = [], data1 = [], data2 = [], data3 = [], data4 = [], data5 = [], data6 = [], data7 = [], data8 = [], data9 = [], data10 = [];

for(var i in jsonChart){
   var periods = jsonChart[i].period;

   xdata.push(periods );

   data1.push(parseFloat(jsonChart[i].clients));
   data2.push(parseFloat(jsonChart[i].invoices));
   data3.push(parseFloat(jsonChart[i].amount_tax));
   data4.push(parseFloat(jsonChart[i].evolution));
   data5.push(parseFloat(jsonChart[i].new_clients));
   data6.push(parseFloat(jsonChart[i].value));
   data7.push(parseFloat(jsonChart[i].returning_clients));
   data8.push(parseFloat(jsonChart[i].value2));
   data9.push(parseFloat(jsonChart[i].lost_clients));
   data10.push(parseFloat(jsonChart[i].value3));
}
	
function isAnchor(str){
	return /^\<a.*\>.*\<\/a\>/i.test(str);
}

function stripHtml(html)
{
   let tmp = document.createElement("DIV");
   tmp.innerHTML = html;
   return tmp.textContent || tmp.innerText || "";
}
function loadChart(container, chart_title){
		/*var data = {
       table: table_id,
       endRow: $('#mainTable tr').length-2
    };*/
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
    	categories: xdata ,
    	//reversed: true,
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
    var credits = {
       enabled: false
    };
    var series = [{
	        name: 'Clients invoiced',     
	        data: data1
	    }, {
	        name: 'Invoices',
	        data: data2
	    }, {
	        name: 'Amount incl. VAT',
	        data: data3
	    },
	    {
	        name: 'Evolution',
	        data: data4
	    },{
	        name: 'New clients',
	        data: data5
	    },{
	        name: 'Value',
	        data: data6
	    },{
	        name: 'Returning clients',
	        data: data7
	    },{
	        name: 'Value',
	        data: data8
	    },{
	        name: 'Lost clients',
	        data: data9
	    },{
	        name: 'Value',
	        data: data10
	    }
	    ];  
    var json = {};   
    json.chart = chart; 
    json.title = title; 
    json.yAxis = yAxis;
    json.xAxis = xAxis;
    json.series = series;
    json.credits = credits;  
    $('#'+container).highcharts(json);
     Highcharts.setOptions({
	    lang: {
	      thousandsSep: ','
	    }
	  });
}
 $(document).ready(function() {
 	var title = "<?php echo $brand; ?>";
 	loadChart('container1', title);
 	var series1 = $('#container1').highcharts().series[0];
 	var series2 = $('#container1').highcharts().series[1];
 	var series4 = $('#container1').highcharts().series[3];
 	var series5 = $('#container1').highcharts().series[4];
 	var series6 = $('#container1').highcharts().series[5];
 	var series7 = $('#container1').highcharts().series[6];
 	var series8 = $('#container1').highcharts().series[7];
 	var series9 = $('#container1').highcharts().series[8];
 	var series10 = $('#container1').highcharts().series[9];
 	
 	series1.update({visible: false});
 	series2.update({visible: false});
 	series4.update({visible: false});
 	series5.update({visible: false});
 	series6.update({visible: false});
 	series7.update({visible: false});
 	series8.update({visible: false});
 	series9.update({visible: false});
 	series10.update({visible: false});
 	//series2.remove();
 });
</script>