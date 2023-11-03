<?php
	
	require_once 'cOnfig/connection.php';
	require_once 'cOnfig/view.php';
	require_once 'cOnfig/authenticate.php';
	require_once 'cOnfig/languages/common.php';
	require_once 'googleConfig.php';
	
	session_start();
	$accessLevel = '3';
	
	// Authenticate & authorize
	authorizeUser($accessLevel);
	
	$domain = $_SESSION['domain'];
	// Display confirmation to reopen
	if (isset($_GET['reopen'])) {
		
		$_SESSION['errorMessage'] = $lang['reopen-confirm'];
		pageStart($lang['title-product'], NULL, $deleteUserScript, "ppurchase", "admin", "Product", $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
		$purchaseid = $_GET['reopen'];
		
		echo <<<EOD
<a href="uTil/bar-reopen-purchase.php?purchaseid=$purchaseid" class="cta">{$lang['pur-reopen']}</a> <a href="new-purchase.php" class="cta">{$lang['newpurchase']}</a>
EOD;
		
	} else {
	
	
	// Does purchase ID exist?
	if (!$_GET['purchaseid']) {
		echo $lang['error-nopurchselected'];
		exit();
	} else  {
		$purchaseid = $_GET['purchaseid'];
	}

	// Query to look for purchase
	$purchaseDetails = "SELECT category, productid, purchaseDate, purchasePrice, salesPrice, purchaseQuantity, adminComment, estClosing, closingComment, closedAt, inMenu, closingDate FROM b_purchases WHERE purchaseid = $purchaseid";
	try
	{
		$result = $pdo3->prepare("$purchaseDetails");
		$result->execute();
	}
	catch (PDOException $e)
	{
			$error = 'Error fetching user: ' . $e->getMessage();
			echo $error;
			exit();
	}
	

	$row = $result->fetch();
		$category = $row['category'];
		$productid = $row['productid'];
		$purchaseDate = $row['purchaseDate'];
		$purchasePrice = $row['purchasePrice'];
		$salesPrice = $row['salesPrice'];
		$purchaseQuantity = $row['purchaseQuantity'];
		$adminComment = $row['adminComment']; // Purchase comment, really
		$estClosing = $row['estClosing'];
		$closingComment = $row['closingComment']; // Only active when product closed (if even then)
		$closedAt = $row['closedAt'];
		$closingDate = date("d M H:i", strtotime($row['closingDate'] . "+$offsetSec seconds"));
		$inMenu = $row['inMenu'];
		$perGramPurchase = number_format($purchasePrice,2);
		$perGramSale = number_format($salesPrice,2);
		$purchasePriceTotal = number_format($purchasePrice * $purchaseQuantity,2);
		$salesPriceTotal = number_format($salesPrice * $purchaseQuantity,2);
		
	// Find photo extension
	$extFind = "SELECT photoExt from b_products WHERE productid = $productid";
		try
		{
			$result = $pdo3->prepare("$extFind");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$photoExt = $row['photoExt'];
		
		
	if ($inMenu == 1) {
		$inMenu = "{$lang['global-yescaps']}";
		$menuClass = 'yellow';
	} else {
		$inMenu = "{$lang['global-nocaps']}";
		$menuClass = 'negative';
	}
	

			
		// Query to look for category
		$categoryDetails = "SELECT name FROM b_categories WHERE id = $category";
		try
		{
			$result = $pdo3->prepare("$categoryDetails");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$categoryName = $row['name'];
			
		// Query to look for product
		$selectProducts = "SELECT name from b_products WHERE productid = $productid";
		try
		{
			$result = $pdo3->prepare("$selectProducts");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$name = $row['name'];
	



	$deleteUserScript = <<<EOD
	
	 $(document).ready(function() {
		// KONSTANT CODE UPDATE BEGIN	 
			$( function() {
				$( "#tabs" ).tabs();
			  } );
		// KONSTANT CODE UPDATE END	  	    
	  $( function() {
	    $( "#datepicker" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });
	  });
	  
	  $( function() {
	    $( "#datepicker2" ).datepicker({
			dateFormat: "dd-mm-yy"
	    });
	  });
	  
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
     
function delete_movement(movementid, purchaseid) {
	if (confirm("{$lang['confirm-deletemovement']}")) {
				window.location = "uTil/bar-delete-movement.php?movement_id=" + movementid + "&purchaseid=" + purchaseid;
				}
}
function delete_purchase(purchaseid) {
	if (confirm("{$lang['confirm-deletepurchase']}")) {
				window.location = "uTil/bar-delete-purchase.php?purchaseid=" + purchaseid;
				}
}
EOD;


		pageStart($lang['global-purchase'], NULL, $deleteUserScript, "ppurchase", "admin", $lang['global-purchase'], $_SESSION['successMessage'], $_SESSION['errorMessage']); 
	
	// Check if 'entre fechas' was utilised
	if (isset($_POST['untilDate'])) {
		
		$fromDate = date("Y-m-d", strtotime($_POST['fromDate']));
		$untilDate = date("Y-m-d", strtotime($_POST['untilDate']));
		
		$timeLimit = "AND DATE(s.saletime) BETWEEN DATE('$fromDate') AND DATE('$untilDate')";
		$timeLimit2 = "AND DATE(movementtime) BETWEEN DATE('$fromDate') AND DATE('$untilDate')";
			
	}
	
  	// Look up sales
	$selectSales = "SELECT SUM(d.quantity) FROM b_salesdetails d, b_sales s WHERE s.saleid = d.saleid AND d.purchaseid = $purchaseid AND d.amount <> 0 $timeLimit";
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
		$sales = $row['SUM(d.quantity)'];
		
  	// Look up gifts
	$selectSales = "SELECT SUM(d.quantity) FROM b_salesdetails d, b_sales s WHERE s.saleid = d.saleid AND d.purchaseid = $purchaseid AND d.amount = 0 $timeLimit";
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
		$gifts = $row['SUM(d.quantity)'];

		
	// Calculate reloads
	$selectReloads = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 1 AND movementTypeid = 1";
		try
		{
			$result = $pdo3->prepare("$selectReloads");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$totalReloads = $row['SUM(quantity)'];
			
	if ($totalReloads == '') {
		
		$totalReloads = 0;
		
	}
	
	
	// Calculate added product (from shake)
	$selectShake = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 1 AND movementTypeid = 22";
		try
		{
			$result = $pdo3->prepare("$selectShake");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$shakeAdded = $row['SUM(quantity)'];
			

	
	// Calculate total purchased
	$totalPurchased = $totalReloads + $purchaseQuantity + $shakeAdded;
	
	$soldPercentage = ($sales / $totalPurchased) * 100;
	$soldPercentageReal = ($realSales / $totalPurchased) * 100;
	$giftedPercentage = ($gifts / $totalPurchased) * 100;
	$giftedPercentageReal = ($realgifts / $totalPurchased) * 100;
	$totalPercentage = (($sales + $gifts) / $totalPurchased) * 100;
	$totalPercentageReal = (($realSales + $realgifts) / $totalPurchased) * 100;
	
	// Select takeouts
	$selectTakeouts = "SELECT movementtypeid, SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 2 $timeLimit2 GROUP BY movementtypeid";
		try
		{
			$result = $pdo3->prepare("$selectTakeouts");
			$result->execute();
			$data = $result->fetchAll();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
			
		

			
		
	// Calculate what's in Internal stash
	$selectStashedInt = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 5 OR movementTypeid = 18)";
		try
		{
			$result = $pdo3->prepare("$selectStashedInt");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$stashedInt = $row['SUM(quantity)'];
		
	$selectUnStashedInt = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 12 OR movementTypeid = 17)";
		try
		{
			$result = $pdo3->prepare("$selectUnStashedInt");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$unStashedInt = $row['SUM(quantity)'];

			
		$inStashInt = $stashedInt - $unStashedInt;
		$inStashInt = $inStashInt;
		$_SESSION['BarinternalStash'] = $inStashInt;


	// Calculate what's in External stash
	$selectStashedExt = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 6 OR movementTypeid = 20)";
		try
		{
			$result = $pdo3->prepare("$selectStashedExt");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$stashedExt = $row['SUM(quantity)'];
		
	$selectUnStashedExt = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 2 OR movementTypeid = 19)";
		try
		{
			$result = $pdo3->prepare("$selectUnStashedExt");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$unStashedExt = $row['SUM(quantity)'];

			
		$inStashExt = $stashedExt - $unStashedExt;
		$inStashExt = $inStashExt;
		$_SESSION['BarexternalStash'] = $inStashExt;

			
	$selectDisplay = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 9)";
		try
		{
			$result = $pdo3->prepare("$selectDisplay");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$inDisplay = $row['SUM(quantity)'];
			
	$selectRemovedFromDisplay = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 3)";
		try
		{
			$result = $pdo3->prepare("$selectRemovedFromDisplay");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$removedFromDisplay = $row['SUM(quantity)'];
			
			$inDisplay = $inDisplay - $removedFromDisplay;

			// display internal
			
	$selectPermAdditions = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 1 AND (movementTypeid = 3 OR movementTypeid = 10)";
		try
		{
			$result = $pdo3->prepare("$selectPermAdditions");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$permAdditions = $row['SUM(quantity)'];
	
	$selectPermRemovals = "SELECT SUM(quantity) FROM b_productmovements WHERE purchaseid = $purchaseid AND type = 2 AND (movementTypeid = 4 OR movementTypeid = 7 OR movementTypeid = 8 OR movementTypeid = 9 OR movementTypeid = 11 OR movementTypeid = 13 OR movementTypeid = 14 OR movementTypeid = 15 OR movementTypeid = 16)";
		try
		{
			$result = $pdo3->prepare("$selectPermRemovals");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$permRemovals = $row['SUM(quantity)'];
	
	$inStash = $inStashInt + $inStashExt;
	$inStashReal = $inStash;
	
	if ($inStash < 0) {
		$inStash = 0;
	}
	
	$estStock = $purchaseQuantity + $totalReloads + $permAdditions - $sales - $gifts - $permRemovals - $inStash + $shakeAdded;
	
	$totalProduct = $estStock + $inStashReal + $inDisplay;
	$_SESSION['BartotalProduct'] = $totalProduct;



?>
<center>
	<div id='productoverview'>
	  <a href='bar-change-product-image.php?productid=<?php echo $productid; ?>&purchaseid=<?php echo $purchaseid; ?>&purchase'><img src='<?php echo $google_root ?>images/_<?php echo $domain; ?>/bar-products/<?php echo $productid . "." . $photoExt; ?>' height='70' style='display: inline; vertical-align: middle;' /></a>
	 <table style="display: inline-block; vertical-align: top;">
	  <tr>
	   <td><?php echo $lang['global-category']; ?>:</td>
	   <td class='yellow fat'><?php echo $categoryName; ?></td>
	  </tr>
	  <tr>
	   <td><?php echo $lang['global-product']; ?>:</td>
	   <td class='yellow fat'><?php echo $name; ?></td>
	  </tr>
	 </table>
	</div>
	<br>
	
	<a href="bar-purchases.php" class="cta1"><span class="ctatext">&laquo; <?php echo $lang['purchases']; ?></span></a>
	<a href="bar-edit-purchase.php?purchaseid=<?php echo $purchaseid;?>" class="cta1"><span class="ctatext"><?php echo $lang['global-edit']; ?></span></a>
	<?php
		if ($closedAt == NULL) {
			echo "<a href='bar-add-remove-options.php?purchaseid=$purchaseid' class='cta1'><span class='ctatext'>{$lang['pur-addremove']}</span></a>";
			echo "<a href='bar-close-purchase-2.php?purchaseid=$purchaseid' class='cta1'><span class='ctatext'>{$lang['pur-close']}</span></a>";
		} else {
			echo "<a href='?reopen=$purchaseid' class='cta1'><span class='ctatext'>{$lang['pur-reopen']}</span></a>";
		}
	?>
	<a href="javascript:delete_purchase(<?php echo $purchaseid; ?>)" class="cta1" style="background-color: red;"><span class="ctatext" style="color: yellow";><?php echo $lang['global-deletecaps']; ?></span></a>

	



	<br />

	
	 <div class='actionbox-np2'>
		  <div class="mainboxheader"><center><?php echo $lang['global-quantity']; ?></center></div>
		  	<div class="boxcontent">
			  <table class="purchasetable">
				   <tr>
				    <td class="biggerFont left"><?php echo $lang['add-amountpurchased']; ?>
				     <span class="purchaseNumber"><?php echo $purchaseQuantity; ?> u</span>
					</td>
				   </tr>
				   <tr style="border-bottom: 1px solid #eee;">
				    <td class="biggerFont left"><?php echo $lang['closeproduct-reloads']; ?>
				   		<span class="purchaseNumber"><?php echo number_format($totalReloads,2); ?> u</span>
				    </td>
				   </tr>
				   <tr style="border-bottom: 2px solid #eee;">
				    <td class="biggerFont left"><?php echo $lang['add-totalpurchased']; ?>
				   		<span class="purchaseNumber"><?php echo number_format($totalPurchased,2); ?> u</span>
				   	</td>
				   </tr>
				   
				<?php
					if ($closedAt != NULL) {
						if ($closedAt < 0) {
							$closeClass = 'negative';
						}
						
						
						echo "
				   <tr>
				    <td class='biggerFont left'>{$lang['pur-closedat']}
				    	<span class='purchaseNumber $closeClass'>$closedAt g</span><br />$closingDate
				    </td>
				   </tr>";

					} else {
						echo "
				   <tr>
				    <td class='biggerFont left' colspan='2' style='text-align: center; padding-top: 8px; font-size: 16px;'>{$lang['pur-inmenu']}: 
				    	<span class='purchaseNumber $menuClass'><strong>$inMenu</strong></span>.
				    </td>
				   </tr>";
					}

				?>


			  </table>
			</div>
		<?php	if ($closingComment != NULL) {
				echo "
			{$lang['pur-closecomment']}:<br />
		    <em>$closingComment</em>";
			}
		?>

	 </div>
	 <div style="display: inline-block;">
		<div class='actionbox-np2'>
		  <div class="mainboxheader"><center><?php echo $lang['add-purchaseprice']; ?></center></div>
			  <div class="boxcontent">
				  <table class="purchasetable">
				   <tr>
				    <td class="left"><?php echo $lang['add-perunit']; ?>
				    	<span class="purchaseNumber"><?php echo number_format($perGramPurchase,2); ?> <?php echo $_SESSION['currencyoperator'] ?></span>
				    </td>
				   </tr>
				   <tr>
				    <td class="left"><?php echo $lang['add-total']; ?>
				    	<span class="purchaseNumber"><?php echo $purchasePriceTotal; ?> <?php echo $_SESSION['currencyoperator'] ?></span>
				    </td>
				   </tr>
				  </table>
				</div>
		 </div>
		 <br>
		<div class='actionbox-np2'>
		  <div class="mainboxheader"><center><?php echo $lang['add-dispenseprice']; ?></center></div>
			   <div class="boxcontent">
					  <table class="purchasetable">
					   <tr>
					    <td><?php echo $lang['add-perunit']; ?>
					    	<span class="purchaseNumber"><?php echo number_format($perGramSale,2); ?> <?php echo $_SESSION['currencyoperator'] ?></span>
					    </td>
					   </tr>
					   <tr>
					    <td><?php echo $lang['add-total']; ?>
					    <span class="purchaseNumber"><?php echo $salesPriceTotal; ?> <?php echo $_SESSION['currencyoperator'] ?></span>
					   </tr>
					  </table>
				 </div> 
		 </div>
	 </div>




		<div class='actionbox-np2'>
			<div class="mainboxheader"><center><?php echo $lang['admin-statistics']; ?> total</center></div>
			<div class="boxcontent">
					  <form action='' method='POST'>
						<?php
							if (isset($_POST['fromDate'])) {
								
								echo <<<EOD
						   <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="fiveDigit defaultinput" value="{$_POST['fromDate']}" />&nbsp;&nbsp;
						   <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="fiveDigit defaultinput" value="{$_POST['untilDate']}" onchange='this.form.submit()' />
						EOD;
							} else {
								
								echo <<<EOD
						   <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="fiveDigit defaultinput" placeholder="{$lang['from-date']}" />&nbsp;&nbsp;
						   <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="fiveDigit defaultinput" placeholder="{$lang['to-date']}" onchange='this.form.submit()' />
						EOD;

							}
						?>

						<br /><br />
					  </form>
					  <table class="defaultpad">
						   <tr>
						    <td class="left"><?php echo $lang['closeday-dispensed']; ?></td>
						    <td class="bigNumber right"><?php echo number_format($sales,2); ?> g</td>
						    <td class="bigNumber right"><?php echo number_format($soldPercentage,1); ?> %</td>
						   </tr>
						   <tr style="border-bottom: 1px solid #bbb;">
						    <td class="left"><?php echo $lang['gifted']; ?></td>
						    <td class="bigNumber right"><?php echo number_format($gifts,2); ?> g</td>
						    <td class="bigNumber right"><?php echo number_format($giftedPercentage,1); ?> %</td>
						   </tr>
						   <tr>
						    <td class="left"><strong><?php echo $lang['add-total']; ?></strong></td>
						    <td class="bigNumber right"><strong><?php echo number_format($gifts + $sales,2); ?> g</strong></td>
						    <td class="bigNumber right"><?php echo number_format($totalPercentage,1); ?> %</td>
						   </tr>
						   <tr>
						    <td colspan="3">&nbsp;</td>
						   </tr>
						<?php 
								

							if ($data) {
								
								echo <<<EOD
								
						   <tr>
						    <td colspan="3"><strong>{$lang['closeday-takeouts']}</strong></td>
						   </tr>

						EOD;

								foreach ($data as $movement) {
										
									$movementtypeid = $movement['movementtypeid'];
									$movementQuantity = number_format($movement['SUM(quantity)'],2);
									$movementPercentage = number_format((($movementQuantity / $totalPurchased) * 100),1);
								
									// Look up movementtype name
									if ($_SESSION['lang'] == 'en') {
										$selMovement = "SELECT movementNameen AS name from productmovementtypes WHERE movementTypeid = $movementtypeid";
									} else {
										$selMovement = "SELECT movementNamees AS name from productmovementtypes WHERE movementTypeid = $movementtypeid";
									}
								try
								{
									$movementResult = $pdo3->prepare("$selMovement");
									$movementResult->execute();
								}
								catch (PDOException $e)
								{
										$error = 'Error fetching user: ' . $e->getMessage();
										echo $error;
										exit();
								}
							
								$rowM = $movementResult->fetch();
										$movementName = $rowM['name'];
										
									echo <<<EOD
										
						   <tr>
						    <td class="left">$movementName</td>
						    <td class="bigNumber right">$movementQuantity g</td>
						    <td class="bigNumber right">$movementPercentage %</td>
						   </tr>
						   
						EOD;
									
												
								}
								
							}
						?>
					  </table>
			
		 	</div>
		 </div>
		 <br>
	 
	
	 
	 <div class='actionbox-np2'>
		  <div class="mainboxheader"><center><?php echo $lang['pur-eststock']; ?></center> <a href="stock.php" class="smallerFont"></a></div>
			  <div class="boxcontent">
				  <table class="purchasetable">
					   <tr>
					    <td class="left"><?php echo $lang['global-stock']; ?>
					    	<span class="purchaseNumber"><?php echo number_format($estStock,2); ?> u</span>
					    </td>
					   </tr>
					   <tr>
					    <td class="left"> <?php echo $lang['pur-displayjar']; ?>
					    	<span class="purchaseNumber"><?php echo number_format($inDisplay,2); ?> u</span>
						</td>
					   </tr>
					   <tr>
					    <td class="left"><?php echo $lang['intstash']; ?>
					    	<span class="purchaseNumber"><?php echo number_format($inStashInt,2); ?> u</span>
						</td>
					   </tr>
					   <tr style="border-bottom: 1px solid yellow;">
					    <td class="left"><?php echo $lang['extstash']; ?>
					    	<span class="purchaseNumber"><?php echo number_format($inStashExt,2); ?> u</span>
						</td>
					   </tr>
					   <tr>
					    <td class="left"><strong><?php echo $lang['global-total']; ?>:</strong>
					    	<span class="purchaseNumber"><?php echo number_format($totalProduct,2); ?> u</span>
						</td>
					   </tr>
				  </table>
		   </div>
	   </div>

  <!-- // KONSTANT CODE UPDATE BEGIN -->
<div class='actionbox-np2'>
    <div  class="mainboxheader"><?php echo $lang['volume-discounts']; ?></div>
    <div class="boxcontent">
	    <table class="purchasetable">
	    <?php 
	    
	                    
	        $volumeDiscounts = "SELECT * FROM b_volume_discounts WHERE purchaseid = $purchaseid";
	        try
	        {
	            $result = $pdo3->prepare("$volumeDiscounts");
	            $result->execute();
	        }
	        catch (PDOException $e)
	        {
	            $error = 'Error fetching user: ' . $e->getMessage();
	            echo $error;
	            exit();
	        }        
	        while ($rs = $result->fetch()) { ?>
	        <tr>
	            <td class="left"><?php echo $lang['units-grams']; ?>
	            <span class="bigNumber purchaseNumber"><?php echo $rs['units']; ?></span> </td>
	        </tr>
	        <tr>    
	            <td class="left"><?php echo $lang['add-total']; ?>
	            <span class="bigNumber purchaseNumber"><?php echo $rs['amount']; ?></span> <?php echo $_SESSION['currencyoperator'] ?></td>
	        </tr>

	    <?php } ?>      
	    </table>
	</div>
</div>
<br />
  <!-- // KONSTANT CODE UPDATE END -->
	 
	   
	
</center>
<center>
<div class='actionbox-np2'>

  <div class="mainboxheader"><center><?php echo $lang['add-movements']; ?></center></div>
  	<div class="boxcontent">
  		<div id="tabs">
		  <center>
		   <a href="?purchaseid=<?php echo $purchaseid; ?>&summary" class='usergrouptext2'><?php echo $lang['summary']; ?></a>
		<!--
		   <a href="?purchaseid=<?php echo $purchaseid; ?>&week" class='usergrouptext2'>Weekly</a>
		   <a href="?purchaseid=<?php echo $purchaseid; ?>&month" class='usergrouptext2'>Monthly</a>
		-->
		   <a href="?purchaseid=<?php echo $purchaseid; ?>&all" class='usergrouptext2'><?php echo $lang['all']; ?></a>
		  </center>
		  <br />
		<ul>
			<li><a href="#tabs-1"><?php echo $lang['dispensary-movements']; ?></a></li>
			<li><a href="#tabs-2"><?php echo $lang['internal-stash-movements'] ?></a></li>
			<li><a href="#tabs-3"><?php echo $lang['external-stash-movements']; ?></a></li>
		</ul>
	<div id ="tabs-1">   
		<?php 

			if (isset($_GET['summary'])) {
				
				// Query to look up movements
				$selectMovements = "SELECT type, SUM(quantity), movementTypeid FROM b_productmovements WHERE purchaseid = $purchaseid GROUP BY movementTypeid ORDER by type DESC, movementTypeid ASC";

				
			} else {
				
				// Query to look up movements
				$selectMovements = "SELECT movementid, movementtime, type, purchaseid, quantity, movementTypeid, comment, price FROM b_productmovements WHERE purchaseid = $purchaseid ORDER by movementtime ASC";

			}
				try
				{
					$results = $pdo3->prepare("$selectMovements");
					$results->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			
					
		?>

			 <table class="defaultnobg">
			  <tbody>
			  
		  	  <tr class='green'>
		  	   <td><?php echo date("d M H:i", strtotime($purchaseDate . "+$offsetSec seconds")); ?></td>
		  	   <td class='left'><?php echo $lang['pur-origpurchase']; ?></td>
		  	   <td style='text-align: right;'><?php echo number_format($purchaseQuantity,2); ?> u</td>
		  	   <td style='text-align: right;'><?php echo number_format($purchasePrice,2); ?> <?php echo $_SESSION['currencyoperator'] ?> / u</td>
		  	   <td></td>
		  	   <td></td>
			  </tr>
			  
			  <?php

				while ($movement = $results->fetch()) {
				
			$movementid = $movement['movementid'];
			$movementtime = $movement['movementtime'];
			$type = $movement['type'];
			$purchaseid = $movement['purchaseid'];
			$price = $movement['price'];
			$quantity = $movement['quantity'];

			$movementTypeid = $movement['movementTypeid'];
			
			if ($movementTypeid == 1) {
				$editOrNot = "<a href='edit-movement.php?movementid=$movementid'><img src='images/edit.png' height='15' title='Edit' /></a>&nbsp;&nbsp;";
				$ppg = $price / $quantity . " ".$_SESSION['currencyoperator']." / u";
			} else {
				$editOrNot = "";
				$ppg = '';
			}
			
			if (isset($_GET['summary'])) {
				$quantity = $movement['SUM(quantity)'];
				$formattedDate = "";
			} else {
				$quantity = $movement['quantity'];
				$formattedDate = date("d M H:i", strtotime($movement['movementtime'] . "+$offsetSec seconds"));
			}

			
			if ($movementTypeid == 17 || $movementTypeid == 18 || $movementTypeid == 19 || $movementTypeid == 20 ) {
				$rowclass = " class='grey' ";
			} else if ($type == 1) {
				$rowclass = " class='green' ";
			} else if ($type == 2) {
				$rowclass = " class='red' ";
			} else {
				$rowclass = "";
			}
			
			if ($movement['comment'] != '') {
				
				$commentRead = "
				                <img src='images/comments.png' id='comment$movementid' /><div id='helpBox$movementid' class='helpBox'>{$movement['comment']}</div>
				                <script>
				                  	$('#comment$movementid').on({
								 		'mouseover' : function() {
										 	$('#helpBox$movementid').css('display', 'block');
								  		},
								  		'mouseout' : function() {
										 	$('#helpBox$movementid').css('display', 'none');
									  	}
								  	});
								</script>
				                ";
				
			} else {
				
				$commentRead = "";
				
			}
			
			
			// Look up movement name
		      	if ($_SESSION['lang'] == 'es') {
					$selectMovementName = "SELECT movementNamees FROM productmovementtypes WHERE movementTypeid = $movementTypeid";
					try
					{
						$result = $pdo3->prepare("$selectMovementName");
						$result->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
				
					$row = $result->fetch();
						$movementName = $row['movementNamees'];
				} else {
					$selectMovementName = "SELECT movementNameen FROM productmovementtypes WHERE movementTypeid = $movementTypeid";
					try
					{
						$result = $pdo3->prepare("$selectMovementName");
						$result->execute();
					}
					catch (PDOException $e)
					{
							$error = 'Error fetching user: ' . $e->getMessage();
							echo $error;
							exit();
					}
				
					$row = $result->fetch();
						$movementName = $row['movementNameen'];
				}

			
			$movement_row =	sprintf("
		  	  <tr%s>
		  	   <td>%s</td>
		  	   <td class='left'>%s</td>
		  	   <td style='text-align: right;'>%0.02f u</td>
		  	   <td style='text-align: right;'>$ppg</td>
		  	   <td class='centered'><span class='relativeitem'>%s</span></td>
		  	   <td class='right'>$editOrNot<a href='javascript:delete_movement(%d,%d)'><img src='images/delete.png' height='15' title='Delete movement' /></a></td>
			  </tr>",
			  $rowclass, $formattedDate, $movementName, $quantity, $commentRead, $movementid, $purchaseid
			  );
			  echo $movement_row;
		  }
		?>

			 </tbody>
			 </table>
		</div>
		<div id ="tabs-2">
					<?php 

						// Query to look up internal stash movements
						$selectinternalMovements = "SELECT movementid, movementtime, type, purchaseid, quantity, movementTypeid, comment, price FROM b_productmovements WHERE purchaseid = ".$_GET['purchaseid']." AND stashMovementType = 1 ORDER by movementtime ASC";
					
						try
						{
							$results = $pdo3->prepare("$selectinternalMovements");
							$results->execute();
						}
						catch (PDOException $e)
						{
								$error = 'Error fetching user: ' . $e->getMessage();
								echo $error;
								exit();
						}
					
							
				?>

					<table class="defaultnobg">
						<tbody>
					
							<?php

								while ($movement = $results->fetch()) {
								
										$movementid = $movement['movementid'];
										$movementtime = $movement['movementtime'];
										$type = $movement['type'];
										$purchaseid = $movement['purchaseid'];
										$price = $movement['price'];
										$quantity = $movement['quantity'];

										$movementTypeid = $movement['movementTypeid'];
										
										if ($movementTypeid == 1) {
											$editOrNot = "<a href='edit-movement.php?movementid=$movementid'><img src='images/edit.png' height='15' title='Edit' /></a>&nbsp;&nbsp;";
											$ppg = $price / $quantity . " ".$_SESSION['currencyoperator']." / u";
										} else {
											$editOrNot = "";
											$ppg = '';
										}
										
										if (isset($_GET['summary'])) {
											$quantity = $movement['SUM(quantity)'];
											$formattedDate = "";
										} else {
											$quantity = $movement['quantity'];
											$formattedDate = date("d M H:i", strtotime($movement['movementtime'] . "+$offsetSec seconds"));
										}

										
										if ($movementTypeid == 17 || $movementTypeid == 18 || $movementTypeid == 19 || $movementTypeid == 20 ) {
											$rowclass = " class='grey' ";
										} else if ($type == 1) {
											$rowclass = " class='red' ";
										} else if ($type == 2) {
											$rowclass = " class='green' ";
										} else {
											$rowclass = "";
										}
										
										if ($movement['comment'] != '') {
											
											$commentRead = "
															<img src='images/comments.png' id='commentinternal$movementid' /><div id='helpBoxinternal$movementid' class='helpBox'>{$movement['comment']}</div>
															<script>
																$('#commentinternal$movementid').on({
																	'mouseover' : function() {
																		$('#helpBoxinternal$movementid').css('display', 'block');
																	},
																	'mouseout' : function() {
																		$('#helpBoxinternal$movementid').css('display', 'none');
																	}
																});
															</script>
															";
											
										} else {
											
											$commentRead = "";
											
										}
										
										
										// Look up movement name
											if ($_SESSION['lang'] == 'es') {
												$selectMovementName = "SELECT movementNamees FROM productmovementtypes WHERE movementTypeid = $movementTypeid";
												try
												{
													$result = $pdo3->prepare("$selectMovementName");
													$result->execute();
												}
												catch (PDOException $e)
												{
														$error = 'Error fetching user: ' . $e->getMessage();
														echo $error;
														exit();
												}
											
												$row = $result->fetch();
														if($movementTypeid == 5){
															$movementName = $lang['add-internal-stash'];
														}
														else if($movementTypeid == 12){
															$movementName = $lang['remove-internal-stash'];
														}
														else{
															$movementName = $row['movementNamees'];
														}
											} else {
												$selectMovementName = "SELECT movementNameen FROM productmovementtypes WHERE movementTypeid = $movementTypeid";
												try
												{
													$result = $pdo3->prepare("$selectMovementName");
													$result->execute();
												}
												catch (PDOException $e)
												{
														$error = 'Error fetching user: ' . $e->getMessage();
														echo $error;
														exit();
												}
											
												$row = $result->fetch();
														if($movementTypeid == 5){
															$movementName = $lang['add-internal-stash'];
														}
														else if($movementTypeid == 12){
															$movementName = $lang['remove-internal-stash'];
														}
														else{
															$movementName = $row['movementNameen'];
														}
											}

										
										$movement_row =	sprintf("
										<tr%s>
										<td>%s</td>
										<td class='left'>%s</td>
										<td style='text-align: right;'>%0.02f u</td>
										<td style='text-align: right;'>$ppg</td>
										<td class='centered'><span class='relativeitem'>%s</span></td>
										<td class='right'>$editOrNot<a href='javascript:delete_movement(%d,%d)'><img src='images/delete.png' height='15' title='Delete movement' /></a></td>
										</tr>",
										$rowclass, $formattedDate, $movementName, $quantity, $commentRead, $movementid, $purchaseid
										);
										echo $movement_row;
								}
						?>

						</tbody>
				</table>
		</div>		
		<div id ="tabs-3">
				<?php 
						// Query to look up movements
						$selectexternalMovements = "SELECT movementid, movementtime, type, purchaseid, quantity, movementTypeid, comment, price FROM b_productmovements WHERE purchaseid = ".$_GET['purchaseid']." AND stashMovementType = 2 ORDER by movementtime ASC";

						try
						{
							$results = $pdo3->prepare("$selectexternalMovements");
							$results->execute();
						}
						catch (PDOException $e)
						{
								$error = 'Error fetching user: ' . $e->getMessage();
								echo $error;
								exit();
						}
					
							
				?>

					<table class="defaultnobg">
						<tbody>
							<?php

								while ($movement = $results->fetch()) {
								
										$movementid = $movement['movementid'];
										$movementtime = $movement['movementtime'];
										$type = $movement['type'];
										$purchaseid = $movement['purchaseid'];
										$price = $movement['price'];
										$quantity = $movement['quantity'];

										$movementTypeid = $movement['movementTypeid'];
										
										if ($movementTypeid == 1) {
											$editOrNot = "<a href='edit-movement.php?movementid=$movementid'><img src='images/edit.png' height='15' title='Edit' /></a>&nbsp;&nbsp;";
											$ppg = $price / $quantity . " ".$_SESSION['currencyoperator']." / u";
										} else {
											$editOrNot = "";
											$ppg = '';
										}
										
										if (isset($_GET['summary'])) {
											$quantity = $movement['SUM(quantity)'];
											$formattedDate = "";
										} else {
											$quantity = $movement['quantity'];
											$formattedDate = date("d M H:i", strtotime($movement['movementtime'] . "+$offsetSec seconds"));
										}

										
										if ($movementTypeid == 17 || $movementTypeid == 18 || $movementTypeid == 19 || $movementTypeid == 20 ) {
											$rowclass = " class='grey' ";
										} else if ($type == 1) {
											$rowclass = " class='red' ";
										} else if ($type == 2) {
											$rowclass = " class='green' ";
										} else {
											$rowclass = "";
										}
										
										if ($movement['comment'] != '') {
											
											$commentRead = "
															<img src='images/comments.png' id='commentexternal$movementid' /><div id='helpBoxexternal$movementid' class='helpBox'>{$movement['comment']}</div>
															<script>
																$('#commentexternal$movementid').on({
																	'mouseover' : function() {
																		$('#helpBoxexternal$movementid').css('display', 'block');
																	},
																	'mouseout' : function() {
																		$('#helpBoxexternal$movementid').css('display', 'none');
																	}
																});
															</script>
															";
											
										} else {
											
											$commentRead = "";
											
										}
										
										
										// Look up movement name
											if ($_SESSION['lang'] == 'es') {
												$selectMovementName = "SELECT movementNamees FROM productmovementtypes WHERE movementTypeid = $movementTypeid";
												try
												{
													$result = $pdo3->prepare("$selectMovementName");
													$result->execute();
												}
												catch (PDOException $e)
												{
														$error = 'Error fetching user: ' . $e->getMessage();
														echo $error;
														exit();
												}
											
												$row = $result->fetch();
														if($movementTypeid == 6){
															$movementName = $lang['add-external-stash'];
														}
														else if($movementTypeid == 2){
															$movementName = $lang['remove-external-stash'];
														}
														else{
															$movementName = $row['movementNamees'];
														}
											} else {
												$selectMovementName = "SELECT movementNameen FROM productmovementtypes WHERE movementTypeid = $movementTypeid";
												try
												{
													$result = $pdo3->prepare("$selectMovementName");
													$result->execute();
												}
												catch (PDOException $e)
												{
														$error = 'Error fetching user: ' . $e->getMessage();
														echo $error;
														exit();
												}
											
												$row = $result->fetch();
														if($movementTypeid == 6){
															$movementName = $lang['add-external-stash'];
														}
														else if($movementTypeid == 2){
															$movementName = $lang['remove-external-stash'];
														}
														else{
															$movementName = $row['movementNameen'];
														}
											}

										
										$movement_row =	sprintf("
										<tr%s>
										<td>%s</td>
										<td class='left'>%s</td>
										<td style='text-align: right;'>%0.02f u</td>
										<td style='text-align: right;'>$ppg</td>
										<td class='centered'><span class='relativeitem'>%s</span></td>
										<td class='right'>$editOrNot<a href='javascript:delete_movement(%d,%d)'><img src='images/delete.png' height='15' title='Delete movement' /></a></td>
										</tr>",
										$rowclass, $formattedDate, $movementName, $quantity, $commentRead, $movementid, $purchaseid
										);
										echo $movement_row;
								}
						?>

						</tbody>
				</table>
		</div>

	 </div>
 </div>
</div>
</center>	 

<br /><br />
 	 <table class="default">
	  <thead>
	   <tr>
	    <th><?php echo $lang['global-time']; ?></th>
	    <th><?php echo $lang['global-member']; ?></th>
	    <th><?php echo $lang['global-quantity']; ?></th>
	    <th><?php echo $lang['global-amount']; ?></th>
	   </tr>
	  </thead>
	  <tbody>

<?php 

	// Purchase dispense history: Time, member, quantity, euro

	$selectSales = "SELECT s.saleid, s.saletime, s.userid, d.quantity, d.amount FROM b_sales s, b_salesdetails d WHERE s.saleid = d.saleid AND d.purchaseid = $purchaseid";
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
	
		while ($sale = $result->fetch()) {
	
		$formattedDate = date("d M H:i", strtotime($sale['saletime'] . "+$offsetSec seconds"));
		$saleid = $sale['saleid'];
		$userid = $sale['userid'];
		$quantity = $sale['quantity'];
		$amount = $sale['amount'];
		
		$member = getUser($userid);
		
		$sale_row =	sprintf("
	  	  <tr>
	  	   <td class='clickableRow' href='bar-sale.php?saleid=%d'>%s</td>
	  	   <td class='clickableRow' href='bar-sale.php?saleid=%d'>%s</td>
	  	   <td style='text-align: right;' class='clickableRow' href='bar-sale.php?saleid=%d'>%0.2f <span class='smallerfont'>u.</span></td>
	  	   <td style='text-align: right;' class='clickableRow' href='bar-sale.php?saleid=%d'>%0.2f <span class='smallerfont'>{$_SESSION['currencyoperator']}</span></td>
		  </tr>",
		  $sale['saleid'], $formattedDate, $sale['saleid'], $member, $sale['saleid'], $quantity, $sale['saleid'], $amount
		  );
		  echo $sale_row;

	}

?>

<br class="clearFloat">

<?php } displayFooter(); ?>
