<?php

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/viewv6.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	

	// promisetype: 0 = old, uncatregorized, 1 = from SW, 2 = direct link (SMS), 3 = 24-hour extension
	// justificantetype: 0 = system, 1 = link from mobile SMS
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	// $period = date('Y-m-02');
	$cycleDate = date('Y-09-02');
	$cycleDateView = date('02-09-Y');
	//$period = date('Y-m');
	$period = '202009';
	
	$query = "SELECT invno, invdate, customer, action, cutoffdate, promise, paid, amount FROM invoices WHERE period = '$period' AND paid = '' ORDER BY customer ASC";
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
		
		$nowDate = date("d-m-Y");
		$nowDateSQL = date("Y-m-d");

		$invno = $row['invno'];
		$invdate = date("d-m-Y", strtotime($row['invdate']));
		$invdateSQL = date("Y-m-d", strtotime($row['invdate']));
		$customer = $row['customer'];
		$amount = $row['amount'];
		$paid = $row['paid'];
		$cutoffdate = date("d-m-Y", strtotime($row['cutoffdate']));
		$promise = date("d-m-Y", strtotime($row['promise']));
		
		$date1 = new DateTime("$invdateSQL");
		$date2 = new DateTime("$nowDateSQL");
		$interval = $date1->diff($date2);
		$age = $interval->days;
		
		if ($promise == '01-01-1970') {
			$promise = "<span style='color: #fff;'>00-00-0000</span>";
		} else if (strtotime($promise) <= strtotime($nowDate)) {
			$promise = "<span style='color: red;'>$promise</span>";
		} else {
			$promise = "<span>$promise</span>";
		}
		
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
			//$promise = date("d-m-Y", strtotime($rowW['cutoff']));
			/*
		if (strtotime($promise) <= strtotime($nowDate) && $promise != "<span style='color: #fff;'>00-00-0000</span>") {
			$promise = "<span style='color: red;'>$promise</span>";
		} else {
			$promise = "<span>$promise</span>";
		}
			*/
		if ($warning == 1) {
			$warningText = "Soft warning";
		} else if ($warning == 2) {
			$warningText = "Final warning";
		} else if ($warning == 3) {
			$warningText = "<strong>CUT OFF</strong>";
		} else {
			$warningText = "";
		}

		$query = "SELECT id, shortName, pl FROM customers WHERE number = '$customer'";
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
			$pl = $rowC['pl'];
			
			if ($pl == 'R') {
				$pl = "<span style='color: green;'>R</span>";
			} else if ($pl == 'L') {
				$pl = "<span style='color: red;'><strong>L</strong></span>";
			} else {
				$pl = "<span style='background-color: #eee;'>&nbsp;&nbsp;&nbsp;&nbsp;</span>";
			}
			
		//$query = "SELECT COUNT(invno), SUM(amount) FROM invoices WHERE paid = '' AND brand = 'SW' AND DATE(invdate) < DATE('$cycleDate') AND customer = '$customer'";
		$query = "SELECT COUNT(invno), SUM(amount) FROM invoices WHERE paid = '' AND brand = 'SW' AND period = '$period' AND customer = '$customer'";

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
		$query = "SELECT time, comment, operator FROM cutoffcomments WHERE invno = '$invno' ORDER BY time DESC";
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
			
			$commentShow = "<a href='add-cutoff-comment.php?client=$customer&period=$period&invno=$invno'><img src='images/plus-new.png' width='15' /></a>";
			
			
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
<a href='add-cutoff-comment.php?client=$customer&period=$period&invno=$invno' class='addComment'><img src='images/plus-new2.png' width='25' style='margin-bottom: -7px;' />&nbsp;&nbsp;&nbsp;Update</a><br /><br /><br />
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
		$query = "SELECT justificante, verified FROM invoices WHERE invno = '$invno'";
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
			$justificante = $row['justificante'];
			$verified = $row['verified'];
			
		if ($justificante == '') {
			
			$justificante = "<a href='upload-justificante.php?client=$customer&period=$period&invno=$invno'><img src='images/plus-new.png' width='15' /></a><span style='display:none'>0</span>";
			
		} else {		
			
			if ($verified == 0) {
				
				$justificante = "<a href='verify-justificante.php?invno=$invno'><img src='images/image-orange.png' width='15' /></a><span style='display:none'>1</span>";
				//$justificante = "<a href='https://www.ccsnubev2.com/v6/$justificante'><img src='images/image-orange.png' width='15' /></a><span style='display:none'>1</span>";
				
			} else if ($verified == 1) {
				
				$justificante = "<a href='https://www.ccsnubev2.com/v6/$justificante'><img src='images/image.png' width='15' target='_blank' /></a><span style='display:none'>2</span>";
				
			} else if ($verified == 2) {
				
				$justificante = "<a href='https://www.ccsnubev2.com/v6/$justificante'><img src='images/image-red.png' width='15' target='_blank' /></a><span style='display:none'>3</span>";
				
			}
			
		}

		
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
		
		$query = "SELECT time FROM cutoffcomments WHERE invno = '$invno' ORDER BY time DESC LIMIT 1";
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
			
		if ($lastComment == '01-01-1970') {
			$lastComment = "<span style='color: #fff;'>00-00-0000</span>";
		}
		
			$tablerow .= "<tr><td><a href='customer.php?user_id=$customerID'>$customer</a></td><td><a href='customer.php?user_id=$customerID'>$shortName</a></td><td class='centered'>$invs</td><td class='right'>$totAmount &euro;</td><td>$warningText</td><td class='centered' style='padding: 0;'><a href='uTil/pl.php?customer=$customerID'>$pl</a></td><td>$invno</td><td>$invdate</td><td>$age</td><td class='right'>$amount &euro;</td><td>$promise</td><td>$cutoffdate</td><td class='noExl'>$lastComment</td><td class='centered noExl'>$commentShow</td><td class='centered noExl'>$justificante</td></tr>";
			
		/*
		while ($rowI = $resultsI->fetch()) {
			$iinvno = $rowI['invno'];		
			$iinvdate = $rowI['invdate'];		
			$iamount = $rowI['amount'];
			$tablerow .= "<tr><td></td><td></td><td>$iinvno</td><td class='right'>$iamount &euro;</td><td></td><td></td></tr>";
		}	
		*/
		
	}
	
	$validationScript = <<<EOD
	  
	    $(document).ready(function() {
		    
			$("#xllink").click(function(){

			  $("#mainTable").table2excel({
			    // exclude CSS class
			    exclude: ".noExl",
			    name: "Cutoff",
			    filename: "Cutoff" //do not include extension
		
			  });
		
			});
		    
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
					7: {
						sorter: "dates"
					},
					9: {
						sorter: "currency"
					},
					10: {
						sorter: "dates"
					},
					11: {
						sorter: "dates"
					},
					12: {
						sorter: "dates"
					}
				}
			}); 
			
		});

