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
	
	// Check if new Filter value was submitted, and assign query variable accordingly
	if (isset($_POST['filter'])) {
				
		$filterVar = $_POST['filter'];
		
		if ($filterVar == 100) {
			
			$limitVar = "LIMIT 100";
			$optionList = "<option value='$filterVar'>{$lang['last']} 100</option>
			<option value='250'>{$lang['last']} 250</option>
			<option value='500'>{$lang['last']} 500</option>";
			
		} else if ($filterVar == 250) {
			
			$limitVar = "LIMIT 250";
			$optionList = "<option value='$filterVar'>{$lang['last']} 250</option>
			<option value='100'>{$lang['last']} 100</option>
			<option value='500'>{$lang['last']} 500</option>";
			
		} else if ($filterVar == 500) {
			
			$limitVar = "LIMIT 500";
			$optionList = "<option value='$filterVar'>{$lang['last']} 500</option>
			<option value='100'>{$lang['last']} 100</option>
			<option value='250'>{$lang['last']} 250</option>";
			
		} else {
			
			// Grab month and year number
			$month = substr($filterVar, 0, strrpos($filterVar, '-'));	
			$year = substr($filterVar, strrpos($filterVar, '-') + 1);
			
			if($month !='' && $year != ''){
				$timeLimit = "AND MONTH(saletime) = $month AND YEAR(saletime) = $year";
			}else{
				$timeLimit = '';
			}
			
			$optionList = "<option value='filterVar'>$filterVar</option>
				<option value='100'>{$lang['last']} 100</option>
				<option value='250'>{$lang['last']} 250</option>
				<option value='500'>{$lang['last']} 500</option>";		
				
		}
			
	} else {
		
		$limitVar = "LIMIT 100";
		
		$optionList = "<option value='100'>{$lang['last']} 100</option>
			<option value='250'>{$lang['last']} 250</option>
			<option value='500'>{$lang['last']} 500</option>";		
	}
		
	// Check if 'entre fechas' was utilised
	if (!empty($_POST['untilDate'])) {
		
		$limitVar = "";
		
		$fromDate = date("Y-m-d", strtotime($_POST['fromDate']));
		$untilDate = date("Y-m-d", strtotime($_POST['untilDate']));
		
		$timeLimit = "AND DATE(saletime) BETWEEN DATE('$fromDate') AND DATE('$untilDate')";
			
	}else{
		$timeLimit = '';
	}
	// KONSTAT CODE UPDATE BEGIN
   $current_date =  date('d-m-Y');
   $expected_date = "01-01-2019";
   $dispalyGroup = 0;
	if(strtotime($current_date) > strtotime($expected_date)){
		$dispalyGroup = 1;
	}
	$selectedUsergroup = "1,2,3";
	$selectedDiscount = "1,2,3,4,6,7";
	if(isset($_POST['submitted'])){
			$firstSelect = 'false';
			//  code to filter the sales from usergroups
			if(isset($_POST['cashBox'])){
				$selectedUserArr = $_POST['cashBox'];
				$selectedUsergroup = implode(",", $selectedUserArr);
/*				$getUsers = "SELECT user_id FROM users WHERE userGroup IN ($selectedUsergroup)";
				$result = $pdo3->prepare("$getUsers");
				$result->execute();
				while($user_ids = $result->fetch()){
					$userArr[] = $user_ids['user_id'];
				}
				if(!empty($userArr)){
					array_push($userArr, 999999);
				}
				$selectedUsers = implode(',',$userArr);
				if(empty($selectedUsers) || $selectedUsers ==''){
					$selectedUsers = -1;
				}*/
				$user_limit = "AND operatorGroup IN ($selectedUsergroup)";
			}else{
				$user_limit = '';
			}
			//  code to filter the sales from discounts
			if(isset($_POST['discountType'])){
				$selectedDiscountArr = $_POST['discountType'];
				$happyIdArr[] = 0;
				$volumeIdArr[] = 0;
				$salesIdArr[] = 0;
				$checkoutIdArr[] = 0;
			    $selectedDiscount = implode(',',$selectedDiscountArr);
			   
				    // happy hour discount
				    if(in_array(3, $selectedDiscountArr)){
				    	$getSales  = "SELECT p.saleid  FROM b_sales p, b_salesdetails q WHERE q.happyhourDiscount = '3' AND p.saleid = q.saleid";
				    	$getResult =  $pdo3->prepare("$getSales");
				    	$getResult->execute();
				    	while($happyRow = $getResult->fetch()){
				    		$happyIdArr[] = $happyRow['saleid']; 
				    	}
				    }
				    // Volume discount
				    if(in_array(6,  $selectedDiscountArr)){
				       $getSales  = "SELECT p.saleid  FROM b_sales p, b_salesdetails q WHERE q.volumeDiscount = '6'  AND p.saleid = q.saleid";
				    	$getResult =  $pdo3->prepare("$getSales");
				    	$getResult->execute();
				    	while($volumeRow = $getResult->fetch()){
				    		$volumeIdArr[] = $volumeRow['saleid']; 
				    	}
					}
					// Checkout discount
				    if(in_array(5,  $selectedDiscountArr)){
				       $getSales  = "SELECT p.saleid  FROM b_sales p, b_sales_discount q WHERE q.discountType = '5'  AND p.saleid = q.salesId";
				    	$getResult =  $pdo3->prepare("$getSales");
				    	$getResult->execute();
				    	while($checkoutRow = $getResult->fetch()){
				    		$checkoutIdArr[] = $checkoutRow['saleid']; 
				    	}
					}
						// get all sales ids from discount
						if(empty($selectedDiscount) || $selectedDiscount == ''){
							$selectedDiscount = -1;
						}
						$getSales  = "SELECT p.saleid  FROM b_sales p, b_salesdetails q WHERE q.discountType IN ($selectedDiscount) AND p.saleid = q.saleid";
					    	$getResult =  $pdo3->prepare("$getSales");
					    	$getResult->execute();
					    	while($salesRow = $getResult->fetch()){
					    		$salesIdArr[] = $salesRow['saleid']; 
					    	}
					    
				    	$filtersales = array_merge($happyIdArr, $volumeIdArr, $salesIdArr ,$checkoutIdArr);
				    	$filterSaleIds = array_unique($filtersales);
				        $selectedDiscountIds = implode(',', $filterSaleIds); 
				        if(empty($selectedDiscountIds) || $selectedDiscountIds == ''){
				        	$selectedDiscountIds = -1;
				        }
				        $discount_limit = "AND saleid IN ($selectedDiscountIds)";
			    
			}else{
			    	$selectedDiscount = 0;
			        $getSales  = "SELECT p.saleid  FROM b_sales p, b_salesdetails q WHERE q.discountType  = '$selectedDiscount' AND p.saleid = q.saleid";
					    	$getResult =  $pdo3->prepare("$getSales");
					    	$getResult->execute();
					    	while($salesRow = $getResult->fetch()){
					    		$salesIdArr[] = $salesRow['saleid']; 
					    	}
					    $selectedDiscountIds = implode(',', $salesIdArr); 
						if(empty($selectedDiscountIds) || $selectedDiscountIds == ''){
				        	$selectedDiscountIds = -1;
				        }
					    $discount_limit = "AND saleid IN ($selectedDiscountIds)";
			    }
	}else{
		 $firstSelect = 'true';
		/*  $getUsers = "SELECT user_id FROM users WHERE userGroup IN ($selectedUsergroup)";
			$result = $pdo3->prepare("$getUsers");
			$result->execute();
			while($user_ids = $result->fetch()){
				$userArr[] = $user_ids['user_id'];
			}
			array_push($userArr, 999999);
		    $selectedUsers = implode(',',$userArr);
		    if(empty($selectedUsers) || $selectedUsers == ''){
				$selectedUsers = -1;		    	
		    }else{
		    	$selectedUsers .= ',0';
		    }*/

		   $user_limit = "AND operatorGroup IN ($selectedUsergroup)";
		   $getSales  = "SELECT p.saleid  FROM b_sales p, b_salesdetails q WHERE q.discountType IN ($selectedDiscount) AND p.saleid = q.saleid";
	    	$getResult =  $pdo3->prepare("$getSales");
	    	$getResult->execute();
	    	while($salesRow = $getResult->fetch()){
	    		$salesIdArr[] = $salesRow['saleid']; 
	    	}
	    	$filterSaleIds = array_unique($salesIdArr);
	    	$selectedDiscountIds = implode(',', $filterSaleIds);
	    	if($selectedDiscountIds == '' || empty($selectedDiscountIds)){
	    		$selectedDiscountIds = -1;
	    	}
	    	$discount_limit = "AND saleid IN ($selectedDiscountIds)";
	}
   
	// KONSTANT CODE UPDATE END
	// Query to look up individual sales
 	 $selectSales = "SELECT saleid, operatorid, saletime, amount, unitsTot, userid, creditBefore, creditAfter, direct, adminComment FROM b_sales  WHERE 1 $timeLimit $user_limit ORDER by saletime DESC $limitVar"; 
		try
		{
			$results = $pdo3->prepare("$selectSales");
			$results->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		
		
	// Create month-by-month split
	$findStartDate = "SELECT saletime FROM b_sales ORDER BY saletime ASC LIMIT 1";
		try
		{
			$result = $pdo3->prepare("$findStartDate");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$startDate = date('01-m-Y', strtotime($row['saletime']));
		$endDate = date('01-m-Y');
		$endDateShort = date('m-Y', strtotime($endDate));
		
		
	if ($endDateShort != $filterVar) {
		$optionList .= "<option value='$endDateShort'>$endDateShort</option>";
	}
	
	$genDateFull = date('01-m-Y', strtotime($endDate));
	$genDate = date('m-Y', strtotime($genDateFull));
	
	while (strtotime($genDateFull) > strtotime($startDate)) {
		
		$genDateFull = date('01-m-Y', strtotime("$genDateFull - 1 month"));
		$genDate = date('m-Y', strtotime($genDateFull));
		
		// Exclude option if already selected
		if ($genDate != $filterVar) {
			$optionList .= "<option value='$genDate'>$genDate</option>";
		}
	}
		
	
	$deleteSaleScript = <<<EOD
	
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
			

			
			
			$('#mainTable').tablesorter({
				usNumberFormat: true,
				headers: {
					0: {
						sorter: "dates"
					},
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
						sorter: "currency"
					},
					9: {
						sorter: "currency"
					}
				}
			}); 
			
		});
		
		$(window).resize(function() {
			$('#cloneTable').width($('#mainTable').width());
		});
		
