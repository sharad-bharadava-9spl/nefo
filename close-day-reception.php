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
	
	if ($_SESSION['openAndClose'] == 2) {
		
		if ($_SESSION['noCompare'] != 'true') {
			
			// Closing only - WITH comparison
			$openingid = $_SESSION['openingid'];
			$openingtime = $_SESSION['openingtime'];
			$responsible = $_SESSION['user_id'];
		
			// Check to see if it's in progress
			$checkOpening = "SELECT recOpened, recOpenedBy FROM closing WHERE closingid = $openingid";
		try
		{
			$result = $pdo3->prepare("$checkOpening");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$recOpened = $row['recOpened'];
				$recOpenedBy = $row['recOpenedBy'];
				
		} else {
			
			// Check to see if it's in progress
			$checkOpening = "SELECT recClosed, recClosedBy FROM closing ORDER by closingtime DESC";
		try
		{
			$result = $pdo3->prepare("$checkOpening");
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
				
				$responsible = $_SESSION['user_id'];
				
				// Not in progress, so let's create the line!
				$query = "INSERT INTO closing (recClosed, recClosedBy, currentClosing) VALUES (1, $responsible, 1)";
		try
		{
			$result = $pdo3->prepare("$query")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
					
		$openingid = $pdo3->lastInsertId();
				
			}
			
			header("Location: close-day-reception-1.php?stepOneSkip");
			exit();
				
		}
			
			
		if ($recOpened == '2' && (!isset($_GET['redo'])) && (!isset($_GET['addexpense']))) {
			
			pageStart($lang['title-closeday'], NULL, $validationScript, "pcloseday", "step2 dev-align-center", $lang['close-day-error'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

			echo  <<<EOD
<div id="scriptMsg">
 <div class='error'>
		{$lang['reception-closed']}
 </div>
</div>

EOD;
			exit();

		} else if ($recOpened == '1' && (!isset($_GET['redo'])) && (!isset($_GET['addexpense']))) {
		
			$closingOperator = getOperator($recOpenedBy);		
				
			pageStart($lang['title-closeday'], NULL, $validationScript, "pcloseday", "step2 dev-align-center", $lang['close-day-error'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
			echo  <<<EOD
	<div id="scriptMsg">
	 <div class='error'>
			{$lang['reception-inprogress-1']} $closingOperator{$lang['reception-inprogress-2']}
	 </div>
	</div>
	
EOD;
			exit();

		}
	
		if ($_SESSION['noCompare'] != 'true') {
			
			// Write to DB Closing table: RecClosing is in process
			$updateOpening = sprintf("UPDATE closing SET recOpened = '1', recOpenedBy = '%d' WHERE closingid = '%d';",
				$responsible,
				$openingid
			);
		try
		{
			$result = $pdo3->prepare("$updateOpening")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
				
			// Re-generate pageheader wtih current closing time
			$closingtime = date('Y-m-d H:i:s');
			$_SESSION['closingtime'] = $closingtime;
			
			$openingtimeView = date('d-m-Y H:i', strtotime($openingtime . "+$offsetSec seconds"));
			$closingtimeView = date('d-m-Y H:i', strtotime($closingtime . "+$offsetSec seconds"));
	
			// Determine shift duration	
			$datetime1 = new DateTime($openingtime);
			$datetime2 = new DateTime($closingtime);
			$interval = $datetime1->diff($datetime2);
			
			$noOfMonths = $interval->format('%m');
			$noOfDays = $interval->format('%d');
			$noOfHours = $interval->format('%h');
			$noOfMins = $interval->format('%i');
			
			if ($noOfMonths == 0) {
				
				if ($noOfDays == 0) {
					if ($noOfHours > 1) {
						$shiftDuration = "$noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
					} else {
						$shiftDuration = "$noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
					}
				} else if ($noOfDays == 1) {
					if ($noOfHours > 1) {
						$shiftDuration = "$noOfDays {$lang['dayLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
					} else {
						$shiftDuration = "$noOfDays {$lang['dayLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
					}
				} else {
					if ($noOfHours > 1) {
						$shiftDuration = "$noOfDays {$lang['daysLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
					} else {
						$shiftDuration = "$noOfDays {$lang['daysLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
					}
				}
				
			} else if ($noOfMonths == 1) {
				
				if ($noOfDays == 0) {
					if ($noOfHours > 1) {
						$shiftDuration = "$noOfMonths {$lang['monthLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
					} else {
						$shiftDuration = "$noOfMonths {$lang['monthLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
					}
				} else if ($noOfDays == 1) {
					if ($noOfHours > 1) {
						$shiftDuration = "$noOfMonths {$lang['monthLC']} $noOfDays {$lang['dayLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
					} else {
						$shiftDuration = "$noOfMonths {$lang['monthLC']} $noOfDays {$lang['dayLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
					}
				} else {
					if ($noOfHours > 1) {
						$shiftDuration = "$noOfMonths {$lang['monthLC']} $noOfDays {$lang['daysLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
					} else {
						$shiftDuration = "$noOfMonths {$lang['monthLC']} $noOfDays {$lang['daysLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
					}
				}
				
			} else {
				
				if ($noOfDays == 0) {
					if ($noOfHours > 1) {
						$shiftDuration = "$noOfMonths {$lang['monthsLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
					} else {
						$shiftDuration = "$noOfMonths {$lang['monthsLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
					}
				} else if ($noOfDays == 1) {
					if ($noOfHours > 1) {
						$shiftDuration = "$noOfMonths {$lang['monthsLC']} $noOfDays {$lang['dayLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
					} else {
						$shiftDuration = "$noOfMonths {$lang['monthsLC']} $noOfDays {$lang['dayLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
					}
				} else {
					if ($noOfHours > 1) {
						$shiftDuration = "$noOfMonths {$lang['monthsLC']} $noOfDays {$lang['daysLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
					} else {
						$shiftDuration = "$noOfMonths {$lang['monthsLC']} $noOfDays {$lang['daysLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
					}
				}
				
			}
				

	$pageHeader = <<<EOD
<div class="actionbox-np2">
  <div class='mainboxheader'>{$lang['close-day-details']}</div>
  <div class="boxcontent">
 <table class='purchasetable'>
	 <tr>
		   <td class='biggerFont'><strong>{$lang['day-opened']}</strong>&nbsp;
		  		{$openingtimeView}
		   </td>
		</tr>
		<tr>   
		     <td class='biggerFont'><strong>{$lang['day-closed']}</strong>&nbsp;
		   			{$closingtimeView}
		   	</td>
		</tr>
		<tr>   	
		   	 <td class='biggerFont'><strong>{$lang['day-duration']}</strong>&nbsp;
		   		{$shiftDuration}
		   	</td>
	  </tr>
 </table>
 </div>
</div>
EOD;

			$_SESSION['pageHeader'] = $pageHeader;
			
		} else {
			
			// Write to DB Closing table: RecClosing is in process
			
			$responsible = $_SESSION['user_id'];

			$updateOpening = sprintf("UPDATE closing SET recClosed = '1', recClosedBy = '%d' WHERE currentClosing = 1;",
				$responsible
			);
		try
		{
			$result = $pdo3->prepare("$updateOpening")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		}
		
	} else {
		
			// Closing only - WITH comparison
			$openingid = $_SESSION['openingid'];
			$openingtime = $_SESSION['openingtime'];
			$responsible = $_SESSION['user_id'];
		
			// Check to see if it's in progress
			$checkOpening = "SELECT recClosed, recClosedBy FROM opening WHERE openingid = $openingid";
			try
			{
				$result = $pdo3->prepare("$checkOpening");
				$result->execute();
			}
			catch (PDOException $e)
			{
					$error = 'Error fetching user: ' . $e->getMessage();
					echo $error;
					exit();
			}
		
			$row = $result->fetch();
				$recOpened = $row['recClosed'];
				$recOpenedBy = $row['recClosedBy'];
			
		if ($recOpened == '2' && (!isset($_GET['redo'])) && (!isset($_GET['addexpense']))) {
			
			pageStart($lang['title-closeday'], NULL, $validationScript, "pcloseday", "step2 dev-align-center", $lang['close-day-error'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

			echo  <<<EOD
<div id="scriptMsg">
 <div class='error'>
		{$lang['reception-opened']}
 </div>
</div>

EOD;
			exit();

		} else if ($recOpened == '1' && (!isset($_GET['redo'])) && (!isset($_GET['addexpense']))) {
		
			$closingOperator = getOperator($recOpenedBy);		
				
			pageStart($lang['title-closeday'], NULL, $validationScript, "pcloseday", "step2 dev-align-center", $lang['close-day-error'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
			echo  <<<EOD
	<div id="scriptMsg">
	 <div class='error'>
			{$lang['reception-inprogress-1']} $closingOperator{$lang['reception-inprogress-2']}
	 </div>
	</div>
	
EOD;
			exit();

		}
	
			// Write to DB Closing table: RecClosing is in process
			$updateOpening = sprintf("UPDATE opening SET recClosed = '1', recClosedBy = '%d' WHERE openingid = '%d';",
				$responsible,
				$openingid
			);
		try
		{
			$result = $pdo3->prepare("$updateOpening")->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
				
			// Re-generate pageheader wtih current closing time
			$closingtime = date('Y-m-d H:i:s');
			$_SESSION['closingtime'] = $closingtime;
			
			$openingtimeView = date('d-m-Y H:i', strtotime($openingtime . "+$offsetSec seconds"));
			$closingtimeView = date('d-m-Y H:i', strtotime($closingtime . "+$offsetSec seconds"));
	
			// Determine shift duration	
			$datetime1 = new DateTime($openingtime);
			$datetime2 = new DateTime($closingtime);
			$interval = $datetime1->diff($datetime2);
			
			$noOfMonths = $interval->format('%m');
			$noOfDays = $interval->format('%d');
			$noOfHours = $interval->format('%h');
			$noOfMins = $interval->format('%i');
			
			if ($noOfMonths == 0) {
				
				if ($noOfDays == 0) {
					if ($noOfHours > 1) {
						$shiftDuration = "$noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
					} else {
						$shiftDuration = "$noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
					}
				} else if ($noOfDays == 1) {
					if ($noOfHours > 1) {
						$shiftDuration = "$noOfDays {$lang['dayLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
					} else {
						$shiftDuration = "$noOfDays {$lang['dayLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
					}
				} else {
					if ($noOfHours > 1) {
						$shiftDuration = "$noOfDays {$lang['daysLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
					} else {
						$shiftDuration = "$noOfDays {$lang['daysLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
					}
				}
				
			} else if ($noOfMonths == 1) {
				
				if ($noOfDays == 0) {
					if ($noOfHours > 1) {
						$shiftDuration = "$noOfMonths {$lang['monthLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
					} else {
						$shiftDuration = "$noOfMonths {$lang['monthLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
					}
				} else if ($noOfDays == 1) {
					if ($noOfHours > 1) {
						$shiftDuration = "$noOfMonths {$lang['monthLC']} $noOfDays {$lang['dayLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
					} else {
						$shiftDuration = "$noOfMonths {$lang['monthLC']} $noOfDays {$lang['dayLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
					}
				} else {
					if ($noOfHours > 1) {
						$shiftDuration = "$noOfMonths {$lang['monthLC']} $noOfDays {$lang['daysLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
					} else {
						$shiftDuration = "$noOfMonths {$lang['monthLC']} $noOfDays {$lang['daysLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
					}
				}
				
			} else {
				
				if ($noOfDays == 0) {
					if ($noOfHours > 1) {
						$shiftDuration = "$noOfMonths {$lang['monthsLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
					} else {
						$shiftDuration = "$noOfMonths {$lang['monthsLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
					}
				} else if ($noOfDays == 1) {
					if ($noOfHours > 1) {
						$shiftDuration = "$noOfMonths {$lang['monthsLC']} $noOfDays {$lang['dayLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
					} else {
						$shiftDuration = "$noOfMonths {$lang['monthsLC']} $noOfDays {$lang['dayLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
					}
				} else {
					if ($noOfHours > 1) {
						$shiftDuration = "$noOfMonths {$lang['monthsLC']} $noOfDays {$lang['daysLC']} $noOfHours {$lang['hoursLC']} $noOfMins {$lang['minLC']}";
					} else {
						$shiftDuration = "$noOfMonths {$lang['monthsLC']} $noOfDays {$lang['daysLC']} $noOfHours {$lang['hourLC']} $noOfMins {$lang['minLC']}";
					}
				}
				
			}

	$pageHeader = <<<EOD
<div class="actionbox-np2">
  <div class='mainboxheader'>{$lang['close-day-details']}</div>
  <div class="boxcontent">
 <table class='purchasetable'>
	 <tr>
		   <td class='biggerFont'><strong>{$lang['day-opened']}</strong>&nbsp;
		  		{$openingtimeView}
		   </td>
		</tr>
		<tr>   
		     <td class='biggerFont'><strong>{$lang['day-closed']}</strong>&nbsp;
		   			{$closingtimeView}
		   	</td>
		</tr>
		<tr>   	
		   	 <td class='biggerFont'><strong>{$lang['day-duration']}</strong>&nbsp;
		   		{$shiftDuration}
		   	</td>
	  </tr>
 </table>
 </div>
</div>
EOD;
			$_SESSION['pageHeader'] = $pageHeader;
		
	}

	if (isset($_GET['addexpense'])) {
		$closingtime = date('Y-m-d H:i:s');
		$_SESSION['closingtime'] = $closingtime;
	} else {
		$closingtime = $_SESSION['closingtime'];
	}
	
	// Query to look up expenses
	$selectExpenses = "SELECT expenseid, registertime, userid, expensetype, expense, moneysource, other, amount, shop, comment, receipt, expensecategory FROM expenses WHERE registertime BETWEEN '$openingtime' AND '$closingtime' ORDER by registertime DESC";
		try
		{
			$resultExpenses = $pdo3->prepare("$selectExpenses");
			$resultExpenses->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		
	$deleteExpenseScript = <<<EOD
		$(document).ready(function() {
			
			$('.default').tablesorter({
				usNumberFormat: true,
				headers: {
					3: {
						sorter: "dates"
					},
					7: {
						sorter: "dates"
					}
				}
			}); 

		});
function delete_expense(expenseid) {
	if (confirm("{$lang['expense-deleteconfirm']}")) {
				window.location = "uTil/delete-expense.php?expenseid=" + expenseid + "&source=closing";
				}
}
EOD;

		
	pageStart($lang['title-closeday'], NULL, $deleteExpenseScript, "pcloseday", "dev-align-center", $lang['closeday-rec-one'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	echo $_SESSION['pageHeader'];
	
?>
<br>
<a href="new-expense.php?closeday" class="cta2"><?php echo $lang['closeday-addexpense']; ?></a>
	 <table class="default">
	  <thead>
	   <tr>
	    <th><?php echo $lang['global-time']; ?></th>
	    <th><?php echo $lang['global-category']; ?></th>
	    <th><?php echo $lang['global-expense']; ?></th>
	    <th><?php echo $lang['global-shop']; ?></th>
	    <th><?php echo $lang['global-member']; ?></th>
	    <th><?php echo $lang['global-amount']; ?></th>
	    <th><?php echo $lang['global-source']; ?></th>
	    <th><?php echo $lang['global-receipt']; ?></th>
	    <th></th>
	    <th><?php echo $lang['global-actions']; ?>?</th>
	   </tr>
	  </thead>
	  <tbody>
	  
	  <?php
		while ($expense = $resultExpenses->fetch()) {

	
	$userid = $expense['userid']; // find member
	$moneysource = $expense['moneysource'];
	$receipt = $expense['receipt'];
	$other = $expense['other'];
	$expenseid = $expense['expenseid'];
	$formattedDate = date("d M H:i", strtotime($expense['registertime'] . "+$offsetSec seconds"));
	
	if ($expense['comment'] != '') {
		
		$commentRead = "
		                <img src='images/comments.png' id='comment$expenseid' /><div id='helpBox$expenseid' class='helpBox'>{$expense['comment']}</div>
		                <script>
		                  	$('#comment$expenseid').on({
						 		'mouseover' : function() {
								 	$('#helpBox$expenseid').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBox$expenseid').css('display', 'none');
							  	}
						  	});
						</script>
		                ";
		
	} else {
		
		$commentRead = "";
		
	}
	
	if ($moneysource == 1) {
		$source = $lang['global-till'];
	} else if ($moneysource == 2) {
		$source = $lang['global-bank'];
	} else if ($moneysource == 3) {
		$source = $other;
	} else {
		$source = 'ERROR';
	}
	
	if ($receipt == 1) {
		$recClass = "";
		$receipt = $lang['global-yes'];
	} else if ($receipt == 2) {
		$recClass = "negative";
		$receipt = $lang['global-no'];
	}
	
		$userDetails = "SELECT memberno, first_name from users WHERE user_id = $userid";
		try
		{
			$result = $pdo3->prepare("$userDetails");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$user = $result->fetch();
			$member = "#" . $user['memberno'] . " - " . $user['first_name'];

		
			$expenseCat = $expense['expensecategory'];

if ($expenseCat == NULL) {
		$expenseCat = '';
	} else {
		if ($_SESSION['lang'] == 'es') {
			$selectExpenseCat = "SELECT namees FROM expensecategories WHERE categoryid = $expenseCat";
		try
		{
			$result = $pdo3->prepare("$selectExpenseCat");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		  	    $expenseCat = $row['namees'];
		} else {
			$selectExpenseCat = "SELECT nameen FROM expensecategories WHERE categoryid = $expenseCat";
		try
		{
			$result = $pdo3->prepare("$selectExpenseCat");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		  	    $expenseCat = $row['nameen'];
		}

	}
	
	
	$expense_row =	sprintf("
  	  <tr>
  	   <td class='clickableRow' href='expense.php?expenseid=%d'>%s</td>
  	   <td class='clickableRow' href='expense.php?expenseid=%d'>%s</td>
  	   <td class='clickableRow' href='expense.php?expenseid=%d'>%s</td>
  	   <td class='clickableRow' href='expense.php?expenseid=%d'>%s</td>
  	   <td class='clickableRow' href='expense.php?expenseid=%d'>%s</td>
  	   <td style='text-align: right;' class='clickableRow' href='expense.php?expenseid=%d'>%0.2f <span class='smallerfont'>{$_SESSION['currencyoperator']}</span></td>
  	   <td class='clickableRow' href='expense.php?expenseid=%d'>%s</td>
  	   <td class='%s' class='clickableRow' href='expense.php?expenseid=%d'>%s</td>
	   <td><span class='relativeitem'>$commentRead</span></td>
  	   <td style='text-align: center;'><a href='edit-expense.php?expenseid=%d'><img src='images/edit.png' height='15' title='{$lang['closeday-editexpense']}' /></a>&nbsp;&nbsp;<a href='javascript:delete_expense(%d)'><img src='images/delete.png' height='15' title='{$lang['closeday-deleteexpense']}' /></a></td>
	  </tr>",
	  $expense['expenseid'], $formattedDate, $expense['expenseid'], $expenseCat, $expense['expenseid'], $expense['expense'], $expense['expenseid'], $expense['shop'], $expense['expenseid'], $member, $expense['expenseid'], $expense['amount'], $expense['expenseid'], $source, $recClass, $expense['expenseid'], $receipt, $expense['expenseid'], $expense['expenseid'], $expense['expenseid']
	  );
	  echo $expense_row;
  }
?>

	 </tbody>
	 </table>

<form onsubmit='oneClick.disabled = true; return true;' id="registerForm" action="close-day-reception-1.php" method="POST"><br />
 <input type="hidden" name="step1" value="complete" />
 <button name='oneClick' class='cta1' type="submit" style="width: auto; padding: 7px;"><?php echo $lang['closeday-confirmexpenses']; ?></button>
</form>

<?php displayFooter();
