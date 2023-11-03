<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	
	session_start();
	$accessLevel = '3';
		
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	if (isset($_GET['user_id'])) {
		$user_id = $_GET['user_id'];
	} else {
		handleError("No user specified", "");
	}

	if (isset($_POST['untilDate'])) {
		
		$limitVar = "";
		
		$openingTime = date("Y-m-d", strtotime($_POST['fromDate']));
		$closingTime = date("Y-m-d", strtotime($_POST['untilDate']));
		
		$openingTimeView = date("d-m-Y", strtotime($_POST['fromDate']));
		$closingTimeView = date("d-m-Y", strtotime($_POST['untilDate']));
		
		$monthDisp = $openingTimeView . " - " . $closingTimeView;
			
	} else {
		
		$openingTime = date("Y-m-1", strtotime(date("Y-m-d")));
		$closingTime = date("Y-m-t", strtotime(date("Y-m-d")));
		
		$monthDisp = date("F Y", strtotime($closingTime));

		
	}
	
	
	// Members signed up
	$selectMembers = "SELECT COUNT(id) from log WHERE logtype = 12 AND operator = $user_id AND (logtime BETWEEN '$openingTime' AND '$closingTime')";
	try
	{
		$result = $pdo3->prepare("$selectMembers");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$newmembers = $row['COUNT(id)'];
		
	// Members signed up for free
	$selectMembers = "SELECT COUNT(id) from log WHERE logtype = 12 AND amount = 0 AND operator = $user_id AND (logtime BETWEEN '$openingTime' AND '$closingTime')";
	try
	{
		$result = $pdo3->prepare("$selectMembers");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}

	$row = $result->fetch();
		$newmembers = $row['COUNT(id)'];
		
	// Look up todays dispenses
	$selectSales = "SELECT SUM(amount), SUM(quantity), SUM(realQuantity), SUM(units) from sales WHERE (saletime BETWEEN '$openingSQL' AND '$closingSQL')";
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
		$salesToday = $row['SUM(amount)'];
		$quantitySold = $row['SUM(quantity)'];
		$quantitySoldReal = $row['SUM(realQuantity)'];
		$unitsSold = $row['SUM(units)'];

		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
	pageStart($lang['worker-module'], NULL, $validationScript, "pnewcategory", "", $lang['worker-module'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
		try
		{
			$result = $pdo3->prepare("SELECT user_id, memberno, first_name, last_name, userGroup FROM users WHERE userGroup < 4");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}

			
		while ($row = $result->fetch()) {
			$name = $row['first_name'] . " " . $row['last_name'];
			$userid = $row['user_id'];
			
			if ($user_id != $userid) {
				
		    	// $listcontent .= "<option value='$userid'>$name</option><br />";
				$listcontent .= "<a href='{$siteroot}change-user.php?loggedinuser=$userid'>$name</a><br />";
		    	
	    	} else {
		    	
		    	$curruser = $name;
		    	
	    	}
		}

	
	
?>
<center>
<div id="mainbox-no-width">
 <div id="mainboxheader">
  <?php echo $curruser; ?>
  <span class='relativeitem'>
  <a href='#' id='chooseOther' class='usergrouptext' style='width: initial; padding: 2px 5px; font-size: 12px; margin-left: 10px; position: absolute; bottom: -12px;'><?php echo $lang['change']; ?></a>
  <span id="stafflist2">
  <?php echo $listcontent; ?>
  </span>
  </span>
 </div>
 <div class='boxcontent'>
         <form action='' method='POST'>
<?php
	if (isset($_POST['fromDate'])) {
		
		echo <<<EOD
		 <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="sixDigit defaultinput" value="{$_POST['fromDate']}" />
		 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="sixDigit defaultinput" value="{$_POST['untilDate']}" onchange='this.form.submit()' />
		 <button type="submit" class='cta1' style='display: inline-block; width: 50px; border: 0;'>OK</button>
EOD;
		
	} else {
		
		echo <<<EOD
		 <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="sixDigit defaultinput" value="$openingTime" />
		 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="sixDigit defaultinput" value="$closingTime" onchange='this.form.submit()' />
		 <button type="submit" class='cta1' style='display: inline-block; width: 50px; border: 0;'>OK</button>
EOD;

	}
?>
        </form>
<br />

<table class='default' style='display: inline-block; margin-right: 20px;'>
 <tr>
  <th colspan='7'><center><strong>DISPENSARY</strong></center></th>
 </tr>
 <tr>
  <td></td>
  <td><strong><?php echo $curruser; ?></strong></td>
  <td></td>
  <td><strong>Club total</strong></td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td class='left'>Dispensed g</td>
  <td class='right'><?php echo $salesToday; ?></td>
  <td></td>
  <td class='right'>7200,44</td>
  <td></td>
  <td></td>
  <td></td>
 </tr>		$salesToday = $row['SUM(amount)'];
		$quantitySold = $row['SUM(quantity)'];
		$quantitySoldReal = $row['SUM(realQuantity)'];
		$unitsSold = $row['SUM(units)'];

 <tr>
  <td class='left'>Dispensed real g</td>
  <td class='right'>153,09</td>
  <td></td>
  <td class='right'>7291,21</td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td class='left'>Dispensed u</td>
  <td class='right'>43</td>
  <td></td>
  <td class='right'>214</td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td class='left'>Dispensed &euro;</td>
  <td class='right'>2.499,40 &euro;</td>
  <td></td>
  <td class='right'>175.307,92 &euro;</td>
  <td></td>
  <td></td>
  <td></td>
 </tr>
 <tr>
  <td colspan='7' style='border: 0;'></td>
 </tr>
 <tr>
  <td></td>
  <td><strong><?php echo $curruser; ?></strong></td>
  <td></td>
  <td><strong>Club avg.</strong></td>
  <td></td>
  <td><strong>Worker avg.</strong></td>
  <td><strong>Volunteer avg.</strong></td>
 </tr>
 <tr>
  <td class='left'>Difference g. / real g.</td>
  <td class='right'>9,54</td>
  <td class='left'>(6,2%)</td>
  <td class='right'>15,72</td>
  <td class='left'>(0,2%)</td>
  <td class='right'>10,45</td>
  <td class='right'>0</td>
 </tr>
 <tr>
  <td class='left'>Difference in &euro;</td>
  <td class='right'>80,42&euro;</td>
  <td class='right'></td>
  <td class='right'>144,94&euro;</td>
  <td class='right'></td>
  <td class='right'>103,87&euro;</td>
  <td class='right'></td>
 </tr>

</table>
<table class='default' style='display: inline-block; vertical-align: top;'>
 <tr>
  <th colspan='3'><center><strong>BAR</strong></center></th>
 </tr>
 <tr>
  <td></td>
  <td><strong><?php echo $curruser; ?></strong></td>
  <td><strong>Club total</strong></td>
 </tr>
 <tr>
  <td class='left'>Sold u.</td>
  <td class='right'>19</td>
  <td class='right'>94</td>
 </tr>
 <tr>
  <td class='left'>Sold &euro;</td>
  <td class='right'>44,50&euro;</td>
  <td class='right'>245,75&euro;</td>
 </tr>
</table>
<br /><br />
<table class='default' style='display: inline-block; vertical-align: top; margin-right: 20px;'>
 <tr>
  <th colspan='2'><center><strong>RECEPTION</strong></center></th>
 </tr>
 <tr>
  <td class='left'>Members signed up</td>
  <td><?php echo $newmembers; ?></td>
 </tr>
 <tr>
  <td class='left'>Members signed up for free</td>
  <td>2</td>
 </tr>
 <tr>
  <td class='left'># of changed expiration date</td>
  <td>1</td>
 </tr>
 <tr>
  <td class='left'># of changed credit</td>
  <td>4</td>
 </tr>
 <tr>
  <td class='left'>Changed credit total in &euro;</td>
  <td>-76,22&euro;</td>
 </tr>
 <tr>
  <td class='left'># of chips sold</td>
  <td>18</td>
 </tr>
 <tr>
  <td class='left'>Chips sold &euro;</td>
  <td>54,00&euro;</td>
 </tr>
 <tr>
  <td class='left'>Chips gifted</td>
  <td>4</td>
 </tr>
 <tr>
  <td class='left'>&euro; loss due to free chips</td>
  <td>12&euro;</td>
 </tr>
</table>

<table class='default' style='display: inline-block; vertical-align: top; margin-right: 20px;'>
 <tr>
  <th colspan='2'><center><strong>SHIFTS</strong></center></th>
 </tr>
 <tr>
  <td class='left'># of shifts</td>
  <td class='right'>11</td>
 </tr>
 <tr>
  <td class='left'>Avg. shift duration (hours)</td>
  <td class='right'>4,5</td>
 </tr>
 <tr>
  <td class='left'>Closed receptions</td>
  <td class='right'>2</td>
 </tr>
 <tr>
  <td class='left'>Closed dispensaries</td>
  <td class='right'>9</td>
 </tr>
 <tr>
  <td class='left'>&euro; delta</td>
  <td class='right'>-14,40&euro;</td>
 </tr>
 <tr>
  <td class='left'>g. delta</td>
  <td class='right'>-18,99</td>
 </tr>
 <tr>
  <td class='left'>u. delta</td>
  <td class='right'>-2</td>
 </tr>
</table>
<table class='default' style='display: inline-block; vertical-align: top;'>
 <tr>
  <th colspan='3'><center><strong>WORKER TRACKING</strong></center></th>
 </tr>
 <tr>
  <td class='left'>Scheduled hours</td>
  <td class='right'>49h 30m</td>
  <td></td>
 </tr>
 <tr>
  <td class='left'>Real worked hours</td>
  <td class='right'>52h 11m</td>
  <td></td>
 </tr>
 <tr>
  <td class='left'># of shifts</td>
  <td class='right'>11</td>
  <td></td>
 </tr>
 <tr>
  <td class='left'># of times arrived late</td>
  <td class='right'>2</td>
  <td class='left'>(22 min)</td>
 </tr>
</table>
<br />
<img src='images/shiftsx.png' />
</div>
</div>
      <script>
    	$("#chooseOther").click(function (e) {
	    	e.preventDefault();
		$('#stafflist2').toggle();
		});	

		</script>
<?php displayFooter(); ?>