function delete_sale(saleid) {
	if (confirm("{$lang['confirm-deletedispense']}")) {
				window.location = "uTil/delete-sale.php?saleid=" + saleid;
				}
}
EOD;
	pageStart("Sales", NULL, $deleteSaleScript, "psales", "sales admin", "SALES", $_SESSION['successMessage'], $_SESSION['errorMessage']);
?>
<center>
<div id='filterbox'>
 <div id='mainboxheader'>
 <?php echo $lang['filter']; ?>
 </div>
 <div class='boxcontent' style="display: inline-flex;">
        <form action='' method='POST'>
	     <select id='filter' name='filter' class='defaultinput' onchange='this.form.submit()' style='display: inline-block; width: 100px; height: 38px; margin-top: 18px;'>
	      <?php echo $optionList; ?>
		 </select>
        </form>
        <span style="margin-top: 27px; font-weight: bold;">OR</span>
        <form action='' method='POST'>
<?php
	if (isset($_POST['fromDate'])) {
		
		echo <<<EOD
		 <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="sixDigit defaultinput" value="{$_POST['fromDate']}" />
		 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="sixDigit defaultinput" value="{$_POST['untilDate']}" onchange='this.form.submit()' />
		 <button type="submit"  class='cta2' style='display: inline-block; width: 50px;'>OK</button>
EOD;
		
	} else {
		
		echo <<<EOD
		 <input type="text" id="datepicker" name="fromDate" autocomplete="nope" class="sixDigit defaultinput" placeholder="{$lang['from-date']}" />
		 <input type="text" id="datepicker2" name="untilDate" autocomplete="nope" class="sixDigit defaultinput" placeholder="{$lang['to-date']}" onchange='this.form.submit()' />
		 <button type="submit"  class='cta2' style='display: inline-block; width: 50px;'>OK</button>
EOD;
	}
