<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view-closing.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
		
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings();
	
	unset($_SESSION['noCompare']);
	
	// Accountability: "Close only" or "None"
	if ($_SESSION['openAndClose'] < 3) {
		
		$_SESSION['errorMessage'] = $lang['open-day-not-using-open'];
		pageStart($lang['title-openday'], NULL, $confirmLeave, "pcloseday", "dev-align-center", $lang['title-openday'] . " - ERROR", $_SESSION['successMessage'], $_SESSION['errorMessage']);
		exit();

	} else {
		
		

		$selectUsers = "SELECT COUNT(closingid) FROM closing";
		$rowCount = $pdo3->query("$selectUsers")->fetchColumn();
		
		// Check if closing exists. 
		$closingLookup = "SELECT closingid, dayOpened FROM closing ORDER BY closingtime DESC LIMIT 1";
		try
		{
			$result = $pdo3->prepare("$closingLookup");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$dayOpened = $row['dayOpened'];
			
		if ($dayOpened == 2) {
			
				$_SESSION['errorMessage'] = $lang['day-already-opened'];
				pageStart($lang['title-openday'], NULL, $confirmLeave, "pcloseday", "dev-align-center", $lang['title-openday'] . " - ERROR", $_SESSION['successMessage'], $_SESSION['errorMessage']);
				exit();
			
		}
			
		/* First check if a closing exists.
		
		If not, see if an opening exists:
		- If an opening DOES exist, and is not closed, launch open-day?noComp
		- If it does NOT exist, launch open day with $_SESSION['firstOpening'] = 'true'; */
		
		/* In VIEW, add checks for openings and closings. Display warnings! This should be first step! */

		// If it does NOT exist, continue without comparison:
		if ($rowCount == 0) {
			
			// Check if an Opening exists. If not, this is the first ever opening.
			$openingLookup = "SELECT openingid FROM opening ORDER BY openingtime DESC";
			try
			{
				$result = $pdo3->prepare("$openingLookup");
				$result->execute();
				$data = $result->fetchAll();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
				
		
			// What if no opening exists? Means this is first ever opening.
			if (!$data) {
				
				$_SESSION['errorMessage'] = $lang['no-closing-data-found-continue'];
				$_SESSION['firstOpening'] = 'true';

			// Launch manual process using no comparison
			header("Location: open-day.php?noComp");
			exit();
			
			}

		}

	}
	
	// Continuing. Closing exists. Launch open day with comparison.

	
	
		
	/* LOGIC:
	
		
		
		If not, start opening proc (first ever opening proc without comparison)
		
		IF closing exists, check if it was completed.
		
		
		
	
	
	
	*************
	
	
		* First check if page was resubmitted with either manual or auto opening. Then:
		* None / Close only: Remove these links from admin panel. Also, in case someone manages to access the link, throw error.
		* Open & Close: Check if last opened day was closed. If not, throw warning: "The day opened on x/x/x has not been closed! You need to close that day before you can open a new one. <a>Close day x/x/x</a>.
		* Open & Close & Shifts: 
		  - Check if last opened day was closed. If it wasn't, check shifts:
		    - If table 'opening' shows no day was closed, no shifts was closed, throw error: The day was opened (x/x/x), but neither shift nor day was closed.<a>Click here to close that shift and day</a>.
		    - If a shift was opened, but not closed, throw warning: A shift was opened (x/x/x), but not closed.<a>Click here to close that shift and day</a>.
		    - If a closed shift exists, but no closed day, throw warning: Last shift x/x/x was closed, but the day wasn't! <a>Close day automatically using values from that close shift</a>. Or <a>Close shift x/x/x and day x/x/x manually</a>:
		      CREATE THIS FILE FOR THE PURPOSE: !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!  uTil/close-day-using-shift-values.php  !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
		    
		    
		*** Remember: NO dual opening/closing should exist from now on!!! Make it IMPOSSIBLE to do so!
		*** What if no shifts/openings/cosings were ever done???? In this case, they HAVE to open day first!!!!!!! Add this checkk and solution in!
		
	*/
	
	// Has this page been resubmitted with either Manual or Automatic opening?
	if (isset($_GET['manual']) || isset($_GET['autoopening'])) {
		
		// Check if closing exists.
		$closingLookup = "SELECT closingid FROM closing ORDER BY closingtime DESC LIMIT 1";
		try
		{
			$result = $pdo3->prepare("$closingLookup");
			$result->execute();
			$data = $result->fetchAll();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
			
		// If it does NOT exist, continue without comparison:
		if (!$data) {
			
				// Launch manual process using no comparison
				header("Location: open-day.php?noComp");
				exit();
			
		} else { // If it DOES exist:
			
			if (isset($_GET['autoopening'])) {
			
				// Launch automatic process (using comparison)
				header("Location: uTil/auto-open-day.php");
				
			} else {
			
				header("Location: open-day.php");
				
			}
	
		}
		
	}

	
	

	// Accountability: Open and close
	if ($_SESSION['openAndClose'] == 3) {
		
			
		$selectUsers = "SELECT COUNT(openingid) FROM opening ORDER BY openingtime DESC";
		$rowCount = $pdo3->query("$selectUsers")->fetchColumn();
		
		// Check if last opened day has been closed
		$openingLookup = "SELECT openingid, openingtime, dayClosed, openedby, firstDayOpen, firstRecOpen, firstDisOpen FROM opening ORDER BY openingtime DESC";
		try
		{
			$result = $pdo3->prepare("$openingLookup");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
	
		// What if no opening exists? Means this is first ever opening. Next, check for closing. If it exists, show both options. If not, continue without comparison
		if ($rowCount == 0) {
			
			$_SESSION['firstOpening'] = 'true';
				
			$selectUsers = "SELECT COUNT(closingid) FROM closing ORDER BY closingtime DESC";
			$rowCount2 = $pdo3->query("$selectUsers")->fetchColumn();
			
			$closingLookup = "SELECT closingid FROM closing ORDER BY closingtime DESC LIMIT 1";
			try
			{
				$result = $pdo3->prepare("$closingLookup");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
				
			// If it does NOT exist, continue without comparison:
			if ($rowCount2 == 0) {
				
				// Launch manual process using no comparison
				$_SESSION['errorMessage'] = $lang['no-closing-data-found-continue'];
				header("Location: open-day.php?noComp");
				exit();
				
			} else {
				
				// Launch manual process with comparison
				header("Location: open-day.php");
				exit();
			}

		// Opening exists, carry on
		// What if only ONE opening exists? If it's not closed yet; Check for comparison/closing data, then redirect to open-day. If it IS closed, throw error (day opened)
		} else if ($rowCount == 1) {
			
			$row = $result->fetch();
				$firstDayOpen = $row['firstDayOpen'];
				$dayClosed = $row['dayClosed'];
				$openingtime = $row['openingtime'];	
				$openedby = $row['openedby'];
				$openingtimeView = date('d-m-Y H:i', strtotime($openingtime));
				$openingBy = getOperator($openedby);
				
				// If the opening that exists is the first ever opening, which is in progress, check for comparison, then redirect to open-day:
				if ($firstDayOpen < 2) {
					
					$_SESSION['firstOpening'] = 'true';
					
					// Check if closing exists.
					$closingLookup = "SELECT closingid FROM closing ORDER BY closingtime DESC LIMIT 1";
					try
					{
						$result = $pdo3->prepare("$closingLookup");
						$result->execute();
						$data = $result->fetchAll();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}

									
					// If it does NOT exist, continue without comparison:
					if (!$data) {
						
						// Launch manual process using no comparison
						header("Location: open-day.php?noComp");
						exit();
						
					} else {
						
						// Launch manual process with comparison
						header("Location: open-day.php");
						exit();
						
					}
					
				// Throw error (day already opened)
				} else if ($dayClosed < 2) {
					
					// Throw the error
					pageStart($lang['title-openday'], NULL, $confirmLeave, "pcloseday", "dev-align-center", $lang['title-openday'] . " - ERROR", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	

	echo  <<<EOD
		<div id="scriptMsg">
		 <div class='error'>{$lang['open-day-day-not-closed']}</div>
		</div>
		<div class="actionbox-np2">
		  <div class='mainboxheader'>{$lang['global-details']}</div>
		  <div class="boxcontent">
		 <table class='purchasetable'>
			 <tr>
				   <td class='biggerFont'><strong>{$lang['day-opened']}</strong>&nbsp;
				  		{$openingtimeView}
				   </td>
				   </tr>
				   <tr>
				     <td class='biggerFont'><strong>{$lang['responsible']}</strong>&nbsp;
				   			{$openingBy}
				   	</td>
			  </tr>
		 </table>
		 </div>
		</div>
		EOD;


					exit();
				}

		// Openings exist, carry on
		} else {
			
			
				
			$row = $result->fetch();
				$closed = $row['dayClosed'];
				$openingid = $row['openingid'];
				$openingtime = $row['openingtime'];	
				$openedby = $row['openedby'];
				$openingtimeView = date('d-m-Y H:i', strtotime($openingtime));
				$_SESSION['openingtimeView'] = $openingtimeView;	
				$openingBy = getOperator($openedby);
				$firstRecOpen = $row['firstRecOpen'];
				$firstDisOpen = $row['firstDisOpen'];
						
				
			// Check last closing, to see if it has been closed, to evaluate whether an opening has been made or is in progress
			$closingLookup = "SELECT dayOpened, dayOpenedNo FROM closing ORDER BY closingtime DESC LIMIT 1";
		try
		{
			$result = $pdo3->prepare("$closingLookup");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$dayOpenedNo = $row['dayOpenedNo'];
				$dayOpened = $row['dayOpened'];
				
			// If a day is opening in progress, take directly to open-day.php
			if ($dayOpenedNo > 0) {
				
				header("Location: open-day.php");
				exit();
						
			}
			
			// If previous day has not been closed
			if ($closed < 2) {
				
				// Throw the error
				pageStart($lang['title-openday'], NULL, $confirmLeave, "pcloseday", "dev-align-center", $lang['title-openday'] . " - ERROR", $_SESSION['successMessage'], $_SESSION['errorMessage']);

echo  <<<EOD
<div id="scriptMsg">
 <div class='error'>{$lang['open-day-day-not-closed']}</div>
</div>
<br /><br />
	<div class="actionbox-np2">
		  <div class='mainboxheader'>{$lang['global-details']}</div>
		  <div class="boxcontent">
		 <table class='purchasetable'>
			 <tr>
				   <td class='biggerFont'><strong>{$lang['day-opened']}</strong>&nbsp;
				  		{$openingtimeView}
				   </td>
			</tr>
			<tr>	   
				     <td class='biggerFont'><strong>{$lang['responsible']}</strong>&nbsp;
				   			{$openingBy}
				   	</td>
				   	
			  </tr>
		 </table>
		 </div>
		</div>
EOD;

				exit();

			}
			
		}
		
	
	} else if ($_SESSION['openAndClose'] == 4) {
		
		$selectUsers = "SELECT COUNT(openingid) FROM opening ORDER BY openingtime DESC";
		$rowCount3 = $pdo3->query("$selectUsers")->fetchColumn();
		// Check if last opened day has been closed
		$openingLookup = "SELECT openingid, openingtime, dayClosed, openedby, firstDayOpen, firstRecOpen, firstDisOpen FROM opening ORDER BY openingtime DESC";
		try
		{
			$result = $pdo3->prepare("$openingLookup");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
	
		// What if no opening exists? Means this is first ever opening. Next, check for closing. If it exists, show both options. If not, continue without comparison
		if ($rowCount3 == 0) {
			
			$_SESSION['firstOpening'] = 'true';
				
			// Check if closing exists.
			$closingLookup = "SELECT closingid FROM closing ORDER BY closingtime DESC LIMIT 1";
			try
			{
				$result = $pdo3->prepare("$closingLookup");
				$result->execute();
				$data = $result->fetchAll();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
				
				
				
				
			// If it does NOT exist, continue without comparison:
			if (!$data) {
				
				// Launch manual process using no comparison
				$_SESSION['errorMessage'] = $lang['no-closing-data-found-continue'];
				header("Location: open-day.php?noComp");
				exit();
				
			} else {
				
				// Launch manual process with comparison
				header("Location: open-day.php");
				exit();
			}

		// Opening exists, carry on
		// What if only ONE opening exists? If it's not closed yet; Check for comparison/closing data, then redirect to open-day. If it IS closed, throw error (day opened)
		} else if ($rowCount3 == 1) {
			
			$row = $result->fetch();
				$firstDayOpen = $row['firstDayOpen'];
				$dayClosed = $row['dayClosed'];
				$openingtime = $row['openingtime'];	
				$openedby = $row['openedby'];
				$openingtimeView = date('d-m-Y H:i', strtotime($openingtime));
				$openingBy = getOperator($openedby);
				
				// If the opening that exists is the first ever opening, which is in progress, check for comparison, then redirect to open-day:
				if ($firstDayOpen < 2) {
					
					$_SESSION['firstOpening'] = 'true';
					
					// Check if closing exists.
					$closingLookup = "SELECT closingid FROM closing ORDER BY closingtime DESC LIMIT 1";
					try
					{
						$result = $pdo3->prepare("$closingLookup");
						$result->execute();
						$data = $result->fetchAll();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
						
						
					// If it does NOT exist, continue without comparison:
					if (!$data) {
						
						// Launch manual process using no comparison
						header("Location: open-day.php?noComp");
						exit();
						
					} else {
						
						// Launch manual process with comparison
						header("Location: open-day.php");
						exit();
						
					}
					
				// Throw error (day already opened)
				} else if ($dayClosed < 2) {
					
					// Throw the error
					pageStart($lang['title-openday'], NULL, $confirmLeave, "pcloseday", "dev-align-center", $lang['title-openday'] . " - ERROR", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
					echo  <<<EOD
<div id="scriptMsg">
 <div class='error'>{$lang['open-day-day-not-closed-shifts']}</div>
</div>
<br /><br />
	<div class="actionbox-np2">
		  <div class='mainboxheader'>{$lang['global-details']}</div>
		  <div class="boxcontent">
		 <table class='purchasetable'>
			 <tr>
				   <td class='biggerFont'><strong>{$lang['day-opened']}</strong>&nbsp;
				  		{$openingtimeView}
				   </td>
			</tr>
			<tr>	   
				     <td class='biggerFont'><strong>{$lang['responsible']}</strong>&nbsp;
				   			{$openingBy}
				   	</td>
			  </tr>
		 </table>
		 </div>
		</div>
EOD;

					exit();
				}

		// Openings exist, carry on
		} else {
				
			$row = $result->fetch();
				$closed = $row['dayClosed'];
				$openingid = $row['openingid'];
				$openingtime = $row['openingtime'];	
				$openedby = $row['openedby'];
				$openingtimeView = date('d-m-Y H:i', strtotime($openingtime));
				$_SESSION['openingtimeView'] = $openingtimeView;	
				$openingBy = getOperator($openedby);
				$firstRecOpen = $row['firstRecOpen'];
				$firstDisOpen = $row['firstDisOpen'];
						
				
			// Check last closing, to see if it has been closed, to evaluate whether an opening has been made or is in progress
			$closingLookup = "SELECT dayOpened, dayOpenedNo FROM closing ORDER BY closingtime DESC LIMIT 1";
			try
			{
				$result = $pdo3->prepare("$closingLookup");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
				$dayOpenedNo = $row['dayOpenedNo'];
				$dayOpened = $row['dayOpened'];
				
			// If a day is opening in progress, take directly to open-day.php
			if ($dayOpenedNo > 0) {
				
				header("Location: open-day.php");
				exit();
						
			}
			
			// If previous day has not been closed
			if ($closed < 2) {
				
				// Throw the error
				pageStart($lang['title-openday'], NULL, $confirmLeave, "pcloseday", "dev-align-center", $lang['title-openday'] . " - ERROR", $_SESSION['successMessage'], $_SESSION['errorMessage']);

echo  <<<EOD
<div id="scriptMsg">
 <div class='error'>{$lang['open-day-day-not-closed-shifts']}</div>
</div>
<br /><br />
	<div class="actionbox-np2">
		  <div class='mainboxheader'>{$lang['global-details']}</div>
		  <div class="boxcontent">
		 <table class='purchasetable'>
			 <tr>
				   <td class='biggerFont'><strong>{$lang['day-opened']}</strong>&nbsp;
				  		{$openingtimeView}
				   </td>
			</tr>
			<tr>
				     <td class='biggerFont'><strong>{$lang['responsible']}</strong>&nbsp;
				   			{$openingBy}
				   	</td>
			  </tr>
		 </table>
		 </div>
		</div>
EOD;

				exit();

			}
			
		}
/*	
			$row = mysql_fetch_array($result);
				$closed = $row['dayClosed'];
				$openingtime = $row['openingtime'];	
				$openedby = $row['openedby'];
				$openingtimeView = date('d-m-Y H:i', strtotime($openingtime));
				$_SESSION['openingtimeView'] = $openingtimeView;	
				$openingBy = getOperator($openedby);
				$firstRecOpen = $row['firstRecOpen'];
				$firstDisOpen = $row['firstDisOpen'];
				$shiftClosed = $row['shiftClosed'];
				
			// If the opening that exists is the first ever opening, which is in progress, check for comparison, then redirect to open-day:
			if ($firstRecOpen > 0 || $firstDisOpen > 0) {
				
				// Check if closing exists.
				$closingLookup = "SELECT closingid FROM closing ORDER BY closingtime DESC LIMIT 1";
				
				$result = mysql_query($closingLookup)
					or handleError($lang['error-closingload'],"Error loading closing from db: " . mysql_error());
					
				// If it does NOT exist, continue without comparison:
				if (mysql_num_rows($result) == 0) {
					
					// Launch manual process using no comparison
					header("Location: open-day.php?noComp");
					exit();
					
				} else {
					
					// Launch manual process with comparison
					header("Location: open-day.php");
					exit();
				}
			}
			
			// If previous day has not been closed, check for open shifts
			if ($closed < 2) {
				
				// Here, check last closing, see if an opening was done after, and see if it was completed.
				$closingLookup = "SELECT dayOpened, dayOpenedNo FROM closing ORDER BY closingtime DESC LIMIT 1";
			
				$result = mysql_query($closingLookup)
					or handleError($lang['error-closingload'],"Error loading closing from db: " . mysql_error());
				
				$row = mysql_fetch_array($result);
					$dayOpened = $row['dayOpened'];	
					$dayOpenedNo = $row['dayOpenedNo'];	
					
				// If it wasn't, redirect to "Open day" page to see status.
				if (($dayOpened < 2) && ($dayOpenedNo > 0)) {
									
					header("Location: open-day.php");
					exit();

				}

				
				// Day was opened, but shift wasn't closed
				if ($shiftClosed < 2) {
					
					// Throw the error
					pageStart($lang['title-openday'], NULL, $confirmLeave, "pcloseday", "", $lang['title-openday'] . " - ERROR", $_SESSION['successMessage'], $_SESSION['errorMessage']);

echo  <<<EOD
<div id="scriptMsg">
 <div class='error'>{$lang['open-day-shift-day-not-closed']}</div>
</div>
<br /><br />
<div id="productoverview">
 <center><strong>{$lang['global-details']}</strong></center><br />
 <table>
  <tr>
   <td style='text-align: left;'>{$lang['day-opened']}:</td>
   <td style='text-align: left;'>{$openingtimeView}</td>
  </tr>
  <tr>
   <td style='text-align: left;'>{$lang['responsible']}:</td>
   <td style='text-align: left;'>$openingBy</td>
  </tr>
 </table>
</div>

EOD;

exit();

				} else {
					
					// Check if last opened shift wasn't closed
					$openingLookup = "SELECT openingtime, shiftClosed, openedby, shiftClosedNo FROM shiftopen ORDER BY openingtime DESC LIMIT 1";
			
					$result = mysql_query($openingLookup)
						or handleError($lang['error-noopeningdetails'],"Error loading opening from db: " . mysql_error());
				
					$row = mysql_fetch_array($result);
						$closed = $row['shiftClosed'];
						$openingtime = $row['openingtime'];	
						$openedby = $row['openedby'];
						$openingtimeView = date('d-m-Y H:i', strtotime($openingtime));
						$openingBy = getOperator($openedby);
						$shiftClosedNo = $row['shiftClosedNo'];
						
					// If last opened shift wasn't closed, throw error
					if ($closed < 2) {
						
						// Throw the error
						pageStart($lang['title-openday'], NULL, $confirmLeave, "pcloseday", "", $lang['title-openday'] . " - ERROR", $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
echo  <<<EOD
<div id="scriptMsg">
 <div class='error'>{$lang['open-day-shift-not-closed']}</div>
</div>
<br /><br />
<div id="productoverview">
 <center><strong>{$lang['global-details']}</strong></center><br />
 <table>
  <tr>
   <td style='text-align: left;'>{$lang['day-opened']}:</td>
   <td style='text-align: left;'>{$openingtimeView}</td>
  </tr>
  <tr>
   <td style='text-align: left;'>{$lang['responsible']}:</td>
   <td style='text-align: left;'>$openingBy</td>
  </tr>
 </table>
</div>

EOD;

					exit();
				
				} else {
					// Shift was closed, but day wasn't! (Likely cause: Someone clicked 'close shift', not 'close day'. Show details for that shift!)
					
					// Look up shiftclosedNo from shiftopen, then look up that shiftclosing to find time closed!
					$shiftCloseLookup = "SELECT closingtime, closedby FROM shiftclose ORDER BY closingtime DESC LIMIT 1";
		
					$result = mysql_query($shiftCloseLookup)
						or handleError($lang['error-noopeningdetails'],"Error loading opening from db: " . mysql_error());
			
					$row = mysql_fetch_array($result);
						$closingtime = $row['closingtime'];
						$closingtimeView = date('d-m-Y H:i', strtotime($closingtime));
						$closingBy = getOperator($closedby);


					
					// Throw the error
					pageStart($lang['title-openday'], NULL, $confirmLeave, "pcloseday", "", $lang['title-openday'] . " - ERROR", $_SESSION['successMessage'], $_SESSION['errorMessage']);

echo  <<<EOD
<div id="scriptMsg">
 <div class='error'>{$lang['open-day-day-closed-but-shift-not-closed']}</div>
</div>
<br /><br />
<div id="productoverview">
 <center><strong>{$lang['global-details']}</strong></center><br />
 <table>
  <tr>
   <td style='text-align: left;'>{$lang['shift-closed']}:</td>
   <td style='text-align: left;'>{$closingtimeView}</td>
  </tr>
  <tr>
   <td style='text-align: left;'>{$lang['responsible']}:</td>
   <td style='text-align: left;'>$closingBy</td>
  </tr>
 </table>
</div>

EOD;

exit();

					} // end: Shift was closed, but day wasn't!
				
				} // end: Last day was NOT closed (closed < 2)
		
			} // end: if previous day has not been closed, check for open shifts
			
		} // end: Opening exists, carry on
	 */	
	} // end: if ($_SESSION['openAndClose'] == 4) {
		
	
		
		pageStart($lang['title-openday'], NULL, $confirmLeave, "pcloseday", "dev-align-center", $lang['title-openday'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
?>

<a href="?manual" class="cta1 auto_width"><?php echo $lang['count-and-weigh']; ?></a>
<a href="?autoopening" class="cta1 auto_width "><?php echo $lang['use-yday-values']; ?></a>

<?php displayFooter();
