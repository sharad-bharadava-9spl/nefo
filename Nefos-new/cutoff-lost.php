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
	
	pageStart("Cutoff losses", NULL, $sortScript, "pmembership", NULL, "Cutoff losses", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	if (isset($_GET['m'])) {
		
		$month = $_GET['m'];
		$year = $_GET['y'];
		
		$query = "SELECT invno, invdate, amount, customer, cutoffdate FROM invoices WHERE MONTH(cutoffdate) = $month AND YEAR(cutoffdate) = $year AND brand = 'SW' AND paid <> 'Paid' ORDER BY cutoffdate DESC, customer ASC";
		try
		{
			$results = $pdo->prepare("$query");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($row = $results->fetch()) {
			
			$invno = $row['invno'];
			$invdate = date("d-m-Y", strtotime($row['invdate']));
			$amount = $row['amount'];
			$customer = $row['customer'];
			$number = $customer;
			$cutoffdate = date("d-m-Y", strtotime($row['cutoffdate']));
			
			$query = "SELECT longName FROM customers WHERE number = '$customer'";
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
		
			$rowCu = $result->fetch();
				$longName = $rowCu['longName'];
				
				// Query comments
				$query = "SELECT time, comment, operator FROM inactivecomments WHERE customer = '$number' ORDER BY time DESC";
				try
				{
					$result = $pdo2->prepare("$query");
					$result->execute();
					$data = $result->fetchAll();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
					
				if (!$data) {
					
					$commentShow = "<span style='display:none'>0</span>";
					
					
				} else {
		
					$comments = '';
						
					foreach ($data as $rowC) {
					
						$commenttime = date("d/m/Y H:i", strtotime($rowC['time']));
						$comment = $rowC['comment'];		
						$operator = $rowC['operator'];	
							
						// Look up user
						$query = "SELECT first_name, last_name FROM users WHERE user_id = '$operator'";
						try
						{
							$result = $pdo2->prepare("$query");
							$result->execute();
						}
						catch (PDOException $e)
						{
								$error = 'Error fetching user: ' . $e->getMessage();
								echo $error;
								exit();
						}
					
						$row = $result->fetch();
							$first_name = $row['first_name'];
							$last_name = $row['last_name'];
						
						$comments .= "<strong><span style='font-size: 16px;'>$first_name $last_name</span><br />$commenttime</strong><br />$comment<br /><br />";
						
					}
				
					$commentShow = <<<EOD
				
<a href='#' id='showComment$invno'><img src='images/comments.png' width='15' /></a><span style='display:none'>1</span>
<div id="commentBox$invno" class='commentBox' style="display: none;">
<a href='#' id='hideComment$invno' class="closeComment"><img src="images/delete.png" width='22' /></a>
<span style='font-size: 22px; color: #606f5a; font-weight: 600;'>Comments for <a href='customer.php?user_id=$clientid' target='_blank'> #$customer $longName</a></span><br /><br />
<a href='add-inactive-comment.php?client=$customer' class='addComment'><img src='images/plus-new2.png' width='25' style='margin-bottom: -7px;' />&nbsp;&nbsp;&nbsp;Update</a><br /><br /><br />
$comments

</div>
<script>
$("#showComment$invno").click(function (e) {
	e.preventDefault();
	$("#commentBox$invno").css("display", "block");
});
$("#hideComment$invno").click(function (e) {
	e.preventDefault();
	$("#commentBox$invno").css("display", "none");
});
</script>
EOD;

				}
				
				

					
			$tableContent .= "
			<tr>
			 <td>$cutoffdate</td>
			 <td>$customer</td>
			 <td>$longName</td>
			 <td>$invno</td>
			 <td>$invdate</td>
			 <td class='right'>$amount €</td>
  	   		 <td class='centered'>$commentShow</td>
			</tr>
			";
			
		}
		
		
			
?>
	<center>
			<a href='cutoff-lost.php' class='cta1'>&laquo; Summary &laquo;</a>
	</center>
<br />
<div id='mainbox-new-club' style='width: initial;'>
 <div id='mainboxheader'>
  <center>
   Clubs lost to cutoff
  </center>
 </div>
 <div class='boxcontent'>
  <center>
	<table class="default" id="mainTable">
	 <thead>
	  <tr>
	   <th>Cut off</th>
	   <th>#</th>
	   <th>Customer</th>
	   <th>Inv #</th>
	   <th>Inv date</th>
	   <th>Amount</th>
	   <th class='left'>Comment</th>
	  </tr>
	 </thead>
	 <tbody>
<?php echo $tableContent; ?>
	 </tbody>
	</table>
  </div>
 </div>


<?php
		
		exit();
		
	}
	
	$query = "SELECT YEAR(cutoffdate), MONTH(cutoffdate), COUNT(DISTINCT customer) FROM invoices WHERE paid <> 'Paid' AND cutoffdate IS NOT NULL AND DATE(cutoffdate) > '2020-08-01' GROUP BY YEAR(cutoffdate), MONTH(cutoffdate) ORDER BY YEAR(cutoffdate) DESC, MONTH(cutoffdate) DESC";
	try
	{
		$results = $pdo->prepare("$query");
		$results->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$i = 0;
	
	while ($row = $results->fetch()) {
		

		
		$month = sprintf('%02d', $row['MONTH(cutoffdate)']);
		$year = $row['YEAR(cutoffdate)'];
		$period = "$month-$year";
		$customers = $row['COUNT(DISTINCT customer)'];
		
		$query = "SELECT COUNT(DISTINCT customer) FROM invoices WHERE MONTH(invDate) = $month AND YEAR(invDate) = $year AND brand = 'SW'";
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
			$clients = $row['COUNT(DISTINCT customer)'];
			
		$lost = number_format($customers / $clients * 100,1);
		$lostTotal = $lostTotal + $lost;
		$customersTotal = $customersTotal + $customers;
		
			
		$i++;
		
	}
	
	
	$lostTotal = $lostTotal / $i;
	$customersTotal = $customersTotal / $i;
	
	
	$query = "SELECT YEAR(cutoffdate), MONTH(cutoffdate), COUNT(DISTINCT customer) FROM invoices WHERE paid <> 'Paid' AND cutoffdate IS NOT NULL AND DATE(cutoffdate) > '2020-08-01' GROUP BY YEAR(cutoffdate), MONTH(cutoffdate) ORDER BY YEAR(cutoffdate) DESC, MONTH(cutoffdate) DESC";
	try
	{
		$results = $pdo->prepare("$query");
		$results->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	while ($row = $results->fetch()) {
		
		unset($arr1);
		unset($arr2);
		
		$month = sprintf('%02d', $row['MONTH(cutoffdate)']);
		$year = $row['YEAR(cutoffdate)'];
		$period = "$month-$year";
		$customers = $row['COUNT(DISTINCT customer)'];
		
		$query = "SELECT COUNT(DISTINCT customer) FROM invoices WHERE MONTH(invDate) = $month AND YEAR(invDate) = $year AND brand = 'SW'";
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
			$clients = $row['COUNT(DISTINCT customer)'];
			
		$lost = number_format($customers / $clients * 100,1);
		
		if ($lost > $lostTotal) {
			$rowColour = "style='color: red;'";
		} else {
			$rowColour = "";
		}
		
		$dateOperator = "$year-$month-01";

		// Array 1:
		$arr1query = "SELECT DISTINCT customer FROM invoices WHERE brand = 'SW' AND DATE(invdate) > '2019-12-31' AND invdate < '$dateOperator'";
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
		$arr2query = "SELECT DISTINCT customer FROM invoices WHERE brand = 'SW' AND MONTH(invdate) = $month AND YEAR(invdate) = $year";
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
        
        $newTotal = $newTotal + $arrDiffNo;
        
        $vsDiff = $arrDiffNo - $customers;
        
        $vsTotal = $vsTotal + $vsDiff;
        
		if ($vsDiff < 0) {
			$rowColour2 = "style='color: red;'";
		} else {
			$rowColour2 = "";
		}

		
		$tableContent .= "
		  <tr>
		   <td class='right clickableRow' href='?m=$month&y=$year'>$period</td>
		   <td class='centered clickableRow' href='?m=$month&y=$year'>$customers</td>
		   <td class='centered clickableRow' href='?m=$month&y=$year' $rowColour>$lost</td>
		   <td class='centered clickableRow' href='?m=$month&y=$year'></td>
		   <td class='centered clickableRow' href='?m=$month&y=$year'>$arrDiffNo</td>
		   <td class='centered clickableRow' href='?m=$month&y=$year' $rowColour2>$vsDiff</td>
		  </tr>
			";
			
		$i++;
		
	}
	
	$customersTotal = number_format($customersTotal,1);
	$lostTotal = number_format($lostTotal,1);
	
		$tableContent .= "
		  <tr>
		   <td class='right'><strong>Average:</strong></td>
		   <td class='centered'>$customersTotal</td>
		   <td class='centered'>$lostTotal</td>
		   <td class='right'><strong>Total</strong></td>
		   <td class='centered'>$newTotal</td>
		   <td class='centered'>$vsTotal</td>
		  </tr>
			";


	
?>

<div id='mainbox-new-club' style='width: initial;'>
 <div id='mainboxheader'>
  <center>
   Clubs lost to cutoff
  </center>
 </div>
 <div class='boxcontent'>
  <center>
  The table shows number of customers lost to cutoff, compared to total number of customers invoiced per month.<br /><br />
  Percentages in <span style='color: red;'>red</span> mean they're higher than the average.<br /><br /><br />
  <div id = "container1" style = "width: 550px; height: 400px; margin: 0 auto"></div>
	<table class="default" id="mainTable">
	 <thead>
	  <tr>
	   <th>Period</th>
	   <th>Lost to cutoff</th>
	   <th>% of clients lost</th>
	   <th></th>
	   <th>New clients</th>
	   <th>New vs lost</th>
	  </tr>
	 </thead>
	 <tbody>
<?php echo $tableContent; ?>
	 </tbody>
	</table>
  </div>
 </div>

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
       endRow: $('#mainTable tr').length-2
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
	        	console.log(stripHtml(this.value))
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
 	var title = "Cutoff losses";
 	loadChart('mainTable', 'container1', title);
 	var series1 = $('#container1').highcharts().series[1];
 	var series2 = $('#container1').highcharts().series[2];
 	series1.update({visible: false});
 	series2.remove();
 });
</script>