EOD;
		
		
	pageStart("Cutoff dashboard", NULL, $validationScript, "pmembership", NULL, "Cutoff dashboard", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
?>

<center>
<a href='cutoff-paid.php' class='cta1'>Settled debt</a>
</center>
<div id='mainbox-new-club' style='width: initial;'>
 <div id='mainboxheader'>
  <center>
   Cutoff dashboard
  </center>
 </div>
 <div class='boxcontent'>
  <center>
  This report shows only clubs with 1 or more unpaid SW invoices, with invoice date before <?php echo $cycleDateView; ?>!<br /><br />
  Next cutoff date: <strong>02-09-2020</strong> (final warnings go up on 01-09-2020).<br /><br />
	 <table class='default' id='cloneTable'>
      <tr class='nonhover'>
       <td colspan='13' style='border-bottom: 0;'>
         <a href="#" id="xllink" onClick="$('#mainTable').tableExport({type:'excel',escape:'false'});"><img src="images/excel-new.png" style='margin: 0 0 -5px 8px;'/></a>
       </td>
      </tr>
     </table>
<br />
<style>
th {
  position: -webkit-sticky;
  position: sticky;
  top: 0;
  z-index: 2;
}
</style>

   <table class='default' id='mainTable'>
    <thead>
     <tr>
      <th>#</th>
      <th>Customer</th>
      <th>Invoices</th>
      <th>Amount</th>
      <th>Status</th>
      <th>R / L</th>
      <th>Inv #</th>
      <th>Inv date</th>
      <th>Age</th>
      <th>Amount</th>
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