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
	
	$closingtime = $_SESSION['closingtime'];
	$closingid = $_SESSION['closingid'];
	$responsible = $_SESSION['user_id'];
	
	// Check first! And ask if it's in process
	$checkClosing = "SELECT closingid, disOpened, disOpenedBy, shiftOpenedNo FROM shiftclose ORDER by closingtime DESC LIMIT 1";
		try
		{
			$result = $pdo3->prepare("$checkClosing");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$closingid = $row['closingid'];
		$disOpened = $row['disOpened'];
		$disOpenedBy = $row['disOpenedBy'];
		$dayOpenedNo = $row['shiftOpenedNo'];
		
	if ($disOpened == '2' && (!isset($_GET['redo']))) {
		
		pageStart($lang['start-shift'], NULL, $validationScript, "pcloseday", "step2 dev-align-center", $lang['open-shift-error'], $_SESSION['successMessage'], $_SESSION['errorMessage']);

		echo  <<<EOD
<div id="scriptMsg">
 <div class='error'>
		{$lang['dispensary-opened']}
 </div>
</div>

EOD;
			exit();
	
		} else if ($disOpened == '1' && (!isset($_GET['redo']))) {
			
			// Look up user details
			$userDetails = "SELECT memberno, first_name FROM users WHERE user_id = '{$disOpenedBy}'";
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
	
		$row = $result->fetch();
				$memberno = $row['memberno'];
				$first_name = $row['first_name'];
		
			pageStart($lang['start-shift'], NULL, $validationScript, "pcloseday", "step2 dev-align-center", $lang['open-shift-error'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
		
		echo  <<<EOD
	<div id="scriptMsg">
	 <div class='error'>
			{$lang['dispensary-open-inprogress-1']}$memberno $first_name{$lang['dispensary-open-inprogress-2']}
	 </div>
	</div>

EOD;
			exit();
	
		} else if (isset($_GET['redo'])) {
		
			// If exist (above 0), delete from openingdetails
			if ($dayOpenedNo > 0) {
				
				$query = "DELETE from shiftopendetails WHERE categoryType = 0 AND openingid = '$dayOpenedNo'";
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
			}
						
			$openingtime = date('Y-m-d H:i:s');
			$_SESSION['openingtime'] = $openingtime;
			
			$openingtimeView = date('d-m-Y H:i', strtotime($openingtime . "+$offsetSec seconds"));
			$closingtimeView = date('d-m-Y H:i', strtotime($closingtime . "+$offsetSec seconds"));
	
		}
		
		// Write to DB Opening table: disOpening is in process
		$updateOpening = sprintf("UPDATE shiftclose SET disOpened = '1', disOpenedBy = '%d' WHERE closingid = '%d';",
			$responsible,
			$closingid
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
		



	$confirmLeave = <<<EOD
    $(document).ready(function() {
	    
var userSubmitted=false;

$('#registerForm').submit(function() {
userSubmitted = true;
});

$('#skipCount').click(function() {
userSubmitted = true;
});

window.onbeforeunload = function() {
    if(!userSubmitted)
        return 'Are you sure that you want to leave this page?';
};
  }); // end ready
EOD;

	pageStart($lang['start-shift'], NULL, $confirmLeave, "pcloseday", "step1 dev-align-center", $lang['openday-dis-one'], $_SESSION['successMessage'], $_SESSION['errorMessage']);
	
	echo "<a href='uTil/auto-open-shift-dispensary.php?cid=$closingid' id='skipCount' class='cta1' style='width: 400px;'>{$lang['use-yday-values']}</a>";
	
?>
	
	<form onsubmit='oneClick.disabled = true; return true;' id="registerForm" action="open-shift-dispensary-1.php" method="POST">

<?php
		
	$selectFlower = "SELECT g.flowerid, g.breed2, g.name, p.productid, p.purchaseid, p.purchaseQuantity, p.growType, p.tupperWeight FROM flower g, purchases p WHERE p.category = 1 AND p.productid = g.flowerid AND p.closedAt IS NULL ORDER BY g.name;";
		try
		{
			$resultFlower = $pdo3->prepare("$selectFlower");
			$resultFlower->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	

		
		$i = 0;
		echo "<h5>{$lang['global-flowerscaps']}</h5><div class='productboxwrap'>";
		while ($flower = $resultFlower->fetch()) {
	
	
	// Look up growtype
	$growDetails = "SELECT growtype FROM growtypes WHERE growtypeid = '{$flower['growType']}'";
		try
		{
			$result = $pdo3->prepare("$growDetails");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
		$growtype = $row['growtype'];

		
	$i++;

	$flower_row = sprintf("
		<div class='actionbox-np2'>
	<div class='mainboxheader'>%s %s ({$flower['purchaseid']})</div>
	%s<br />
	<div class='boxcontent'>
	<input type='number' lang='nb' name='dayopenProduct[%d][weight]' class='fourDigit defaultinput' placeholder='g' step='0.01' required /><br />
  	   <input type='hidden' name='dayopenProduct[%d][name]' value='%s' />
  	   <input type='hidden' name='dayopenProduct[%d][breed2]' value='%s' />
  	   <input type='hidden' name='dayopenProduct[%d][category]' value='1' />
  	   <input type='hidden' name='dayopenProduct[%d][productid]' value='%d' />
  	   <input type='hidden' name='dayopenProduct[%d][purchaseid]' value='%d' />
  	   <input type='hidden' name='dayopenProduct[%d][growtype]' value='%s' />
  	   <input type='hidden' name='dayopenProduct[%d][breed2]' value='%s' />
  	   <input type='hidden' name='dayopenProduct[%d][tupperWeight]' value='%f' /></div></div>",
	  $flower['name'], $flower['breed2'], $growtype, $i, $i, $flower['name'], $i, $flower['breed2'], $i, $i, $flower['productid'], $i, $flower['purchaseid'], $i, $growtype, $i, $flower['breed2'], $i, $flower['tupperWeight']
	  );
	  echo $flower_row;
  }
  
  // AND NOW THE H TABLE:
  	$selectExtract = "SELECT h.extractid, h.name, p.productid, p.purchaseid, p.purchaseQuantity, p.tupperWeight FROM extract h, purchases p WHERE p.category = 2 AND p.productid = h.extractid AND p.closedAt IS NULL ORDER BY h.name;";
		try
		{
			$resultExtract = $pdo3->prepare("$selectExtract");
			$resultExtract->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		echo "</div><h5>{$lang['global-extractscaps']}</h5><div class='productboxwrap'>";
		while ($extract = $resultExtract->fetch()) {
	$i++;

	$extract_row =	sprintf("
		<div class='actionbox-np2'>
	<div class='mainboxheader'>%s ({$extract['purchaseid']})</div>
	<div class='boxcontent'>
	   <input type='number' lang='nb' name='dayopenProduct[%d][weight]' class='fourDigit defaultinput' placeholder='g' step='0.01' required /><br />
  	   <input type='hidden' name='dayopenProduct[%d][name]' value='%s' />
  	   <input type='hidden' name='dayopenProduct[%d][category]' value='2' />
  	   <input type='hidden' name='dayopenProduct[%d][productid]' value='%d' />
  	   <input type='hidden' name='dayopenProduct[%d][purchaseid]' value='%d' />  	   
  	   <input type='hidden' name='dayopenProduct[%d][tupperWeight]' value='%f' /></div></div>",
	  $extract['name'], $i, $i, $extract['name'], $i, $i, $extract['productid'], $i, $extract['purchaseid'], $i, $extract['tupperWeight']
	  );
	  echo $extract_row;
  }
  
  	// AND NOW OTHER CATEGORIES (GRAM ONLY):
	// First look up categories. For each cat, type 1, run header, then look up products.
	$selectCats = "SELECT DISTINCT category FROM purchases WHERE category > 2 AND closedAt IS NULL";
		try
		{
			$resultCats = $pdo3->prepare("$selectCats");
			$resultCats->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($cat = $resultCats->fetch()) {
		
		$category = $cat['category'];
		
		$checkCat = "SELECT name, type FROM categories WHERE id = $category";
		try
		{
			$result = $pdo3->prepare("$checkCat");
			$result->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		$row = $result->fetch();
			$type = $row['type'];
			$name = $row['name'];
		
		if ($type == 1) {
			
			$catArray[] = $category;
			
			echo "</div><h5>$name</h5><div class='productboxwrap'>";
			
		  	$selectOther = "SELECT h.productid, h.name, p.purchaseid, p.purchaseQuantity, p.category, tupperWeight FROM products h, purchases p WHERE p.category = $category AND p.productid = h.productid AND p.closedAt IS NULL ORDER BY h.name ASC;";
		try
		{
			$resultExtract = $pdo3->prepare("$selectOther");
			$resultExtract->execute();
		}
		catch (PDOException $e)
		{
				$error = 'Error fetching user: ' . $e->getMessage();
				echo $error;
				exit();
		}
	
		while ($extract = $resultExtract->fetch()) {
	
				$i++;
	
				$extract_row =	sprintf("
		<div class='actionbox-np2'>
	<div class='mainboxheader'>%s ({$extract['purchaseid']})</div>
	<div class='boxcontent'>
	   <input type='number' lang='nb' name='dayopenProduct[%d][weight]' class='fourDigit defaultinput' placeholder='g' step='0.01' required /><br />
  	   <input type='hidden' name='dayopenProduct[%d][name]' value='%s' />
  	   <input type='hidden' name='dayopenProduct[%d][category]' value='%d' />
  	   <input type='hidden' name='dayopenProduct[%d][productid]' value='%d' />
  	   <input type='hidden' name='dayopenProduct[%d][purchaseid]' value='%d' />  	   
  	   <input type='hidden' name='dayopenProduct[%d][tupperWeight]' value='%f' /></div></div>",
	  $extract['name'], $i, $i, $extract['name'], $i, $category, $i, $extract['productid'], $i, $extract['purchaseid'], $i, $extract['tupperWeight']
	  );
			  	echo $extract_row;
	  
  			}
		}
	}

	foreach($catArray as $value) {
		echo '<input type="hidden" name="catArray[]" value="'. $value. '">';
	}
?>
  
 </div>

	 <input type='hidden' name='closingConfirm' value='yes' /><br />
 <button class='cta1' name='oneClick' type="submit"><?php echo $lang['global-save']; ?></button>
</form>



<?php displayFooter(); ?>