?>
       
       <br>
     
		<div style='display: inline-block; text-align: left; float: left; padding-right: 32px;'>
			&nbsp;<strong>Workers:</strong><br /> <br /> 
			<?php  
				if($firstSelect == 'true'){
			 ?>
			<div class='fakeboxholder firstbox'>	
			 <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			  Administrador
			  <input type="checkbox" name="cashBox[]" id="accept1" value='1' checked />
			  <div class="fakebox"></div>
			 </label>
			</div>
			<br />
			<br />
			<div class='fakeboxholder'>	
			 <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			  Trabajador
			  <input type="checkbox" name="cashBox[]" id="accept2" value='2' checked />
			  <div class="fakebox"></div>
			 </label>
			</div>
			<br />
			<br />
			<div class='fakeboxholder'>	
			 <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			  Voluntario
			  <input type="checkbox" name="cashBox[]" id="accept3" value='3' checked />
			  <div class="fakebox"></div>
			 </label>
			</div>
			<br />
			<br />
			<?php }else{ ?>
			<div class='fakeboxholder firstbox'>	
			 <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			  Administrador
			  <input type="checkbox" name="cashBox[]" id="accept1" value='1' <?php if(in_array(1, $selectedUserArr)){ echo "checked"; } ?> />
			  <div class="fakebox"></div>
			 </label>
			</div>
			<br />
			<br />
			<div class='fakeboxholder'>	
			 <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			  Trabajador
			  <input type="checkbox" name="cashBox[]" id="accept2" value='2' <?php if(in_array(2, $selectedUserArr)){ echo "checked"; } ?> />
			  <div class="fakebox"></div>
			 </label>
			</div>
			<br />
			<br />
			<div class='fakeboxholder'>	
			 <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			  Voluntario
			  <input type="checkbox" name="cashBox[]" id="accept3" value='3' <?php if(in_array(3, $selectedUserArr)){ echo "checked"; } ?> />
			  <div class="fakebox"></div>
			 </label>
			</div>
			<br />
			<br />
			<?php } ?>	
			
		<!-- <button type="submit">Submit</button> -->
		
		
	      
	        </div>
      
