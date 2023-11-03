<?php
	ob_start();
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings();	
	// Query to look up users
	// $selectUsers = "SELECT u.user_id, u.memberno, u.first_name, u.last_name, u.registeredSince, u.dni, u.gender, u.day, u.month, u.year, u.doorAccess, u.paidUntil, u.userGroup, ug.groupName, ug.groupDesc, u.form1, u.form2, u.credit, u.usageType, u.creditEligible, u.dniscan, u.dniext1, u.starCat, u.oldNumber, u.exento FROM users u, usergroups ug WHERE u.userGroup = ug.userGroup AND memberno <> '0' AND u.userGroup < 6 ORDER by u.memberno ASC LIMIT 1000";
	
	// Check if 'entre fechas' was utilised
	if (isset($_POST['untilDate'])) {
		
		$fromDate = date("Y-m-d", strtotime($_POST['fromDate']));
		$untilDate = date("Y-m-d", strtotime($_POST['untilDate']));
			
	} else {
		
		$fromDate = date("Y-m-d");
		$untilDate = date("Y-m-d");
				
	}

	$timeLimit = "DATE(d.donationTime) BETWEEN '$fromDate' AND '$untilDate 23:59:59'";
	$timeLimit2 = "DATE(donationTime) BETWEEN '$fromDate' AND '$untilDate 23:59:59'";
	$timeLimit3 = "DATE(saletime) BETWEEN '$fromDate' AND '$untilDate 23:59:59'";
	
	$selectUsers = "SELECT u.user_id, u.memberno, u.first_name, u.last_name, u.registeredSince, u.dni, u.gender, u.day, u.month, u.year, u.doorAccess, u.paidUntil, u.userGroup, u.form1, u.form2, u.credit, u.usageType, u.creditEligible, u.dniscan, u.dniext1, u.starCat, u.oldNumber, u.exento FROM users u, donations d WHERE u.user_id = d.userid AND u.memberno <> '0' AND u.userGroup < 6 AND $timeLimit GROUP BY u.user_id ORDER by u.memberno ASC";
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
		  
	  $( function() {
	    $( "#datepicker" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });
	    $( "#exceldatepicker" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });
	  });
	  $( function() {
	    $( "#datepicker2" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });	    
	    $( "#exceldatepicker2" ).datepicker({
			dateFormat: "dd-mm-yy"
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

			$('#mainTable').tablesorter({
				usNumberFormat: true,
				headers: {
					4: {
						sorter: "currency"
					},
					5: {
						sorter: "currency"
					},
					6: {
						sorter: "currency"
					},
					7: {
						sorter: "currency"
					},
					8: {
						sorter: "dates"
					}
				}
			}); 

		});
		
		$(window).resize(function() {
			$('#cloneTable').width($('#mainTable').width());
		});
		
EOD;


	pageStart($lang['donations-vs-dispenses'], NULL, $memberScript, "pmembership", NULL, $lang['donations-vs-dispenses'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	$nowDate = date('d-m-Y');
?>
<center>
<div id='filterbox' style='min-width: 350px;'>
 <div id='mainboxheader'>
 <?php echo $lang['filter']; ?>
 </div>
   <br />
  <form action='' method='POST'>
		<?php
			if (isset($_POST['fromDate'])) {
				
				echo <<<EOD
				 <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="defaultinput-no-margin sixDigit" value="{$_POST['fromDate']}" />&nbsp;&nbsp;
				 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="defaultinput-no-margin sixDigit" value="{$_POST['untilDate']}" onchange='this.form.submit()' />
		EOD;
				
			} else {
				
				echo <<<EOD
				 <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="defaultinput-no-margin sixDigit" value="$nowDate" />&nbsp;&nbsp;
				 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="defaultinput-no-margin sixDigit" value="$nowDate"" onchange='this.form.submit()' />
		EOD;
			}
		?>
		  <br>
		  <br>

			 <input type="hidden" name="submitted" value="1">
				<button type="submit" class='cta2'><?php echo $lang['filter'] ?></button>
	      
     </form>
 </div>
</div>
</center>

<center><!--<a href="donated-dispensed-2.php" class="cta1"><?php echo $lang['dispensary-yesterday']; ?></a>--><br />

         <a href="javascript:void(0)" id="openCOnfirm"><img src="images/excel-new.png" style='margin: 0 0 -5px 8px;'/></a>
</center>
<br />
	 <table class='default' id='mainTable'>
	  <thead>	
	   <tr style='cursor: pointer;'>
	    <th class='centered'>C</th>
	    <th class='centered'>#</th>
	    <th><?php echo $lang['global-name']; ?></th>
	    <th><?php echo $lang['member-lastnames']; ?></th>
	    <th><?php echo $lang['global-donations']; ?></th>
	    <th><?php echo $lang['bar']; ?></th>
	    <th><?php echo $lang['dispensary']; ?></th>
	    <th><?php echo $lang['global-delta']; ?></th>
	    <th>Donation Date</th>
	   </tr>
	  </thead>
	  <tbody>
<?php
		while ($user = $results->fetch()) {
	
	
	$starCat = $user['starCat'];
	$oldNumber = $user['oldNumber'];
	$user_id = $user['user_id'];
	// get donation time

	$selectDonation = "SELECT donationTime FROM donations WHERE userid = $user_id";
		try
		{
			$dresult = $pdo3->prepare("$selectDonation");
			$dresult->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
		$drow = $dresult->fetch();

	$donationTime = date("d-m-Y H:i:s", strtotime($drow['donationTime']));
	
	$donatedQuery = "SELECT SUM(amount) FROM donations WHERE $timeLimit2 AND userid = $user_id";
		try
		{
			$result = $pdo3->prepare("$donatedQuery");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$donated = $row['SUM(amount)'];

	$dispensedQuery = "SELECT SUM(amount) FROM sales WHERE $timeLimit3 AND userid = $user_id";
		try
		{
			$result = $pdo3->prepare("$dispensedQuery");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$dispensed = $row['SUM(amount)'];

	$barQuery = "SELECT SUM(amount) FROM b_sales WHERE $timeLimit3 AND userid = $user_id";
		try
		{
			$result = $pdo3->prepare("$barQuery");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$bar = $row['SUM(amount)'];

		$delta = $donated - $bar - $dispense;
		
	if ($starCat == 1) {
   		$userStar = "<img src='images/star-yellow.png' width='16' /><span style='display:none'>1</span>";
	} else if ($starCat == 2) {
   		$userStar = "<img src='images/star-black.png' width='16' /><span style='display:none'>2</span>";
	} else if ($starCat == 3) {
   		$userStar = "<img src='images/star-green.png' width='16' /><span style='display:none'>3</span>";
	} else if ($starCat == 4) {
   		$userStar = "<img src='images/star-red.png' width='16' /><span style='display:none'>4</span>";
	} else {
   		$userStar = "<span style='display:none'>0</span>";
	}
	
	if ($donated > 0) {

		echo sprintf("
	  	  <tr>
	  	   <td class='clickableRow' href='mini-profile.php?user_id=%d'>%s</td>
	  	   <td class='clickableRow' href='mini-profile.php?user_id=%d'>%s</td>
	  	   <td class='clickableRow' href='mini-profile.php?user_id=%d'>%s</td>
	  	   <td class='clickableRow' href='mini-profile.php?user_id=%d'>%s</td>",
		  $user['user_id'], $userStar, $user['user_id'], $user['memberno'], $user['user_id'], $user['first_name'], $user['user_id'], $user['last_name']);
	
		echo sprintf("
	  	   <td class='clickableRow right' href='mini-profile.php?user_id=%d'>%0.02f{$_SESSION['currencyoperator']}</td>
	  	   <td class='clickableRow right' href='mini-profile.php?user_id=%d'>%0.02f{$_SESSION['currencyoperator']}</td>
	  	   <td class='clickableRow right' href='mini-profile.php?user_id=%d'>%0.02f{$_SESSION['currencyoperator']}</td>
	  	   <td class='clickableRow right' href='mini-profile.php?user_id=%d'>%0.02f{$_SESSION['currencyoperator']}</td>
	  	   <td class='clickableRow right' href='mini-profile.php?user_id=%d'>%s</td>",
	  	  $user['user_id'], $donated, $user['user_id'], $bar, $user['user_id'], $dispensed, $user['user_id'], $donated - $bar - $dispensed, $user['user_id'], $donationTime);

	
	  	  
  	  }

  }
  
?>
	 </tbody>
	 </table>
	   <div  class="actionbox-npr" id = "dialog-3" title = "<?php echo $lang['donations-vs-dispenses']; ?>">
			
			<div class='boxcomtemt'>
				<p>Export excel between time ranges</p><br>
				<input type="text" id="exceldatepicker" name="fromDate" autocomplete="nope" class="sixDigit defaultinput" placeholder="<?php echo $lang['from-date'] ?>" />
				 <input type="text" id="exceldatepicker2" name="untilDate" autocomplete="nope" class="sixDigit defaultinput" placeholder="<?php echo $lang['to-date'] ?>"/>
 				<button class='cta1' id="fullList">Ok</button>
 			
			</div>
		</div> 
<?php  displayFooter(); ?>
<!-- <script type="text/javascript">
	$("#xllink").click(function(){
	    $("#load").show();
	    window.location.href = "donated-dispensed.php?action=xls"; 
	    setTimeout(function () {
	        $("#load").hide();
	    }, 2000);     
	 });
</script>	 -->
<script type="text/javascript">
	$( "#dialog-3" ).dialog({
	    autoOpen: false, 
	    hide: "puff",
	    show : "slide",
	     position: {
	       my: "top top",
	       at: "top top"
	    }      
	 });
	 $( "#openCOnfirm" ).click(function() {
	    $( "#dialog-3" ).dialog( "open" );
	 });

	 $("#fullList").click(function(){
	    $("#load").show();
	    $( "#dialog-3" ).dialog( "close" );

	    var fromDate = $("#exceldatepicker").val();
	    var untilDate = $("#exceldatepicker2").val();
	    var url = 'donated-dispensed-report.php?fromDate='+fromDate+'&untilDate='+untilDate+'&count=0&totalCount=0';
	    window.open(url, "Dispenses Report","height=300,width=300,modal=yes,alwaysRaised=yes");
	    setTimeout(function () {
	        $("#load").hide();
	    }, 2000);    
	 });

</script>

