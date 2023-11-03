<?php

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	

	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// $period = date('Y-m-02');
	$cycleDate = date('Y-m-02');
	$cycleDateView = date('02-m-Y');
	//$period = date('Y-m');
	$period = '202007';
	
	$query = "SELECT customer, cutoffdate FROM cutoff WHERE period = '$period'";
	try
	{
		$results = $pdo2->prepare("$query");
		$results->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	
	$i = 1;

	while ($row = $results->fetch()) {
		
		$customer = $row['customer'];
		$cutoffdate = date("d-m-Y", strtotime($row['cutoffdate']));
		
		if ($cutoffdate == '01-01-1970') {
			$cutoffdate = "<span style='color: #fff;'>00-00-0000</span>";
		}
		
		$query = "SELECT warning, cutoff FROM db_access WHERE customer = '$customer'";
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
	
		$rowW = $result->fetch();
			$warning = $rowW['warning'];		
			$promise = date("d-m-Y", strtotime($rowW['cutoff']));
			$nowDate = date("d-m-Y");
			
		if (strtotime($promise) <= strtotime($nowDate)) {
			$promise = "<span style='color: red;'>$promise</span>";
		} else {
			$promise = "<span>$promise</span>";
		}
			
		if ($warning == 1) {
			$warningText = "Soft warning";
		} else if ($warning == 2) {
			$warningText = "Final warning";
		} else if ($warning == 3) {
			$warningText = "<strong>CUT OFF</strong>";
		} else {
			$warningText = "";
		}

		$query = "SELECT id, shortName FROM customers WHERE number = '$customer'";
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
	
		$rowC = $result->fetch();
			$shortName = $rowC['shortName'];
			$customerID = $rowC['id'];
			
		$query = "SELECT COUNT(invno), SUM(amount) FROM invoices WHERE paid = '' AND brand = 'SW' AND DATE(invdate) < DATE('$cycleDate') AND customer = '$customer'";
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
			$invs = $row['COUNT(invno)'];
			$totAmount = number_format($row['SUM(amount)'],2);

/*		$query = "SELECT invno, invdate, amount FROM invoices WHERE paid = '' AND brand = 'SW' AND DATE(invdate) < DATE('$cycleDate') AND customer = '$customer'";
		try
		{
			$resultsI = $pdo->prepare("$query");
			$resultsI->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
*/

		// Query exception flag
		
		// Query invoices
		
		// Query comments
		$query = "SELECT time, comment, operator FROM cutoffcomments WHERE period = '$period' AND customer = '$customer' ORDER BY time DESC";
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
			
			$commentShow = "<a href='add-cutoff-comment.php?client=$customer&period=$period'><img src='images/plus-new.png' width='15' /></a>";
			
			
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
					$first_name = $row['first_name'];
					$last_name = $row['last_name'];
				
				$comments .= "<strong><span style='font-size: 16px;'>$first_name $last_name</span><br />$commenttime</strong><br />$comment<br /><br />";
				
			}
		
			$commentShow = <<<EOD
				
<a href='#' id='showComment$customer'><img src='images/comments.png' width='15' /></a>
<div id="commentBox$customer" class='commentBox' style="display: none;">
<a href='#' id='hideComment$customer' class="closeComment"><img src="images/delete.png" width='22' /></a>
<span style='font-size: 22px; color: #606f5a; font-weight: 600;'>Comments for $shortName</span><br /><br />
<a href='add-cutoff-comment.php?client=$customer&period=$period' class='addComment'><img src='images/plus-new.png' width='25' style='margin-bottom: -7px;' />&nbsp;&nbsp;&nbsp;Add comment</a><br /><br /><br />
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

		
		// Query justificantes
		
		// Check if customer has been sunset
		$findDomain = "SELECT domain, db_pwd, warning, cutoff FROM db_access WHERE customer = '$customer'";
		try
		{
			$result = $pdo->prepare("$findDomain");
			$result->execute();
			$data2 = $result->fetchAll();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

		if ($data2) {

			$row = $data2[0];
			$domain = $row['domain'];
			$_SESSION['customerdomain'] = $domain;
			$db_pwd = $row['db_pwd'];
			$warning = $row['warning'];
			$cutoff = date("d-m-Y", strtotime($row['cutoff']));
			$db_name = "ccs_" . $domain;
			$db_user = $db_name . "u";
			
			/* DEBUG
			echo "domain: $domain<br />";
			echo "db_pwd: $db_pwd<br />";
			echo "db_name: $db_name<br />";
			echo "db_user: $db_user<br />";
			echo "domain: $domain<br />";
			*/
			
			// Look for db name. If it doesn't exist, throw error.

			try	{
		 		$pdo9 = new PDO('mysql:host='.DATABASE_HOST.';dbname='.$db_name, $db_user, $db_pwd);
		 		$pdo9->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		 		$pdo9->exec('SET NAMES "utf8"');
			}
			catch (PDOException $e)	{
		 		$warningText = '<strong>SUNSET</strong>';
			}
			
		}
		
		$query = "SELECT time FROM cutoffcomments WHERE period = '$period' AND customer = '$customer' ORDER BY time DESC LIMIT 1";
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
			$lastComment = date("d-m-Y", strtotime($row['time']));
		
		if ($invs > 0) {
			
			$tablerow .= "<tr><td><a href='customer.php?user_id=$customerID'>$customer</a></td><td><a href='customer.php?user_id=$customerID'>$shortName</a></td><td class='centered'><a href='#' onClick='showInv($i, $customerID)'>$invs</a></td><td class='right'>$totAmount &euro;</td><td>$warningText</td><td>$promise</td><td>$cutoffdate</td><td>$lastComment</td><td class='centered'>$commentShow</td></tr>";
			
		}
		/*
		while ($rowI = $resultsI->fetch()) {
			$iinvno = $rowI['invno'];		
			$iinvdate = $rowI['invdate'];		
			$iamount = $rowI['amount'];
			$tablerow .= "<tr><td></td><td></td><td>$iinvno</td><td class='right'>$iamount &euro;</td><td></td><td></td></tr>";
		}	
		*/
		
		$i++;
		
	}
	
	$validationScript = <<<EOD
	  
	    $(document).ready(function() {
		    
			//$('#cloneTable').width($('#mainTable').width());
			
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
				headers: {
					3: {
						sorter: "currency"
					},
					5: {
						sorter: "dates"
					},
					6: {
						sorter: "dates"
					},
					7: {
						sorter: "dates"
					}
				}
			}); 
			
		});

EOD;
		
		
	pageStart("Unpaid invoices - summary", NULL, $validationScript, "pmembership", NULL, "Unpaid invoices - summary", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
?>
<div id='mainbox-new-club' style='width: initial;'>
 <div id='mainboxheader'>
  <center>
   Unpaid invoices & warning status
  </center>
 </div>
 <div class='boxcontent'>
  <center>
  This report shows only clubs with 1 or more unpaid SW invoices, with invoice date before <?php echo $cycleDateView; ?>!<br /><br />
   <table class='default' id='mainTable'>
    <thead>
     <tr>
      <th>#</th>
      <th>Customer</th>
      <th>Invoices</th>
      <th>Amount</th>
      <th>Status</th>
      <th>Promise</th>
      <th>Cut off</th>
      <th>Last comment</th>
	  <th class='noExl'>Comment</th>
	  <th class='noExl'>Justificante</th>
     </tr>
    </thead>
    <tbody>
     <?php echo $tablerow; ?>
    </tbody>
   </table>
  </center>
 </div>
</div>

<?php

displayFooter();