<!-- 			<div style='display: inline-block; text-align: left; '>
					&nbsp;<strong>Discount Type:</strong><br /> <br /> 
					<?php  
						if($firstSelect == 'true'){
					 ?>
					<div class='fakeboxholder firstbox'>	
					 <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					  Individual discount
					  <input type="checkbox" name="discountType[]" id="discount1" value='1' checked />
					  <div class="fakebox"></div>
					 </label>
					</div>
					<br />
					<br />
					<div class='fakeboxholder'>	
					 <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					  Medical discount
					  <input type="checkbox" name="discountType[]" id="discount2" value='2' checked />
					  <div class="fakebox"></div>
					 </label>
					</div>
					<br />
					<br />
					<div class='fakeboxholder'>	
					 <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					  Happy Hour discount
					  <input type="checkbox" name="discountType[]" id="discount3" value='3' checked />
					  <div class="fakebox"></div>
					 </label>
					</div>
					<br />
					<br />
					<div class='fakeboxholder'>	
					 <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					  Usergroup discount
					  <input type="checkbox" name="discountType[]" id="discount4" value='4' checked />
					  <div class="fakebox"></div>
					 </label>
					</div>
					<br />
					<br />
					<div class='fakeboxholder'>	
					 <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					  Checkout discount
					  <input type="checkbox" name="discountType[]" id="discount5" value='5' checked />
					  <div class="fakebox"></div>
					 </label>
					</div>
					<br />
					<br />
					<div class='fakeboxholder'>	
					 <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					  Volume discount
					  <input type="checkbox" name="discountType[]" id="discount6" value='6' checked />
					  <div class="fakebox"></div>
					 </label>
					</div>
					<br />
					<br />
					<div class='fakeboxholder'>	
					 <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					  Gift
					  <input type="checkbox" name="discountType[]" id="discount7" value='7' checked />
					  <div class="fakebox"></div>
					 </label>
					</div>
					<?php }else{  ?>
				 	<div class='fakeboxholder firstbox'>	
					 <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					  Individual discount
					  <input type="checkbox" name="discountType[]" id="discount1" value='1'  <?php if(in_array(1, $selectedDiscountArr)){ echo "checked"; } ?> />
					  <div class="fakebox"></div>
					 </label>
					</div>
					<br />
					<br />
					<div class='fakeboxholder'>	
					 <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					  Medical discount
					  <input type="checkbox" name="discountType[]" id="discount2" value='2'  <?php if(in_array(2, $selectedDiscountArr)){ echo "checked"; } ?> />
					  <div class="fakebox"></div>
					 </label>
					</div>
					<br />
					<br />
					<div class='fakeboxholder'>	
					 <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					  Happy Hour discount
					  <input type="checkbox" name="discountType[]" id="discount3" value='3'  <?php if(in_array(3, $selectedDiscountArr)){ echo "checked"; } ?> />
					  <div class="fakebox"></div>
					 </label>
					</div>
					<br />
					<br />
					<div class='fakeboxholder'>	
					 <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					  Usergroup discount
					  <input type="checkbox" name="discountType[]" id="discount4" value='4'  <?php if(in_array(4, $selectedDiscountArr)){ echo "checked"; } ?> />
					  <div class="fakebox"></div>
					 </label>
					</div>
					<br />
					<br />
					<div class='fakeboxholder'>	
					 <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					  Checkout discount
					  <input type="checkbox" name="discountType[]" id="discount5" value='5'  <?php if(in_array(5, $selectedDiscountArr)){ echo "checked"; } ?> />
					  <div class="fakebox"></div>
					 </label>
					</div>
					<br />
					<br />
					<div class='fakeboxholder'>	
					 <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					  Volume discount
					  <input type="checkbox" name="discountType[]" id="discount6" value='6'  <?php if(in_array(6, $selectedDiscountArr)){ echo "checked"; } ?> />
					  <div class="fakebox"></div>
					 </label>
					</div>
					<br />
					<br />
					<div class='fakeboxholder'>	
					 <label class="control">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					  Gift
					  <input type="checkbox" name="discountType[]" id="discount7" value='7'  <?php if(in_array(7, $selectedDiscountArr)){ echo "checked"; } ?> />
					  <div class="fakebox"></div>
					 </label>
					</div>
				<?php } ?>
				<input type="hidden" name="submitted" value="1">
		   </div> -->
	    <br />
	    <br />
	    <br />
	    <br />
	    <br />
	    <br />
	    <br />
	    <br />
       <input type="hidden" name="submitted" value="1">
     	<center><button type="submit" class="cta2" style="right:148px;">OK</button></center>
 	</form>
