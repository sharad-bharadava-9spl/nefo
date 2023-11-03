<?php

	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
		
	session_start();
	$accessLevel = '3';

	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	getSettings();
	
	$shifttype = $_GET['type'];
	$shiftid = $_GET['id'];
	
	if ($_SESSION['openAndClose'] == 2) {
		
		// close day
		if ($shifttype == 1) {
			
			// Retrieve close day details
			$closingLookup = "SELECT closingtime, cashintill, tillDelta, tillComment, membershipFees, donations, expenses, moneytaken, takenduringday FROM closing WHERE closingid = $shiftid";
			
			$result = mysql_query($closingLookup)
				or handleError($lang['error-closingload'],"Error loading closing from db: " . mysql_error());
	
			$row = mysql_fetch_array($result);
				$closingid = $shiftid;
				$closingtime = $row['closingtime'];
				$tillTot = $row['cashintill'];	
				$tillDelta = $row['tillDelta'];	
				$tillComment = $row['tillComment'];	
				$membershipFees = $row['membershipFees'];	
				$donationsToday = $row['donations'];	
				$expenses = $row['expenses'];	
				$banked = $row['moneytaken'];	
				$bankedDuring = $row['takenduringday'];	
				
			// Check if previous closing exists, to decide whether to compare or not! Simply look if a closing exists before this one
			$openingLookup = "SELECT closingid, closingtime, cashintill FROM closing WHERE closingtime < '$closingtime' ORDER BY openingtime DESC LIMIT 1";
			
			$result = mysql_query($openingLookup)
				or handleError($lang['error-closingload'],"Error loading closing from db: " . mysql_error());
				
			if (mysql_num_rows($result) == 0) {
				
				// No previous closing exists. Continue without comparison
				$closingtimeView = date('d-m-Y H:i', strtotime($closingtime . "+$offsetSec seconds"));

				$pageHeader = <<<EOD
	<br /><div class="textInset">
	 <center><strong>{$lang['close-day-details']}</strong></center><br />
	 <table>
	  <tr>
	   <td style='text-align: left;'>{$lang['day-closed']}:</td>
	   <td style='text-align: left;'>{$closingtimeView}</td>
	  </tr>
	 </table>
	</div><br /><br />
EOD;
				
				pageStart($lang['dayclose-details'], NULL, $confirmLeave, "pcloseday", "step4", $lang['dayclose-details'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
				
				echo $pageHeader;
					
				echo "<h3 class='title'>{$lang['global-till']}</h3>";
	
		?>	

	   <table>
	    <tr>
		 <td><?php echo $lang['global-bankedtoday']; ?>:</td>
		 <td><input type="number" lang="nb" name="bankedNow" id="bankedNow" class="fourDigit" value="<?php echo $banked; ?>" readonly /></td>
	    </tr>
	    <tr>
		 <td><?php echo $lang['global-till']; ?>:</td>
		 <td><input type="number" lang="nb" name="tillTot" id="tillTot" class="fourDigit" value="<?php echo $tillTot; ?>" readonly /></td>
	    </tr>		 
	   </table>
	   <br />
<?php
				if ($tillComment != '') {
				
				echo "<em>{$lang['closeday-tillcomment']}:</em>
					  <br />
					  $tillComment
					  <br /><br />";
				
				} else {
					
					echo "<br /><br />";	
					
				}
	
				// Look up closing product weight
				$closingLookup = "SELECT d.purchaseid, d.category, d.weight, d.specificComment FROM closingdetails d, closing o WHERE o.closingid = $closingid AND d.closingid = o.closingid AND category < 3 ORDER by d.category ASC";
										
				$prodCloseresult = mysql_query($closingLookup)
					or handleError($lang['error-loadprodclosedetails'],"Error loading closing from db: " . mysql_error());
				
				echo "<br /><h3 class='title'>{$lang['global-flowerscaps']}</h3><div class='productboxwrap'>";
	
				while ($prodClose = mysql_fetch_array($prodCloseresult)) {
					
					$category = $prodClose['category'];
					$purchaseid = $prodClose['purchaseid'];
					$weight = $prodClose['weight'];
					$specificComment = $prodClose['specificComment'];
					
					// Look up from purchases: productid + growtype
					$selectPurchase = "SELECT productid, growType FROM purchases WHERE purchaseid = $purchaseid;";
					
					$resultPurchase = mysql_query($selectPurchase)
						or handleError($lang['error-prodprices'],"Error loading flower prices from db: " . mysql_error());
						
					$row = mysql_fetch_array($resultPurchase);
						$productid = $row['productid'];
						$growtype = $row['growType'];
					
					// Look up from flower/extract: breed2, name
					if ($category == 1) {
						
						$selectFlower = "SELECT name, breed2 FROM flower WHERE flowerid = $productid;";
						
						$resultFlower = mysql_query($selectFlower)
							or handleError($lang['error-prodprices'],"Error loading flower prices from db: " . mysql_error());
							
						$row = mysql_fetch_array($resultFlower);
							$name = $row['name'] . $row['breed2'];
	
						// Look up growtype
						$growDetails = "SELECT growtype FROM growtypes WHERE growtypeid = $growtype";
						
						$result = mysql_query($growDetails)
							or handleError($lang['error-growtypeload'],"Error loading growtype: " . mysql_error());
							
						$row = mysql_fetch_array($result);
							$growtype = $row['growtype'];
							
					} else {
						
						if ($dividerset != 'yes') {
						
							// insert divider
							$dividerset = 'yes';
							echo "</div><br /><h3 class='title'>{$lang['global-extractscaps']}</h3><div class='productboxwrap'>";
						}
	
	
						$selectExtract = "SELECT name FROM extract WHERE extractid = $productid;";
						
						$resultExtract = mysql_query($selectExtract)
							or handleError($lang['error-prodprices'],"Error loading extract prices from db: " . mysql_error());
							
						$row = mysql_fetch_array($resultExtract);
							$name = $row['name'];
						
						$growtype = '';
					}
				
					$product_row = sprintf("
		
			<div class='productbox'>
			 <h3>%s</h3>
			 %s&nbsp;<br /><br />
			 <table>
			  <tr>
			   <td>{$lang['closed-at']}:</td>
			   <td><input type='number' lang='nb' value='%0.02f' readonly style='background-color: white;' class='fourDigit' /></td>
			  </tr>
			 </table><br />
	
			 <em>{$lang['global-comment']}</em>:<br />
			 %s
			</div>",
		  $name, $growtype, $weight, $specificComment
		  );
				
		  			echo $product_row;
				
				}
	
			} else {
				
				// Previous closing DOES exist. Do comparison
				
				$row = mysql_fetch_array($result);
					$openingid = $row['closingid'];	
					$openingtime = $row['closingtime'];	
					$tillBalance = $row['cashintill'];	
	
				// Calculate estimated till
				$estimatedTill = $tillBalance + $membershipFees + $donationsToday - $expenses - $banked - $bankedDuring;
					
				if ($tillDelta == 0) {
					$tillDelta = '';
				} else if ($tillDelta > 0) {
					$cashintillDeltaColour = "color: green;";
				} else {
					$cashintillDeltaColour = "color: red;";
				}
					
				$closingtimeView = date('d-m-Y H:i', strtotime($closingtime . "+$offsetSec seconds"));
				$openingtimeView = date('d-m-Y H:i', strtotime($openingtime . "+$offsetSec seconds"));
	
				
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
	<br /><div class="textInset">
	 <center><strong>{$lang['close-day-details']}</strong></center><br />
	 <table>
	  <tr>
	   <td style='text-align: left;'>{$lang['day-opened']}:</td>
	   <td style='text-align: left;'>{$openingtimeView}</td>
	  </tr>
	  <tr>
	   <td style='text-align: left;'>{$lang['day-closed']}:</td>
	   <td style='text-align: left;'>{$closingtimeView}</td>
	  </tr>
	  <tr>
	   <td colspan='2'><br /></td>
	  </tr>
	  <tr>
	   <td style='text-align: left;'>{$lang['day-duration']}:</td>
	   <td style='text-align: left;'>{$shiftDuration}</td>
	  </tr>
	 </table>
	</div><br /><br />
EOD;
				
				pageStart($lang['dayclose-details'], NULL, $confirmLeave, "pcloseday", "step4", $lang['dayclose-details'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
				
				echo $pageHeader;
					
				echo "<h3 class='title'>{$lang['global-till']}</h3>";
	
		?>	
			<form id="registerForm" action="" method="POST" onsubmit="return checkMismatch()"><br />		 
	   <table>
	    <tr>
		 <td><?php echo $lang['closeday-tillatopening']; ?>:</td>
		 <td><input type="number" lang="nb" name="tillBalance" id="tillBalance" class="fourDigit" value="<?php echo $tillBalance; ?>" readonly /></td>
	    </tr>		 
	    <tr>
		 <td>+ <?php echo $lang['closeday-membershipfees-till']; ?>:</td>
		 <td><input type="number" lang="nb" name="membershipFees" id="membershipFees" class="green fourDigit" value="<?php echo $membershipFees; ?>" readonly /></td>
	    </tr>		 
	    <tr>
		 <td>+ <?php echo $lang['global-donations']; ?>:</td>
		 <td><input type="number" lang="nb" name="donationsToday" id="donationsToday" class="green fourDigit" value="<?php echo $donationsToday; ?>" readonly /></td>
	    </tr>		 
	    <tr>
		 <td>- <?php echo $lang['closeday-tillexpenses']; ?>:</td>
		 <td><input type="number" lang="nb" name="expenses" id="expenses" class="fourDigit red" value="<?php echo $expenses; ?>" readonly /></td>
	    </tr>		 
	    <tr>
		 <td>- <?php echo $lang['banked-now']; ?>:</td>
		 <td><input type="number" lang="nb" name="bankedNow" id="bankedNow" class="fourDigit red" value="<?php echo $banked; ?>" readonly /></td>
	    </tr>
	    <tr>
		 <td>- <?php echo $lang['banked-during-day']; ?>:</td>
		 <td><input type="number" lang="nb" name="bankedDuring" id="bankedDuring" class="fourDigit red" value="<?php echo $bankedDuring; ?>" readonly /></td>
	    </tr>
	    <tr>
		 <td><?php echo $lang['closeday-estimatedtill']; ?>:</td>
		 <td><input type="number" lang="nb" name="estimatedTill" id="estimatedTill" class="fourDigit" value="<?php echo $estimatedTill; ?>" readonly /></td>
	    </tr>		 
	    <tr>
		 <td><?php echo $lang['closeday-yourcount']; ?>:</td>
		 <td><input type="number" lang="nb" name="tillTot" id="tillTot" class="fourDigit" value="<?php echo $tillTot; ?>" readonly /></td>
	    </tr>		 
	    <tr>
		 <td><strong><?php echo $lang['global-delta']; ?>:</strong></td>
		 <td><strong><input type="number" lang="nb" name="tillDelta" id="tillDelta" class="fourDigit" value="<?php echo number_format($tillDelta,2,'.',''); ?>" readonly /></strong></td>
	    </tr>		 
	   </table>
	   <br />
<?php
				if ($tillComment != '') {
				
				echo "<em>{$lang['closeday-tillcomment']}:</em>
					  <br />
					  $tillComment
					  <br /><br />";
				
				} else {
					
					echo "<br /><br />";	
					
				}
				
				// Look up closing product weight
				$closingLookup = "SELECT d.purchaseid, d.category, d.weightToday, d.weight, d.addedToday, d.soldToday, d.takeoutsToday, d.weightEst, d.weightDelta, d.specificComment FROM closingdetails d, closing o WHERE o.closingid = $closingid AND d.closingid = o.closingid AND category < 3 ORDER by d.category ASC";
				
										
				$prodCloseresult = mysql_query($closingLookup)
					or handleError($lang['error-loadprodclosedetails'],"Error loading closing from db: " . mysql_error());
				
					
				echo "<br /><h3 class='title'>{$lang['global-flowerscaps']}</h3><div class='productboxwrap'>";
	
				while ($prodClose = mysql_fetch_array($prodCloseresult)) {
					
					$category = $prodClose['category'];
					$purchaseid = $prodClose['purchaseid'];
					$weight = $prodClose['weight'];
					$addedToday = $prodClose['addedToday'];
					$soldToday = $prodClose['soldToday'];
					$takeoutsToday = $prodClose['takeoutsToday'];
					$weightEst = $prodClose['weightEst'];
					$weightDelta = $prodClose['weightDelta'];
					$specificComment = $prodClose['specificComment'];
					$openingWeight = $prodClose['weightToday'];
					
					// Look up from purchases: productid + growtype
					$selectPurchase = "SELECT productid, growType FROM purchases WHERE purchaseid = '$purchaseid';";
					
					$resultPurchase = mysql_query($selectPurchase)
						or handleError($lang['error-prodprices'],"Error loading flower prices from db: " . mysql_error());
						
					$row = mysql_fetch_array($resultPurchase);
						$productid = $row['productid'];
						$growtype = $row['growType'];
					
					// Look up from flower/extract: breed2, name
					if ($category == 1) {
						
						$selectFlower = "SELECT name, breed2 FROM flower WHERE flowerid = '$productid';";
						
						$resultFlower = mysql_query($selectFlower)
							or handleError($lang['error-prodprices'],"Error loading flower prices from db: " . mysql_error());
							
						$row = mysql_fetch_array($resultFlower);
							$name = $row['name'] . $row['breed2'];
	
					// Look up growtype
					$growDetails = "SELECT growtype FROM growtypes WHERE growtypeid = '$growtype'";
					
					$result = mysql_query($growDetails)
						or handleError($lang['error-growtypeload'],"Error loading growtype: " . mysql_error());
						
					$row = mysql_fetch_array($result);
						$growtype = $row['growtype'];
							
					} else {
						
						if ($dividerset != 'yes') {
						
							// insert divider
							$dividerset = 'yes';
							echo "</div><br /><h3 class='title'>{$lang['global-extractscaps']}</h3><div class='productboxwrap'>";
						}
	
	
						$selectExtract = "SELECT name FROM extract WHERE extractid = '$productid';";
						
						$resultExtract = mysql_query($selectExtract)
							or handleError($lang['error-prodprices'],"Error loading extract prices from db: " . mysql_error());
							
						$row = mysql_fetch_array($resultExtract);
							$name = $row['name'];
						
						$growtype = '';
					}
					
					
				
									
					if ($weightDelta == 0) {
						$weightDelta = '';
						$weightDeltaColour = "";
					} else if ($weightDelta > 0) {
						$weightDeltaColour = "color: green;";
					} else {
						$weightDeltaColour = "color: red;";
					}
					
					$product_row = sprintf("
		
			<div class='productbox'>
			 <h3>%s</h3>
			 %s&nbsp;<br />
			 <table>
			  <tr>		  
			   <td>{$lang['opened-at']}:</td>
			   <td><input type='number' lang='nb' value='%0.02f' readonly style='background-color: white;' class='fourDigit' /></td>
			  </tr>
			  <tr>
			   <td>+ {$lang['closeday-added']}:</td>
			   <td><input type='number' lang='nb' value='%0.02f' readonly class='fourDigit green' /></td>
			  </tr>
			  <tr>
			   <td>- {$lang['closeday-dispensed']}:</td>
			   <td><input type='number' lang='nb' value='%0.02f' readonly class='fourDigit red' /></td>
			  </tr>
			  <tr>
			   <td>- {$lang['closeday-takeouts']}:</td>
			   <td><input type='number' lang='nb' value='%0.02f' readonly class='fourDigit red' /></td>
			  </tr>
			  <tr>
			   <td>{$lang['closeday-estweight']}:</td>
			   <td><input type='number' lang='nb' value='%0.02f' readonly style='background-color: white;' class='fourDigit' /></td>
			  </tr>
			  <tr>
			   <td>{$lang['closed-at']}:</td>
			   <td><input type='number' lang='nb' value='%0.02f' readonly style='background-color: white;' class='fourDigit' /></td>
			  </tr>
			  <tr>
			   <td>{$lang['global-delta']}:</td>
			   <td><input type='text' value='%s' readonly style='background-color: white; text-align: right; %s' class='fourDigit' /></td>
			  </tr>
			 </table><br />
	
			 <em>{$lang['global-comment']}</em>:<br />
			 %s
			</div>",
		  $name, $growtype, $openingWeight, $addedToday, $soldToday, $takeoutsToday, $weightEst, $weight, $weightDelta, $weightDeltaColour, $specificComment
		  );
				
		  			echo $product_row;
				
				}
				
			}
				
		} else if ($shifttype == 2) {
			
			
			
		}
		
	}
