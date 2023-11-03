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
	
	// Query to look up users
	$selectUsers = "SELECT YEAR(launchdate), MONTH(launchdate), COUNT(id) FROM `customers` WHERE launchdate IS NOT NULL GROUP BY YEAR(launchdate), MONTH(launchdate) ORDER BY YEAR(launchdate) DESC, MONTH(launchdate) DESC";
		try
		{
			$results = $pdo3->prepare("$selectUsers");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		
	
		
	$memberScript = <<<EOD
	
	    $(document).ready(function() {
		    
		    
			$("#xllink").click(function(){

			  $("#mainTable").table2excel({
			    // exclude CSS class
			    exclude: ".noExl",
			    name: "Socios",
			    filename: "Socios" //do not include extension
		
			  });
		
			});
		    
		    
		    
			$('#cloneTable').width($('#mainTable').width());
			
			$.tablesorter.addParser({
			  id: 'dates',
			  is: function(s) { return false },
			  format: function(s) {
			    var dateArray = s.split('-');
			    return dateArray[2].substring(0,4) + dateArray[1] + dateArray[0];
			  },
			  type: 'numeric'
			});
			
EOD;

	if ($_SESSION['creditOrDirect'] == 1 && $_SESSION['membershipFees'] == 1) {
		
		$memberScript .= <<<EOD
			
			$('#mainTable').tablesorter({
				usNumberFormat: true,
				headers: {
					4: {
						sorter: "currency"
					},
					6: {
						sorter: "dates"
					},
					11: {
						sorter: "dates"
					}
				}
			}); 

		
EOD;
		
	} else if ($_SESSION['creditOrDirect'] == 1 && $_SESSION['membershipFees'] == 0) {
		
		$memberScript .= <<<EOD
			
			$('#mainTable').tablesorter({
				usNumberFormat: true,
				headers: {
					4: {
						sorter: "currency"
					},
					6: {
						sorter: "dates"
					}
				}
			}); 

		
EOD;
		
	} else if ($_SESSION['creditOrDirect'] == 0 && $_SESSION['membershipFees'] == 1) {
		
		$memberScript .= <<<EOD
			
			$('#mainTable').tablesorter({
				usNumberFormat: true,
				headers: {
					4: {
						sorter: "dates"
					},
					9: {
						sorter: "dates"
					}
				}
			}); 

		
EOD;
		
	} else {
		
		$memberScript .= <<<EOD
			
			$('#mainTable').tablesorter({
				usNumberFormat: true,
				headers: {
					3: {
						sorter: "dates"
					}
				}
			}); 

		
EOD;
		
	}
	
	$memberScript .= <<<EOD
		});
		
		$(window).resize(function() {
			$('#cloneTable').width($('#mainTable').width());
		});
		
EOD;


	pageStart("Launched clubs per month", NULL, $memberScript, "pmembership", NULL, "Launched clubs per month", $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>
	<div id = "container1" style = "width: 550px; height: 400px; margin: 0 auto"></div><br>
	 <table class='default' id='mainTable'>
	  <thead>	
	   <tr style='cursor: pointer;'>
	    <th>Year  Month</th>
	    <th># of clubs</th>
	    <th>Comment</th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php
	while ($user = $results->fetch()) {

		$year = $user['YEAR(launchdate)'];
		$monthRaw = sprintf("%02d", $user['MONTH(launchdate)']);
		$month = sprintf("%02d", $user['MONTH(launchdate)']);
		
		if ($month == 1) {
			$month = "January";
		} else if ($month == 2) {
			$month = "February";
		} else if ($month == 3) {
			$month = "March";
		} else if ($month == 4) {
			$month = "April";
		} else if ($month == 5) {
			$month = "May";
		} else if ($month == 6) {
			$month = "June";
		} else if ($month == 7) {
			$month = "July";
		} else if ($month == 8) {
			$month = "August";
		} else if ($month == 9) {
			$month = "September";
		} else if ($month == 10) {
			$month = "October";
		} else if ($month == 11) {
			$month = "November";
		} else if ($month == 12) {
			$month = "December";
		}
		
		$number = $user['COUNT(id)'];
		
		$period = $year . $monthRaw;
		
		// Query comments
		$query = "SELECT time, comment, operator FROM launchcomments WHERE period = '$period' ORDER BY time DESC";
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
			
			$commentShow = "<a href='add-launched-comment.php?period=$period'><img src='images/plus-new.png' width='15' /></a>";
			
			
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
				
<a href='#' id='showComment$customer'><img src='images/comments.png' width='15' /></a>
<div id="commentBox$customer" class='commentBox' style="display: none;">
<a href='#' id='hideComment$customer' class="closeComment"><img src="images/delete.png" width='22' /></a>
<span style='font-size: 22px; color: #606f5a; font-weight: 600;'>Comments for $month $year</span><br /><br />
<a href='add-launched-comment.php?period=$period' class='addComment'><img src='images/plus-new2.png' width='25' style='margin-bottom: -7px;' />&nbsp;&nbsp;&nbsp;Update</a><br /><br /><br />
$comments

</div>
<script>
$("#showComment$customer").click(function (e) {
	e.preventDefault();
	$("#commentBox$customer").css("display", "block");
});
$("#hideComment$customer").click(function (e) {
	e.preventDefault();
	$("#commentBox$customer").css("display", "none");
});
</script>
EOD;

				}

	
		echo sprintf("
  	  <tr>
  	   <td class='clickableRow' href='clients.php?y=$year&m=$monthRaw' style='cursor: pointer;'>%s  %s</td>
  	   <td class='centered clickableRow' href='clients.php?y=$year&m=$monthRaw' style='cursor: pointer;'>%s</td>
  	   <td class='centered'>$commentShow</td>
  	  </tr>",
	  $year, $month, $number);
	  
	  
  }
?>

	 </tbody>
	 </table>

<?php  displayFooter(); ?>
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
       endColumn: 1
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
	        	//console.log(stripHtml(this.value))
	          return stripHtml(this.value)
	        }
	      },
	    },
    };
    var xAxis = {
    	type: "category",
    	reversed: true,
    	//showFirstLabel: false,
		labels: {
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
       /*formatter: function () {
          return '<b>' + this.series.name + '</b><br/>' +
             this.point.y + ' <br/>' + this.point.name.toLowerCase();
       }*/
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
 	var title = "Launched clubs per month";
 	loadChart('mainTable', 'container1', title);
 });
</script>