</div>

</center>
<br />
<br />	 <table class='default' id='mainTable'>
	  <thead>
	   <tr style='cursor: pointer;'>
	    <th style='position: relative;'><a href="#" id="openCOnfirm" style='position: absolute; top: 0; left: 10px; margin-top: -66px;'><img src="images/excel-new.png" /></a><?php echo $lang['global-time']; ?></th>
	   <?php if($dispalyGroup == 1){ ?>
	    	<th>Usergroup</th>
		<?php } ?>
	    <th><?php echo $lang['global-member']; ?></th>
	    <th><?php echo $lang['global-category']; ?></th>
	    <th><?php echo $lang['global-product']; ?></th>
	    <th class='right'><?php echo $lang['global-quantity']; ?></th>
	    <th class='right'><?php echo $_SESSION['currencyoperator'] ?></th>
	     <th class="right">Discount applied</th>
	     <th class="right">Summary</th>
	    <th>Total u</th>
	    <th class='right'>Total <?php echo $_SESSION['currencyoperator'] ?></th>
	    <th class='right'><?php echo $lang['dispense-oldcredit']; ?></th>
	    <th class='right'><?php echo $lang['dispense-newcredit']; ?></th>
<?php if ($_SESSION['creditOrDirect'] != 1) { ?>
	    <th><?php echo $lang['paid-by']; ?></th>
<?php } ?>
	    <th class='noExl'></th>
	    <th class='noExl'><?php echo $lang['global-delete']; ?></th>
	   </tr>
	  </thead>
	  <tbody>
	 
<?php
	  $startIndex = 2;
	  $p = 0;
		while ($sale = $results->fetch()) {
	
		$formattedDate = date("d-m-Y H:i", strtotime($sale['saletime'] . "+$offsetSec seconds"));
		// KONSTANT CODE UPDATE BEGIN
		$operatorId = $sale['operatorid'];
		// KONSTANT CODE UPDATE END
		$saleid = $sale['saleid'];
		$userid = $sale['userid'];
		$units = $sale['unitsTot'];
		$credit = $sale['creditBefore'];
		$newcredit = $sale['creditAfter'];
		$direct = $sale['direct'];
		$direct = $sale['direct'];
		
		if ($direct == 3) {
			$paymentMethod = $lang['global-credit'];
		} else if ($direct == 2) {
			$paymentMethod = $lang['card'];
		} else if ($direct == 1) {
			$paymentMethod = $lang['cash'];
		} else {
			$paymentMethod = '';
		}
		
		$amount = $sale['amount'];
		// KONSTANT CODE UPDATE BEGIN
			if($operatorId > 0 && $operatorId != 999999){
				$operatorLookup = "SELECT a.userGroup, b.groupName FROM users a, usergroups b WHERE a.userGroup = b.userGroup AND a.user_id = {$operatorId}";
				try
				{
					$result = $pdo3->prepare("$operatorLookup");
					$result->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
			
				$row = $result->fetch();
					$operatorGroup = $row['userGroup'];
				    $operatorName = $row['groupName']; 
			}else{
				if($operatorId == 999999){
					$operatorGroup = 1;
					$operatorName = 'Administrador';
				}else{
					$operatorGroup = 0;
					$operatorName = 'None';
				}
			}
		// KONSTANT CODE UPDATE END
		$userLookup = "SELECT first_name, memberno FROM users WHERE user_id = {$userid}";
		try
		{
			$result = $pdo3->prepare("$userLookup");
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
			$memberno = $row['memberno'];
			
	if ($sale['adminComment'] != '') {
		
		$commentRead = "
		                <img src='images/comments.png' id='comment$saleid' /><div id='helpBox$saleid' class='helpBox'>{$sale['adminComment']}</div>
		                <script>
		                  	$('#comment$saleid').on({
						 		'mouseover' : function() {
								 	$('#helpBox$saleid').css('display', 'block');
						  		},
						  		'mouseout' : function() {
								 	$('#helpBox$saleid').css('display', 'none');
							  	}
						  	});
						</script>
		                ";
		
	} else {
		
		$commentRead = "";
		
	}
		
		// KONSTANT CODE UPDATE BEGIN
		$selectoneSale = "SELECT d.category, d.productid, d.quantity, d.amount, d.purchaseid, d.discountType, d.discountPercentage, d.happyhourDiscount, d.volumeDiscount, s.discount FROM b_salesdetails d, b_sales s WHERE d.saleid = {$saleid} and s.saleid = d.saleid";
		// KONSTANT CODE UPDATE END
		try
		{
			$onesaleResult = $pdo3->prepare("$selectoneSale");
			$onesaleResult->execute();
			$onesaleResult2 = $pdo3->prepare("$selectoneSale");
			$onesaleResult2->execute();
			$onesaleResult3 = $pdo3->prepare("$selectoneSale");
			$onesaleResult3->execute();
			$onesaleResult4 = $pdo3->prepare("$selectoneSale");
			$onesaleResult4->execute();
			// KONSTANT CODE UPDATE BEGIN		
			$onesaleResult5 = $pdo3->prepare("$selectoneSale");
			$onesaleResult5->execute();			
			$onesaleResult6 = $pdo3->prepare("$selectoneSale");
			$onesaleResult6->execute();
			// KONSTANT CODE UPDATE END
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		
	   
		echo "
  	   <td class='clickableRow' href='bar-sale.php?saleid={$saleid}'>{$formattedDate}</td>";
  	     	   // KONSTANT CODE UPDATE BEGIN
		if($dispalyGroup == 1){
  	   		echo "<td class='clickableRow' href='bar-sale.php?saleid={$saleid}'>{$operatorName}</td>";
  		}
  		// KONSTANT CODE UPDATE END
  	   echo "<td class='clickableRow' href='bar-sale.php?saleid={$saleid}'>#{$memberno} - {$first_name}</td>
  	   <td class='clickableRow' href='bar-sale.php?saleid={$saleid}'>";
  	   $a = 0;
		while ($onesale = $onesaleResult->fetch()) {
			
			$selectCatName = "SELECT name from b_categories where id = {$onesale['category']}";
		try
		{
			$result = $pdo3->prepare("$selectCatName");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$catName = $row['name'] ;
				$catarr[$p][$a]= $catName; 
				echo $catName . "</br>";
				$a++;
		}
		$catex = implode(',', $catarr[$p]);
		echo "</td><td class='clickableRow' href='bar-sale.php?saleid={$saleid}'>";
		$b = 0;
		while ($onesale = $onesaleResult2->fetch()) {
			
			// Look up service name
			$selectServName = "SELECT name from b_products where productid = {$onesale['productid']}";
		try
		{
			$result = $pdo3->prepare("$selectServName");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
				$servName = $row['name'] ;
				$servarr[$p][$b] .= $servName;
				echo $servName . "<br>";
				$b++;
		}
		$servex = implode(",", $servarr[$p]);
		echo "</td><td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'>";
		$c = 0;
		while ($onesale = $onesaleResult3->fetch()) {
				echo number_format($onesale['quantity'],0) . " u<br />";

				$quantarr[$p][$c]= number_format($onesale['quantity'],0) . " u";
				$c++;
		}
		$quantex = implode(",", $quantarr[$p]);
		echo "</td><td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'>";
		$d = 0;
		while ($onesale = $onesaleResult4->fetch()) {
			echo number_format($onesale['amount'],2) . " <span class='smallerfont'>{$_SESSION['currencyoperator']}</span><br />";
			$amountarr[$p][$d] =   number_format($onesale['amount'],2) . $_SESSION['currencyoperator'];
			$d++;
		}
		$amountex = implode(",", $amountarr[$p]);
		echo "</td>";
				echo "<td  class='clickableRow' href='bar-sale.php?saleid={$saleid}'>";
		$kk =0;
		 while ($onesale = $onesaleResult5->fetch()) {
		 	 $discountName = '';
	    	 $discountType = $onesale['discountType'];
	    	if($discountType == 1){
	    		$discountName = "Individual discount";
	    	}else if($discountType == 2){
	    		$discountName = "Medical discount";
	    	}
	    	else if($discountType == 3){
	    		$discountName = "Happy Hour discount";
	    	}
	    	else if($discountType == 4){
	    		$discountName = "Usergroup discount";
	    	}
	    	else if($discountType == 5){
	    		$discountName = "Checkout discount";
	    	}
	    	else if($discountType == 6){
	    		$discountName = "Volume discount";
	    	}
	    	else if($discountType == 7){
	    		$discountName = "Gift";
	    	}
	    	$checkoutValue = $onesale['discount'];
	    	$happyhourDiscount = $onesale['happyhourDiscount'];
	    	$volumeDiscount = $onesale['volumeDiscount'];
	    	if($checkoutValue != '0.00'){
	    		if($kk == 0){
	    			echo "(Checkout Disocunt) <br>";
	    			$discountarr[$p][$kk] = "(Checkout Disocunt)";
	    		}
	    	}
	    	if($happyhourDiscount != '0.00' && $volumeDiscount != '0.00'){
	    		if($discountType == 3){
	    			$discountName2 = "";
	    		}else{
	    			$discountName2 = " + $discountName";
	    		}
	    		echo "Happy Hour + Volume Discount$discountName2<br>";
	    		$discountarr[$p][$kk] = "Happy Hour + Volume Discount$discountName2";
	    	}else{
	    		echo "$discountName<br>";
	    		$discountarr[$p][$kk]  = "$discountName";
	    	}

		$kk++; 
	}
	   $discountex = implode(",", $discountarr[$p]);
		echo "</td>";
		echo "<td  class='clickableRow' href='bar-sale.php?saleid={$saleid}'>";
		$e = 0;
		 while ($onesale = $onesaleResult6->fetch()) {
		 	   $selectPrice = "SELECT salesPrice FROM b_purchases WHERE purchaseid = {$onesale['purchaseid']}";
				try
				{
					$result = $pdo3->prepare("$selectPrice");
					$result->execute();
				}
				catch (PDOException $e)
				{
						$error = 'Error fetching user: ' . $e->getMessage();
						echo $error;
						exit();
				}
				$row = $result->fetch();
				$salesPrice = $row['salesPrice'];
			    $thisQuantity = $onesale['quantity'];
			    $normalPrice = round($salesPrice * $thisQuantity,2);
	    		$paid_price =  number_format($onesale['amount'],2);
	    	    $loss_price = $normalPrice - $paid_price;
	    		$discountPer = $onesale['discountPercentage'];
	    		echo  "$loss_price {$_SESSION['currencyoperator']}<br>";
	    		$losspricearr[$p][$e] =  $loss_price.$_SESSION['currencyoperator']; 
	    		$e++;
		}
		echo "</td>";
		$losspriceex = implode(",", $losspricearr[$p]);
		$quantity = number_format($quantity,2);
		$amount = number_format($amount,2);
		$units = number_format($units,0);
		
			
			if ($credit == NULL && $oldcredit == NULL) {
				echo "
				<td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'><strong>{$units} u</strong></td>
				<td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'><strong>{$amount} {$_SESSION['currencyoperator']}</strong></td>
				<td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'></td>
				<td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'></td>";
				if ($_SESSION['creditOrDirect'] != 1) {
					echo "<td class='left'>$paymentMethod</td>";
				}
				echo "<td class='centered'><span class='relativeitem'>$commentRead</span></td>";
				echo "<td class='noExl' style='text-align: center;'><a href='javascript:delete_sale({$saleid})'><img src='images/delete.png' height='15' title='{$lang['dispenses-deletesale']}' /></a></td></tr>
				";
				
			} else {
				
				echo "
				<td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'><strong>{$units} u</strong></td>
				<td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'><strong>{$amount} {$_SESSION['currencyoperator']}</strong></td>
				<td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'>{$credit} {$_SESSION['currencyoperator']}</td>
				<td class='clickableRow right' href='bar-sale.php?saleid={$saleid}'>{$newcredit} {$_SESSION['currencyoperator']}</td>";
					if ($_SESSION['creditOrDirect'] != 1) {
									echo "<td class='left'>$paymentMethod</td>";
					}
				echo "<td class='centered'><span class='relativeitem'>$commentRead</span></td>";
				echo "<td class='noExl' style='text-align: center;'><a href='javascript:delete_sale({$saleid})'><img src='images/delete.png' height='15' title='{$lang['dispenses-deletesale']}' /></a></td></tr>
				";
			
			}

		    $startIndex++; 
		    $p++;
	}


?>
	 </tbody>
	 </table>

	 	 <div  class="actionbox-npr" id = "dialog-3" title = "Sales">
			
			<div class='boxcomtemt'>
				<p>Export excel between time ranges</p><br>
				<input type="text" id="exceldatepicker" name="fromDate" autocomplete="nope" class="sixDigit defaultinput" placeholder="<?php echo $lang['from-date'] ?>" />
				 <input type="text" id="exceldatepicker2" name="untilDate" autocomplete="nope" class="sixDigit defaultinput" placeholder="<?php echo $lang['to-date'] ?>"/>
 				<button class='cta1' id="fullList">Ok</button>
 			
			</div>
		</div> 
<?php 
 displayFooter(); ?>
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
	    var url = 'bar-sales-report.php?fromDate='+fromDate+'&untilDate='+untilDate+'&count=0&totalCount=0';
	    window.open(url, "Bar sales Report","height=300,width=300,modal=yes,alwaysRaised=yes");
	    setTimeout(function () {
	        $("#load").hide();
	    }, 2000);    
	 });


	/*function loadExcel(){
	    $("#load").show();
	    var filter = "<?php echo $_POST['filter'] ?>";
	    var fromDate = "<?php echo $_POST['fromDate'] ?>";
	    var untilDate = "<?php echo $_POST['untilDate'] ?>";
	    var submitted = "<?php echo $_POST['submitted'] ?>";
	    var cashBox = "<?php echo implode(',', $_POST['cashBox']); ?>";
	    window.location.href = "bar-sales-report.php?filter="+filter+'&fromDate='+fromDate+'&untilDate='+untilDate+'&submitted='+submitted+'&cashBox='+cashBox; 
	    setTimeout(function () {
	        $("#load").hide();
	    }, 2000);     
	 }*/
</script>	